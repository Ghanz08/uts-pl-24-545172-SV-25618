#!/bin/sh
set -e

if [ -z "$APP_KEY" ]; then
    echo "APP_KEY belum diset. Isi di file .env (lihat .env.example)." >&2
    exit 1
fi

php artisan config:cache
php artisan route:cache

# Compose waits for the db healthcheck, so migrations can run right away.
php artisan migrate --force
# Idempotent: only creates the demo account (and sample ideas) when it doesn't exist yet.
php artisan db:seed --force

exec "$@"
