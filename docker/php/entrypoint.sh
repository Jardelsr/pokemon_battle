#!/bin/sh
set -e

echo "Fixing storage and cache permissions..."
chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache

echo "Installing Composer dependencies..."
composer install --no-interaction --no-progress --prefer-dist

exec php-fpm
