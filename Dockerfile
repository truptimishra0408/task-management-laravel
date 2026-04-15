FROM php:8.2-apache

# Install dependencies (SQLite instead of MySQL)
RUN apt-get update && apt-get install -y \
    zip unzip git curl libzip-dev libsqlite3-dev \
    && docker-php-ext-install zip pdo pdo_sqlite

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy project
COPY . .

# Install Laravel dependencies
RUN composer install --no-dev --optimize-autoloader

# Create SQLite database file
RUN mkdir -p database && touch database/database.sqlite

# Set permissions
RUN chmod -R 777 storage bootstrap/cache database

# Enable Apache rewrite
RUN a2enmod rewrite

# Set document root to public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public

# Update Apache config
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf

# Fix Apache port to 10000 for Render
RUN sed -i 's/Listen 80/Listen 10000/' /etc/apache2/ports.conf \
    && sed -i 's/<VirtualHost \*:80>/<VirtualHost *:10000>/' /etc/apache2/sites-available/000-default.conf

# Expose port
EXPOSE 10000

# Start server + run migrations
CMD php artisan migrate --force && apache2-foreground