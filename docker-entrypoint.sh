#!/bin/sh
set -e

# Railway provides dynamic PORT (default to 80 if unset)
PORT="${PORT:-80}"

# Replace the placeholder in nginx config with the actual port
sed -i "s/__PORT__/${PORT}/" /etc/nginx/sites-available/default

# Fix permissions
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Clear existing stale caches first
php artisan config:clear || true
php artisan cache:clear || true
php artisan storage:link || true

# Run migrations
php artisan migrate --force || echo "Database migration skipped or failed during startup."

# Cache optimized configuration and views
php artisan config:cache || true
php artisan view:cache || true

# Fix permissions on storage and log files after running artisan commands as root
touch /var/www/storage/logs/laravel.log /var/www/storage/logs/reverb.log || true
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R 777 /var/www/storage /var/www/bootstrap/cache

# Reverb runs as a separate Railway service — do not start it here

# Start PHP-FPM in background, then start Nginx in foreground
php-fpm -D > /dev/null 2>&1
exec nginx -g 'daemon off;'
