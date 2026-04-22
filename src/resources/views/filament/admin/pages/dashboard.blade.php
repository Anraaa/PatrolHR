<x-filament-panels::page @refresh-component="$refresh">
    @php
        $data = $this->getData();

        // ── Bangun calendarData ──────────────────────────────────────────────
        $calendarData = [];
        $picColors    = [];
        $colorPalette = ['sky', 'emerald', 'violet', 'amber', 'rose', 'teal', 'indigo', 'orange'];
        $colorIdx     = 0;

        foreach ($data['table_data'] as $row) {
            $userName = $row['user_name'];
            if (!isset($picColors[$userName])) {
                $picColors[$userName] = $colorIdx++ % count($colorPalette);
            }

            for ($day = 1; $day <= $data['days_in_month']; $day++) {
                $dayInfo = $row['daily_data'][$day] ?? null;
                if (!$dayInfo) continue;

                if (!isset($calendarData[$day][$userName])) {
                    $calendarData[$day][$userName] = [
                        'patrol_count'   => 0,
                        'missed_count'   => 0,
                        'total_assigned' => 0,
                        'color_index'    => $picColors[$userName],
                    ];
                }

                foreach ($data['shifts'] as $shift) {
                    $status = $dayInfo['shifts_status'][$shift->id] ?? -1;
                    if ($status === 1) {
                        $calendarData[$day][$userName]['patrol_count']++;
                        $calendarData[$day][$userName]['total_assigned']++;
                    } elseif ($status === 0) {
                        $calendarData[$day][$userName]['missed_count']++;
                        $calendarData[$day][$userName]['total_assigned']++;
                    }
                }
            }
        }

        // Hitung summary stats bulan ini
        $totalPatrolMonth  = 0;
        $totalMissedMonth  = 0;
        $totalDaysActive   = 0;
        foreach ($calendarData as $day => $pics) {
            $hasAny = false;
            foreach ($pics as $picData) {
                $totalPatrolMonth += $picData['patrol_count'];
                $totalMissedMonth += $picData['missed_count'];
                if ($picData['patrol_count'] > 0) $hasAny = true;
            }
            if ($hasAny) $totalDaysActive++;
        }
        $totalAssigned = $totalPatrolMonth + $totalMissedMonth;
        $ratePercent   = $totalAssigned > 0 ? round(($totalPatrolMonth / $totalAssigned) * 100) : 0;

        // Kalender positioning
        $firstDayOfMonth = \Carbon\Carbon::create($data['year'], $data['month'], 1);
        $startBlank      = $firstDayOfMonth->dayOfWeek; // 0=Min
        $dayNames        = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];

        // Token warna per palette key
        $badgeBg = [
            'sky'     => ['bg' => 'bg-sky-100 dark:bg-sky-900/40',       'text' => 'text-sky-700 dark:text-sky-300',     'dot' => 'bg-sky-500',     'border' => 'border-sky-300 dark:border-sky-600', 'gradient' => 'from-sky-400 to-sky-600', 'header' => 'from-sky-400 via-sky-500 to-cyan-500'],
            'emerald' => ['bg' => 'bg-emerald-100 dark:bg-emerald-900/40','text' => 'text-emerald-700 dark:text-emerald-300','dot' => 'bg-emerald-500','border' => 'border-emerald-300 dark:border-emerald-600', 'gradient' => 'from-emerald-400 to-emerald-600', 'header' => 'from-emerald-400 via-emerald-500 to-teal-500'],
            'violet'  => ['bg' => 'bg-violet-100 dark:bg-violet-900/40', 'text' => 'text-violet-700 dark:text-violet-300','dot' => 'bg-violet-500', 'border' => 'border-violet-300 dark:border-violet-600', 'gradient' => 'from-violet-400 to-violet-600', 'header' => 'from-violet-400 via-violet-500 to-purple-600'],
            'amber'   => ['bg' => 'bg-amber-100 dark:bg-amber-900/40',   'text' => 'text-amber-700 dark:text-amber-300', 'dot' => 'bg-amber-500',  'border' => 'border-amber-300 dark:border-amber-600', 'gradient' => 'from-amber-400 to-amber-600', 'header' => 'from-amber-400 via-amber-500 to-orange-500'],
            'rose'    => ['bg' => 'bg-rose-100 dark:bg-rose-900/40',     'text' => 'text-rose-700 dark:text-rose-300',   'dot' => 'bg-rose-500',   'border' => 'border-rose-300 dark:border-rose-600', 'gradient' => 'from-rose-400 to-rose-600', 'header' => 'from-rose-400 via-rose-500 to-pink-600'],
            'teal'    => ['bg' => 'bg-teal-100 dark:bg-teal-900/40',     'text' => 'text-teal-700 dark:text-teal-300',   'dot' => 'bg-teal-500',   'border' => 'border-teal-300 dark:border-teal-600', 'gradient' => 'from-teal-400 to-teal-600', 'header' => 'from-teal-400 via-teal-500 to-cyan-600'],
            'indigo'  => ['bg' => 'bg-indigo-100 dark:bg-indigo-900/40', 'text' => 'text-indigo-700 dark:text-indigo-300','dot' => 'bg-indigo-500', 'border' => 'border-indigo-300 dark:border-indigo-600', 'gradient' => 'from-indigo-400 to-indigo-600', 'header' => 'from-indigo-400 via-indigo-500 to-blue-600'],
            'orange'  => ['bg' => 'bg-orange-100 dark:bg-orange-900/40', 'text' => 'text-orange-700 dark:text-orange-300','dot' => 'bg-orange-500', 'border' => 'border-orange-300 dark:border-orange-600', 'gradient' => 'from-orange-400 to-orange-600', 'header' => 'from-orange-400 via-orange-500 to-red-500'],
        ];
        $badgeKeys = array_keys($badgeBg);

        $avatarSolid = [
            'sky'     => 'bg-gradient-to-br from-sky-400 to-sky-600',
            'emerald' => 'bg-gradient-to-br from-emerald-400 to-emerald-600',
            'violet'  => 'bg-gradient-to-br from-violet-400 to-violet-600',
            'amber'   => 'bg-gradient-to-br from-amber-400 to-amber-600',
            'rose'    => 'bg-gradient-to-br from-rose-400 to-rose-600',
            'teal'    => 'bg-gradient-to-br from-teal-400 to-teal-600',
            'indigo'  => 'bg-gradient-to-br from-indigo-400 to-indigo-600',
            'orange'  => 'bg-gradient-to-br from-orange-400 to-orange-600',
        ];

        $monthNameId = [
            1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',
            7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'
        ];
    @endphp

    {{-- ── Inline Styles ─────────────────────────────────────────────────── --}}
    <style>
        /* ===== COLORFUL THEME OVERRIDES ===== */

        /* Rainbow shimmer animation */
        @keyframes rainbowShift {
            0%   { background-position: 0% 50%; }
            50%  { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        @keyframes floatBubble {
            0%, 100% { transform: translateY(0px) rotate(0deg); opacity: 0.6; }
            33%       { transform: translateY(-12px) rotate(5deg); opacity: 0.9; }
            66%       { transform: translateY(-6px) rotate(-3deg); opacity: 0.75; }
        }

        @keyframes pulseGlow {
            0%, 100% { box-shadow: 0 0 0 0 rgba(139, 92, 246, 0.4); }
            50%       { box-shadow: 0 0 20px 6px rgba(139, 92, 246, 0.15); }
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideDown {
            from { opacity: 1; transform: translateY(0); }
            to   { opacity: 0; transform: translateY(24px); }
        }

        @keyframes fadeInScale {
            from { opacity: 0; transform: scale(0.92); }
            to   { opacity: 1; transform: scale(1); }
        }

        @keyframes shimmer {
            0%   { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }

        @keyframes softPulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50%       { opacity: 0.85; transform: scale(1.04); }
        }

        @keyframes spin-slow {
            from { transform: rotate(0deg); }
            to   { transform: rotate(360deg); }
        }

        /* ===== HERO HEADER ===== */
        .hero-header {
            background: linear-gradient(135deg,
                #667eea 0%, #764ba2 20%, #f093fb 40%,
                #4facfe 60%, #00f2fe 80%, #43e97b 100%);
            background-size: 300% 300%;
            animation: rainbowShift 8s ease infinite;
            position: relative;
            overflow: hidden;
        }

        .hero-header::before {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(0,0,0,0.15);
        }

        /* Floating bubbles decoration */
        .bubble {
            position: absolute;
            border-radius: 50%;
            background: rgba(255,255,255,0.12);
            animation: floatBubble ease-in-out infinite;
        }

        /* ===== STAT CARDS ===== */
        .stat-card-colorful {
            position: relative;
            overflow: hidden;
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
            border: none !important;
        }

        .stat-card-colorful::after {
            content: '';
            position: absolute;
            top: -40%;
            right: -40%;
            width: 80%;
            height: 80%;
            border-radius: 50%;
            background: rgba(255,255,255,0.12);
            pointer-events: none;
        }

        .stat-card-colorful:hover {
            transform: translateY(-6px) scale(1.01);
            box-shadow: 0 24px 40px -12px rgba(0,0,0,0.25);
        }

        .stat-card-colorful .stat-icon {
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255,255,255,0.3);
            transition: transform 0.3s ease;
        }

        .stat-card-colorful:hover .stat-icon {
            transform: scale(1.12) rotate(5deg);
        }

        /* ===== CALENDAR ===== */
        .cal-cell {
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }

        .cal-cell:hover {
            background: rgb(239 246 255 / 0.98) !important;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px -6px rgba(99, 102, 241, 0.25);
            z-index: 10;
            position: relative;
        }

        .dark .cal-cell:hover {
            background: rgb(30 58 138 / 0.2) !important;
            box-shadow: 0 8px 20px -6px rgba(99, 102, 241, 0.3);
        }

        .cal-cell.selected {
            box-shadow: inset 0 0 0 2px #818cf8, 0 6px 20px -6px rgba(99, 102, 241, 0.4);
            background: rgb(238 242 255 / 0.98) !important;
            transform: scale(0.99);
            z-index: 10;
        }

        .dark .cal-cell.selected {
            background: rgb(30 27 75 / 0.3) !important;
            box-shadow: inset 0 0 0 2px #818cf8, 0 6px 20px -6px rgba(129, 140, 248, 0.4);
        }

        /* ===== COLORFUL DAY HEADERS ===== */
        .day-header-rainbow {
            background: linear-gradient(135deg,
                #f9a8d4 0%, #c084fc 14.28%,
                #818cf8 28.56%, #67e8f9 42.84%,
                #6ee7b7 57.12%, #fcd34d 71.4%,
                #fb7185 85.68%, #f9a8d4 100%);
        }

        /* ===== TABLE ROWS ===== */
        .colorful-row:hover td {
            background: linear-gradient(90deg, rgba(99,102,241,0.04) 0%, rgba(168,85,247,0.04) 100%) !important;
        }

        /* ===== LOCATION CARDS ===== */
        .loc-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .loc-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            background: linear-gradient(90deg, #667eea, #764ba2, #f093fb);
            opacity: 0;
            transition: opacity 0.3s;
        }

        .loc-card:hover::before { opacity: 1; }

        .loc-card:hover {
            transform: translateY(-4px) scale(1.005);
            box-shadow: 0 20px 40px -12px rgba(99,102,241,0.25);
        }

        /* ===== PROGRESS BARS ===== */
        .progress-bar {
            transition: width 0.9s cubic-bezier(0.65, 0, 0.35, 1);
        }

        .progress-rainbow {
            background: linear-gradient(90deg, #667eea, #764ba2, #f093fb, #4facfe);
            background-size: 200% auto;
            animation: shimmer 3s linear infinite;
        }

        /* ===== BADGE CHIPS ===== */
        .pic-badge {
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
        }

        .pic-badge:hover {
            transform: translateY(-3px) scale(1.03);
            box-shadow: 0 8px 20px -6px rgba(0,0,0,0.2);
        }

        .detail-chip {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
        }

        .detail-chip:hover {
            transform: translateY(-3px) scale(1.01);
            box-shadow: 0 16px 30px -10px rgba(0,0,0,0.2);
        }

        /* ===== ANIMATIONS ===== */
        .slide-up   { animation: slideUp 0.35s cubic-bezier(0.34, 1.2, 0.64, 1) forwards; }
        .slide-down { animation: slideDown 0.25s ease-in forwards; }
        .fade-scale-in { animation: fadeInScale 0.25s cubic-bezier(0.34, 1.2, 0.64, 1) forwards; }

        .today-badge {
            animation: softPulse 2s ease-in-out infinite;
            background: linear-gradient(135deg, #667eea, #764ba2) !important;
            box-shadow: 0 0 0 3px rgba(129,140,248,0.35), 0 4px 12px rgba(99,102,241,0.4);
        }

        /* ===== SCROLLBAR ===== */
        .custom-scroll::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scroll::-webkit-scrollbar-track { background: rgba(99,102,241,0.08); border-radius: 10px; }
        .custom-scroll::-webkit-scrollbar-thumb { background: rgba(99,102,241,0.4); border-radius: 10px; }
        .custom-scroll::-webkit-scrollbar-thumb:hover { background: rgba(99,102,241,0.65); }

        /* ===== SECTION HEADERS ===== */
        .section-header-gradient {
            background: linear-gradient(135deg, rgba(99,102,241,0.08) 0%, rgba(168,85,247,0.06) 50%, rgba(236,72,153,0.04) 100%);
        }

        .dark .section-header-gradient {
            background: linear-gradient(135deg, rgba(99,102,241,0.15) 0%, rgba(168,85,247,0.1) 50%, rgba(236,72,153,0.08) 100%);
        }

        /* ===== CALENDAR SECTION HEADER ===== */
        .calendar-header-bg {
            background: linear-gradient(135deg, #eef2ff 0%, #f5f3ff 50%, #fdf2f8 100%);
        }

        .dark .calendar-header-bg {
            background: linear-gradient(135deg, rgba(49,46,129,0.3) 0%, rgba(76,29,149,0.2) 50%, rgba(131,24,67,0.15) 100%);
        }

        /* ===== WEEKEND CELLS ===== */
        .weekend-cell {
            background: linear-gradient(135deg, rgba(253,242,248,0.8) 0%, rgba(245,243,255,0.6) 100%) !important;
        }

        .dark .weekend-cell {
            background: linear-gradient(135deg, rgba(131,24,67,0.1) 0%, rgba(76,29,149,0.08) 100%) !important;
        }

        /* ===== FOCUS ===== */
        .cal-cell:focus-visible {
            outline: 2px solid #818cf8;
            outline-offset: 2px;
            border-radius: 0.5rem;
        }
    </style>

    <div class="space-y-6 custom-scroll">

        {{-- ── HERO HEADER ─────────────────────────────────────────────── --}}
        <div class="hero-header rounded-3xl p-6 lg:p-8 shadow-2xl">
            {{-- Decorative bubbles --}}
            <div class="bubble w-24 h-24 top-[-20px] right-[10%]" style="animation-duration:6s; animation-delay:0s;"></div>
            <div class="bubble w-16 h-16 bottom-[-10px] left-[15%]" style="animation-duration:8s; animation-delay:1s;"></div>
            <div class="bubble w-10 h-10 top-[30%] left-[5%]" style="animation-duration:5s; animation-delay:0.5s;"></div>
            <div class="bubble w-20 h-20 bottom-[10%] right-[20%]" style="animation-duration:7s; animation-delay:2s;"></div>
            <div class="bubble w-8 h-8 top-[10%] right-[40%]" style="animation-duration:9s; animation-delay:1.5s;"></div>

            <div class="relative z-10 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="space-y-2">
                    <div class="flex items-center gap-4">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white/20 backdrop-blur-sm border border-white/30 shadow-lg shadow-black/20">
                            <svg class="h-7 w-7 text-white drop-shadow" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-3xl font-black text-white tracking-tight drop-shadow-md lg:text-4xl">
                                Monitoring Patrol
                            </h1>
                            <p class="text-white/80 text-sm font-medium mt-0.5">
                                Distribusi aktivitas patrol harian
                            </p>
                        </div>
                    </div>

                    {{-- Quick stats in header --}}
                    <div class="flex flex-wrap gap-2 mt-2">
                        <span class="inline-flex items-center gap-1.5 bg-white/20 backdrop-blur-sm border border-white/30 text-white text-xs font-bold px-3 py-1.5 rounded-full shadow">
                            <span class="h-2 w-2 rounded-full bg-emerald-300 animate-pulse"></span>
                            {{ $totalPatrolMonth }} Patrol Selesai
                        </span>
                        <span class="inline-flex items-center gap-1.5 bg-white/20 backdrop-blur-sm border border-white/30 text-white text-xs font-bold px-3 py-1.5 rounded-full shadow">
                            <span class="h-2 w-2 rounded-full bg-rose-300"></span>
                            {{ $totalMissedMonth }} Missed
                        </span>
                        <span class="inline-flex items-center gap-1.5 bg-white/20 backdrop-blur-sm border border-white/30 text-white text-xs font-bold px-3 py-1.5 rounded-full shadow">
                            <span class="h-2 w-2 rounded-full bg-amber-300"></span>
                            {{ $ratePercent }}% Rate
                        </span>
                    </div>
                </div>

                {{-- Month & Year Filter --}}
                <div class="flex items-center gap-2.5">
                    <div class="flex items-center gap-2 rounded-2xl bg-white/20 backdrop-blur-md shadow-lg p-2">
                        <select wire:model.live="selectedMonth"
                            class="rounded-xl border-0 bg-white/90 text-gray-800 px-4 py-2 text-sm font-bold focus:ring-2 focus:ring-white/50 transition-all cursor-pointer shadow-sm">
                            @foreach($this->getMonths() as $num => $name)
                                <option value="{{ $num }}" @selected($num == $data['month'])>{{ $name }}</option>
                            @endforeach
                        </select>
                        <div class="h-6 w-px bg-white/40"></div>
                        <select wire:model.live="selectedYear"
                            class="rounded-xl border-0 bg-white/90 text-gray-800 px-4 py-2 text-sm font-bold focus:ring-2 focus:ring-white/50 transition-all cursor-pointer shadow-sm">
                            @foreach($this->getYears() as $year)
                                <option value="{{ $year }}" @selected($year == $data['year'])>{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── SUMMARY STATS GRID ───────────────────────────────────────── --}}
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">

            {{-- Active Users Card - Blue/Cyan --}}
            <div class="stat-card-colorful rounded-2xl p-5 shadow-xl" style="background: linear-gradient(135deg, #3b82f6 0%, #06b6d4 100%);">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex-1">
                        <p class="text-xs font-bold uppercase tracking-wider text-blue-100 mb-2">User Aktif</p>
                        <div class="flex items-baseline gap-2">
                            <span class="text-4xl font-black text-white">
                                {{ count(array_filter($data['table_data'], fn($row) => !empty(array_filter($row['daily_data'], fn($day) => in_array(1, $day['shifts_status']))))) }}
                            </span>
                            <span class="text-xs font-semibold text-blue-200">dari {{ count($data['users']) }}</span>
                        </div>
                    </div>
                    <div class="stat-icon flex h-12 w-12 items-center justify-center rounded-xl shadow-md">
                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                        </svg>
                    </div>
                </div>
                <p class="text-xs text-blue-200 font-semibold">User yang sudah melakukan patrol</p>
                <div class="mt-3 h-1 w-full rounded-full bg-white/20 overflow-hidden">
                    @php
                        $activeUsers = count(array_filter($data['table_data'], fn($row) => !empty(array_filter($row['daily_data'], fn($day) => in_array(1, $day['shifts_status'])))));
                        $totalUsers = count($data['users']);
                        $activeRate = $totalUsers > 0 ? round(($activeUsers / $totalUsers) * 100) : 0;
                    @endphp
                    <div class="h-full bg-white/60 rounded-full" style="width: {{ $activeRate }}%; transition: width 0.8s ease;"></div>
                </div>
            </div>

            {{-- Total Petugas - Purple/Violet --}}
            <div class="stat-card-colorful rounded-2xl p-5 shadow-xl" style="background: linear-gradient(135deg, #8b5cf6 0%, #a855f7 100%);">
                <p class="text-xs font-bold uppercase tracking-wider text-violet-200 mb-3">Total Petugas</p>
                <div class="flex items-center justify-between">
                    <div>
                        <div class="flex items-baseline gap-2 mb-1">
                            <span class="text-3xl font-black text-white">{{ count($data['users']) }}</span>
                            <span class="text-xs font-semibold text-violet-200">petugas</span>
                        </div>
                        <p class="text-xs font-semibold text-violet-200">Terdaftar aktif</p>
                    </div>
                    <div class="stat-icon flex h-12 w-12 items-center justify-center rounded-xl shadow-md">
                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Titik Patrol - Emerald/Teal --}}
            <div class="stat-card-colorful rounded-2xl p-5 shadow-xl" style="background: linear-gradient(135deg, #10b981 0%, #14b8a6 100%);">
                <p class="text-xs font-bold uppercase tracking-wider text-emerald-100 mb-3">Titik Patrol</p>
                <div class="flex items-center justify-between">
                    <div>
                        <div class="flex items-baseline gap-2 mb-1">
                            <span class="text-3xl font-black text-white">{{ count($data['locations']) }}</span>
                            <span class="text-xs font-semibold text-emerald-200">lokasi</span>
                        </div>
                        <p class="text-xs font-semibold text-emerald-200">Wajib patrol</p>
                    </div>
                    <div class="stat-icon flex h-12 w-12 items-center justify-center rounded-xl shadow-md">
                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Kelengkapan - Rose/Orange --}}
            <div class="stat-card-colorful rounded-2xl p-5 shadow-xl" style="background: linear-gradient(135deg, #f43f5e 0%, #fb923c 100%);">
                <p class="text-xs font-bold uppercase tracking-wider text-rose-100 mb-3">Rata-rata Kelengkapan</p>
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <div class="flex items-baseline gap-2 mb-2">
                            <span class="text-3xl font-black text-white">
                                @php
                                    $avgCompletion = 0;
                                    if (count($data['users']) > 0) {
                                        $totalCompletion = 0;
                                        foreach ($data['table_data'] as $row) {
                                            if ($row['row_span'] > 0) {
                                                $totalCompletion += ($row['locations_patrolled'] / $row['total_locations']) * 100;
                                            }
                                        }
                                        $avgCompletion = round($totalCompletion / count($data['users']));
                                    }
                                @endphp
                                {{ $avgCompletion }}%
                            </span>
                        </div>
                        <div class="w-full h-2 bg-white/25 rounded-full overflow-hidden">
                            <div class="h-full rounded-full bg-white/70" style="width: {{ $avgCompletion }}%; transition: width 0.8s ease;"></div>
                        </div>
                    </div>
                    <div class="stat-icon flex h-12 w-12 items-center justify-center rounded-xl shadow-md ml-3">
                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── PIC Progress Summary Table ──────────────────────────────── --}}
        @php
            $picSummary = [];
            foreach ($data['users'] as $user) {
                $picSummary[$user->id] = [
                    'name' => $user->name,
                    'color_idx' => $picColors[$user->name] ?? 0,
                    'total_locations' => count($data['locations']),
                    'locations_visited' => 0,
                    'total_shifts' => count($data['shifts']),
                    'total_assignments' => count($data['locations']) * count($data['shifts']),
                    'patrols_completed' => 0,
                    'patrols_pending' => 0,
                ];
            }

            foreach ($data['users'] as $user) {
                $locationsVisited = \App\Models\Patrol::where('user_id', $user->id)
                    ->whereBetween('patrol_time', [
                        \Carbon\Carbon::create($data['year'], $data['month'], 1),
                        \Carbon\Carbon::create($data['year'], $data['month'], $data['days_in_month'], 23, 59, 59)
                    ])
                    ->whereNotNull('qr_scanned_at')
                    ->distinct('location_id')
                    ->count('location_id');

                $patrolsCompleted = \App\Models\Patrol::where('user_id', $user->id)
                    ->whereBetween('patrol_time', [
                        \Carbon\Carbon::create($data['year'], $data['month'], 1),
                        \Carbon\Carbon::create($data['year'], $data['month'], $data['days_in_month'], 23, 59, 59)
                    ])
                    ->whereNotNull('qr_scanned_at')
                    ->count();

                $patrolsTotal = \App\Models\Patrol::where('user_id', $user->id)
                    ->whereBetween('patrol_time', [
                        \Carbon\Carbon::create($data['year'], $data['month'], 1),
                        \Carbon\Carbon::create($data['year'], $data['month'], $data['days_in_month'], 23, 59, 59)
                    ])
                    ->count();

                $patrolsPending = $patrolsTotal - $patrolsCompleted;

                $picSummary[$user->id]['locations_visited'] = $locationsVisited;
                $picSummary[$user->id]['patrols_completed'] = $patrolsCompleted;
                $picSummary[$user->id]['patrols_pending'] = $patrolsPending;
                $picSummary[$user->id]['patrols_total'] = $patrolsTotal;
            }
        @endphp

        <div class="overflow-hidden rounded-2xl shadow-xl border-0" style="background: linear-gradient(135deg, #f8faff 0%, #faf5ff 50%, #fff1f8 100%);">
            {{-- Colorful Table Header --}}
            <div class="section-header-gradient px-6 py-4 border-b border-indigo-100 dark:border-indigo-900/30">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl shadow-md" style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);">
                        <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-black text-gray-900 dark:text-white text-base">Ringkasan Petugas Patrol</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Performa individual per bulan</p>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr style="background: linear-gradient(90deg, #eef2ff 0%, #f5f3ff 50%, #fdf2f8 100%);">
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-indigo-700 dark:text-indigo-300 uppercase tracking-wider">
                                <div class="flex items-center gap-2">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg>
                                    Petugas Patrol
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-4 text-center text-xs font-bold text-violet-700 dark:text-violet-300 uppercase tracking-wider">Lokasi</th>
                            <th scope="col" class="px-6 py-4 text-center text-xs font-bold text-emerald-700 dark:text-emerald-300 uppercase tracking-wider">Patrol Selesai</th>
                            <th scope="col" class="px-6 py-4 text-center text-xs font-bold text-amber-700 dark:text-amber-300 uppercase tracking-wider">Tertunda</th>
                            <th scope="col" class="px-6 py-4 text-center text-xs font-bold text-blue-700 dark:text-blue-300 uppercase tracking-wider">Progres Lokasi</th>
                            <th scope="col" class="px-6 py-4 text-center text-xs font-bold text-rose-700 dark:text-rose-300 uppercase tracking-wider">Kelengkapan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-indigo-50 dark:divide-gray-700 bg-white dark:bg-gray-800">
                        @foreach($picSummary as $userId => $pic)
                            @php
                                $colorKey = $badgeKeys[$pic['color_idx'] % count($badgeKeys)];
                                $cs = $badgeBg[$colorKey];
                                $progressPercent = $pic['total_locations'] > 0
                                    ? round(($pic['locations_visited'] / $pic['total_locations']) * 100)
                                    : 0;
                                $completionPercent = $pic['patrols_total'] > 0
                                    ? round(($pic['patrols_completed'] / $pic['patrols_total']) * 100)
                                    : 0;
                                $initials = collect(explode(' ', $pic['name']))->take(2)->map(fn($w) => strtoupper($w[0] ?? ''))->join('');

                                $locPercentage = $pic['total_locations'] > 0 ? ($pic['locations_visited'] / $pic['total_locations']) * 100 : 0;
                                $completionPercentValue = $pic['patrols_total'] > 0 ? ($pic['patrols_completed'] / $pic['patrols_total']) * 100 : 0;
                                $overallPercent = round(($locPercentage + $completionPercentValue) / 2);

                                $statusColor = $overallPercent >= 80 ? 'emerald' : ($overallPercent >= 50 ? 'amber' : 'rose');
                                $statusIcon  = $overallPercent >= 80 ? '✓' : ($overallPercent >= 50 ? '⟳' : '✗');
                                $statusGradient = $overallPercent >= 80
                                    ? 'from-emerald-500 to-teal-500'
                                    : ($overallPercent >= 50 ? 'from-amber-400 to-orange-500' : 'from-rose-500 to-pink-500');
                            @endphp
                            <tr class="colorful-row transition-colors duration-200 hover:bg-indigo-50/40 dark:hover:bg-indigo-900/10">
                                {{-- Nama --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-11 w-11 items-center justify-center rounded-xl {{ $avatarSolid[$colorKey] }} text-black text-sm font-black shadow-lg">
                                            {{ $initials }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-gray-900 dark:text-white">{{ $pic['name'] }}</div>
                                            <div class="text-xs font-semibold {{ $cs['text'] }}">Petugas Patrol</div>
                                        </div>
                                    </div>
                                </td>

                                {{-- Lokasi --}}
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="text-xl font-black {{ $cs['text'] }}">{{ $pic['locations_visited'] }}</div>
                                    <div class="text-xs font-semibold text-gray-400">dari {{ $pic['total_locations'] }}</div>
                                </td>

                                {{-- Selesai --}}
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="text-xl font-black text-emerald-600 dark:text-emerald-400">{{ $pic['patrols_completed'] }}</div>
                                    <div class="text-xs font-semibold text-gray-400">dari {{ $pic['patrols_total'] }}</div>
                                </td>

                                {{-- Tertunda --}}
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="text-xl font-black text-amber-600 dark:text-amber-400">{{ $pic['patrols_pending'] }}</div>
                                    <div class="text-xs font-semibold text-gray-400">belum selesai</div>
                                </td>

                                {{-- Progres bar --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="w-36">
                                        <div class="flex items-center justify-between mb-1.5">
                                            <span class="text-xs font-bold {{ $cs['text'] }}">{{ $progressPercent }}%</span>
                                        </div>
                                        <div class="w-full h-2.5 rounded-full bg-gray-100 dark:bg-gray-700 overflow-hidden shadow-inner">
                                            <div class="h-full rounded-full bg-gradient-to-r {{ $cs['gradient'] }}" style="width: {{ $progressPercent }}%; transition: width 0.9s cubic-bezier(0.65, 0, 0.35, 1);"></div>
                                        </div>
                                    </div>
                                </td>

                                {{-- Kelengkapan badge --}}
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-black text-white shadow-md bg-gradient-to-r {{ $statusGradient }}">
                                        <span>{{ $statusIcon }}</span>
                                        <span>{{ $overallPercent }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="background: linear-gradient(90deg, #eef2ff 0%, #f5f3ff 50%, #fdf2f8 100%);">
                            <td colspan="6" class="px-6 py-4">
                                <div class="flex items-center justify-between text-xs">
                                    <div class="flex items-center gap-4">
                                        <div class="flex items-center gap-2">
                                            <div class="w-3 h-3 rounded-full bg-gradient-to-r from-emerald-500 to-teal-500 shadow-sm"></div>
                                            <span class="text-gray-600 dark:text-gray-400 font-semibold">Selesai (≥80%)</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <div class="w-3 h-3 rounded-full bg-gradient-to-r from-amber-400 to-orange-500 shadow-sm"></div>
                                            <span class="text-gray-600 dark:text-gray-400 font-semibold">Sedang (50-79%)</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <div class="w-3 h-3 rounded-full bg-gradient-to-r from-rose-500 to-pink-500 shadow-sm"></div>
                                            <span class="text-gray-600 dark:text-gray-400 font-semibold">Rendah (&lt;50%)</span>
                                        </div>
                                    </div>
                                    <div class="font-bold text-indigo-600 dark:text-indigo-400">
                                        Total: {{ count($picSummary) }} Petugas
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- ── Location Performance ─────────────────────────────────────── --}}
        @php
            $locationPerformance = [];
            $totalUsers = count($data['users']);

            foreach ($data['locations'] as $location) {
                $usersPatrolled = \App\Models\Patrol::where('location_id', $location->id)
                    ->whereBetween('patrol_time', [
                        \Carbon\Carbon::create($data['year'], $data['month'], 1),
                        \Carbon\Carbon::create($data['year'], $data['month'], $data['days_in_month'], 23, 59, 59)
                    ])
                    ->whereNotNull('qr_scanned_at')
                    ->distinct('user_id')
                    ->count('user_id');

                $totalPatrols = \App\Models\Patrol::where('location_id', $location->id)
                    ->whereBetween('patrol_time', [
                        \Carbon\Carbon::create($data['year'], $data['month'], 1),
                        \Carbon\Carbon::create($data['year'], $data['month'], $data['days_in_month'], 23, 59, 59)
                    ])
                    ->whereNotNull('qr_scanned_at')
                    ->count();

                $performancePercent = $totalUsers > 0 ? round(($usersPatrolled / $totalUsers) * 100) : 0;

                $locationPerformance[$location->id] = [
                    'name' => $location->name,
                    'users_patrolled' => $usersPatrolled,
                    'total_users' => $totalUsers,
                    'total_patrols' => $totalPatrols,
                    'performance_percent' => $performancePercent,
                ];
            }

            usort($locationPerformance, fn($a, $b) => $b['performance_percent'] <=> $a['performance_percent']);

            // Color palette for location cards - cycle through vibrant gradients
            $locGradients = [
                'from-blue-500 to-cyan-400',
                'from-violet-500 to-purple-400',
                'from-emerald-500 to-teal-400',
                'from-rose-500 to-pink-400',
                'from-amber-500 to-orange-400',
                'from-indigo-500 to-blue-400',
                'from-teal-500 to-emerald-400',
                'from-fuchsia-500 to-violet-400',
                'from-sky-500 to-indigo-400',
                'from-orange-500 to-red-400',
                'from-cyan-500 to-sky-400',
                'from-pink-500 to-rose-400',
            ];
            $locBgGradients = [
                'from-blue-50 to-cyan-50',
                'from-violet-50 to-purple-50',
                'from-emerald-50 to-teal-50',
                'from-rose-50 to-pink-50',
                'from-amber-50 to-orange-50',
                'from-indigo-50 to-blue-50',
                'from-teal-50 to-emerald-50',
                'from-fuchsia-50 to-violet-50',
                'from-sky-50 to-indigo-50',
                'from-orange-50 to-red-50',
                'from-cyan-50 to-sky-50',
                'from-pink-50 to-rose-50',
            ];
            $locBorderColors = [
                'border-blue-200', 'border-violet-200', 'border-emerald-200', 'border-rose-200',
                'border-amber-200', 'border-indigo-200', 'border-teal-200', 'border-fuchsia-200',
                'border-sky-200', 'border-orange-200', 'border-cyan-200', 'border-pink-200',
            ];
            $locTextColors = [
                'text-blue-700', 'text-violet-700', 'text-emerald-700', 'text-rose-700',
                'text-amber-700', 'text-indigo-700', 'text-teal-700', 'text-fuchsia-700',
                'text-sky-700', 'text-orange-700', 'text-cyan-700', 'text-pink-700',
            ];
        @endphp

        <div class="overflow-hidden rounded-2xl shadow-xl border border-indigo-100 dark:border-indigo-900/30">
            <div class="section-header-gradient px-6 py-4 border-b border-indigo-100 dark:border-indigo-900/30">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl shadow-md" style="background: linear-gradient(135deg, #10b981 0%, #14b8a6 100%);">
                        <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-black text-gray-900 dark:text-white text-base">Performa Lokasi Patrol</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Diurutkan dari performa tertinggi</p>
                    </div>
                </div>
            </div>

            <div class="p-5" style="background: linear-gradient(135deg, #f8faff 0%, #faf5ff 50%, #fff1f8 100%);">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach($locationPerformance as $locIndex => $location)
                        @php
                            $lci = $locIndex % count($locGradients);
                            $locGrad = $locGradients[$lci];
                            $locBg   = $locBgGradients[$lci];
                            $locBdr  = $locBorderColors[$lci];
                            $locTxt  = $locTextColors[$lci];
                            $remaining = $location['total_users'] - $location['users_patrolled'];
                        @endphp

                        <div class="loc-card group relative flex flex-col rounded-xl border {{ $locBdr }} bg-gradient-to-br {{ $locBg }} dark:bg-gray-800/60 dark:border-gray-700/50 p-4 shadow-md">
                            {{-- Rank badge --}}
                            <div class="absolute top-3 right-3">
                                <span class="inline-flex h-7 w-7 items-center justify-center rounded-full text-xs font-black text-white shadow-md bg-gradient-to-br {{ $locGrad }}">
                                    {{ $locIndex + 1 }}
                                </span>
                            </div>

                            {{-- Title --}}
                            <div class="mb-3 pr-8">
                                <h4 class="font-black text-gray-900 dark:text-white text-sm group-hover:{{ $locTxt }} transition-colors">{{ $location['name'] }}</h4>
                                <p class="text-[10px] font-bold {{ $locTxt }} uppercase tracking-wider mt-0.5">
                                    {{ $location['users_patrolled'] }}/{{ $location['total_users'] }} PIC
                                </p>
                            </div>

                            {{-- Percent --}}
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-2xl font-black bg-gradient-to-r {{ $locGrad }} bg-clip-text text-transparent">
                                    {{ $location['performance_percent'] }}%
                                </span>
                            </div>

                            {{-- Progress --}}
                            <div class="relative w-full h-3 rounded-full bg-white/70 dark:bg-gray-700/50 overflow-hidden mb-4 shadow-inner border border-white/80">
                                <div class="h-full rounded-full bg-gradient-to-r {{ $locGrad }} shadow"
                                     style="width: {{ $location['performance_percent'] }}%; transition: width 1s cubic-bezier(0.4, 0, 0.2, 1);">
                                </div>
                            </div>

                            {{-- Stats --}}
                            <div class="grid grid-cols-3 gap-2">
                                <div class="rounded-lg bg-white/80 dark:bg-gray-800/70 p-2 shadow-sm border border-white/60 text-center">
                                    <p class="text-sm font-black {{ $locTxt }}">{{ $location['users_patrolled'] }}</p>
                                    <p class="text-[9px] font-bold text-gray-400 uppercase mt-0.5">Aktif</p>
                                </div>
                                <div class="rounded-lg bg-white/80 dark:bg-gray-800/70 p-2 shadow-sm border border-white/60 text-center">
                                    <p class="text-sm font-black {{ $locTxt }}">{{ $location['total_patrols'] }}</p>
                                    <p class="text-[9px] font-bold text-gray-400 uppercase mt-0.5">Patrol</p>
                                </div>
                                <div class="rounded-lg bg-white/80 dark:bg-gray-800/70 p-2 shadow-sm border border-white/60 text-center">
                                    <p class="text-sm font-black {{ $remaining > 0 ? 'text-rose-600' : 'text-emerald-600' }}">{{ $remaining }}</p>
                                    <p class="text-[9px] font-bold text-gray-400 uppercase mt-0.5">Sisa</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ── PIC Color Legend ────────────────────────────────────────── --}}
        @if(count($picColors) > 0)
        <div class="overflow-hidden rounded-2xl border border-indigo-100 dark:border-indigo-900/30 shadow-xl">
            <div class="section-header-gradient px-5 py-3 border-b border-indigo-100 dark:border-indigo-900/30">
                <div class="flex items-center gap-2">
                    <div class="flex h-7 w-7 items-center justify-center rounded-lg shadow-sm" style="background: linear-gradient(135deg, #f43f5e, #fb923c);">
                        <svg class="h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-black text-gray-700 dark:text-gray-300">Daftar Petugas</h3>
                    <span class="text-xs font-bold bg-gradient-to-r from-indigo-500 to-violet-500 text-black px-2.5 py-0.5 rounded-full shadow-sm">
                        {{ count($picColors) }} petugas
                    </span>
                </div>
            </div>
            <div class="flex flex-wrap gap-2.5 p-4" style="background: linear-gradient(135deg, #f8faff 0%, #faf5ff 50%, #fff1f8 100%);">
                @foreach($picColors as $picName => $colorIdx)
                    @php
                        $colorKey = $badgeKeys[$colorIdx % count($badgeKeys)];
                        $cs       = $badgeBg[$colorKey];
                        $initials = collect(explode(' ', $picName))->take(2)->map(fn($w) => strtoupper($w[0] ?? ''))->join('');
                    @endphp
                    <div class="pic-badge flex items-center gap-2.5 rounded-xl border {{ $cs['border'] }} {{ $cs['bg'] }} pl-2 pr-4 py-1.5 shadow-md transition-all duration-200">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg {{ $avatarSolid[$colorKey] }} text-black text-[11px] font-black shadow-md">
                            {{ $initials }}
                        </span>
                        <span class="text-xs font-bold {{ $cs['text'] }}">{{ $picName }}</span>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- ── Calendar Grid ───────────────────────────────────────────── --}}
        <div class="overflow-hidden rounded-2xl shadow-xl border border-indigo-100 dark:border-indigo-800/30">

            {{-- Calendar header --}}
            <div class="calendar-header-bg flex flex-col gap-3 border-b border-indigo-100 dark:border-indigo-900/30 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl shadow-lg" style="background: linear-gradient(135deg, #6366f1, #8b5cf6);">
                        <span class="text-xl font-black text-white">{{ $data['month'] }}</span>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-gray-900 dark:text-white tracking-tight">
                            {{ $monthNameId[$data['month']] }} {{ $data['year'] }}
                        </h3>
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400">
                            {{ $data['days_in_month'] }} hari &bull; {{ count($calendarData) }} hari aktif
                        </p>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-2 text-xs font-semibold">
                    <span class="flex items-center gap-1.5 rounded-lg bg-white/80 dark:bg-gray-800/80 px-3 py-1.5 shadow border border-emerald-200 dark:border-emerald-800">
                        <span class="inline-block h-2.5 w-2.5 rounded-full bg-emerald-500 shadow-sm shadow-emerald-500/40"></span>
                        <span class="text-emerald-700 dark:text-emerald-300">Patrol</span>
                    </span>
                    <span class="flex items-center gap-1.5 rounded-lg bg-white/80 dark:bg-gray-800/80 px-3 py-1.5 shadow border border-rose-200 dark:border-rose-800">
                        <span class="inline-block h-2.5 w-2.5 rounded-full bg-rose-500 shadow-sm shadow-rose-500/40"></span>
                        <span class="text-rose-700 dark:text-rose-300">Missed</span>
                    </span>
                    <span class="flex items-center gap-1.5 rounded-lg bg-white/80 dark:bg-gray-800/80 px-3 py-1.5 shadow border border-fuchsia-200 dark:border-fuchsia-800">
                        <span class="inline-block h-4 w-5 rounded bg-fuchsia-50 dark:bg-fuchsia-900/30 border border-fuchsia-200"></span>
                        <span class="text-fuchsia-700 dark:text-fuchsia-300">Weekend</span>
                    </span>
                </div>
            </div>

            {{-- Day name headers - colorful! --}}
            <div class="grid grid-cols-7 border-b border-indigo-100 dark:border-gray-700">
                @php
                    $dayHeaderColors = [
                        'text-rose-500 bg-rose-50 dark:bg-rose-900/20 dark:text-rose-400',    // Min
                        'text-blue-600 bg-blue-50 dark:bg-blue-900/20 dark:text-blue-400',    // Sen
                        'text-violet-600 bg-violet-50 dark:bg-violet-900/20 dark:text-violet-400', // Sel
                        'text-emerald-600 bg-emerald-50 dark:bg-emerald-900/20 dark:text-emerald-400', // Rab
                        'text-amber-600 bg-amber-50 dark:bg-amber-900/20 dark:text-amber-400', // Kam
                        'text-indigo-600 bg-indigo-50 dark:bg-indigo-900/20 dark:text-indigo-400', // Jum
                        'text-rose-500 bg-rose-50 dark:bg-rose-900/20 dark:text-rose-400',    // Sab
                    ];
                @endphp
                @foreach($dayNames as $i => $dn)
                    <div class="py-3 text-center {{ $dayHeaderColors[$i] }}">
                        <span class="text-xs font-black uppercase tracking-widest">{{ $dn }}</span>
                    </div>
                @endforeach
            </div>

            {{-- Day cells --}}
            <div class="grid grid-cols-7 bg-white dark:bg-gray-800">
                {{-- Leading blanks --}}
                @for($b = 0; $b < $startBlank; $b++)
                    <div class="min-h-[110px] border-b border-r border-indigo-50 dark:border-gray-700/30 bg-gradient-to-br from-gray-50/60 to-gray-100/30 dark:from-gray-800/60 dark:to-gray-800/20"></div>
                @endfor

                @for($day = 1; $day <= $data['days_in_month']; $day++)
                    @php
                        $cellDate   = \Carbon\Carbon::create($data['year'], $data['month'], $day);
                        $isWeekend  = in_array($cellDate->dayOfWeek, [0, 6]);
                        $isToday    = $cellDate->isToday();
                        $dayPics    = $calendarData[$day] ?? [];
                        $showPics   = array_slice($dayPics, 0, 3, true);
                        $extraCount = count($dayPics) - count($showPics);

                        $dayPatrol  = array_sum(array_column($dayPics, 'patrol_count'));
                        $dayMissed  = array_sum(array_column($dayPics, 'missed_count'));
                        $hasData    = count($dayPics) > 0;
                        $healthStatus = $hasData ? ($dayMissed === 0 ? 'success' : ($dayPatrol === 0 ? 'danger' : 'warning')) : 'none';
                    @endphp

                    <div
                        onclick="selectDay({{ $day }})"
                        id="cal-day-{{ $day }}"
                        data-day="{{ $day }}"
                        tabindex="0"
                        role="button"
                        aria-label="Tanggal {{ $day }} {{ $monthNameId[$data['month']] }}, {{ $dayPatrol }} patrol selesai"
                        class="cal-cell group min-h-[110px] border-b border-r border-indigo-50 dark:border-gray-700/30 p-3 cursor-pointer select-none focus:outline-none
                            {{ $isWeekend ? 'weekend-cell' : 'bg-white dark:bg-gray-800' }}">

                        {{-- Date header --}}
                        <div class="mb-2 flex items-center justify-between">
                            <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg text-xs font-black transition-all duration-200
                                {{ $isToday
                                    ? 'today-badge text-white'
                                    : ($isWeekend
                                        ? 'text-rose-400 dark:text-rose-400 group-hover:bg-rose-50 dark:group-hover:bg-rose-900/20'
                                        : 'text-gray-700 dark:text-gray-300 group-hover:bg-indigo-50 dark:group-hover:bg-indigo-900/20') }}">
                                {{ $day }}
                            </span>
                            {{-- Health dot --}}
                            @if($hasData)
                                <div class="relative flex items-center">
                                    <span class="inline-flex h-2.5 w-2.5 rounded-full
                                        {{ $healthStatus === 'success' ? 'bg-emerald-500 shadow-emerald-500/60' : ($healthStatus === 'danger' ? 'bg-rose-500 shadow-rose-500/60' : 'bg-amber-400 shadow-amber-500/60') }}
                                        shadow-md {{ $healthStatus === 'danger' ? 'animate-pulse' : '' }}">
                                    </span>
                                    @if($healthStatus === 'danger')
                                        <span class="absolute inset-0 inline-flex h-2.5 w-2.5 rounded-full bg-rose-400 opacity-60 animate-ping"></span>
                                    @endif
                                </div>
                            @endif
                        </div>

                        {{-- PIC Badges --}}
                        <div class="space-y-1.5">
                            @foreach($showPics as $picName => $picData)
                                @php
                                    $colorKey   = $badgeKeys[$picData['color_index'] % count($badgeKeys)];
                                    $cs         = $badgeBg[$colorKey];
                                    $shortName  = \Str::limit($picName, 9, '..');
                                    $hasMissed  = $picData['missed_count'] > 0;
                                    $percentage = $picData['total_assigned'] > 0 ? round(($picData['patrol_count'] / $picData['total_assigned']) * 100) : 0;
                                @endphp
                                <div class="flex items-center justify-between gap-1 rounded-lg border {{ $cs['border'] }} {{ $cs['bg'] }} px-2 py-1.5 transition-all duration-150 hover:shadow-sm hover:-translate-y-px">
                                    <div class="flex items-center gap-1.5 min-w-0">
                                        <span class="h-2 w-2 rounded-full flex-shrink-0 {{ $cs['dot'] }} shadow-sm {{ $hasMissed ? 'ring-2 ring-rose-400/60' : '' }}"></span>
                                        <span class="text-[10px] font-bold truncate {{ $cs['text'] }}">{{ $shortName }}</span>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <span class="text-[10px] font-black {{ $cs['text'] }} flex-shrink-0 px-1 py-0.5 rounded bg-white/60 dark:bg-gray-800/60">
                                            {{ $picData['patrol_count'] }}
                                        </span>
                                        @if($percentage < 100)
                                            <span class="text-[9px] font-semibold text-gray-400 dark:text-gray-500">{{ $percentage }}%</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach

                            @if($extraCount > 0)
                                <div class="flex items-center gap-1 px-2 py-1 rounded-lg bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-700 hover:bg-indigo-100 dark:hover:bg-indigo-900/30 transition-colors">
                                    <svg class="h-3 w-3 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                    </svg>
                                    <span class="text-[10px] font-bold text-indigo-600 dark:text-indigo-400">+{{ $extraCount }} lainnya</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endfor

                {{-- Trailing blanks --}}
                @php
                    $totalCells     = $startBlank + $data['days_in_month'];
                    $trailingBlanks = (7 - ($totalCells % 7)) % 7;
                @endphp
                @for($t = 0; $t < $trailingBlanks; $t++)
                    <div class="min-h-[110px] border-b border-r border-indigo-50 dark:border-gray-700/30 bg-gradient-to-br from-gray-50/60 to-gray-100/30 dark:from-gray-800/60 dark:to-gray-800/20"></div>
                @endfor
            </div>
        </div>

        {{-- ── Detail Panel ─────────────────────────────────────────────── --}}
        <div id="cal-detail-panel"
            class="hidden overflow-hidden rounded-2xl shadow-2xl border border-indigo-200 dark:border-indigo-800/50">

            {{-- Panel header --}}
            <div style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #a855f7 100%);" class="px-5 py-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-3">
                        <div id="cal-detail-dot-wrapper" class="flex h-11 w-11 items-center justify-center rounded-xl bg-white/20 backdrop-blur-sm border border-white/30 shadow-md">
                            <div id="cal-detail-dot" class="h-3.5 w-3.5 rounded-full bg-emerald-400 shadow-md shadow-emerald-500/50"></div>
                        </div>
                        <div>
                            <h3 id="cal-detail-title" class="text-base font-black text-white tracking-tight"></h3>
                            <p class="text-xs text-white/70">Detail aktivitas patrol per petugas</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span id="cal-detail-badge-patrol"
                            class="inline-flex items-center gap-1.5 rounded-xl bg-white/20 backdrop-blur-sm border border-white/30 pl-2.5 pr-3.5 py-1.5 text-xs font-bold text-white shadow">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                            </svg>
                            <span id="cal-detail-patrol-count"></span>
                        </span>
                        <span id="cal-detail-badge-missed"
                            class="hidden inline-flex items-center gap-1.5 rounded-xl bg-rose-500/80 backdrop-blur-sm border border-rose-400/50 pl-2.5 pr-3.5 py-1.5 text-xs font-bold text-white shadow">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                            </svg>
                            <span id="cal-detail-missed-count"></span>
                        </span>
                        <button onclick="closeDetail()"
                            class="flex h-8 w-8 items-center justify-center rounded-lg text-white/70 hover:bg-white/20 hover:text-white transition-all duration-200 active:scale-95"
                            aria-label="Tutup detail">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Panel body --}}
            <div id="cal-detail-body" class="grid gap-4 p-6 sm:grid-cols-2 lg:grid-cols-3 custom-scroll max-h-[500px] overflow-y-auto" style="background: linear-gradient(135deg, #f8faff 0%, #faf5ff 50%, #fff1f8 100%);"></div>
        </div>

    </div>

    {{-- ── Scripts ───────────────────────────────────────────────────────── --}}
    @push('scripts')
    <script>
        const calendarData = @json($calendarData);
        const colorPalette = @json($badgeKeys);
        const monthYear    = { month: {{ $data['month'] }}, year: {{ $data['year'] }} };

        const dayNamesFull  = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
        const monthNamesId  = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

        const avatarColors = {
            sky:     'from-sky-400 to-sky-600',
            emerald: 'from-emerald-400 to-emerald-600',
            violet:  'from-violet-400 to-violet-600',
            amber:   'from-amber-400 to-amber-600',
            rose:    'from-rose-400 to-rose-600',
            teal:    'from-teal-400 to-teal-600',
            indigo:  'from-indigo-400 to-indigo-600',
            orange:  'from-orange-400 to-orange-600',
        };

        const detailBgColors = [
            'from-blue-50 to-cyan-50 border-blue-200',
            'from-violet-50 to-purple-50 border-violet-200',
            'from-emerald-50 to-teal-50 border-emerald-200',
            'from-rose-50 to-pink-50 border-rose-200',
            'from-amber-50 to-orange-50 border-amber-200',
            'from-indigo-50 to-blue-50 border-indigo-200',
            'from-teal-50 to-emerald-50 border-teal-200',
            'from-fuchsia-50 to-violet-50 border-fuchsia-200',
        ];

        let selectedDay = null;
        let isAnimating = false;

        function selectDay(day) {
            if (isAnimating) return;

            if (selectedDay !== null) {
                const prevCell = document.getElementById('cal-day-' + selectedDay);
                if (prevCell) prevCell.classList.remove('selected');
            }

            if (selectedDay === day) {
                selectedDay = null;
                closeDetail();
                return;
            }

            selectedDay = day;
            const currentCell = document.getElementById('cal-day-' + day);
            if (currentCell) {
                currentCell.classList.add('selected');
                if (navigator.vibrate) navigator.vibrate(40);
            }

            const panel       = document.getElementById('cal-detail-panel');
            const title       = document.getElementById('cal-detail-title');
            const dot         = document.getElementById('cal-detail-dot');
            const body        = document.getElementById('cal-detail-body');
            const badgePatrol = document.getElementById('cal-detail-badge-patrol');
            const badgeMissed = document.getElementById('cal-detail-badge-missed');
            const patrolCount = document.getElementById('cal-detail-patrol-count');
            const missedCount = document.getElementById('cal-detail-missed-count');

            const cellDate  = new Date(monthYear.year, monthYear.month - 1, day);
            const dayName   = dayNamesFull[cellDate.getDay()];
            const monthName = monthNamesId[monthYear.month - 1];
            title.textContent = `${dayName}, ${day} ${monthName} ${monthYear.year}`;

            const dayData    = calendarData[day] ?? {};
            const pics       = Object.entries(dayData);
            const totalP     = pics.reduce((s,[,d]) => s + d.patrol_count, 0);
            const totalM     = pics.reduce((s,[,d]) => s + d.missed_count, 0);

            patrolCount.textContent = totalP + ' patrol';
            missedCount.textContent = totalM + ' missed';

            if (totalM === 0) {
                badgeMissed.classList.add('hidden');
                badgeMissed.classList.remove('inline-flex');
            } else {
                badgeMissed.classList.remove('hidden');
                badgeMissed.classList.add('inline-flex');
            }

            dot.className = 'h-3.5 w-3.5 rounded-full shadow-md transition-all duration-300';
            if (!pics.length)      dot.className += ' bg-gray-400';
            else if (totalM === 0) dot.className += ' bg-emerald-400 shadow-emerald-500/50';
            else if (totalP === 0) dot.className += ' bg-rose-400 shadow-rose-500/50 animate-pulse';
            else                   dot.className += ' bg-amber-400 shadow-amber-500/50';

            body.style.opacity = '0';
            body.innerHTML = '';

            if (!pics.length) {
                body.innerHTML = `
                    <div class="col-span-full flex flex-col items-center justify-center py-16 text-center">
                        <div class="w-20 h-20 rounded-full flex items-center justify-center mb-4 shadow-inner" style="background: linear-gradient(135deg, #eef2ff, #f5f3ff);">
                            <svg class="h-10 w-10 text-indigo-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                            </svg>
                        </div>
                        <p class="text-sm font-bold text-indigo-400">Tidak ada data patrol</p>
                        <p class="text-xs text-gray-400 mt-1">untuk hari ini</p>
                    </div>
                `;
            } else {
                pics.forEach(([name, d], index) => {
                    const colorKey  = colorPalette[d.color_index % colorPalette.length];
                    const avatarCls = avatarColors[colorKey] ?? 'from-gray-400 to-gray-600';
                    const bgCls     = detailBgColors[d.color_index % detailBgColors.length];
                    const initials  = name.split(' ').slice(0,2).map(w => (w[0] || '').toUpperCase()).join('');
                    const hasMissed = d.missed_count > 0;
                    const rateLocal = d.total_assigned > 0
                        ? Math.round((d.patrol_count / d.total_assigned) * 100)
                        : 0;

                    const rateGradient = rateLocal === 100
                        ? 'from-emerald-500 to-teal-500'
                        : (rateLocal > 50 ? 'from-amber-400 to-orange-500' : 'from-rose-500 to-pink-500');

                    const chip = document.createElement('div');
                    chip.className = `detail-chip fade-scale-in flex flex-col gap-3 rounded-2xl border bg-gradient-to-br ${bgCls} p-4 shadow-md`;
                    chip.style.animationDelay = `${index * 0.06}s`;
                    chip.style.animationFillMode = 'backwards';
                    chip.innerHTML = `
                        <div class="flex items-center gap-3">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br ${avatarCls} text-white text-base font-black shadow-lg flex-shrink-0">
                                ${initials}
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-bold text-gray-900 dark:text-gray-100 truncate mb-1">${escapeHtml(name)}</p>
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center gap-1 text-xs font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                        ${d.patrol_count}
                                    </span>
                                    ${hasMissed ? `
                                        <span class="inline-flex items-center gap-1 text-xs font-bold text-rose-500 bg-rose-50 px-2 py-0.5 rounded-full border border-rose-200">
                                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                                            ${d.missed_count}
                                        </span>
                                    ` : ''}
                                </div>
                            </div>
                            <div class="flex-shrink-0">
                                <span class="text-sm font-black text-white px-2.5 py-1 rounded-full shadow-md bg-gradient-to-r ${rateGradient}">${rateLocal}%</span>
                            </div>
                        </div>
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-gray-500 font-semibold">Progress Patrol</span>
                                <span class="font-bold ${rateLocal === 100 ? 'text-emerald-600' : (rateLocal > 50 ? 'text-amber-600' : 'text-rose-500')}">${rateLocal}%</span>
                            </div>
                            <div class="h-2.5 w-full rounded-full bg-white/80 overflow-hidden shadow-inner border border-white/60">
                                <div class="progress-bar h-full rounded-full bg-gradient-to-r ${rateGradient} shadow-sm" style="width:0%"></div>
                            </div>
                        </div>
                    `;
                    body.appendChild(chip);

                    requestAnimationFrame(() => {
                        const progressBar = chip.querySelector('.progress-bar');
                        if (progressBar) progressBar.style.width = `${rateLocal}%`;
                    });
                });
            }

            if (panel.classList.contains('hidden')) {
                panel.classList.remove('hidden');
                panel.classList.remove('slide-down');
                panel.classList.add('slide-up');
                body.style.opacity = '1';
                setTimeout(() => {
                    panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }, 50);
            } else {
                body.style.opacity = '1';
            }
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function closeDetail() {
            if (isAnimating) return;
            isAnimating = true;

            if (selectedDay !== null) {
                const cell = document.getElementById('cal-day-' + selectedDay);
                if (cell) cell.classList.remove('selected');
                selectedDay = null;
            }

            const panel = document.getElementById('cal-detail-panel');
            panel.classList.remove('slide-up');
            panel.classList.add('slide-down');

            setTimeout(() => {
                panel.classList.add('hidden');
                panel.classList.remove('slide-down');
                isAnimating = false;
            }, 250);
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && selectedDay !== null) closeDetail();
            if (selectedDay !== null && (e.key === 'ArrowLeft' || e.key === 'ArrowRight')) {
                e.preventDefault();
                let newDay = selectedDay + (e.key === 'ArrowRight' ? 1 : -1);
                if (newDay >= 1 && newDay <= {{ $data['days_in_month'] }}) selectDay(newDay);
            }
        });

        document.addEventListener('click', function(e) {
            const panel = document.getElementById('cal-detail-panel');
            const target = e.target.closest('.cal-cell');
            const panelTarget = e.target.closest('#cal-detail-panel');
            if (selectedDay !== null && !target && !panelTarget && !panel.classList.contains('hidden')) {
                closeDetail();
            }
        });
    </script>
    @endpush

</x-filament-panels::page>