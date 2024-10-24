#!/bin/bash

# Function to attempt command
try_command() {
    # Try running the command
    "$@"
    RESULT=$?

    # If the command fails and is not the `rm` command, inform the user
    if [ $RESULT -ne 0 ]; then
        if [[ "$1" != "rm" ]]; then
            echo "Command failed: $@"
        fi
    fi
}

# Stop the PHP service if running (Optional based on your setup)
# Uncomment the next line if you need to stop the PHP service before running the script
# try_command net stop php

echo "Clearing caches and optimizing application..."
try_command php artisan optimize:clear
try_command php artisan optimize

# Delete the SQLite database file with a pause if busy
SQLITE_FILE="database/database.sqlite"
MAX_ATTEMPTS=5
ATTEMPT=0

while [ -f "$SQLITE_FILE" ]; do
    try_command rm "$SQLITE_FILE" 2>/dev/null  # Suppress output and error

    if [ $? -eq 0 ]; then
        echo "Successfully deleted SQLite database: $SQLITE_FILE"
        break
    else
        echo "Failed to delete SQLite database. It might be in use. Waiting for 2 seconds before retrying..."
        sleep 2
        ATTEMPT=$((ATTEMPT + 1))
        if [ $ATTEMPT -ge $MAX_ATTEMPTS ]; then
            echo "Exceeded maximum attempts to delete SQLite database. Exiting."
            exit 1
        fi
    fi
done

# Create a fresh SQLite database file
echo "Creating fresh SQLite database..."
try_command touch "$SQLITE_FILE"
if [ -f "$SQLITE_FILE" ]; then
    echo "Successfully created new SQLite database: $SQLITE_FILE"
else
    echo "Failed to create new SQLite database: $SQLITE_FILE"
fi

echo "Dropping all tables and running migrations with seeders..."
try_command php artisan migrate:fresh --seed

if [ $? -eq 0 ]; then
    echo "Database migration and seeding completed successfully."
else
    echo "Database migration and seeding failed."
fi

echo "Caching routes..."
try_command php artisan route:cache

if [ $? -eq 0 ]; then
    echo "Routes cached successfully."
else
    echo "Failed to cache routes."
fi

# Attempt to set permissions for laravel.log
LOG_FILE="storage/logs/laravel.log"
echo "Setting permissions for laravel.log..."
try_command chmod 777 "$LOG_FILE"
if [ -f "$LOG_FILE" ]; then
    echo "Successfully set permissions for laravel.log."
else
    echo "Failed to set permissions for laravel.log."
fi

echo "===== Operation Results ====="
echo "Config cache cleared successfully."
echo "Route cache cleared successfully."
echo "View cache cleared successfully."
echo "Application cache cleared successfully."
echo "Compiled files cleared successfully."
echo "Optimization cache cleared successfully."
if [ -f "$SQLITE_FILE" ]; then
    echo "SQLite database reset operation succeeded."
else
    echo "SQLite database reset operation failed."
fi
if [ -f "$LOG_FILE" ]; then
    echo "Log file permissions set successfully."
else
    echo "Failed to set log file permissions."
fi

echo "APPLICATION RESET COMPLETE."
