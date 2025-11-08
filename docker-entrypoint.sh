#!/bin/sh

# Create storage and cache directories if they don't exist
mkdir -p /var/www/storage/framework/sessions
mkdir -p /var/www/storage/framework/views
mkdir -p /var/www/storage/framework/cache/data
mkdir -p /var/www/storage/logs
mkdir -p /var/www/bootstrap/cache

# Mettre en cache la configuration, les routes et les vues
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Exécuter la commande principale du conteneur
exec "$@"
