#!/bin/sh
set -e

# Preserve the key used by existing encrypted data and cookies across deployments.
: "${APP_KEY:?Set APP_KEY to the existing application key in Railway Variables}"

# Volumes are mounted at runtime and initially owned by root.
mkdir -p storage/app/public storage/app/private storage/framework/cache/data \
    storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chown -R www-data:www-data storage/app storage/framework storage/logs bootstrap/cache

# Allow a separate Railway scheduler service to run its explicit start command.
if [ "$#" -gt 0 ]; then
    exec "$@"
fi

# Set Nginx port dynamically from Railway's environment variable $PORT (default 8080)
PORT="${PORT:-8080}"
echo "Configuring Nginx to listen on port $PORT..."
sed -i "s/LISTEN_PORT/$PORT/g" /etc/nginx/sites-available/default

# Run database migrations
echo "Running migrations..."
php artisan migrate --force
php artisan storage:link

# Optimize Laravel
echo "Caching configuration and routes..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start supervisor
echo "Starting Supervisor..."
exec /usr/bin/supervisord -n -c /etc/supervisor/conf.d/supervisord.conf
