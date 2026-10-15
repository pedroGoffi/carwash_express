#!/usr/bin/env sh
set -eu
cd /var/www/html
mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

if [ "${APP_OPTIMIZE:-true}" = "true" ]; then
  php artisan optimize:clear >/dev/null 2>&1 || true
  php artisan config:cache
  php artisan route:cache
  php artisan view:cache
fi

exec "$@"
