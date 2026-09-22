FROM node:22-bookworm AS assets
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY resources ./resources
COPY vite.config.js ./
RUN npm run build

FROM php:8.3-apache
WORKDIR /var/www/html

RUN apt-get update \
    && apt-get install -y git unzip libpq-dev \
    && docker-php-ext-install pdo_pgsql \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY . .
RUN mkdir -p bootstrap/cache storage/framework/cache storage/framework/sessions storage/framework/views storage/logs
COPY --from=assets /app/public/build ./public/build

RUN composer install --no-dev --optimize-autoloader --no-interaction \
    && chown -R www-data:www-data storage bootstrap/cache \
    && printf '%s\n' \
       '<VirtualHost *:80>' \
       '    DocumentRoot /var/www/html/public' \
       '    <Directory /var/www/html/public>' \
       '        AllowOverride All' \
       '        Require all granted' \
       '    </Directory>' \
       '</VirtualHost>' \
       > /etc/apache2/sites-available/000-default.conf

EXPOSE 80

CMD ["sh","-c","php artisan migrate --force && php artisan db:seed --class='Database\Seeders\DatabaseSeeder' --force && apache2-foreground"]
