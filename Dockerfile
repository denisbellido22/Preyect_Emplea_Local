# syntax=docker/dockerfile:1

###############################################################################
# Etapa 1: compilación de los recursos frontend (Vite + Tailwind + Laravel Echo)
###############################################################################
FROM node:22-alpine AS frontend

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY vite.config.js ./
COPY resources ./resources
COPY public ./public

# Las variables VITE_* se incrustan en el bundle durante la compilación.
# La clave debe coincidir con REVERB_APP_KEY y el host/puerto es la dirección
# PÚBLICA que usa el navegador (no la dirección interna del contenedor).
ARG VITE_REVERB_APP_KEY=emplea-local-key
ARG VITE_REVERB_HOST=localhost
ARG VITE_REVERB_PORT=8080
ARG VITE_REVERB_SCHEME=http

ENV VITE_REVERB_APP_KEY=$VITE_REVERB_APP_KEY \
    VITE_REVERB_HOST=$VITE_REVERB_HOST \
    VITE_REVERB_PORT=$VITE_REVERB_PORT \
    VITE_REVERB_SCHEME=$VITE_REVERB_SCHEME

RUN npm run build

###############################################################################
# Etapa 2: aplicación Laravel 12 sobre PHP 8.3 (compatible con composer.json)
###############################################################################
FROM php:8.3-fpm-alpine AS app

# Extensiones requeridas por Laravel, Sanctum, Reverb y SQLite.
RUN apk add --no-cache \
        nginx \
        su-exec \
        curl \
        sqlite-libs \
        libzip \
    && apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        sqlite-dev \
        libzip-dev \
        oniguruma-dev \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_sqlite \
        mbstring \
        bcmath \
        pcntl \
        posix \
        zip \
        opcache \
    && apk del .build-deps

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Dependencias PHP primero para aprovechar la caché de capas.
# Se incluyen las dependencias de desarrollo para poder ejecutar
# "php artisan test" dentro del contenedor (entorno académico).
COPY composer.json composer.lock ./
RUN composer install --prefer-dist --no-interaction --no-scripts --no-autoloader

COPY . .
COPY --from=frontend /app/public/build ./public/build

RUN composer dump-autoload --optimize --no-scripts \
    && mkdir -p \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
        public/uploads/ofertas \
        public/uploads/oportunidades \
        public/uploads/publicidades \
    && chown -R www-data:www-data storage bootstrap/cache public/uploads \
    && chmod -R ug+rwX storage bootstrap/cache public/uploads

COPY docker/php/php.ini /usr/local/etc/php/conf.d/emplea-local.ini
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint

EXPOSE 9000 80 8080

ENTRYPOINT ["entrypoint"]
CMD ["php-fpm"]
