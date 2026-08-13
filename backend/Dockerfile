FROM php:8.3-cli

# Install system dependencies and Postgres extension required for Supabase
RUN apt-get update && apt-get install -y \
    libpq-dev \
    git \
    unzip \
    && docker-php-ext-install pdo pdo_pgsql

# Copy Composer from official image
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy backend files into container
COPY . /app

# Install Laravel PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Set permissions for Laravel storage
RUN chmod -R 777 storage bootstrap/cache

EXPOSE 10000

# Run Laravel artisan serve with Render PORT
CMD php artisan serve --host=0.0.0.0 --port=${PORT:-10000}
