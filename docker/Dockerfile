FROM php:8.0.1-fpm as build-stage

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

# Test stage
FROM build-stage as test-stage

WORKDIR /opt/app
RUN composer install --optimize-autoloader

RUN composer lint
RUN composer phpstan
#RUN phpdbg -qrr ./vendor/bin/phpunit test/
#RUN cat tests/_reports/coverage.txt | head -n 10 | grep "Lines"
