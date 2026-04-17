<x-filament-panels::page>

<script>
    document.addEventListener('livewire:initialized', function () {
        Livewire.on('open-url', function (data) {
            window.open(data.url ?? data[0]?.url ?? data, '_blank');
        });
    });
</script>

<style>
    /* ── Page entrance animation ── */
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(10px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .cs-panel   { animation: fadeUp .3s ease both; }
    .cs-panel:nth-child(1) { animation-delay: .05s; }
    .cs-panel:nth-child(2) { animation-delay: .10s; }
    .cs-panel:nth-child(3) { animation-delay: .15s; }
    .cs-panel:nth-child(4) { animation-delay: .20s; }

    /* ── Top hero bar ── */
    .cs-hero {
        position: relative;
        overflow: hidden;
        border-radius: 16px;
        background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 55%, #0f4c81 100%);
        padding: 28px 32px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
    }
    .cs-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background:
            radial-gradient(ellipse 60% 80% at 80% 50%, rgba(56,189,248,.12) 0%, transparent 60%),
            radial-gradient(ellipse 40% 60% at 10% 80%, rgba(99,102,241,.10) 0%, transparent 55%);
        pointer-events: none;
    }
    .cs-hero-grid {
        position: absolute;
        inset: 0;
        background-image:
            linear-gradient(rgba(255,255,255,.04) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255,255,255,.04) 1px, transparent 1px);
        background-size: 32px 32px;
        pointer-events: none;
    }
    .cs-hero-title {
        font-size: 22px;
        font-weight: 700;
        color: #f8fafc;
        letter-spacing: -.4px;
        position: relative;
    }
    .cs-hero-sub {
        font-size: 13px;
        color: rgba(148,163,184,1);
        margin-top: 3px;
        position: relative;
    }
    .cs-hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(255,255,255,.08);
        border: 1px solid rgba(255,255,255,.14);
        border-radius: 99px;
        padding: 6px 14px;
        font-size: 13px;
        font-weight: 600;
        color: #e2e8f0;
        backdrop-filter: blur(8px);
        position: relative;
        white-space: nowrap;
    }
    .cs-hero-badge-dot {
        width: 7px; height: 7px;
        border-radius: 50%;
        background: #34d399;
        box-shadow: 0 0 0 3px rgba(52,211,153,.25);
        animation: pulse-dot 2s infinite;
    }
    @keyframes pulse-dot {
        0%,100% { box-shadow: 0 0 0 3px rgba(52,211,153,.25); }
        50%      { box-shadow: 0 0 0 6px rgba(52,211,153,.10); }
    }

    /* ── Filter card ── */
    .cs-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,.06), 0 1px 2px rgba(0,0,0,.04);
        transition: box-shadow .2s;
    }
    .dark .cs-card {
        background: #1e293b;
        border-color: rgba(255,255,255,.08);
        box-shadow: 0 1px 3px rgba(0,0,0,.3);
    }
    .cs-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 20px;
        border-bottom: 1px solid #f1f5f9;
        background: #f8fafc;
    }
    .dark .cs-card-header {
        background: rgba(255,255,255,.03);
        border-bottom-color: rgba(255,255,255,.06);
    }
    .cs-card-header-label {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        font-weight: 600;
        color: #374151;
    }
    .dark .cs-card-header-label { color: #e2e8f0; }
    .cs-card-header-label svg {
        width: 15px; height: 15px;
        color: #6b7280;
    }
    .dark .cs-card-header-label svg { color: #94a3b8; }
    .cs-card-body { padding: 20px; }

    /* ── Action buttons ── */
    .cs-btn {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 9px 18px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        border: none;
        transition: transform .15s, filter .15s, box-shadow .15s;
        white-space: nowrap;
    }
    .cs-btn:hover  { transform: translateY(-1px); filter: brightness(1.08); }
    .cs-btn:active { transform: translateY(0px);  filter: brightness(.96); }
    .cs-btn svg { width: 15px; height: 15px; flex-shrink: 0; }
    .cs-btn:disabled { opacity: .55; cursor: not-allowed; transform: none; filter: none; }

    .cs-btn-blue {
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        color: #fff;
        box-shadow: 0 2px 8px rgba(37,99,235,.35);
    }
    .cs-btn-red {
        background: linear-gradient(135deg, #dc2626, #b91c1c);
        color: #fff;
        box-shadow: 0 2px 8px rgba(220,38,38,.30);
    }
    .cs-btn-emerald {
        background: linear-gradient(135deg, #059669, #047857);
        color: #fff;
        box-shadow: 0 2px 8px rgba(5,150,105,.30);
    }

    /* ── Stat cards ── */
    .cs-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 14px;
        margin-bottom: 20px;
    }
    @media (max-width: 640px) { .cs-stats { grid-template-columns: 1fr; } }

    .cs-stat {
        border-radius: 14px;
        padding: 18px 20px;
        position: relative;
        overflow: hidden;
    }
    .cs-stat::after {
        content: '';
        position: absolute;
        top: -24px; right: -24px;
        width: 80px; height: 80px;
        border-radius: 50%;
        opacity: .12;
    }
    .cs-stat-total {
        background: #fff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(0,0,0,.06);
    }
    .dark .cs-stat-total {
        background: #1e293b;
        border-color: rgba(255,255,255,.08);
    }
    .cs-stat-total::after { background: #64748b; }

    .cs-stat-done {
        background: linear-gradient(135deg, #ecfdf5, #d1fae5);
        border: 1px solid #a7f3d0;
    }
    .dark .cs-stat-done {
        background: rgba(16,185,129,.12);
        border-color: rgba(16,185,129,.25);
    }
    .cs-stat-done::after { background: #10b981; }

    .cs-stat-pending {
        background: linear-gradient(135deg, #fffbeb, #fef3c7);
        border: 1px solid #fcd34d;
    }
    .dark .cs-stat-pending {
        background: rgba(245,158,11,.10);
        border-color: rgba(245,158,11,.25);
    }
    .cs-stat-pending::after { background: #f59e0b; }

    .cs-stat-icon {
        width: 36px; height: 36px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        margin-bottom: 12px;
    }
    .cs-stat-icon svg { width: 18px; height: 18px; }

    .cs-stat-total .cs-stat-icon  { background: #f1f5f9; color: #475569; }
    .dark .cs-stat-total .cs-stat-icon { background: rgba(255,255,255,.08); color: #94a3b8; }
    .cs-stat-done .cs-stat-icon    { background: rgba(16,185,129,.18); color: #059669; }
    .cs-stat-pending .cs-stat-icon { background: rgba(245,158,11,.18); color: #d97706; }

    .cs-stat-label {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: #9ca3af;
        margin-bottom: 2px;
    }
    .cs-stat-done .cs-stat-label    { color: #059669; }
    .dark .cs-stat-done .cs-stat-label { color: #34d399; }
    .cs-stat-pending .cs-stat-label  { color: #d97706; }
    .dark .cs-stat-pending .cs-stat-label { color: #fbbf24; }

    .cs-stat-value {
        font-size: 32px;
        font-weight: 800;
        letter-spacing: -1px;
        line-height: 1;
        color: #0f172a;
    }
    .dark .cs-stat-value { color: #f1f5f9; }
    .cs-stat-done .cs-stat-value    { color: #065f46; }
    .dark .cs-stat-done .cs-stat-value { color: #ecfdf5; }
    .cs-stat-pending .cs-stat-value  { color: #78350f; }
    .dark .cs-stat-pending .cs-stat-value { color: #fffbeb; }

    .cs-stat-meta {
        font-size: 12px;
        color: #9ca3af;
        margin-top: 4px;
    }
    .cs-stat-done .cs-stat-meta    { color: #6ee7b7; }
    .dark .cs-stat-done .cs-stat-meta { color: #6ee7b7; }
    .cs-stat-pending .cs-stat-meta  { color: #fcd34d; }
    .dark .cs-stat-pending .cs-stat-meta { color: #fcd34d; }

    /* Progress bar */
    .cs-progress-wrap {
        margin-top: 10px;
        height: 4px;
        border-radius: 99px;
        background: rgba(0,0,0,.08);
        overflow: hidden;
    }
    .dark .cs-progress-wrap { background: rgba(255,255,255,.10); }
    .cs-progress-bar {
        height: 100%;
        border-radius: 99px;
        background: linear-gradient(90deg, #10b981, #34d399);
        transition: width .6s cubic-bezier(.4,0,.2,1);
    }

    /* ── Table ── */
    .cs-table-wrap { overflow-x: auto; }

    .cs-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }
    .cs-table thead tr {
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
    }
    .dark .cs-table thead tr {
        background: rgba(255,255,255,.03);
        border-bottom-color: rgba(255,255,255,.06);
    }
    .cs-table th {
        padding: 11px 16px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: #6b7280;
        white-space: nowrap;
        text-align: left;
    }
    .dark .cs-table th { color: #64748b; }
    .cs-table th.tc { text-align: center; }

    .cs-table td {
        padding: 12px 16px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
        color: #374151;
    }
    .dark .cs-table td {
        color: #cbd5e1;
        border-bottom-color: rgba(255,255,255,.04);
    }
    .cs-table td.tc { text-align: center; }
    .cs-table tbody tr { transition: background .15s; }
    .cs-table tbody tr:hover td { background: #f8fafc; }
    .dark .cs-table tbody tr:hover td { background: rgba(255,255,255,.03); }
    .cs-table tbody tr:last-child td { border-bottom: none; }

    /* Row number */
    .cs-rownum {
        width: 28px; height: 28px;
        border-radius: 8px;
        background: #f1f5f9;
        display: flex; align-items: center; justify-content: center;
        font-size: 11px;
        font-weight: 700;
        color: #9ca3af;
        margin: auto;
    }
    .dark .cs-rownum { background: rgba(255,255,255,.07); color: #475569; }

    /* Date cell */
    .cs-date-main { font-size: 13px; font-weight: 600; color: #1e293b; }
    .dark .cs-date-main { color: #e2e8f0; }
    .cs-date-rel  { font-size: 11px; color: #94a3b8; margin-top: 2px; }

    /* Shift badge */
    .cs-shift {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        border-radius: 99px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .02em;
    }
    .cs-shift-1 { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
    .cs-shift-2 { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
    .cs-shift-3 { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
    .cs-shift-x { background: #f9fafb; color: #6b7280; border: 1px solid #e5e7eb; }
    .dark .cs-shift-1 { background: rgba(37,99,235,.18); color: #93c5fd; border-color: rgba(37,99,235,.3); }
    .dark .cs-shift-2 { background: rgba(22,163,74,.15); color: #86efac; border-color: rgba(22,163,74,.3); }
    .dark .cs-shift-3 { background: rgba(180,83,9,.18); color: #fcd34d; border-color: rgba(180,83,9,.3); }

    /* Group chip */
    .cs-group {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 12px;
        color: #475569;
        font-weight: 500;
    }
    .dark .cs-group { color: #94a3b8; }
    .cs-group-dot {
        width: 6px; height: 6px;
        border-radius: 50%;
        background: #94a3b8;
        flex-shrink: 0;
    }

    /* Time */
    .cs-time {
        font-family: 'SF Mono', 'Fira Code', 'Cascadia Code', monospace;
        font-size: 13px;
        font-weight: 600;
        color: #0f172a;
        background: #f1f5f9;
        border-radius: 6px;
        padding: 3px 8px;
        display: inline-block;
    }
    .dark .cs-time { background: rgba(255,255,255,.07); color: #e2e8f0; }

    /* Officer */
    .cs-officer { display: flex; align-items: center; gap: 10px; }
    .cs-avatar {
        width: 32px; height: 32px;
        border-radius: 10px;
        background: linear-gradient(135deg, #2563eb, #7c3aed);
        display: flex; align-items: center; justify-content: center;
        font-size: 11px;
        font-weight: 700;
        color: #fff;
        flex-shrink: 0;
        letter-spacing: .5px;
    }
    .cs-officer-name {
        font-size: 13px;
        font-weight: 500;
        color: #1e293b;
    }
    .dark .cs-officer-name { color: #e2e8f0; }

    /* Signature cell */
    .cs-sig-img {
        height: 38px;
        width: 90px;
        object-fit: contain;
        border-radius: 6px;
        border: 1px solid #e2e8f0;
        background: #fff;
        display: block;
        margin: auto;
    }
    .dark .cs-sig-img { border-color: rgba(255,255,255,.1); background: rgba(255,255,255,.05); }

    .cs-sig-pending {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 99px;
        background: #fffbeb;
        border: 1px solid #fde68a;
        color: #92400e;
        font-size: 11px;
        font-weight: 600;
    }
    .dark .cs-sig-pending {
        background: rgba(245,158,11,.10);
        border-color: rgba(245,158,11,.25);
        color: #fcd34d;
    }
    .cs-sig-pending svg { width: 11px; height: 11px; }

    /* Empty state */
    .cs-empty {
        padding: 56px 24px;
        text-align: center;
    }
    .cs-empty-icon {
        width: 56px; height: 56px;
        border-radius: 16px;
        background: #f1f5f9;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 14px;
    }
    .dark .cs-empty-icon { background: rgba(255,255,255,.06); }
    .cs-empty-icon svg { width: 26px; height: 26px; color: #94a3b8; }
    .cs-empty-title { font-size: 14px; font-weight: 600; color: #374151; }
    .dark .cs-empty-title { color: #94a3b8; }
    .cs-empty-sub { font-size: 12px; color: #9ca3af; margin-top: 4px; }

    /* Total chip in actions row */
    .cs-total-chip {
        margin-left: auto;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        color: #6b7280;
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 8px 14px;
    }
    .dark .cs-total-chip {
        background: rgba(255,255,255,.04);
        border-color: rgba(255,255,255,.08);
        color: #64748b;
    }
    .cs-total-chip strong { font-weight: 700; color: #1e293b; }
    .dark .cs-total-chip strong { color: #e2e8f0; }

    /* Table record badge */
    .cs-record-badge {
        font-size: 12px;
        font-weight: 600;
        padding: 3px 12px;
        border-radius: 99px;
        background: #eff6ff;
        color: #1d4ed8;
        border: 1px solid #bfdbfe;
    }
    .dark .cs-record-badge {
        background: rgba(37,99,235,.15);
        color: #93c5fd;
        border-color: rgba(37,99,235,.25);
    }

    /* Spin animation for loading */
    .cs-spin { animation: spin .8s linear infinite; }
    @keyframes spin { to { transform: rotate(360deg); } }
</style>

{{-- ── Hero Bar ─────────────────────────────────────────────────────────────── --}}
<div class="cs-hero cs-panel">
    <div class="cs-hero-grid"></div>
    <div>
        <div class="cs-hero-title">Checksheet Patrol</div>
        <div class="cs-hero-sub">HR Operation · Rekap kegiatan patrol & tanda tangan petugas</div>
    </div>
    <div class="cs-hero-badge">
        <span class="cs-hero-badge-dot"></span>
        {{ now()->translatedFormat('d F Y') }}
    </div>
</div>

{{-- ── Filter Panel ─────────────────────────────────────────────────────────── --}}
<div class="cs-card cs-panel" style="margin-bottom:20px;">
    <div class="cs-card-header">
        <span class="cs-card-header-label">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19
                       a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
            </svg>
            Filter Data
        </span>
    </div>
    <div class="cs-card-body">
        <form wire:submit.prevent>
            {{ $this->form }}
        </form>

        <div style="margin-top:16px; display:flex; flex-wrap:wrap; align-items:center; gap:10px;">

            {{-- Terapkan Filter --}}
            <button
                wire:click="loadData"
                wire:loading.attr="disabled"
                wire:target="loadData"
                type="button"
                class="cs-btn cs-btn-blue">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"
                     wire:loading.remove wire:target="loadData">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z"/>
                </svg>
                <svg class="cs-spin" fill="none" viewBox="0 0 24 24"
                     wire:loading wire:target="loadData">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                </svg>
                Terapkan Filter
            </button>

            {{-- Export PDF --}}
            <button
                wire:click="exportPdf"
                wire:loading.attr="disabled"
                wire:target="exportPdf"
                type="button"
                class="cs-btn cs-btn-red">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"
                     wire:loading.remove wire:target="exportPdf">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586
                           a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <svg class="cs-spin" fill="none" viewBox="0 0 24 24"
                     wire:loading wire:target="exportPdf">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                </svg>
                Export PDF
            </button>

            {{-- Export Excel --}}
            <button
                wire:click="exportExcel"
                wire:loading.attr="disabled"
                wire:target="exportExcel"
                type="button"
                class="cs-btn cs-btn-emerald">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"
                     wire:loading.remove wire:target="exportExcel">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586
                           a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <svg class="cs-spin" fill="none" viewBox="0 0 24 24"
                     wire:loading wire:target="exportExcel">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                </svg>
                Export Excel
            </button>

            {{-- Total chip --}}
            <span class="cs-total-chip">
                <svg style="width:13px;height:13px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586
                           a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Total: <strong>{{ $total }}</strong> data
            </span>
        </div>
    </div>
</div>

{{-- ── Summary Cards ────────────────────────────────────────────────────────── --}}
@php
    $sudahParaf = collect($patrols)->filter(fn($r) => !empty($r['signature']))->count();
    $belumParaf = collect($patrols)->filter(fn($r) =>  empty($r['signature']))->count();
    $pctSelesai = $total > 0 ? round($sudahParaf / $total * 100) : 0;
@endphp

<div class="cs-stats cs-panel">

    {{-- Total --}}
    <div class="cs-stat cs-stat-total">
        <div class="cs-stat-icon">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2
                       M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
        </div>
        <div class="cs-stat-label">Total Patrol</div>
        <div class="cs-stat-value">{{ $total }}</div>
        <div class="cs-stat-meta">periode terpilih</div>
    </div>

    {{-- Sudah Paraf --}}
    <div class="cs-stat cs-stat-done">
        <div class="cs-stat-icon">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div class="cs-stat-label">Sudah Paraf</div>
        <div class="cs-stat-value">{{ $sudahParaf }}</div>
        <div class="cs-stat-meta">{{ $pctSelesai }}% selesai</div>
        <div class="cs-progress-wrap" style="margin-top:12px;">
            <div class="cs-progress-bar" style="width: {{ $pctSelesai }}%;"></div>
        </div>
    </div>

    {{-- Belum Paraf --}}
    <div class="cs-stat cs-stat-pending">
        <div class="cs-stat-icon">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3
                       L13.71 3.86a2 2 0 00-3.42 0z"/>
            </svg>
        </div>
        <div class="cs-stat-label">Belum Paraf</div>
        <div class="cs-stat-value">{{ $belumParaf }}</div>
        <div class="cs-stat-meta">perlu ditindaklanjuti</div>
    </div>

</div>

{{-- ── Table ────────────────────────────────────────────────────────────────── --}}
<div class="cs-card cs-panel">

    <div class="cs-card-header">
        <span class="cs-card-header-label">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2
                       M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            Data Checksheet Patrol
        </span>
        @if ($total > 0)
            <span class="cs-record-badge">{{ $total }} record</span>
        @endif
    </div>

    <div class="cs-table-wrap">
        <table class="cs-table">
            <thead>
                <tr>
                    <th class="tc" style="width:52px;">No</th>
                    <th>Tanggal</th>
                    <th>Shift</th>
                    <th>Group</th>
                    <th class="tc" style="width:90px;">Jam</th>
                    <th>Petugas Patrol</th>
                    <th class="tc" style="width:120px;">Paraf</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($patrols as $i => $row)
                    @php
                        $patrolTime = $row['patrol_time']
                            ? \Carbon\Carbon::parse($row['patrol_time'])
                            : null;
                        $shift     = $row['shift']    ?? null;
                        $user      = $row['user']     ?? null;
                        $employee  = $row['employee'] ?? null;
                        $dept      = $employee['department'] ?? null;
                        $signature = $row['signature'] ?? null;

                        $shiftName = $shift['name'] ?? null;
                        $shiftClass = match(true) {
                            str_contains(strtolower($shiftName ?? ''), '1') => 'cs-shift-1',
                            str_contains(strtolower($shiftName ?? ''), '2') => 'cs-shift-2',
                            str_contains(strtolower($shiftName ?? ''), '3') => 'cs-shift-3',
                            default                                         => 'cs-shift-x',
                        };

                        $nameStr   = $user['name'] ?? '?';
                        $initials  = strtoupper(
                            collect(explode(' ', $nameStr))
                                ->take(2)
                                ->map(fn($w) => $w[0] ?? '')
                                ->implode('')
                        );
                    @endphp

                    <tr>
                        {{-- No --}}
                        <td class="tc">
                            <div class="cs-rownum">{{ $i + 1 }}</div>
                        </td>

                        {{-- Tanggal --}}
                        <td>
                            <div class="cs-date-main">
                                {{ $patrolTime?->translatedFormat('d F Y') ?? '—' }}
                            </div>
                            @if ($patrolTime)
                                <div class="cs-date-rel">{{ $patrolTime->diffForHumans() }}</div>
                            @endif
                        </td>

                        {{-- Shift --}}
                        <td>
                            @if ($shiftName)
                                <span class="cs-shift {{ $shiftClass }}">{{ $shiftName }}</span>
                            @else
                                <span style="color:#d1d5db;">—</span>
                            @endif
                        </td>

                        {{-- Group --}}
                        <td>
                            <span class="cs-group">
                                <span class="cs-group-dot"></span>
                                {{ $dept['name'] ?? '—' }}
                            </span>
                        </td>

                        {{-- Jam --}}
                        <td class="tc">
                            @if ($patrolTime)
                                <span class="cs-time">{{ $patrolTime->format('H:i') }}</span>
                            @else
                                <span style="color:#d1d5db;">—</span>
                            @endif
                        </td>

                        {{-- Petugas --}}
                        <td>
                            <div class="cs-officer">
                                <div class="cs-avatar">{{ $initials }}</div>
                                <span class="cs-officer-name">{{ $nameStr }}</span>
                            </div>
                        </td>

                        {{-- Paraf --}}
                        <td class="tc">
                            @if ($signature && str_starts_with($signature, 'data:image'))
                                <img src="{{ $signature }}" alt="Paraf" class="cs-sig-img">
                            @elseif ($signature)
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($signature) }}"
                                     alt="Paraf" class="cs-sig-img">
                            @else
                                <span class="cs-sig-pending">
                                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94
                                               a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                                    </svg>
                                    Belum
                                </span>
                            @endif
                        </td>
                    </tr>

                @empty
                    <tr>
                        <td colspan="7">
                            <div class="cs-empty">
                                <div class="cs-empty-icon">
                                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586
                                               a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <p class="cs-empty-title">Tidak ada data</p>
                                <p class="cs-empty-sub">
                                    Silakan pilih filter yang sesuai lalu klik Terapkan Filter
                                </p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>

</x-filament-panels::page>