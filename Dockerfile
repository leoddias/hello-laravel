FROM php:7.2-apache

ARG DEBIAN_FRONTEND=noninteractive

#Dependecies
RUN apt-get update && apt-get install -y \
    zlib1g-dev \
    libzip-dev
RUN docker-php-ext-install pdo pdo_mysql mysqli zip
RUN curl -sS https://getcomposer.org/installer | php
RUN mv composer.phar /usr/local/bin/composer

#Configs
COPY .docker/vhost.conf /etc/apache2/sites-available/000-default.conf
COPY ./src /var/www/html
RUN a2enmod rewrite

WORKDIR /var/www/html
RUN composer install

EXPOSE 80