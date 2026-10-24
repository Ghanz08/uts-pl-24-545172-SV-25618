# Laravel API served by FrankenPHP (Caddy + embedded PHP), replacing `php artisan serve` / PHP-FPM + Nginx.
# Pinned: FrankenPHP 1.13.3 ships PHP 8.4.27, which includes the Oct 2026 RCE fixes (CVE-2026-109618/109619).
FROM dunglas/frankenphp:1.13.3-php8.4-alpine

RUN install-php-extensions pdo_mysql opcache intl zip bcmath pcntl

COPY --from=composer:2.10.3 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Dependencies first: this layer is reused from cache as long as composer.json/lock don't change.
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction --no-progress

# Application code (see .dockerignore for what stays out).
COPY . .
RUN composer dump-autoload --optimize --classmap-authoritative --no-dev \
    && php artisan package:discover --ansi

COPY docker/Caddyfile /etc/frankenphp/Caddyfile
COPY docker/php.ini /usr/local/etc/php/conf.d/zz-app.ini
COPY docker/entrypoint.sh /usr/local/bin/app-entrypoint
RUN chmod +x /usr/local/bin/app-entrypoint

# Run as an unprivileged user; port 8000 needs no extra capabilities.
RUN adduser -D -u 1000 app \
    && chown -R app:app /app/storage /app/bootstrap/cache /data/caddy /config/caddy
USER app

ENV SERVER_NAME=:8000 \
    APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr

EXPOSE 8000

HEALTHCHECK --interval=10s --timeout=3s --start-period=20s --retries=5 \
    CMD wget -qO- http://127.0.0.1:8000/up > /dev/null || exit 1

ENTRYPOINT ["app-entrypoint"]
CMD ["frankenphp", "run", "--config", "/etc/frankenphp/Caddyfile"]
