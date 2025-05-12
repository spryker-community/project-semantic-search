FROM php:8.3-fpm

# Install system dependencies (excluding git and pdo-related libs)
RUN apt-get update && apt-get install -y \
    libzip-dev \
    unzip \
    curl \
    && docker-php-ext-install \
    zip \
    bcmath \
    opcache \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set working directory
WORKDIR /var/www/html

ENV PHP_IDE_CONFIG="serverName=Docker"

# Copy PHP configuration (optional)
COPY php.ini /usr/local/etc/php/
