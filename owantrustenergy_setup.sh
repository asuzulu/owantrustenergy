#!/bin/bash

echo "Loading Laravel environment variables..."
# (Assumes .env is already loaded by Laravel; no extra steps required)

echo "Checking MySQL service..."
echo "Waiting for MySQL to be available..."
RETRIES=10
while ! sudo mysqladmin ping -h127.0.0.1 --silent; do
  sleep 3
  RETRIES=$((RETRIES - 1))
  if [ $RETRIES -le 0 ]; then
      echo "MySQL not available after multiple attempts."
      exit 1
  fi
done

echo "Dropping and recreating MySQL database..."
# Using inline credentials (user 'ote' with password 'HappyDay@1')
mysql -uote -p'HappyDay@1' -e "DROP DATABASE IF EXISTS owantrustenergy;"
mysql -uote -p'HappyDay@1' -e "CREATE DATABASE owantrustenergy;"

if [ $? -eq 0 ]; then
    echo "Database reset successful."
else
    echo "Failed to reset database."
fi

echo "Running migrations with seeders..."
php artisan migrate:fresh --seed
if [ $? -eq 0 ]; then
    echo "Database migration and seeding completed successfully."
else
    echo "Database migration and seeding failed."
fi

echo "Clearing caches and optimizing application..."
# Now that migrations have run and the cache table exists, these commands should succeed.
php artisan optimize:clear || echo "Warning: optimize:clear encountered errors, continuing..."
php artisan optimize || echo "Warning: optimize encountered errors, continuing..."

echo "Ensuring logs directory exists..."
LOG_FILES=(
    "storage/logs/cylinder_creation.log"
    "storage/logs/warehouse_creation.log"
    "storage/logs/warehouse_update.log"
)
for LOG_FILE in "${LOG_FILES[@]}"; do
    if [ -f "$LOG_FILE" ]; then
        sudo rm "$LOG_FILE"
        echo "Deleted $LOG_FILE."
    else
        echo "$LOG_FILE does not exist."
    fi
    sudo touch "$LOG_FILE"
    echo "Created $LOG_FILE."
done

echo "Recreating QR codes folder..."
QR_CODES_FOLDER="storage/app/private/public/qrcodes"
if [ -d "$QR_CODES_FOLDER" ]; then
    sudo rm -rf "$QR_CODES_FOLDER"
    echo "Deleted $QR_CODES_FOLDER."
else
    echo "$QR_CODES_FOLDER does not exist."
fi
sudo mkdir -p "$QR_CODES_FOLDER"
echo "Created $QR_CODES_FOLDER."

echo "Caching routes..."
php artisan route:cache
if [ $? -eq 0 ]; then
    echo "Routes cached successfully."
else
    echo "Failed to cache routes. Clearing route cache and skipping route caching..."
    php artisan route:clear
fi

echo "Setting permissions for laravel.log..."
LOG_FILE="storage/logs/laravel.log"
sudo chmod 777 "$LOG_FILE"
if [ -f "$LOG_FILE" ]; then
    echo "Successfully set permissions for laravel.log."
else
    echo "Failed to set permissions for laravel.log."
fi

echo "Setting ownership and permissions for storage and bootstrap/cache..."
sudo chown -R nginx:nginx /var/www/html/owantrustenergy/storage
sudo chown -R nginx:nginx /var/www/html/owantrustenergy/bootstrap/cache
sudo find /var/www/html/owantrustenergy/storage -type d -exec chmod 775 {} \;
sudo find /var/www/html/owantrustenergy/storage -type f -exec chmod 664 {} \;
sudo find /var/www/html/owantrustenergy/bootstrap/cache -type d -exec chmod 775 {} \;
sudo find /var/www/html/owantrustenergy/bootstrap/cache -type f -exec chmod 664 {} \;

echo "Ownership and permissions set. Application should now run without permission errors."

echo "APPLICATION RESET COMPLETE."

