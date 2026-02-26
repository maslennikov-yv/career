#!/usr/bin/env sh
set -eu

mkdir -p /var/www/html/storage /var/www/html/bootstrap/cache
mkdir -p \
  /var/www/html/storage/app \
  /var/www/html/storage/framework/cache \
  /var/www/html/storage/framework/sessions \
  /var/www/html/storage/framework/views \
  /var/www/html/storage/logs

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache || true

exec "$@"
