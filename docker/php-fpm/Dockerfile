FROM php:8.3-fpm-alpine

# Устанавливаем необходимые расширения
RUN apk add --no-cache \
    git \
    curl \
    unzip \
    libpq-dev && \
    docker-php-ext-install pdo pdo_pgsql pgsql

RUN mv $PHP_INI_DIR/php.ini-development $PHP_INI_DIR/php.ini

WORKDIR /app

EXPOSE 9000