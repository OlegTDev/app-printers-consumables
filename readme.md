# Система учета расходных материалов

Сервис 

![](https://raw.githubusercontent.com/inertiajs/pingcrm/master/screenshot.png)

## Установка

Клонировать репозиторий:

```sh
git clone https://github.com/OlegTDev/app-printers-consumables.git app-printers-consumables
cd app-printers-consumables
```

Установить PHP зависимости:

```sh
composer install
```

Установить NPM зависимости:

```sh
npm ci
```

Build assets:

```sh
npm run dev
```

Настроить файл конфигурации:

```sh
cp .env.example .env
```

Сгенерировать application key:

```sh
php artisan key:generate
```

Создать БД PostgreSQL. Настроить подключение в файле `.env`.

Запустить миграции:

```sh
php artisan migrate
```

Запустить database seeder:

```sh
php artisan db:seed
```

Запустить встроенный веб-сервер:

```sh
php artisan serve
```

You're ready to go! Visit Ping CRM in your browser, and login with:

- **Username:** admin@example.com
- **Password:** secret


## Running tests

To run the Ping CRM tests, run:

```
phpunit
```
