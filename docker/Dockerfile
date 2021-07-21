FROM undabot/php_dev:8

ARG env
ARG XDEBUG_ENABLED=true

USER root
RUN if [[ $XDEBUG_ENABLED = false ]]; then echo "xdebug.mode=off" > /usr/local/etc/php/conf.d/xdebug.ini; fi

COPY --chown=app:app . /opt/app
USER app

RUN echo "$env" > /opt/app/.env.local

WORKDIR /opt/app

RUN composer status
RUN composer validate --strict
RUN composer outdated --strict

RUN composer install --optimize-autoloader