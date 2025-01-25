# Use an official PHP image with Apache
FROM php:8.2-apache

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    git zip unzip libicu-dev libpq-dev libzip-dev libpng-dev && \
    docker-php-ext-install intl pdo pdo_mysql zip gd

# Enable Apache mod_rewrite for Symfony
RUN a2enmod rewrite

# Install Composer globally
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set working directory
WORKDIR /var/www/html

# Copy the Symfony project files
COPY . .

# Install Symfony dependencies
RUN composer install --no-dev --optimize-autoloader

# Set proper permissions for cache and logs
RUN chown -R www-data:www-data /var/www/html/var

# Expose the default port
EXPOSE 80

# Start Apache server
CMD ["apache2-foreground"]
