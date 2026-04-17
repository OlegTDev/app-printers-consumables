include .env.docker

docker-up:
	docker compose --env-file .env --env-file .env.docker up -d

docker-up-build:
	docker compose --env-file .env --env-file .env.docker up -d --build

docker-down:
	docker compose --env-file .env --env-file .env.docker down --remove-orphans

docker-clear:
	docker compose --env-file .env --env-file .env.docker down down -v --remove-orphans

docker-prod-build:
	docker compose -f docker-compose.prod.yml --env-file .env --env-file .env.docker build

docker-prod-up:
	docker compose -f docker-compose.prod.yml --env-file .env --env-file .env.docker up -d

docker-prod-up-build:
	make images-build
	docker compose -f docker-compose.prod.yml --env-file .env --env-file .env.docker up -d --build

docker-prod-down:
	docker compose -f docker-compose.prod.yml --env-file .env --env-file .env.docker down -v --remove-orphans

docker-exec:
	docker compose --env-file .env --env-file .env.docker exec php-fpm sh



#################### IMAGES BEGIN ######################

images-create: php-fpm-create node-create php-cli-create nginx-create postgres-create
images-build: php-fpm-build node-build php-cli-build nginx-build postgres-build

# PHP-FPM
php-fpm-create: php-fpm-build php-fpm-save-image-to-file php-fpm-remove-images

php-fpm-build:
	docker build \
		--build-arg PHP_FPM_VERSION=${PHP_FPM_VERSION} \
		-t app-printers-consumable-php-fpm:${PHP_FPM_TAG} \
		-f ./docker/images/php-fpm/Dockerfile .

php-fpm-save-image-to-file:
	docker save -o ./docker/images/var/app-printers-consumable-php-fpm:${PHP_FPM_TAG}.tar app-printers-consumable-php-fpm:${PHP_FPM_TAG}

php-fpm-remove-images:
	docker rmi -f app-printers-consumable-php-fpm:${PHP_FPM_TAG}

# PHP-CLI
php-cli-create: php-cli-build php-cli-save-image-to-file php-cli-remove-images

php-cli-build:
	docker build \
		--build-arg PHP_CLI_VERSION=${PHP_CLI_VERSION} \
		-t app-printers-consumable-php-cli:${PHP_CLI_TAG} \
		-f ./docker/images/php-cli/Dockerfile .

php-cli-save-image-to-file:
	docker save -o ./docker/images/var/app-printers-consumable-php-cli:${PHP_CLI_TAG}.tar app-printers-consumable-php-cli:${PHP_CLI_TAG}

php-cli-remove-images:
	docker rmi -f app-printers-consumable-php-cli:${PHP_CLI_TAG}


# NODE
node-create: node-build node-save-image-to-file node-remove-images

node-build:
	docker build \
		--build-arg NODE_VERSION=${NODE_VERSION} \
		-t app-printers-consumable-node:${NODE_TAG} \
		-f ./docker/images/node/Dockerfile .

node-save-image-to-file:
	docker save -o ./docker/images/var/app-printers-consumable-node:${NODE_TAG}.tar app-printers-consumable-node:${NODE_TAG}

node-remove-images:
	docker rmi -f app-printers-consumable-node:${NODE_TAG}

# NGINX
nginx-create: nginx-build nginx-save-image-to-file nginx-remove-images

nginx-build:
	docker build \
		--build-arg NGINX_VERSION=${NGINX_VERSION} \
		-t app-printers-consumable-nginx:${NGINX_TAG} \
		-f ./docker/images/nginx/Dockerfile .

nginx-save-image-to-file:
	docker save -o ./docker/images/var/app-printers-consumable-nginx:${NGINX_TAG}.tar app-printers-consumable-nginx:${NGINX_TAG}

nginx-remove-images:
	docker rmi -f app-printers-consumable-nginx:${NGINX_TAG}


# POSTGRES
postgres-create: postgres-build postgres-save-image-to-file postgres-remove-images

postgres-build:
	docker build \
		--build-arg POSTGRES_VERSION=${POSTGRES_VERSION} \
		-t app-printers-consumable-postgres:${POSTGRES_TAG} \
		-f ./docker/images/postgres/Dockerfile .

postgres-save-image-to-file:
	docker save -o ./docker/images/var/app-printers-consumable-postgres:${POSTGRES_TAG}.tar app-printers-consumable-postgres:${POSTGRES_TAG}

postgres-remove-images:
	docker rmi -f app-printers-consumable-postgres:${POSTGRES_TAG}

#################### IMAGES END ######################


composer-install-dev:
	docker run --rm -v ./:/app -w /app app-printers-consumable-php-cli:${PHP_CLI_TAG} composer install --no-scripts

npm-install-dev:
	docker run --rm -v ./:/app -w /app app-printers-consumable-node:${PHP_CLI_TAG} npm install


composer-install-prod:
	docker run --rm -v ./:/app -w /app app-printers-consumable-php-cli:${PHP_CLI_TAG} composer install --no-dev --optimize-autoloader --no-scripts

npm-install-prod:
	docker run --rm -v ./:/app -w /app app-printers-consumable-node:${PHP_CLI_TAG} npm ci --omit=dev
