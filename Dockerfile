# ──────────────────────────
#  Dockerfile
# ──────────────────────────
FROM php:8.1-apache

# copy the web app
COPY web/ /var/www/html

# keep Apache happy
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
