#!/bin/bash
set -e

echo "=== Vercel Build Script ==="
echo "PHP Version:"
php -v

echo -e "\n=== Installing Composer Dependencies ==="
composer install --no-interaction --prefer-dist --optimize-autoloader

echo -e "\n=== Installing NPM Dependencies ==="
npm ci

echo -e "\n=== Building Assets with Vite ==="
npm run build

echo -e "\n=== Generating Key ==="
php artisan key:generate --force

echo -e "\n=== Running Migrations ==="
php artisan migrate --force || true

echo -e "\n=== Creating Storage Link ==="
php artisan storage:link || true

echo -e "\n=== Publishing Filament Assets ==="
php artisan filament:assets || php artisan vendor:publish --tag=filament --force || true

echo -e "\n=== Publishing Vendor Assets ==="
php artisan vendor:publish --tag=laravel-assets --force || true

echo -e "\n=== Caching Configuration ==="
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

echo -e "\n=== Build Complete ==="
