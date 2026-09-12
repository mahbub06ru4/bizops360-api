# Production image for Render — php-fpm + nginx behind one process (supervisord).
# CONTAINER_ROLE (env, set per Render service) selects what actually runs:
#   web    -> nginx + php-fpm, serving HTTP on $PORT
#   worker -> `php artisan queue:work`, no HTTP server
FROM composer:2.8 AS vendor

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install \
        --no-dev \
        --no-interaction \
        --no-progress \
        --no-scripts \
        --optimize-autoloader \
        --prefer-dist

FROM php:8.4-fpm-alpine AS app

RUN apk add --no-cache \
        nginx \
        supervisor \
        postgresql-dev \
        libzip-dev \
        icu-dev \
        oniguruma-dev \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && docker-php-ext-install \
        pdo_pgsql \
        pgsql \
        bcmath \
        intl \
        opcache \
        zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

RUN { \
        echo 'opcache.enable=1'; \
        echo 'opcache.validate_timestamps=0'; \
        echo 'opcache.max_accelerated_files=20000'; \
        echo 'opcache.memory_consumption=192'; \
    } > /usr/local/etc/php/conf.d/opcache-prod.ini

WORKDIR /var/www/html

COPY --from=vendor /app/vendor ./vendor
COPY . .
RUN cp .env.example .env

RUN mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# The vendor stage installed with --no-scripts (no artisan there yet). Any
# bootstrap/cache/*.php copied in from the host (a dev machine's Sail cache)
# would list dev-only providers (laravel/pail) that don't exist in this
# --no-dev image and crash artisan before it even gets to regenerate it —
# so wipe it first, then rebuild clean for this exact (no-dev) install.
RUN rm -f bootstrap/cache/*.php \
    && php artisan package:discover --ansi

COPY docker/production/nginx.conf.template /etc/nginx/http.d/default.conf.template
COPY docker/production/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/production/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENV CONTAINER_ROLE=web
EXPOSE 8080

ENTRYPOINT ["/entrypoint.sh"]
