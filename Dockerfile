FROM php:8.3-apache

RUN a2enmod rewrite

COPY ./app /var/www/html

EXPOSE 80