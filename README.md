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

## Логика страницы «Карьера»

Страница доступна по маршруту главной (`/`) и показывает вакансии с HH.RU по одной специализации.

**Источники данных**

- **Специализация** — задаётся в конфиге (`config/career.php`, `CAREER_VACANCY_SPECIALIZATION`), по умолчанию «Менеджер». Пользователь её не меняет.
- **Регион** — либо из профиля пользователя (поле «Регион» в личных данных, сохраняется как `hh_region_id`), либо выбран вручную на странице через подсказки.

**Поведение**

1. При открытии страницы в поле «Регион» подставляется регион из профиля (если есть). Если `hh_region_id` уже задан — автоматически выполняется запрос вакансий.
2. Пользователь может ввести в поле «Регион» текст; после 2+ символов и паузы 300 мс идёт запрос к API подсказок регионов HH.RU (`/api/areas/suggest?text=...`). При выборе региона из списка подсказок сохраняется его ID и сразу запрашиваются вакансии.
3. Кнопка «Загрузить» запрашивает вакансии с учётом текущего выбранного региона (или региона из профиля, если поле не меняли).
4. Вакансии отдаёт endpoint `GET /api/career/vacancies`. Параметр `region_id` опционален: при его отсутствии используется `hh_region_id` авторизованного пользователя. Если регион ни откуда не известен — в ответе приходят пустой список и подсказка: «Для отображения вакансий выберите регион».
5. Бэкенд запрашивает у HH.RU вакансии по специализации из конфига и выбранному региону; возвращаются первые N штук (N задаётся в `config/career.php`, `CAREER_VACANCY_COUNT`, по умолчанию 6).
6. Результаты запроса к HH.RU кэшируются (Redis при наличии, иначе file/database). Время жизни кэша задаётся в конфиге (`cache_ttl_minutes`, по умолчанию 20 минут). Ключ кэша учитывает специализацию и `region_id`.

**Конфигурация** (`config/career.php`, переменные в `.env`):

- `CAREER_VACANCY_SPECIALIZATION` — строка поиска по вакансиям (по умолчанию «менеджер»).
- `CAREER_VACANCY_COUNT` — сколько вакансий показывать (по умолчанию 6).
- `CAREER_CACHE_TTL_MINUTES` — TTL кэша ответов HH.RU в минутах (по умолчанию 20).

## Структура окружения

Основные переменные в `.env`:

- `APP_*` — имя, ключ, URL, порт, локаль.
- `DB_*` — подключение к PostgreSQL.
- `REDIS_*` — Redis (очереди/кэш).
- `CAREER_VACANCY_SPECIALIZATION`, `CAREER_VACANCY_COUNT`, `CAREER_CACHE_TTL_MINUTES` — страница «Карьера» (см. выше).
- Порт приложения: `APP_PORT`, порт Vite: `VITE_PORT`.

Для продакшена используется отдельный `.env` в каталоге `deploy/`.
