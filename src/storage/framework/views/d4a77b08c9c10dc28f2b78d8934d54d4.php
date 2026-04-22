<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?php echo e($title); ?></title>
    <style>
        @page { size: A4 portrait; margin: 0; }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        html, body {
            font-family: 'Helvetica Neue', 'Helvetica', 'Arial', sans-serif;
            background: #ffffff;
            color: #1e293b;
            width: 100%;
            height: 100%;
        }

        /* ── Page Container ── */
        .page {
            position: relative;
            width: 100%;
            min-height: 297mm;
            padding: 36px 50px 60px;
            background: #f8fafc;
        }

        /* ── Header ── */
        .page-header {
            background: #0a1628;
            border-radius: 10px 10px 0 0;
            overflow: hidden;
            margin-bottom: 0;
            position: relative;
        }
        .page-header::before {
            content: '';
            position: absolute;
            top: -40px; right: -30px;
            width: 160px; height: 160px;
            background: rgba(37,99,235,0.1);
            border-radius: 50%;
        }
        .page-header::after {
            content: '';
            position: absolute;
            bottom: -20px; left: -10px;
            width: 100px; height: 100px;
            background: rgba(59,130,246,0.07);
            border-radius: 50%;
        }
        .header-inner {
            position: relative;
            z-index: 1;
            padding: 18px 22px 16px;
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .header-icon {
            width: 42px; height: 42px;
            background: rgba(37,99,235,0.2);
            border: 1px solid rgba(96,165,250,0.35);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .header-icon svg { width: 22px; height: 22px; }
        .header-text h1 {
            font-size: 13px;
            font-weight: 800;
            color: #ffffff;
            text-transform: uppercase;
            letter-spacing: 2px;
            line-height: 1.2;
        }
        .header-text p {
            font-size: 8.5px;
            color: #60a5fa;
            letter-spacing: 1px;
            margin-top: 3px;
            text-transform: uppercase;
        }
        .header-stripe {
            height: 3px;
            background: linear-gradient(90deg, #1d4ed8 0%, #3b82f6 50%, #60a5fa 100%);
        }

        /* ── Main Card ── */
        .card-wrap {
            width: 360px;
            margin: 70px auto 0;
            border-radius: 14px;
            overflow: hidden;
            border: 1px solid #e0eaff;
            box-shadow: 0 4px 24px rgba(30,58,138,0.08), 0 1px 4px rgba(0,0,0,0.05);
            background: #ffffff;
        }

        /* ── Location Header ── */
        .loc-header {
            background: #1e3a8a;
            padding: 18px 20px 16px;
            position: relative;
            overflow: hidden;
        }
        .loc-header::before {
            content: '';
            position: absolute;
            top: -20px; right: -20px;
            width: 100px; height: 100px;
            background: rgba(255,255,255,0.05);
            border-radius: 50%;
        }
        .loc-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.18);
            border-radius: 20px;
            padding: 4px 10px;
            margin-bottom: 10px;
        }
        .loc-dot {
            width: 6px; height: 6px;
            background: #34d399;
            border-radius: 50%;
        }
        .loc-badge span {
            font-size: 8px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: #93c5fd;
        }
        .loc-name {
            font-size: 19px;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: -0.3px;
            line-height: 1.2;
            position: relative;
            z-index: 1;
        }
        .loc-meta {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-top: 8px;
            position: relative;
            z-index: 1;
        }
        .loc-meta-item {
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .loc-meta-item svg {
            width: 10px; height: 10px;
            flex-shrink: 0;
        }
        .loc-meta-item span {
            font-size: 9px;
            color: #93c5fd;
        }
        .loc-header-accent {
            height: 3px;
            background: linear-gradient(90deg, #3b82f6, #60a5fa);
        }

        /* ── QR Section ── */
        .qr-section {
            padding: 28px 20px 20px;
            display: flex;
            flex-direction: column; /* Mengubah orientasi menjadi vertikal */
            align-items: center;    /* Menengahkan elemen secara horizontal */
            text-align: center;     /* Menengahkan teks */
            gap: 16px;
            background: #fff;
        }
        .qr-frame-wrap {
            flex-shrink: 0;
            position: relative;
        }
        .qr-frame {
            padding: 10px;
            border: 2px solid #dbeafe;
            border-radius: 10px;
            background: #fff;
            display: inline-flex;
            justify-content: center;
            align-items: center;
        }
        .qr-frame img {
            display: block;
            width: 150px;
            height: 150px;
        }
        .qr-corner {
            position: absolute;
            width: 18px; height: 18px;
            border-color: #1d4ed8;
            border-style: solid;
        }
        .qr-corner.tl { top: -2px; left: -2px; border-width: 3px 0 0 3px; border-radius: 4px 0 0 0; }
        .qr-corner.br { bottom: -2px; right: -2px; border-width: 0 3px 3px 0; border-radius: 0 0 4px 0; }
        .qr-info {
            width: 100%;
        }
        .qr-info-title {
            font-size: 13.5px;
            font-weight: 700;
            color: #1e3a8a;
            line-height: 1.35;
            margin-bottom: 6px;
        }
        .qr-info-desc {
            font-size: 9.5px;
            color: #64748b;
            line-height: 1.55;
            margin-bottom: 16px;
            padding: 0 10px; /* Memberi jarak agar teks tidak menempel ke tepi pinggir */
        }
        .scan-cta {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #1d4ed8;
            color: #fff;
            border-radius: 8px;
            padding: 10px 18px;
            font-size: 9.5px;
            font-weight: 800;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            text-decoration: none;
        }
        .scan-cta svg {
            width: 12px; height: 12px;
        }

        /* ── Steps ── */
        .steps-row {
            padding: 0 20px 24px;
            display: flex;
            gap: 6px;
        }
        .step-item {
            flex: 1;
            background: #f0f4ff;
            border: 1px solid #dbeafe;
            border-radius: 8px;
            padding: 10px 6px;
            text-align: center;
        }
        .step-num {
            width: 20px; height: 20px;
            background: #1d4ed8;
            color: #fff;
            border-radius: 50%;
            font-size: 9px;
            font-weight: 800;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 6px;
        }
        .step-label {
            font-size: 8.5px;
            color: #475569;
            line-height: 1.4;
            font-weight: 500;
        }

        /* ── Card Footer ── */
        .card-foot {
            border-top: 1px solid #e0eaff;
            padding: 9px 18px;
            background: #f8faff;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .uuid {
            font-family: 'Courier New', monospace;
            font-size: 8px;
            color: #94a3b8;
            letter-spacing: 0.2px;
        }
        .status-indicator {
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .status-dot {
            width: 6px; height: 6px;
            background: #34d399;
            border-radius: 50%;
        }
        .status-text {
            font-size: 8px;
            color: #64748b;
            font-weight: 600;
        }

        /* ── Page Footer ── */
        .page-footer {
            position: absolute;
            bottom: 32px;
            left: 50px;
            right: 50px;
            border-top: 1px solid #e2e8f0;
            padding-top: 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .footer-brand {
            display: flex;
            align-items: center;
            gap: 7px;
        }
        .footer-brand-dot {
            width: 8px; height: 8px;
            background: #2563eb;
            border-radius: 50%;
        }
        .footer-brand-name {
            font-size: 8.5px;
            font-weight: 800;
            color: #1e3a8a;
            letter-spacing: 0.8px;
            text-transform: uppercase;
        }
        .footer-meta {
            font-size: 7.5px;
            color: #94a3b8;
        }
    </style>
</head>
<body>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $locations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $location): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div class="page" style="<?php echo e($loop->last ? '' : 'page-break-after: always;'); ?>">

    
    <div class="page-header">
        <div class="header-inner">
            <div class="header-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="#60a5fa" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 11l3 3L22 4"/>
                    <path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/>
                </svg>
            </div>
            <div class="header-text">
                <h1><?php echo e($title); ?></h1>
                <p>Sistem Monitoring &amp; Checksheet Digital Patroli</p>
            </div>
        </div>
        <div class="header-stripe"></div>
    </div>

    
    <div class="card-wrap">

        
        <div class="loc-header">
            <div class="loc-badge">
                <div class="loc-dot"></div>
                <span>Lokasi Patroli Aktif</span>
            </div>
            <div class="loc-name"><?php echo e($location->name); ?></div>
            <div class="loc-meta">
                <div class="loc-meta-item">
                    <svg viewBox="0 0 16 16" fill="none" stroke="#93c5fd" stroke-width="1.5">
                        <path d="M8 2C5.8 2 4 3.8 4 6c0 3 4 8 4 8s4-5 4-8c0-2.2-1.8-4-4-4zm0 5.5A1.5 1.5 0 118 5a1.5 1.5 0 010 3z"/>
                    </svg>
                    <span>Pos #<?php echo e(str_pad($location->id, 3, '0', STR_PAD_LEFT)); ?></span>
                </div>
                <div class="loc-meta-item">
                    <svg viewBox="0 0 16 16" fill="none" stroke="#93c5fd" stroke-width="1.5">
                        <rect x="2" y="3" width="12" height="10" rx="1.5"/>
                        <path d="M5 3V2M11 3V2M2 7h12"/>
                    </svg>
                    <span><?php echo e(now()->format('d M Y')); ?></span>
                </div>
            </div>
        </div>
        <div class="loc-header-accent"></div>

        
        <div class="qr-section">
            <div class="qr-frame-wrap">
                <div class="qr-corner tl"></div>
                <div class="qr-corner br"></div>
                <div class="qr-frame">
                    <?php
                        $png = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')
                                ->size(150)
                                ->margin(0)
                                ->errorCorrection('H')
                                ->generate($location->qr_content);
                    ?>
                    <img src="data:image/png;base64,<?php echo e(base64_encode($png)); ?>" alt="QR <?php echo e($location->name); ?>">
                </div>
            </div>
            <div class="qr-info">
                <div class="qr-info-title">Scan untuk buat laporan patroli</div>
                <div class="qr-info-desc">Arahkan kamera ponsel ke QR Code ini untuk memulai pengisian checksheet digital di lokasi ini.</div>
                <div class="scan-cta">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                        <rect x="3" y="14" width="7" height="7"/>
                        <path d="M14 14h3v3M17 14h3M17 17v3"/>
                    </svg>
                    Scan QR Code
                </div>
            </div>
        </div>

        
        <div class="steps-row">
            <div class="step-item">
                <div class="step-num">1</div>
                <div class="step-label">Buka kamera ponsel</div>
            </div>
            <div class="step-item">
                <div class="step-num">2</div>
                <div class="step-label">Arahkan ke QR Code</div>
            </div>
            <div class="step-item">
                <div class="step-num">3</div>
                <div class="step-label">Isi laporan patroli</div>
            </div>
            <div class="step-item">
                <div class="step-num">4</div>
                <div class="step-label">Submit &amp; selesai</div>
            </div>
        </div>

        
        <div class="card-foot">
            <div class="uuid"><?php echo e($location->uuid); ?></div>
            <div class="status-indicator">
                <div class="status-dot"></div>
                <div class="status-text">Aktif</div>
            </div>
        </div>

    </div>

    
    <div class="page-footer">
        <div class="footer-brand">
            <div class="footer-brand-dot"></div>
            <div class="footer-brand-name">Checksheet Digital Patroli</div>
        </div>
        <div class="footer-meta">QR unik per lokasi &middot; Tempel di area yang mudah dijangkau petugas &middot; Dicetak: <?php echo e(now()->format('d F Y, H:i')); ?> WIB</div>
    </div>

</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

</body>
</html><?php /**PATH /var/www/html/resources/views/exports/location-qr.blade.php ENDPATH**/ ?>