FROM php:8.1-apache

COPY web/ /var/www/html

RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
