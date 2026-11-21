#!/bin/bash
set -e

PORT="${PORT:-10000}"

# Bind Apache to Render's dynamic PORT
sed -i "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost \*:${PORT}>/g" /etc/apache2/sites-available/000-default.conf
sed -i "s!DocumentRoot /var/www/html/public!DocumentRoot /var/www/html!g" /etc/apache2/sites-available/000-default.conf

# Ensure storage and assets upload directories exist and are writable
mkdir -p /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/logs \
         /var/www/html/bootstrap/cache \
         /var/www/html/assets/global/images \
         /var/www/html/public/assets/global/images

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/assets /var/www/html/public/assets
chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/assets /var/www/html/public/assets

# Clear runtime caches
php artisan config:clear || true
php artisan cache:clear || true
php artisan view:clear || true

exec "$@"
