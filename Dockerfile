FROM php:8.0.15-apache

RUN a2enmod rewrite

RUN docker-php-source extract && docker-php-ext-install pdo_mysql mysqli && docker-php-source delete

RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    zip \
    curl \
    git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
