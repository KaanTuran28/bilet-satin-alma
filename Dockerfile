FROM php:8.2-apache

RUN apt-get update && apt-get install -y libsqlite3-dev

RUN docker-php-ext-install pdo pdo_sqlite

RUN a2enmod rewrite

COPY . /var/www/html/

COPY apache-config.conf /etc/apache2/sites-available/000-default.conf

RUN chown -R www-data:www-data /var/www/html/database.sqlite \
    && chmod -R 755 /var/www/html/