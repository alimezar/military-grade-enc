FROM php:8.1-apache

COPY web/ /var/www/html

COPY flag1.txt /flag1.txt

RUN chown -R www-data:www-data /var/www/html /flag1.txt

EXPOSE 80
