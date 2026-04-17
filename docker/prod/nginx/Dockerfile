ARG VERSION_TAG=v1

FROM app-printers-consumable-node:${VERSION_TAG} AS node-builder

COPY ./ ./

RUN npm run prod



ARG VERSION_TAG=v1

FROM app-printers-consumable-nginx:${VERSION_TAG}

COPY ./docker/prod/nginx/conf.d/default.conf /etc/nginx/conf.d/default.conf

WORKDIR /app

COPY ./public ./public

COPY --from=node-builder /app/public/ ./public/
