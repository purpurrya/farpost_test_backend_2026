#!/bin/sh
set -e
cd /var/www/html
if [ ! -f vendor/autoload_runtime.php ]; then
  if [ "${APP_ENV:-dev}" = "prod" ]; then
    composer install --no-dev --no-interaction --prefer-dist
  else
    composer install --no-interaction --prefer-dist
  fi
  mkdir -p var/cache var/log
  chown -R www-data:www-data var 2>/dev/null || true
fi
exec docker-php-entrypoint "$@"
