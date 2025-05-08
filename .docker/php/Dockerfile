FROM php:8.2-fpm-alpine

# Workdir
WORKDIR /var/www/

# PHP Extensions
RUN apk --update add \
    postgresql-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    imagemagick-dev \
    autoconf \
    build-base \
    imagemagick \
    imagemagick-libs \
    imagemagick-c++

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install pdo_pgsql zip exif

RUN pecl install imagick && docker-php-ext-enable imagick

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
