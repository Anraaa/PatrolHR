<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Lokasi Patroli</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f3f4f6;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .card {
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 4px 24px rgba(0,0,0,.1);
            padding: 2.5rem 2rem;
            max-width: 360px;
            width: 100%;
            text-align: center;
        }
        .icon { font-size: 3.5rem; margin-bottom: 1rem; }
        .title { font-size: 1.2rem; font-weight: 700; color: #111827; margin-bottom: .5rem; }
        .msg { font-size: .9rem; color: #6b7280; line-height: 1.5; }
        .spinner {
            display: inline-block;
            width: 2rem;
            height: 2rem;
            border: 3px solid #e5e7eb;
            border-top-color: #4f46e5;
            border-radius: 50%;
            animation: spin .8s linear infinite;
            margin-top: 1.5rem;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .btn {
            display: inline-block;
            margin-top: 1.5rem;
            background: #4f46e5;
            color: #fff;
            border: none;
            padding: .625rem 1.5rem;
            border-radius: .5rem;
            font-size: .9rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
        }
        .btn:hover { background: #4338ca; }
        .btn-gray { background: #6b7280; }
        .btn-gray:hover { background: #4b5563; }
        .hidden { display: none !important; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon" id="icon">📍</div>
        <div class="title" id="title">Memverifikasi Lokasi</div>
        <div class="msg" id="msg">Meminta akses GPS, mohon tunggu…</div>
        <div class="spinner" id="spinner"></div>
        <button class="btn hidden" id="retryBtn" onclick="checkGPS()">Coba Lagi</button>
        <a class="btn btn-gray hidden" id="cancelBtn" href="/admin">Batal</a>
    </div>

    <script>
        var UUID       = @json($uuid);
        var successUrl = @json($successUrl ?? url('/admin/patrols/create?loc=' . $uuid));

        function setState(icon, title, msg, showSpinner, showRetry, showCancel) {
            document.getElementById('icon').textContent  = icon;
            document.getElementById('title').textContent = title;
            document.getElementById('msg').textContent   = msg;
            document.getElementById('spinner').classList.toggle('hidden', !showSpinner);
            document.getElementById('retryBtn').classList.toggle('hidden', !showRetry);
            document.getElementById('cancelBtn').classList.toggle('hidden', !showCancel);
        }

        function blocked(reason) {
            setState('🚫', 'Akses Ditolak', reason, false, true, true);
        }

        async function checkGPS() {
            setState('📍', 'Memverifikasi Lokasi', 'Memeriksa konfigurasi lokasi…', true, false, false);

            // ── Langkah 1: cek apakah lokasi ini butuh GPS ──────────────────
            var geoConfigured = false;
            try {
                var cfgResp = await fetch(
                    '/admin/patrols/geo-verify/' + encodeURIComponent(UUID),
                    { headers: { 'X-Requested-With': 'XMLHttpRequest' } }
                );

                if (!cfgResp.ok) {
                    // Server error → BLOKIR (bukan loloskan)
                    blocked('Server tidak dapat diakses (HTTP ' + cfgResp.status + '). Hubungi administrator.');
                    return;
                }

                var cfgData = await cfgResp.json();

                if (cfgData.geo_configured === false) {
                    // Lokasi memang tidak pakai GPS → langsung loloskan
                    setState('✅', 'Dialihkan…', 'Lokasi tidak memerlukan GPS. Mengarahkan…', true, false, false);
                    window.location.href = successUrl;
                    return;
                }

                geoConfigured = true;
            } catch (e) {
                // Tidak bisa reach server → BLOKIR
                blocked('Tidak dapat menghubungi server. Pastikan koneksi internet aktif.');
                return;
            }

            // ── Langkah 2: minta GPS ────────────────────────────────────────
            // Catatan: di HTTP (dev) navigator.geolocation TETAP tersedia di
            // localhost/127.0.0.1 karena browser memperlakukan localhost sebagai
            // "secure context" meski tanpa HTTPS.
            if (!navigator.geolocation) {
                blocked('Browser tidak mendukung GPS. Gunakan browser yang mendukung geolocation.');
                return;
            }

            setState('📍', 'Memverifikasi Lokasi', 'Meminta akses GPS, mohon tunggu…', true, false, false);

            navigator.geolocation.getCurrentPosition(
                // ── GPS berhasil ──────────────────────────────────────────
                async function (pos) {
                    setState('🔍', 'Memeriksa Jarak', 'Menghitung jarak ke titik patroli…', true, false, false);

                    try {
                        var resp = await fetch(
                            '/admin/patrols/geo-verify/' + encodeURIComponent(UUID)
                            + '?lat=' + pos.coords.latitude
                            + '&lng=' + pos.coords.longitude,
                            { headers: { 'X-Requested-With': 'XMLHttpRequest' } }
                        );

                        if (!resp.ok) {
                            blocked('Gagal memverifikasi ke server (HTTP ' + resp.status + ').');
                            return;
                        }

                        var data = await resp.json();

                        if (data.allowed) {
                            var locName = data.location ? ' (' + data.location + ')' : '';
                            setState('✅', 'Lokasi Terverifikasi',
                                'Berhasil' + locName + '. Mengarahkan…',
                                true, false, false);
                            setTimeout(function () { window.location.href = successUrl; }, 800);
                        } else {
                            // Di luar radius → BLOKIR dengan info jarak
                            setState(
                                '❌', 'Di Luar Jangkauan',
                                'Anda berada ' + data.distance + ' m dari titik "'
                                + data.location + '". Harus dalam radius ' + data.radius + ' m.',
                                false, true, true
                            );
                        }

                    } catch (e) {
                        // Gagal fetch setelah dapat GPS → BLOKIR
                        blocked('Koneksi terputus saat memverifikasi. Coba lagi.');
                    }
                },

                // ── GPS gagal / ditolak ───────────────────────────────────
                function (err) {
                    if (err.code === 1 /* PERMISSION_DENIED */) {
                        setState(
                            '🚫', 'Izin Lokasi Ditolak',
                            'Izinkan akses lokasi GPS di browser, lalu tekan "Coba Lagi".',
                            false, true, true
                        );
                    } else if (err.code === 2 /* POSITION_UNAVAILABLE */) {
                        blocked('GPS tidak tersedia di perangkat ini. Aktifkan GPS dan coba lagi.');
                    } else if (err.code === 3 /* TIMEOUT */) {
                        blocked('GPS timeout. Pastikan sinyal GPS aktif lalu tekan "Coba Lagi".');
                    } else {
                        blocked('GPS error: ' + err.message);
                    }
                },
                { timeout: 15000, maximumAge: 0, enableHighAccuracy: true }
            );
        }

        window.addEventListener('load', checkGPS);
    </script>
</body>
</html>