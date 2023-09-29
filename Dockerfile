FROM php:8.1.9-fpm-alpine
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nodejs \
    npm

COPY . /var/www/html

RUN composer install --no-interaction --no-scripts --no-suggest
RUN npm install
RUN npm run production
EXPOSE 80