#!/bin/sh
set -e

cd /var/www/html

echo "[ENTRYPOINT] Initializing Laravel application..."

# --------------------------------------------------------------
# 0. Create .env automatically if missing
# --------------------------------------------------------------
if [ ! -f .env ]; then
    if [ -f .env.docker ]; then
        echo "[ENTRYPOINT] Creating .env from .env.docker"
        cp .env.docker .env
    else
        echo "[ENTRYPOINT] WARNING: .env and .env.docker are missing."
    fi
fi

# Reload environment variables from .env
export $(grep -v '^#' .env | xargs)

# --------------------------------------------------------------
# 1. Ensure storage folders exist
# --------------------------------------------------------------
mkdir -p storage/framework/views storage/framework/cache storage/logs
touch storage/logs/laravel.log

chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# --------------------------------------------------------------
# 2. Remove Vite dev indicator
# --------------------------------------------------------------
rm -f public/hot

# --------------------------------------------------------------
# 3. Wait for MySQL if DB_CONNECTION=mysql
# --------------------------------------------------------------
if [ "$DB_CONNECTION" = "mysql" ]; then
    DB_HOST=${DB_HOST:-mysql}
    DB_PORT=${DB_PORT:-3306}

    echo "[ENTRYPOINT] Waiting for MySQL at $DB_HOST:$DB_PORT..."

    # Wait until PDO can connect, not just port open
    until php -r "
        try {
            new PDO(
                'mysql:host=${DB_HOST};port=${DB_PORT};dbname=${DB_DATABASE}',
                '${DB_USERNAME}',
                '${DB_PASSWORD}'
            );
            exit(0);
        } catch (Exception \$e) {
            exit(1);
        }
    "; do
        echo '[ENTRYPOINT] MySQL not ready... retrying in 2 seconds.'
        sleep 2
    done

    echo "[ENTRYPOINT] MySQL connection established."
fi

# --------------------------------------------------------------
# 4. Clear caches
# --------------------------------------------------------------
php artisan view:clear || true
php artisan cache:clear || true
php artisan config:clear || true

# --------------------------------------------------------------
# 5. Run migrations once (ONLY IF DB IS READY)
# --------------------------------------------------------------
if [ ! -f storage/.migrated ]; then
    echo "[ENTRYPOINT] Running migrations..."

    if php artisan migrate --force; then
        echo "[ENTRYPOINT] Migrations executed successfully."
        touch storage/.migrated
    else
        echo "[ENTRYPOINT] Migration failed. Retrying on next startup."
        exit 1
    fi
else
    echo "[ENTRYPOINT] Migrations already applied. Skipping."
fi

# --------------------------------------------------------------
# 6. Start PHP-FPM
# --------------------------------------------------------------
echo "[ENTRYPOINT] Starting PHP-FPM..."
exec php-fpm
