FROM php:7.4-apache

RUN docker-php-ext-install mysqli

RUN a2enmod rewrite

WORKDIR /var/www/html

COPY ./public /var/www/html/
COPY ./config /var/www/config/
COPY ./includes /var/www/includes/
COPY ./sql /var/www/sql/

RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html
