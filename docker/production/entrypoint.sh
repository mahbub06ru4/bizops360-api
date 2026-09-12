#!/bin/sh
set -e

# Config is cached from whatever env vars are present at container start, so it
# must happen here (runtime), not at image build time.
php artisan config:cache
php artisan route:cache
php artisan event:cache

if [ "$CONTAINER_ROLE" = "web" ]; then
    PORT="${PORT:-8080}"
    sed "s/__PORT__/${PORT}/" /etc/nginx/http.d/default.conf.template > /etc/nginx/http.d/default.conf

    # Render's health check (GET /up) hits this before the first deploy is
    # marked live, so migrations must be applied before nginx starts serving.
    php artisan migrate --force

    exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
fi

if [ "$CONTAINER_ROLE" = "worker" ]; then
    exec php artisan queue:work --tries=3 --max-time=3600 --sleep=1
fi

echo "Unknown CONTAINER_ROLE '${CONTAINER_ROLE}' — set it to 'web' or 'worker'." >&2
exit 1
