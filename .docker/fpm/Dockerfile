FROM php:8.1.11-fpm-alpine3.16

ARG UID=1000
ARG GID=1000

RUN set -eux \
    && apk add --update --no-cache shadow \
    && groupmod -o -g ${GID} www-data \
    && usermod -o -g ${GID} -u ${UID} www-data \
    && apk del shadow

# install PHP extentions
RUN set -eux \
    && apk add --update --no-cache --virtual .build-deps ${PHPIZE_DEPS} \
    && apk add --update --no-cache \
        libzip-dev \
        icu-dev \
    && docker-php-ext-install pdo pdo_mysql \
    && docker-php-ext-enable pdo_mysql \
    && docker-php-ext-install zip \
    && docker-php-ext-enable zip \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl \
    && docker-php-ext-configure opcache \
    && docker-php-ext-install opcache \
    && pecl install -o -f xdebug-3.1.5 \
    && docker-php-ext-enable xdebug \
    && apk del --no-network .build-deps \
    && rm -rf /var/cache/apk/*

COPY ./.docker/fpm/php.ini /usr/local/etc/php/conf.d/php.ini

COPY ./ /var/www/html
WORKDIR /var/www/html

# !!! ONLY FOR DEV ENV: install composer
RUN wget https://getcomposer.org/download/2.4.2/composer.phar && \
    chmod +x composer.phar && \
    php -r "if (hash_file('sha256', 'composer.phar') !== '8fe98a01050c92cc6812b8ead3bd5b6e0bcdc575ce7a93b242bde497a31d7732') { echo 'Invalid composer checksum'; unlink('composer.phar'); } echo PHP_EOL;" && \
    mv composer.phar /usr/local/bin/composer

# remove unused folders/files and update permissions
RUN rm .docker/ -Rf
RUN chown -R www-data:www-data /var/www/html

USER www-data
