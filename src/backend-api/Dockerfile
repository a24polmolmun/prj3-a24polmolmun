FROM php:8.4-fpm-alpine

# Instalar dependencias del sistema necesarias para Laravel
RUN apk add --no-cache \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    curl \
    freetype-dev \
    libjpeg-turbo-dev \
    linux-headers

# Instalar extensiones de PHP
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd xml dom

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
