ARG PHP_FPM_VERSION=8.3

FROM php:${PHP_FPM_VERSION}-fpm-alpine

RUN apk add --no-cache \
        libpng \
        libzip \
        libpq \
        libldap \
    && apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        unzip \
        libpng-dev \
        libzip-dev \
        libpq-dev \
        openldap-dev \
        zlib-dev \
    && docker-php-ext-install -j$(nproc) pdo pdo_pgsql pgsql ldap gd zip \
    && apk del .build-deps

WORKDIR /app

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

EXPOSE 9000
