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
    && docker-php-ext-install -j$(nproc) pdo pdo_pgsql pgsql ldap gd zip opcache \
    && apk del .build-deps

ARG USER_ID=1000
ARG GROUP_ID=1000

RUN addgroup -g ${GROUP_ID} app && \
    adduser -u ${USER_ID} -G app -s /bin/sh -D app

WORKDIR /app
RUN chown app:app /app

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

EXPOSE 9000


# DEV
FROM base AS dev

RUN mv $PHP_INI_DIR/php.ini-development $PHP_INI_DIR/php.ini

USER app


# PROD
FROM base AS prod

RUN mv $PHP_INI_DIR/php.ini-production $PHP_INI_DIR/php.ini

RUN { \
        echo 'opcache.enable=1'; \
        echo 'opcache.enable_cli=1'; \
        echo 'opcache.memory_consumption=128'; \
        echo 'opcache.interned_strings_buffer=8'; \
        echo 'opcache.max_accelerated_files=10000'; \
        # Идеально для контейнеров (PHP не будет тратить время на проверку изменений файлов):
        echo 'opcache.validate_timestamps=0'; \
        echo 'opcache.fast_shutdown=1'; \
    } > /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini


USER app
