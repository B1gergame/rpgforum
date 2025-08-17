# Бекфилл пользовательских акторов

Команда `php artisan actors:backfill-basic` создаёт акторов вида `player_character` для всех пользователей, у которых их ещё нет. Каждому пользователю назначается роль `owner` через таблицу `actor_memberships`.

## Использование

```bash
php artisan actors:backfill-basic --dry-run  # просмотр изменений без записи в БД
php artisan actors:backfill-basic            # фактическое создание
```

Команда повторяемая: при повторном запуске существующие записи не дублируются.
