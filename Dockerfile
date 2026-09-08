FROM node:24-alpine AS frontend

WORKDIR /app

ARG VITE_REVERB_APP_KEY=blog-local-key
ARG VITE_REVERB_HOST=localhost
ARG VITE_REVERB_PORT=8081
ARG VITE_REVERB_SCHEME=http
ARG VITE_CKEDITOR_LICENSE_KEY=GPL

ENV VITE_REVERB_APP_KEY=$VITE_REVERB_APP_KEY \
    VITE_REVERB_HOST=$VITE_REVERB_HOST \
    VITE_REVERB_PORT=$VITE_REVERB_PORT \
    VITE_REVERB_SCHEME=$VITE_REVERB_SCHEME \
    VITE_CKEDITOR_LICENSE_KEY=$VITE_CKEDITOR_LICENSE_KEY

COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund

COPY vite.config.js ./
COPY resources ./resources
COPY Modules ./Modules
RUN npm run build

FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./
COPY Modules ./Modules
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --optimize-autoloader \
    --ignore-platform-req=ext-exif \
    --ignore-platform-req=ext-pcntl \
    --ignore-platform-req=ext-posix \
    --no-scripts

FROM php:8.4-fpm-alpine

RUN apk add --no-cache \
        fcgi \
        freetype \
        icu-libs \
        libjpeg-turbo \
        libpng \
        libzip \
    && apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        freetype-dev \
        icu-dev \
        libjpeg-turbo-dev \
        libpng-dev \
        libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) bcmath exif gd intl opcache pcntl pdo_mysql zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

WORKDIR /var/www/html

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY . .
COPY --from=vendor /app/vendor ./vendor
COPY --from=frontend /app/public/build ./public/build

RUN mkdir -p storage/framework/{cache,sessions,testing,views} storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

USER www-data

EXPOSE 9000

CMD ["php-fpm"]
