FROM php:8.3-cli-alpine

# Принимаем переменные (по умолчанию 1000)
ARG USER_ID=1000
ARG GROUP_ID=1000

# Устанавливаем CLI‑полезности
RUN apk add --no-cache \
git curl unzip libpq-dev openldap-dev

RUN mv $PHP_INI_DIR/php.ini-development $PHP_INI_DIR/php.ini

RUN docker-php-ext-install pdo pdo_pgsql pgsql ldap

# Устанавливаем Composer
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

RUN addgroup -g ${GROUP_ID} app && \
    adduser -u ${USER_ID} -G app -s /bin/sh -D app

# Рабочий каталог
WORKDIR /app

USER app
