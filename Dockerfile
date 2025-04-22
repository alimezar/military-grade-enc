FROM php:8.1-apache

# Copy all PHP files to web root
COPY . /var/www/html

# Copy flag securely outside web root
COPY flag1.txt /flag1.txt

# Fix ownership
RUN chown -R www-data:www-data /var/www/html /flag1.txt

EXPOSE 80
