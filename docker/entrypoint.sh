#!/bin/sh
set -e

# Wait for database
echo "Waiting for database..."
while ! pg_isready -h $DB_HOST -p $DB_PORT -U $DB_USERNAME; do
    sleep 1
done

# Run migrations
echo "Running migrations..."
php artisan migrate --force

# Cache config
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start supervisor
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
