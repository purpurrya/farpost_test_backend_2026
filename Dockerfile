FROM php:8.3-fpm-alpine

ENV APP_ENV=prod
ENV APP_DEBUG=0
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV DEFAULT_URI=http://127.0.0.1:8080

RUN apk add --no-cache \
    icu-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-install -j$(nproc) intl pdo_mysql zip opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY docker/php/docker-entrypoint.sh /usr/local/bin/app-entrypoint.sh
RUN sed -i 's/\r$//' /usr/local/bin/app-entrypoint.sh && chmod +x /usr/local/bin/app-entrypoint.sh

WORKDIR /var/www/html

COPY . .

RUN git config --global --add safe.directory /var/www/html \
    && composer install --no-dev --no-interaction --prefer-dist --no-progress --no-scripts \
    && php bin/console cache:clear --env=prod

RUN mkdir -p var/cache var/log && chown -R www-data:www-data var

ENTRYPOINT ["/usr/local/bin/app-entrypoint.sh"]
CMD ["php-fpm"]
