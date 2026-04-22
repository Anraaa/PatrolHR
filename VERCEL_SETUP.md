# Vercel Deployment Guide

## Environment Variables yang perlu di-set di Vercel Dashboard:

Buka https://vercel.com/project/settings/environment-variables dan tambahkan:

```
APP_KEY=base64:jU6xg8sp9ia37ypFlTVk1CAFx6MmeXRukO1W987uUzI=
APP_ENV=production
APP_DEBUG=false
APP_TIMEZONE=Asia/Jakarta
DB_HOST=gateway01.ap-southeast-1.prod.aws.tidbcloud.com
DB_PORT=4000
DB_DATABASE=checksheet
DB_USERNAME=egxPbYaj34NmCgs.root
DB_PASSWORD=QA5gEkMbPbT7GOtk
```

## Apa yang sudah dikonfigurasi:

1. **vercel.json** - Konfigurasi build, routing, dan environment variables
2. **vercel-build.sh** - Script build yang menjalankan:
   - Composer install
   - NPM install & build assets
   - Migrations
   - Storage link
   - Config caching

3. **.env.production** - Environment file untuk production mode

## Testing Deployment:

Setelah push ke GitHub dan trigger Vercel deployment:

1. Buka https://patrol-one.vercel.app/admin/login
2. Login dengan credentials Anda
3. Jika ada error, cek Vercel Function Logs:
   - https://vercel.com/project/functions

## Troubleshooting:

### Jika assets tidak muncul:
- Check bahwa ASSET_URL di .env.production = "/"
- Verifikasi bahwa npm run build berhasil generate public/build/

### Jika database error:
- Verifikasi DB credentials di Vercel Environment Variables
- Jalankan `php artisan migrate --force` manual

### Jika Filament tidak load:
- Cek APP_DEBUG=false di production
- Check storage logs di public/storage/logs/
