# Use an official PHP image as the base image
FROM php:8.2-apache

# Set the working directory inside the container
WORKDIR /var/www/html

# Enable Apache rewrite module
RUN a2enmod rewrite

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libmcrypt-dev \
    libssl-dev \
    libcurl4-openssl-dev

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql mbstring zip exif pcntl bcmath xml curl

# Install Composer globally
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy the Laravel application files into the container
COPY . /var/www/html/

# Install application dependencies using Composer
RUN composer install \
    --ignore-platform-reqs \
    --no-interaction \
    --no-plugins \
    --no-scripts \
    --prefer-dist

# Set permission
RUN chmod -R o+w /var/www/html/storage
RUN chown -R www-data:www-data ./storage
RUN chgrp -R www-data storage bootstrap/cache
RUN chmod -R ug+rwx storage bootstrap/cache
RUN chmod -R 755 /var/www/html/
RUN find /var/www/html/ -type d -exec chmod 775 {} \;
RUN chown -R www-data:www-data /var/www

# Set the correct permissions for Laravel
#RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

RUN php artisan cache:clear

# Copy the Apache virtual host configuration
COPY .docker/vhost.conf /etc/apache2/sites-available/laravel.conf

# Enable the virtual host
RUN a2ensite laravel.conf

# Expose port 80 for Apache
EXPOSE 80

# Start Apache web server
CMD ["apache2-foreground"]
