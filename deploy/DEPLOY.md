# Deploy (Docker) on root@37.46.128.102

## 1) Server: install Docker + Compose (Ubuntu/Debian)

```bash
apt-get update
apt-get install -y ca-certificates curl gnupg

install -m 0755 -d /etc/apt/keyrings
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | gpg --dearmor -o /etc/apt/keyrings/docker.gpg
chmod a+r /etc/apt/keyrings/docker.gpg

echo \
  "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu \
  $(. /etc/os-release && echo \"$VERSION_CODENAME\") stable" \
  > /etc/apt/sources.list.d/docker.list

apt-get update
apt-get install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
```

## 2) Server: get code

Example:

```bash
mkdir -p /var/www
cd /var/www
git clone <your_repo_url> career
cd /var/www/career
```

## 3) Configure environment for Compose + Laravel

```bash
cd /var/www/career/deploy
cp .env.example.production .env
```

Edit `/var/www/career/deploy/.env`:

- Set `APP_KEY` (see command below)
- Set `APP_URL`
- Change `DB_PASSWORD`

Generate `APP_KEY` via container and paste it into `.env`:

```bash
docker compose -f docker-compose.prod.yml run --rm app php -r "echo 'base64:'.base64_encode(random_bytes(32)).PHP_EOL;"
```

## 4) Build + start

```bash
cd /var/www/career/deploy
docker compose -f docker-compose.prod.yml up -d --build
```

## 5) First-time Laravel commands

```bash
cd /var/www/career/deploy
docker compose -f docker-compose.prod.yml exec app php artisan migrate --force
docker compose -f docker-compose.prod.yml exec app php artisan package:discover --ansi
docker compose -f docker-compose.prod.yml exec app php artisan config:cache
docker compose -f docker-compose.prod.yml exec app php artisan route:cache
docker compose -f docker-compose.prod.yml exec app php artisan view:cache
```

## 6) Check

- `http://37.46.128.102/`
- `http://37.46.128.102/up`

