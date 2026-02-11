FROM php:8.3-fpm-alpine

# Устанавливаем необходимые расширения
RUN apk add --no-cache \
    curl unzip libpq-dev openldap-dev

RUN docker-php-ext-install pdo pdo_pgsql pgsql ldap

RUN mv $PHP_INI_DIR/php.ini-development $PHP_INI_DIR/php.ini

WORKDIR /app

EXPOSE 9000
