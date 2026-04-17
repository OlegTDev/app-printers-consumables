include .env
export

all-create: php-fpm-create node-create php-cli-create nginx-create postgres-create
all-build: php-fpm-build node-build php-cli-build nginx-build postgres-build

# PHP-FPM
php-fpm-create: php-fpm-build php-fpm-save-image-to-file php-fpm-remove-images

php-fpm-build:
	cp ../../composer.lock ./var/ && cp ../../composer.json ./var/
	docker build \
		--build-arg PHP_FPM_VERSION=${PHP_FPM_VERSION} \
		-t app-printers-consumable-php-fpm:${PHP_FPM_TAG} \
		-f ./php-fpm/Dockerfile .
	rm ./var/composer.json && rm ./var/composer.lock

php-fpm-save-image-to-file:
	docker save -o var/app-printers-consumable-php-fpm:${PHP_FPM_TAG}.tar app-printers-consumable-php-fpm:${PHP_FPM_TAG}

php-fpm-remove-images:
	docker rmi -f app-printers-consumable-php-fpm:${PHP_FPM_TAG}


# PHP-CLI
php-cli-create: php-cli-build php-cli-save-image-to-file php-cli-remove-images

php-cli-build:
	cp ../../composer.lock ./var/ && cp ../../composer.json ./var/
	docker build \
		--build-arg PHP_CLI_VERSION=${PHP_CLI_VERSION} \
		-t app-printers-consumable-php-cli:${PHP_CLI_TAG} \
		-f ./php-cli/Dockerfile .
	rm ./var/composer.json && rm ./var/composer.lock

php-cli-save-image-to-file:
	docker save -o var/app-printers-consumable-php-cli:${PHP_CLI_TAG}.tar app-printers-consumable-php-cli:${PHP_CLI_TAG}

php-cli-remove-images:
	docker rmi -f app-printers-consumable-php-cli:${PHP_CLI_TAG}


# NODE
node-create: node-build node-save-image-to-file node-remove-images

node-build:
	cp ../../package.json ./var/ && cp ../../package-lock.json ./var/
	docker build \
		--build-arg NODE_VERSION=${NODE_VERSION} \
		-t app-printers-consumable-node:${NODE_TAG} \
		-f ./node/Dockerfile .
	rm ./var/package.json && rm ./var/package-lock.json

node-save-image-to-file:
	docker save -o var/app-printers-consumable-node:${NODE_TAG}.tar app-printers-consumable-node:${NODE_TAG}

node-remove-images:
	docker rmi -f app-printers-consumable-node:${NODE_TAG}


# NGINX
nginx-create: nginx-build nginx-save-image-to-file nginx-remove-images

nginx-build:
	docker build \
		--build-arg NGINX_VERSION=${NGINX_VERSION} \
		-t app-printers-consumable-nginx:${NGINX_TAG} \
		-f ./nginx/Dockerfile .

nginx-save-image-to-file:
	docker save -o var/app-printers-consumable-nginx:${NGINX_TAG}.tar app-printers-consumable-nginx:${NGINX_TAG}

nginx-remove-images:
	docker rmi -f app-printers-consumable-nginx:${NGINX_TAG}


# POSTGRES
postgres-create: postgres-build postgres-save-image-to-file postgres-remove-images

postgres-build:
	docker build \
		--build-arg POSTGRES_VERSION=${POSTGRES_VERSION} \
		-t app-printers-consumable-postgres:${POSTGRES_TAG} \
		-f ./postgres/Dockerfile .

postgres-save-image-to-file:
	docker save -o var/app-printers-consumable-postgres:${POSTGRES_TAG}.tar app-printers-consumable-postgres:${POSTGRES_TAG}

postgres-remove-images:
	docker rmi -f app-printers-consumable-postgres:${POSTGRES_TAG}




# SYSTEM
system-prune:
	docker system prune -a -f

clear-build-cache:
	docker builder prune


