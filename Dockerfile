FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --prefer-dist --no-interaction --no-progress --optimize-autoloader

FROM node:20-alpine AS assets

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY vite.config.js ./
COPY resources ./resources
COPY public ./public
RUN npm run build

FROM php:8.3-cli-alpine

WORKDIR /var/www/html

RUN docker-php-ext-install pdo_mysql

COPY . ./
COPY --from=vendor /app/vendor ./vendor
COPY --from=assets /app/public/build ./public/build

RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chmod -R ug+rw storage bootstrap/cache \
    && chmod +x /var/www/html/docker/entrypoint.sh

EXPOSE 8000

ENTRYPOINT ["/var/www/html/docker/entrypoint.sh"]
