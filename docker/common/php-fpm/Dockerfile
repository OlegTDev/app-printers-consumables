ARG PHP_FPM_VERSION=8.3

FROM php:${PHP_FPM_VERSION}-fpm-alpine AS base

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

ARG USER_ID=1000
ARG GROUP_ID=1000
RUN addgroup -g ${GROUP_ID} app && \
    adduser -u ${USER_ID} -G app -s /bin/sh -D app

USER app

WORKDIR /app

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

EXPOSE 9000



FROM base AS dev
RUN mv $PHP_INI_DIR/php.ini-development $PHP_INI_DIR/php.ini



FROM base AS prod
RUN mv $PHP_INI_DIR/php.ini-production $PHP_INI_DIR/php.ini
