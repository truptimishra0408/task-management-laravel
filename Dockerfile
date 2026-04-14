FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    zip unzip git curl libzip-dev \
    && docker-php-ext-install zip

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy project
COPY . .

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Set permissions
RUN chmod -R 777 storage bootstrap/cache

# Enable Apache rewrite
RUN a2enmod rewrite

# Expose port (Render uses 10000)
EXPOSE 10000

# IMPORTANT: run migrate at runtime (NOT build time)
CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=10000