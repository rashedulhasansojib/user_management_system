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

# Install Symfony dependencies
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --no-dev --optimize-autoloader --no-scripts && \
    composer run-script auto-scripts

# Set proper permissions for cache and logs
RUN chmod -R 777 var/cache var/log

# Set the entrypoint
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
