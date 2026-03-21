FROM php:8.3-fpm-alpine

ENV APP_ENV=prod
ENV APP_DEBUG=0
ENV COMPOSER_ALLOW_SUPERUSER=1

RUN apk add --no-cache \
    icu-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-install -j$(nproc) intl pdo_mysql zip opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN git config --global --add safe.directory /var/www/html \
    && composer install --no-dev --no-interaction --prefer-dist --no-progress --no-scripts \
    && php bin/console cache:clear --env=prod

RUN mkdir -p var/cache var/log && chown -R www-data:www-data var

