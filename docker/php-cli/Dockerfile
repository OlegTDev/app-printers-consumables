FROM php:8.3-cli-alpine

# Устанавливаем CLI‑полезности
RUN apk add --no-cache \
    git curl unzip libpq-dev openldap-dev

RUN docker-php-ext-install pdo pdo_pgsql pgsql ldap

# Устанавливаем Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Рабочий каталог
WORKDIR /app
