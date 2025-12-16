FROM php:8.3-apache

RUN apt-get update \
 && docker-php-ext-install pdo pdo_mysql \
 && a2enmod rewrite \
 && rm -rf /var/lib/apt/lists/*

COPY . /var/www/html