#!/bin/sh
set -e

# Set Nginx port dynamically from Railway's environment variable $PORT (default 8080)
PORT="${PORT:-8080}"
echo "Configuring Nginx to listen on port $PORT..."
sed -i "s/LISTEN_PORT/$PORT/g" /etc/nginx/sites-available/default

# Generate app key if not set (or let it run if it's set in env)
if [ -z "$APP_KEY" ]; then
    echo "No APP_KEY set, generating one..."
    php artisan key:generate --force
fi

# Run database migrations
echo "Running migrations..."
php artisan migrate --force

# Optimize Laravel
echo "Caching configuration and routes..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start supervisor
echo "Starting Supervisor..."
exec /usr/bin/supervisord -n -c /etc/supervisor/conf.d/supervisord.conf
