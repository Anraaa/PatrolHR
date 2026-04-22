#!/bin/bash
set -e

cd src

echo "=== Vercel Build Script ==="
echo "PHP Version:"
php -v

echo -e "\n=== Building Assets with Vite ==="
npm run build || true

echo -e "\n=== Generating Key ==="
php artisan key:generate --force || true

echo -e "\n=== Publishing Filament Assets ==="
php artisan filament:assets --ansi || true

echo -e "\n=== Caching Configuration ==="
php artisan config:cache --ansi || true
php artisan route:cache --ansi || true

echo -e "\n=== Build Complete ==="
