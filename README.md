# Career

Приложение на Laravel 12 (PHP 8.2+): PostgreSQL, Redis, Vite, очереди, тесты на Pest.

## Требования

- PHP 8.2+
- Composer
- Node.js и npm
- Docker и Docker Compose (для Sail или продакшн-деплоя)
- PostgreSQL 18 (через Sail) или свой инстанс

## Установка и запуск

### Локально (без Docker)

```bash
composer install
cp .env.example .env
php artisan key:generate
# Настройте .env: DB_*, REDIS_*, MAIL_* и т.д.
php artisan migrate
npm install
npm run build
```

Запуск в режиме разработки (сервер, очереди, логи, Vite):

```bash
composer run dev
```

Или по отдельности:

```bash
php artisan serve
php artisan queue:listen --tries=1 --timeout=0
npm run dev
```

### С Docker (Laravel Sail)

```bash
cp .env.example .env
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate
./vendor/bin/sail npm install
./vendor/bin/sail npm run build
```

Приложение: `http://localhost` (порт из `APP_PORT` в `.env`), Vite — порт из `VITE_PORT`.  
Mailpit: порт 8025 (веб-интерфейс для писем).

### Полная установка «с нуля»

```bash
composer run setup
```

Выполняет: `composer install`, копирование `.env.example` → `.env`, `key:generate`, миграции, `npm install`, `npm run build`.

## Тесты

```bash
composer run test
```

Или: `php artisan test`. Используется Pest.

## Деплой (Docker)

Инструкции по развёртыванию на сервере — в [deploy/DEPLOY.md](deploy/DEPLOY.md).

Кратко:

1. Установить Docker и Docker Compose на сервере (Ubuntu/Debian).
2. Клонировать репозиторий в `/var/www/career`.
3. В `deploy/` скопировать `.env.example.production` в `.env`, задать `APP_KEY`, `APP_URL`, `DB_PASSWORD`.
4. Запустить: `docker compose -f docker-compose.prod.yml up -d --build`.
5. Выполнить миграции и кэширование: `migrate --force`, `config:cache`, `route:cache`, `view:cache`.

## Структура окружения

Основные переменные в `.env`:

- `APP_*` — имя, ключ, URL, порт, локаль.
- `DB_*` — подключение к PostgreSQL.
- `REDIS_*` — Redis (очереди/кэш).
- Порт приложения: `APP_PORT`, порт Vite: `VITE_PORT`.

Для продакшена используется отдельный `.env` в каталоге `deploy/`.
