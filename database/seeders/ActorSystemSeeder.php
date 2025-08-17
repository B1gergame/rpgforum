<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ActorSystemSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $ensure = function (array $attrs) use ($now) {
            $existing = DB::table('actors')
                ->where('kind', $attrs['kind'])
                ->where('system_code', $attrs['system_code'] ?? null)
                ->where('name', $attrs['name'])
                ->first();

            if (!$existing) {
                DB::table('actors')->insert([
                    'kind'        => $attrs['kind'],
                    'name'        => $attrs['name'],
                    'avatar_url'  => $attrs['avatar_url'] ?? null,
                    'owner_user_id' => $attrs['owner_user_id'] ?? null,
                    'system_code' => $attrs['system_code'] ?? null,
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ]);
            }
        };

        // System actors (invisible to players in UI)
        $ensure(['kind'=>'system','name'=>'Narrator','system_code'=>'narrator']);
        $ensure(['kind'=>'system','name'=>'Dice','system_code'=>'dice']);
        $ensure(['kind'=>'system','name'=>'ModBot','system_code'=>'modbot']);

        // Neutral character (for GM posting)
        $ensure(['kind'=>'gm_character','name'=> config('app.neutral_actor_name', 'Нейтральный персонаж')]);
    }
}
