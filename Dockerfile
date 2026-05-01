# 1. Use the official PHP 8.4 CLI image (Lightweight and Fast)
FROM php:8.4-cli

# 2. Install System Dependencies for Postgres and PHP extensions
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libpng-dev \
    zip \
    unzip \
    git \
    curl \
    && docker-php-ext-install pdo_pgsql bcmath gd pcntl

# 3. Install Composer from the official image
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 4. Set the working directory inside the container
WORKDIR /var/www

# 5. Copy the project files to the container
COPY . .

# 6. Install Laravel dependencies (Optimized for Production)
RUN composer install --no-interaction --optimize-autoloader --no-dev

# 7. Set Permissions for Laravel Storage
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache && \
    chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# 8. Expose the port Render will use
EXPOSE 8000

# 9. Make the start script executable
RUN chmod +x /var/www/start.sh

# 10. Run the startup script
CMD ["/var/www/start.sh"]