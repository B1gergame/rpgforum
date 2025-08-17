<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ActorsBackfillBasic extends Command
{
    protected $signature = 'actors:backfill-basic {--dry-run}';
    protected $description = 'Создать player_character-актора каждому пользователю и назначить owner-доступ';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');
        $this->info('Запуск бекфилла акторов (player_character)'.($dry?' [DRY-RUN]':''));

        // Проверка наличия нужных таблиц
        foreach (['users','actors','actor_memberships'] as $tbl) {
            if (!DB::getSchemaBuilder()->hasTable($tbl)) {
                $this->error("Нет таблицы: {$tbl}");
                return self::FAILURE;
            }
        }

        $now = now();

        // Пройдёмся по всем пользователям
        $users = DB::table('users')->select('id','name')->orderBy('id')->get();
        $created = 0; $granted = 0;

        foreach ($users as $u) {
            // Есть ли уже actor, которым владеет пользователь
            $actor = DB::table('actors')
                ->where('owner_user_id', $u->id)
                ->where('kind', 'player_character')
                ->first();

            if (!$actor) {
                $name = $u->name ?: ('User#'.$u->id);
                if (!$dry) {
                    $actorId = DB::table('actors')->insertGetId([
                        'kind' => 'player_character',
                        'name' => $name,
                        'avatar_url' => null,
                        'owner_user_id' => $u->id,
                        'system_code' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
                $created++;
            } else {
                $actorId = $actor->id;
            }

            // Выдать owner-доступ через actor_memberships (на случай будущих проверок по membership)
            $hasMembership = DB::table('actor_memberships')
                ->where('actor_id', $actorId)
                ->where('user_id', $u->id)
                ->exists();

            if (!$hasMembership) {
                if (!$dry) {
                    DB::table('actor_memberships')->insert([
                        'actor_id' => $actorId,
                        'user_id' => $u->id,
                        'role' => 'owner',
                        'granted_by' => $u->id,
                        'created_at' => $now,
                    ]);
                }
                $granted++;
            }
        }

        $this->info("Создано новых акторов: {$created}. Выдано owner-доступов: {$granted}.");
        $this->info('Готово.');
        return self::SUCCESS;
    }
}
