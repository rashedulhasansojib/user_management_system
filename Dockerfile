# Use the official PHP image
FROM php:8.2-cli

# Install required system dependencies
RUN apt-get update && apt-get install -y \
    zip unzip git curl libicu-dev libpq-dev libonig-dev && \
    docker-php-ext-install intl pdo pdo_pgsql

# Set the working directory
WORKDIR /app

# Copy project files
COPY . .

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Clear Composer cache
RUN composer clear-cache

# Install Symfony dependencies
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Remove MakerBundle if not needed in production
RUN composer remove symfony/maker-bundle --no-update || true

# Set proper permissions for cache and logs
RUN chmod -R 777 var/cache var/log

# Expose the application port
EXPOSE 8000

# Run Symfony server
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
