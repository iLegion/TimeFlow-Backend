FROM php:8.2-fpm-alpine

# Workdir
WORKDIR /var/www/

# PHP Extensions
RUN apk --update add \
    postgresql-dev \
    libzip-dev

RUN docker-php-ext-install pdo_pgsql zip exif

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
