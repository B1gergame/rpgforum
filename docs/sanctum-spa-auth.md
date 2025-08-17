# SPA-авторизация через Sanctum

Для фронтенда на отдельном домене используется cookie-based авторизация Laravel Sanctum.

## Настройка окружения
- `SESSION_DRIVER=cookie`
- `SESSION_DOMAIN=localhost`
- `SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1,spa.localhost`

## CORS
Файл `config/cors.php` разрешает запросы к `api/*`, `sanctum/csrf-cookie`, `login`, `logout` и включает передачу cookies.

## Маршруты
- `GET /sanctum/csrf-cookie` — получение CSRF-cookie.
- `POST /login` — логин по email и паролю.
- `POST /logout` — выход из сессии.
- `POST /api/posts` — создание поста (middleware `auth:sanctum`, `throttle:posts`).

## Rate limiting
Лимит `posts` разрешает до 30 запросов в минуту на пользователя.

## Пример cURL
```bash
# 1) Получить CSRF-cookie
curl -i -c cookies.txt http://localhost:8000/sanctum/csrf-cookie

# 2) Логин
TOKEN=$(awk '/XSRF-TOKEN/ {print $7}' cookies.txt)
curl -i -b cookies.txt -c cookies.txt -X POST http://localhost:8000/login \
  -H "Content-Type: application/json" \
  -H "X-XSRF-TOKEN: $TOKEN" \
  -d '{"email":"player@example.com","password":"secret"}'

# 3) Авторизованный POST
TOKEN=$(awk '/XSRF-TOKEN/ {print $7}' cookies.txt)
curl -i -b cookies.txt -X POST http://localhost:8000/api/posts \
  -H "Content-Type: application/json" \
  -H "X-XSRF-TOKEN: $TOKEN" \
  -d '{"scene_id":1,"actor_id":123,"content":"Пост через Sanctum"}'
```
