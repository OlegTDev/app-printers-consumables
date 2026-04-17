ARG VERSION_TAG=v1

FROM app-printers-consumable-php-fpm:${VERSION_TAG}

ARG USER_ID=1000
ARG GROUP_ID=1000
RUN addgroup -g ${GROUP_ID} app && \
    adduser -u ${USER_ID} -G app -s /bin/sh -D app

USER app

COPY --chown=app:app ./ ./
