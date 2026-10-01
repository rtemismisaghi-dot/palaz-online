#!/bin/sh
set -e
php artisan migrate --force
php artisan storage:link --force || true
exec apache2-foreground
