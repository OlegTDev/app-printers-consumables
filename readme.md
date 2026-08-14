# Система учета расходных материалов

Сервис позволяет вести учет принтеров и расходных материалов. Учитываются поступившие и установленные расходные материалы, принтеры на рабочих местах. Выгрузка отчетов о принтерах установленных на местах, остатков расходных материалов, количества установленных расходных материалов.

<img width="1918" height="957" alt="Main" src="https://github.com/user-attachments/assets/5a9569e2-225c-420b-a2ac-4dc5d11b9b7d" />


### 🛠️ Технологический стек
![PHP](https://shields.io)
![Laravel](https://shields.io)
![PostgreSQL](https://shields.io)
![Docker](https://shields.io)
![Vite](https://shields.io)
![Vue.js](https://shields.io)


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

Собрать скрипт js:

```sh
npm run build
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
# основные (производители, роли, пользователь админ)
php artisan db:seed

# дополнительные, фейковые данные, для демонстрации
php artisan db:seed --class=DatabaseAdditionalSeeder 
```

Запустить встроенный веб-сервер:

```sh
php artisan serve

```

Все готово! Откройте браузер, перейдите по ссылке:
`http://localhost:XXXX`, если запущен через `php artisan`, то скорее всего `8000`, если через `docker`, то `8080` (по умолчанию в `env.docker`)

- **Имя пользователя:** admin
- **Пароль:** secret



## Работа через Docker

Файл конфигурации:

```sh
cp .env.docker.example .env.docker
```

Запустить контейнеры:
```sh
# через Makefile
make docker-up

# или
docker compose --env-file .env --env-file .env.docker up -d
```

Команды docker, которые могут пригодиться:
```sh
# просмотр логов (realtime)
docker compose --env-file .env --env-file .env.docker logs -f

# подключение к php-fpm контейнеру
make docker-exec
или
docker compose --env-file .env --env-file .env.docker exec php-fpm sh

# создание и подключение к php-сli контейнеру
make docker-run-cli
или
docker compose --env-file .env --env-file .env.docker run --rm php-cli sh
```


## Запуск тестов

```sh
php artisan test
```


### Обновление моделей для IDE

```sh
php artisan ide-helper:models -W
```
