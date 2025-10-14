# Use the official PHP 8.3 with Apache
FROM php:8.3-apache
RUN apt-get update && apt-get install -y libssl-dev pkg-config
RUN pecl install mongodb && docker-php-ext-enable mongodb

# Enable required PHP extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Install a compatible MongoDB extension
RUN pecl uninstall mongodb \
    && pecl install mongodb-1.21.2 \
    && docker-php-ext-enable mongodb

# Copy app files
COPY . /var/www/html/

# Set working directory
WORKDIR /var/www/html/

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader

# Expose Render's default port
EXPOSE 8080

# Run the PHP server
CMD ["php", "-S", "0.0.0.0:8080", "-t", "patient"]

