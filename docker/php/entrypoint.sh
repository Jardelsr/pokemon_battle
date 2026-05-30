#!/bin/sh
set -e

echo "Installing Composer dependencies..."
composer install --no-interaction --no-progress --prefer-dist

exec php-fpm
