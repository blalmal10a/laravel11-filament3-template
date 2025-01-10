#!/bin/bash

get_json_value() {
    local key=$1
    local file=$2
    # Using sed instead of grep -P for better compatibility
    # awk -F'"' "/${key}\":/{ print \$4 }" "$file"
    grep "\"$key\"" "$file" | cut -d'"' -f4
}

# Check if app_config.json exists

if [ -f app_config.json ]; then
    # Read app_name and server_port from app_config.json
    app_name=$(get_json_value "app_name" app_config.json)
    port_no=$(get_json_value "server_port" app_config.json)
else
    # Prompt user for application name and port number
    read -p "Enter the Application Name: " app_name
    read -p "Enter port number (default is 8000): " port_no

    # Use default if no input is given for port
    if [[ -z "$port_no" ]]; then
        port_no=8000  # Set default port
    fi

fi

echo "app name is $app_name"
echo "port no is $port_no"

# Convert folder name to snake_case for DB_DATABASE
db_database=$(echo "$app_name" | tr '[:upper:]' '[:lower:]' | tr ' ' '_')

# Create the .env file with the required content
cat <<EOL > .env
APP_NAME="$app_name"
APP_ENV=local
APP_KEY=base64:bbPTI5Bkep98BXQ+RUVEDaids6mJZAlDAAc8lgaE17c=
APP_DEBUG=true
APP_TIMEZONE="Asia/Kolkata"
SERVER_PORT=$port_no
#SERVER_HOST=0.0.0.0
APP_URL="http://localhost:\${SERVER_PORT}"

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

APP_MAINTENANCE_DRIVER=file

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE="$db_database"
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

CACHE_STORE=database
CACHE_PREFIX=

MEMCACHED_HOST=127.0.0.1

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=log
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="\$APP_NAME"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

VITE_APP_NAME="\$APP_NAME"

# SERVER_HOST=0.0.0.0
EOL

echo ".env file created successfully."

# Create app_config.json file with app_name and server_port if it doesn't exist.
if [ ! -f app_config.json ]; then
    cat <<EOL > app_config.json
{
    "app_name": "$app_name",
    "server_port": "$port_no"
}
EOL

    echo "app_config.json file created successfully."
fi

# Check if the database exists before migrating.
result=$(mysql -u root -e "create database $db_database;")

echo "resuolt is $result"
# Run composer install to install dependencies.
composer install || { echo "Composer installation failed"; exit 1; }
echo "Installation completed"

php artisan migrate:fresh --seed || { echo "Seeding failed"; exit 1; }

echo "Migration and seeding completed"

# Additional Laravel commands.
php artisan storage:link
php artisan key:generate

# Start the Laravel server.
php artisan serve

echo "Laravel server started on port $port_no"
