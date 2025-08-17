# RPG Forum

## Описание

RPG Forum — веб‑платформа для обсуждения ролевых игр. Проект использует стек Laravel и Inertia для объединения серверной части на PHP и современного фронтенда на React.

## Технологии

- PHP 8.2 / [Laravel 12](https://laravel.com)
- [Inertia](https://inertiajs.com) + [React 19](https://react.dev)
- [Tailwind CSS 4](https://tailwindcss.com)
- [Vite 7](https://vitejs.dev) для сборки фронтенда
- [Pest](https://pestphp.com) для тестирования
- ESLint и Prettier для статического анализа

## Требования к окружению

- PHP >= 8.2
- Composer >= 2
- Node.js >= 22 и npm >= 10
- SQLite (по умолчанию) или другая поддерживаемая СУБД

## Развертывание

1. Установить зависимости:
   ```bash
   composer install
   npm install
   ```
2. Скопировать файл окружения и сгенерировать ключ приложения:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
3. Настроить подключение к БД в `.env` и выполнить миграции:
   ```bash
   php artisan migrate
   ```
4. Засеять системных акторов:
   ```bash
   php artisan db:seed --class=ActorSystemSeeder
   ```
5. Запустить dev‑серверы:
   ```bash
   composer dev         # сервер Laravel, очередь и Vite
   # или отдельно
   php artisan serve    # сервер Laravel
   npm run dev          # Vite
   ```

## Команды разработки и тестирования

- `composer dev` — запуск локального окружения.
- `composer test` — запуск тестов Pest.
- `npm run lint` — проверка ESLint.
- `npm run format` — автоматическое форматирование.
- `npm run format:check` — проверка форматирования.
- `npm run build` — production‑сборка фронтенда.
- `npm run dev` — дев‑сервер Vite.

При запуске тестов заглушка Vite‑манифеста создаётся автоматически: скрипт `tests/bootstrap.php` генерирует файл `public/build/manifest.json` с фиктивными путями к ассетам, чтобы Blade‑шаблоны не падали в тестовой среде.

## Системные акторы

Миграция `create_actors_core` добавляет таблицы `actors`, `actor_memberships` и `scene_actors`, а также поля `actor_id` и `posted_by_user_id` в таблицу `posts`.

Для создания записей `Narrator`, `Dice`, `ModBot` и «Нейтрального персонажа» выполните сидер `ActorSystemSeeder` (см. шаг развертывания выше).

Имя нейтрального персонажа можно переопределить переменной окружения `APP_NEUTRAL_ACTOR_NAME`.

