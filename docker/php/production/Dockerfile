FROM php:8.1-fpm

ENV APP_HOME /var/www/app_symfony
ENV USERNAME=www-data

RUN apt update \
    && apt install -y zlib1g-dev g++ git libicu-dev zip libzip-dev zip \
    && docker-php-ext-install intl opcache pdo pdo_mysql \
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip

WORKDIR /var/www/app_symfony

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN curl -sS https://get.symfony.com/cli/installer | bash
RUN mv /root/.symfony5/bin/symfony /usr/local/bin/symfony

COPY ./app /var/www/app_symfony
COPY ./docker/php/production/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

RUN chown -R ${USERNAME}:${USERNAME} $APP_HOME

RUN composer install