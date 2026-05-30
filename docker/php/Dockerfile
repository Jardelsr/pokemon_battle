FROM php:8.3-fpm

# System dependencies required for PHP extensions
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libexif-dev \
    unzip \
    git \
    curl \
    && rm -rf /var/lib/apt/lists/*

# Configure and install PHP extensions
RUN docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
    && docker-php-ext-install \
        pdo_pgsql \
        pgsql \
        zip \
        exif \
        pcntl \
        bcmath \
        gd

# Install Composer from official image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy composer files first for better layer caching
COPY composer.json composer.lock ./

# Install PHP dependencies without running post-install scripts (artisan not available yet)
RUN composer install \
    --no-interaction \
    --no-progress \
    --no-scripts \
    --prefer-dist

# Copy application source code
COPY . .

# Re-run autoloader dump now that artisan and full app are present
RUN composer dump-autoload \
    --optimize \
    --no-interaction

# Copy and configure the entrypoint script
COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Set correct ownership and permissions for Laravel storage and cache
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

EXPOSE 9000

ENTRYPOINT ["entrypoint.sh"]
