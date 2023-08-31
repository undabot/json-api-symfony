FROM php:8.1.22-fpm as build-stage

RUN apt-get update && apt-get install --no-install-recommends -y \
    git \
    zip \
    wget \
    unzip \
    nano \
    zlib1g-dev libicu-dev g++

RUN docker-php-ext-configure intl
RUN docker-php-ext-install intl

# Install composer
ADD docker/install-composer.sh .
RUN chmod u+x install-composer.sh
RUN ./install-composer.sh
RUN composer --version

WORKDIR /opt/app

COPY . /opt/app

# Don't allow installation with outdated dependencies or invalid composer file
RUN composer status
RUN composer validate --strict
RUN composer outdated --strict

RUN composer install --optimize-autoloader
