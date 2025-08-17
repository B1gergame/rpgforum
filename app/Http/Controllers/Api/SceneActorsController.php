<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Actor;
use App\Models\Scene;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SceneActorsController extends Controller
{
    // ------------------------ helpers ------------------------

    private function isGmOrStaff(Request $request, Scene $scene): bool
    {
        $user = $request->user();

        // TODO: подставьте вашу логику ролей/ГМ.
        // Здесь примем, что есть методы hasRole() и isGmOfScene().
        $isStaff = method_exists($user, 'hasRole') && $user->hasRole(['admin','moderator']);
        $isGm = method_exists($user, 'isGmOfScene') && $user->isGmOfScene($scene->id);

        return $isStaff || $isGm;
    }

    // ------------------------ queries ------------------------

    /**
     * Вернуть акторов, от кого текущий пользователь МОЖЕТ постить в сцене
     */
    public function available(Request $request, Scene $scene)
    {
        $user = $request->user();

        $q = Actor::query()
            ->select('actors.*', 'scene_actors.visibility', 'scene_actors.can_post')
            ->join('scene_actors', 'scene_actors.actor_id', '=', 'actors.id')
            ->where('scene_actors.scene_id', $scene->id)
            // право постить от лица актора: owner или membership
            ->where(function($q) use ($user) {
                $q->where('actors.owner_user_id', $user->id)
                  ->orWhereIn('actors.id', function($sq) use ($user) {
                      $sq->from('actor_memberships')
                         ->select('actor_id')
                         ->where('user_id', $user->id);
                  });
            })
            ->where('scene_actors.can_post', true);

        // скрыть gm_only для не-ГМ
        $userIsGm = method_exists($user, 'isGmOfScene') && $user->isGmOfScene($scene->id);
        if (!$userIsGm) {
            $q->where('scene_actors.visibility', '!=', 'gm_only');
        }

        $actors = $q->orderByRaw("CASE actors.kind
            WHEN 'player_character' THEN 0
            WHEN 'gm_character' THEN 1
            WHEN 'npc' THEN 2
            ELSE 3 END")
            ->orderBy('actors.name')
            ->get();

        return response()->json([
            'data' => $actors->map(function($a){
                return [
                    'id' => $a->id,
                    'kind' => $a->kind,
                    'name' => $a->name,
                    'avatar_url' => $a->avatar_url,
                    'visibility' => $a->visibility,
                    'can_post' => (bool)$a->can_post,
                ];
            }),
        ]);
    }

    /**
     * Подключить актора к сцене
     */
    public function attach(Request $request, Scene $scene)
    {
        if (!$this->isGmOrStaff($request, $scene)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $data = $request->validate([
            'actor_id' => ['required','integer','exists:actors,id'],
            'visibility' => ['nullable','in:participants,gm_only,public'],
            'can_post' => ['nullable','boolean'],
            'note' => ['nullable','string'],
        ]);

        $exists = DB::table('scene_actors')
            ->where('scene_id', $scene->id)
            ->where('actor_id', $data['actor_id'])
            ->exists();

        if ($exists) {
            return response()->json(['message'=>'Already attached'], 200);
        }

        DB::table('scene_actors')->insert([
            'scene_id' => $scene->id,
            'actor_id' => $data['actor_id'],
            'visibility' => $data['visibility'] ?? 'participants',
            'can_post' => $data['can_post'] ?? true,
            'note' => $data['note'] ?? null,
        ]);

        return response()->json(['message'=>'Attached'], 201);
    }

    /**
     * Обновить настройки актора в сцене
     */
    public function update(Request $request, Scene $scene, Actor $actor)
    {
        if (!$this->isGmOrStaff($request, $scene)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $data = $request->validate([
            'visibility' => ['nullable','in:participants,gm_only,public'],
            'can_post' => ['nullable','boolean'],
            'note' => ['nullable','string'],
        ]);

        $affected = DB::table('scene_actors')
            ->where('scene_id', $scene->id)
            ->where('actor_id', $actor->id)
            ->update(array_filter([
                'visibility' => $data['visibility'] ?? null,
                'can_post' => array_key_exists('can_post', $data) ? (bool)$data['can_post'] : null,
                'note' => $data['note'] ?? null,
            ], fn($v) => $v !== null));

        if (!$affected) {
            return response()->json(['message'=>'Not attached'], 404);
        }

        return response()->json(['message'=>'Updated']);
    }

    /**
     * Отключить актора от сцены
     */
    public function detach(Request $request, Scene $scene, Actor $actor)
    {
        if (!$this->isGmOrStaff($request, $scene)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $deleted = DB::table('scene_actors')
            ->where('scene_id', $scene->id)
            ->where('actor_id', $actor->id)
            ->delete();

        return $deleted
            ? response()->json(['message'=>'Detached'])
            : response()->json(['message'=>'Not attached'], 404);
    }
}
