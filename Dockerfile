# --------------------------------------------------
# Stage 1: Build frontend assets
# --------------------------------------------------
FROM node:24-bookworm-slim AS frontend

WORKDIR /app

COPY package.json package-lock.json ./

RUN npm ci

COPY . .

RUN npm run build


# --------------------------------------------------
# Stage 2: Laravel production runtime
# --------------------------------------------------
FROM php:8.3-apache

WORKDIR /var/www/html

# Install required PHP extensions and CA certificates.
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
    ca-certificates \
    libonig-dev \
    libzip-dev \
    unzip \
    && docker-php-ext-install \
    pdo_mysql \
    mbstring \
    opcache \
    zip \
    && rm -rf /var/lib/apt/lists/*

# Install Composer.
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Configure Apache to serve Laravel's public directory.
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN sed -ri \
    -e "s!/var/www/html!${APACHE_DOCUMENT_ROOT}!g" \
    /etc/apache2/sites-available/000-default.conf \
    && a2enmod rewrite headers

# Copy application source.
COPY . .

# Copy Vite production assets.
COPY --from=frontend /app/public/build ./public/build

# Install production dependencies.
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader

# Prepare Laravel writable directories.
RUN mkdir -p \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwx storage bootstrap/cache

# Render provides the PORT environment variable.
# Apache listens on that port, defaulting to 10000.
CMD ["bash", "-lc", "PORT=${PORT:-10000}; sed -i \"s/^Listen 80/Listen ${PORT}/\" /etc/apache2/ports.conf; sed -i \"s/<VirtualHost \\*:80>/<VirtualHost *:${PORT}>/\" /etc/apache2/sites-available/000-default.conf; exec apache2-foreground"]
