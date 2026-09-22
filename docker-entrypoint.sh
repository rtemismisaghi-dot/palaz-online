#!/bin/sh
set -e
php artisan migrate --force
php artisan db:seed --class='App\\Seeders\\DatabaseSeeder' --force
exec apache2-foreground
