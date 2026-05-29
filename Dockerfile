FROM richarvey/nginx-php-fpm:latest

# Copy your Laravel code into the container
COPY . /var/www/html

# Set the working directory
WORKDIR /var/www/html

# Configuration variables for Laravel
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV RUN_SCRIPTS=1
ENV WEBROOT=/var/www/html/public

# Run composer to install your PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Expose port 80 for web traffic
EXPOSE 80