FROM php:8.1-apache

# Copy only the public web files to web root
COPY web/ /var/www/html

# Copy the flag to a secure location (not exposed)
COPY flag1.txt /flag1.txt

RUN chown -R www-data:www-data /var/www/html /flag1.txt

EXPOSE 80
