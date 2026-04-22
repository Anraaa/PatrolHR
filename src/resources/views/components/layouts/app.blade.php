<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />

    <!-- PWA Configuration -->
    <meta name="theme-color" content="#3b82f6" />
    <meta name="description" content="Sistem Patroli dan Checksheet dengan QR Code Validation" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
    <meta name="apple-mobile-web-app-title" content="Checksheet" />
    <link rel="apple-touch-icon" href="/images/icon-192x192.png" />
    <link rel="icon" type="image/png" sizes="32x32" href="/images/icon-256x256.png" />
    <link rel="manifest" href="/manifest.json" />

    <title>{{ $title ?? config('app.name', 'Filament') }}</title>

    {{ $head ?? '' }}
</head>
<body class="antialiased">
    {{ $slot }}
</body>
</html>
