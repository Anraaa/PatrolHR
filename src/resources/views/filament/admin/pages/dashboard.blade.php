<x-filament-panels::page @refresh-component="$refresh">
    @php
        $data = $this->getData();

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

        $totalPatrolMonth = 0;
        $totalMissedMonth = 0;
        $totalDaysActive  = 0;
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

        $firstDayOfMonth = \Carbon\Carbon::create($data['year'], $data['month'], 1);
        $startBlank      = $firstDayOfMonth->dayOfWeek;
        $dayNames        = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];

        // ── Badge tokens: semua sudah dark-mode aware ──────────────────────
        $badgeBg = [
            'sky'     => [
                'bg'       => 'bg-sky-100 dark:bg-sky-900/50',
                'text'     => 'text-sky-700 dark:text-sky-300',
                'dot'      => 'bg-sky-500 dark:bg-sky-400',
                'border'   => 'border-sky-200 dark:border-sky-700',
                'gradient' => 'from-sky-400 to-sky-600',
            ],
            'emerald' => [
                'bg'       => 'bg-emerald-100 dark:bg-emerald-900/50',
                'text'     => 'text-emerald-700 dark:text-emerald-300',
                'dot'      => 'bg-emerald-500 dark:bg-emerald-400',
                'border'   => 'border-emerald-200 dark:border-emerald-700',
                'gradient' => 'from-emerald-400 to-emerald-600',
            ],
            'violet'  => [
                'bg'       => 'bg-violet-100 dark:bg-violet-900/50',
                'text'     => 'text-violet-700 dark:text-violet-300',
                'dot'      => 'bg-violet-500 dark:bg-violet-400',
                'border'   => 'border-violet-200 dark:border-violet-700',
                'gradient' => 'from-violet-400 to-violet-600',
            ],
            'amber'   => [
                'bg'       => 'bg-amber-100 dark:bg-amber-900/50',
                'text'     => 'text-amber-700 dark:text-amber-300',
                'dot'      => 'bg-amber-500 dark:bg-amber-400',
                'border'   => 'border-amber-200 dark:border-amber-700',
                'gradient' => 'from-amber-400 to-amber-600',
            ],
            'rose'    => [
                'bg'       => 'bg-rose-100 dark:bg-rose-900/50',
                'text'     => 'text-rose-700 dark:text-rose-300',
                'dot'      => 'bg-rose-500 dark:bg-rose-400',
                'border'   => 'border-rose-200 dark:border-rose-700',
                'gradient' => 'from-rose-400 to-rose-600',
            ],
            'teal'    => [
                'bg'       => 'bg-teal-100 dark:bg-teal-900/50',
                'text'     => 'text-teal-700 dark:text-teal-300',
                'dot'      => 'bg-teal-500 dark:bg-teal-400',
                'border'   => 'border-teal-200 dark:border-teal-700',
                'gradient' => 'from-teal-400 to-teal-600',
            ],
            'indigo'  => [
                'bg'       => 'bg-indigo-100 dark:bg-indigo-900/50',
                'text'     => 'text-indigo-700 dark:text-indigo-300',
                'dot'      => 'bg-indigo-500 dark:bg-indigo-400',
                'border'   => 'border-indigo-200 dark:border-indigo-700',
                'gradient' => 'from-indigo-400 to-indigo-600',
            ],
            'orange'  => [
                'bg'       => 'bg-orange-100 dark:bg-orange-900/50',
                'text'     => 'text-orange-700 dark:text-orange-300',
                'dot'      => 'bg-orange-500 dark:bg-orange-400',
                'border'   => 'border-orange-200 dark:border-orange-700',
                'gradient' => 'from-orange-400 to-orange-600',
            ],
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

        // ── Location card palette ──────────────────────────────────────────
        $locGradients    = ['from-blue-500 to-cyan-400','from-violet-500 to-purple-400','from-emerald-500 to-teal-400','from-rose-500 to-pink-400','from-amber-500 to-orange-400','from-indigo-500 to-blue-400','from-teal-500 to-emerald-400','from-fuchsia-500 to-violet-400','from-sky-500 to-indigo-400','from-orange-500 to-red-400','from-cyan-500 to-sky-400','from-pink-500 to-rose-400'];
        $locBgLight      = ['bg-blue-50','bg-violet-50','bg-emerald-50','bg-rose-50','bg-amber-50','bg-indigo-50','bg-teal-50','bg-fuchsia-50','bg-sky-50','bg-orange-50','bg-cyan-50','bg-pink-50'];
        $locBorderLight  = ['border-blue-200','border-violet-200','border-emerald-200','border-rose-200','border-amber-200','border-indigo-200','border-teal-200','border-fuchsia-200','border-sky-200','border-orange-200','border-cyan-200','border-pink-200'];
        $locBorderDark   = ['dark:border-blue-800','dark:border-violet-800','dark:border-emerald-800','dark:border-rose-800','dark:border-amber-800','dark:border-indigo-800','dark:border-teal-800','dark:border-fuchsia-800','dark:border-sky-800','dark:border-orange-800','dark:border-cyan-800','dark:border-pink-800'];
        $locTextLight    = ['text-blue-700','text-violet-700','text-emerald-700','text-rose-700','text-amber-700','text-indigo-700','text-teal-700','text-fuchsia-700','text-sky-700','text-orange-700','text-cyan-700','text-pink-700'];
        $locTextDark     = ['dark:text-blue-300','dark:text-violet-300','dark:text-emerald-300','dark:text-rose-300','dark:text-amber-300','dark:text-indigo-300','dark:text-teal-300','dark:text-fuchsia-300','dark:text-sky-300','dark:text-orange-300','dark:text-cyan-300','dark:text-pink-300'];
    @endphp

    {{-- ── Styles ──────────────────────────────────────────────────────────── --}}
    <style>
        /* ── Animations ─────────────────────────────────────────────────── */
        @keyframes rainbowShift {
            0%,100% { background-position: 0% 50%; }
            50%      { background-position: 100% 50%; }
        }
        @keyframes floatUp {
            0%,100% { transform: translateY(0) rotate(0deg); opacity:.55; }
            50%      { transform: translateY(-14px) rotate(6deg); opacity:.85; }
        }
        @keyframes slideUp {
            from { opacity:0; transform:translateY(20px); }
            to   { opacity:1; transform:translateY(0); }
        }
        @keyframes slideDown {
            from { opacity:1; transform:translateY(0); }
            to   { opacity:0; transform:translateY(20px); }
        }
        @keyframes fadeScale {
            from { opacity:0; transform:scale(.93); }
            to   { opacity:1; transform:scale(1); }
        }
        @keyframes shimmer {
            0%   { background-position:-200% 0; }
            100% { background-position: 200% 0; }
        }
        @keyframes softPulse {
            0%,100% { transform:scale(1); }
            50%      { transform:scale(1.05); }
        }

        /* ── Hero banner ─────────────────────────────────────────────────── */
        .hero-banner {
            background: linear-gradient(135deg,#667eea 0%,#764ba2 22%,#f093fb 44%,#4facfe 66%,#43e97b 100%);
            background-size: 300% 300%;
            animation: rainbowShift 9s ease infinite;
            position: relative;
            overflow: hidden;
        }
        .hero-banner::before {
            content:'';
            position:absolute;
            inset:0;
            background: rgba(0,0,0,.18);
        }
        .bubble {
            position:absolute;
            border-radius:50%;
            background:rgba(255,255,255,.13);
            animation: floatUp ease-in-out infinite;
        }

        /* ── Stat cards ──────────────────────────────────────────────────── */
        .stat-card {
            position:relative;
            overflow:hidden;
            transition: transform .3s, box-shadow .3s;
        }
        .stat-card::after {
            content:'';
            position:absolute;
            top:-45%; right:-45%;
            width:90%; height:90%;
            border-radius:50%;
            background:rgba(255,255,255,.1);
            pointer-events:none;
        }
        .stat-card:hover { transform:translateY(-5px) scale(1.01); box-shadow:0 22px 40px -12px rgba(0,0,0,.28); }
        .stat-icon { background:rgba(255,255,255,.2); backdrop-filter:blur(8px); border:1px solid rgba(255,255,255,.3); transition:transform .3s; }
        .stat-card:hover .stat-icon { transform:scale(1.12) rotate(5deg); }

        /* ── Calendar cells ──────────────────────────────────────────────── */
        .cal-cell {
            transition: all .2s cubic-bezier(.4,0,.2,1);
        }
        .cal-cell:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px -6px rgba(99,102,241,.2);
            z-index:5;
        }
        .cal-cell.selected {
            box-shadow: inset 0 0 0 2px #818cf8, 0 6px 20px -6px rgba(99,102,241,.35);
            z-index:5;
        }
        .cal-cell:focus-visible { outline:2px solid #818cf8; outline-offset:2px; border-radius:.5rem; }

        /* ── Today badge ─────────────────────────────────────────────────── */
        .today-num {
            background: linear-gradient(135deg,#6366f1,#8b5cf6);
            color: #fff;
            animation: softPulse 2.2s ease-in-out infinite;
            box-shadow: 0 0 0 3px rgba(129,140,248,.35), 0 4px 12px rgba(99,102,241,.4);
        }

        /* ── Progress bars ───────────────────────────────────────────────── */
        .progress-bar { transition: width .9s cubic-bezier(.65,0,.35,1); }

        /* ── Cards / chips ───────────────────────────────────────────────── */
        .loc-card { transition: all .3s cubic-bezier(.4,0,.2,1); position:relative; overflow:hidden; }
        .loc-card::before { content:''; position:absolute; top:0;left:0;right:0; height:3px; background:linear-gradient(90deg,#667eea,#f093fb); opacity:0; transition:opacity .3s; }
        .loc-card:hover::before { opacity:1; }
        .loc-card:hover { transform:translateY(-4px); box-shadow:0 20px 40px -12px rgba(99,102,241,.2); }

        .pic-badge { transition: all .2s cubic-bezier(.4,0,.2,1); cursor:pointer; }
        .pic-badge:hover { transform:translateY(-2px) scale(1.02); box-shadow:0 6px 14px -4px rgba(0,0,0,.15); }

        .detail-chip { transition: all .3s cubic-bezier(.4,0,.2,1); }
        .detail-chip:hover { transform:translateY(-3px); box-shadow:0 14px 28px -10px rgba(0,0,0,.18); }

        /* ── Animations ──────────────────────────────────────────────────── */
        .slide-up   { animation: slideUp .35s cubic-bezier(.34,1.2,.64,1) forwards; }
        .slide-down { animation: slideDown .25s ease-in forwards; }
        .fade-scale { animation: fadeScale .25s cubic-bezier(.34,1.2,.64,1) forwards; }

        /* ── Scrollbar ───────────────────────────────────────────────────── */
        .custom-scroll::-webkit-scrollbar       { width:6px; height:6px; }
        .custom-scroll::-webkit-scrollbar-track { background:rgba(99,102,241,.07); border-radius:10px; }
        .custom-scroll::-webkit-scrollbar-thumb { background:rgba(99,102,241,.35); border-radius:10px; }
        .custom-scroll::-webkit-scrollbar-thumb:hover { background:rgba(99,102,241,.6); }

        /* ── Section header (light/dark) ─────────────────────────────────── */
        .sec-header {
            background: linear-gradient(135deg,
                rgba(99,102,241,.07) 0%,
                rgba(168,85,247,.05) 50%,
                rgba(236,72,153,.03) 100%);
        }
        .dark .sec-header {
            background: linear-gradient(135deg,
                rgba(99,102,241,.18) 0%,
                rgba(168,85,247,.13) 50%,
                rgba(236,72,153,.09) 100%);
        }

        /* ── Page surface (light/dark) ───────────────────────────────────── */
        .page-surface {
            background: linear-gradient(135deg,#f8faff 0%,#faf5ff 50%,#fff1f8 100%);
        }
        .dark .page-surface {
            background: transparent;
        }

        /* ── Calendar header (light/dark) ────────────────────────────────── */
        .cal-header-bg {
            background: linear-gradient(135deg,#eef2ff 0%,#f5f3ff 50%,#fdf2f8 100%);
        }
        .dark .cal-header-bg {
            background: linear-gradient(135deg,
                rgba(49,46,129,.35) 0%,
                rgba(76,29,149,.25) 50%,
                rgba(131,24,67,.18) 100%);
        }

        /* ── Table heading row (light/dark) ──────────────────────────────── */
        .tbl-head-row {
            background: linear-gradient(90deg,#eef2ff 0%,#f5f3ff 50%,#fdf2f8 100%);
        }
        .dark .tbl-head-row {
            background: linear-gradient(90deg,
                rgba(49,46,129,.35) 0%,
                rgba(76,29,149,.25) 50%,
                rgba(131,24,67,.2) 100%);
        }

        /* ── Calendar cell backgrounds ───────────────────────────────────── */
        .cal-cell-normal {
            background: #fff;
        }
        .dark .cal-cell-normal {
            background: rgb(31 41 55); /* gray-800 */
        }
        .cal-cell-normal:hover {
            background: rgb(238 242 255 / .98) !important;
        }
        .dark .cal-cell-normal:hover {
            background: rgb(30 27 75 / .25) !important;
        }
        .cal-cell-normal.selected {
            background: rgb(238 242 255 / .98) !important;
        }
        .dark .cal-cell-normal.selected {
            background: rgb(30 27 75 / .3) !important;
        }

        .cal-cell-weekend {
            background: linear-gradient(135deg,rgba(253,242,248,.85),rgba(245,243,255,.65));
        }
        .dark .cal-cell-weekend {
            background: linear-gradient(135deg,rgba(131,24,67,.12),rgba(76,29,149,.09));
        }
        .cal-cell-weekend:hover {
            background: linear-gradient(135deg,rgba(253,242,248,.98),rgba(245,243,255,.95)) !important;
        }
        .dark .cal-cell-weekend:hover {
            background: linear-gradient(135deg,rgba(131,24,67,.2),rgba(76,29,149,.18)) !important;
        }

        .cal-cell-blank {
            background: linear-gradient(135deg,rgba(249,250,251,.7),rgba(243,244,246,.4));
        }
        .dark .cal-cell-blank {
            background: linear-gradient(135deg,rgba(17,24,39,.5),rgba(31,41,55,.3));
        }

        /* ── Location card surface ───────────────────────────────────────── */
        .loc-card-surface {
            background: #fff;
        }
        .dark .loc-card-surface {
            background: rgb(31 41 55 / .6);
        }

        /* ── Detail panel body ───────────────────────────────────────────── */
        .detail-body-bg {
            background: linear-gradient(135deg,#f8faff 0%,#faf5ff 50%,#fff1f8 100%);
        }
        .dark .detail-body-bg {
            background: rgb(17 24 39);
        }

        /* ── Summary table surface ───────────────────────────────────────── */
        .summary-surface {
            background: linear-gradient(135deg,#f8faff 0%,#faf5ff 50%,#fff1f8 100%);
        }
        .dark .summary-surface {
            background: rgb(17 24 39);
        }
    </style>

    <div class="space-y-6 custom-scroll">

        {{-- ══ HERO BANNER ════════════════════════════════════════════════ --}}
        <div class="hero-banner rounded-3xl p-6 lg:p-8 shadow-2xl">
            <div class="bubble w-24 h-24" style="top:-18px;right:9%;animation-duration:6.5s;animation-delay:0s;"></div>
            <div class="bubble w-16 h-16" style="bottom:-10px;left:14%;animation-duration:8s;animation-delay:1s;"></div>
            <div class="bubble w-10 h-10" style="top:30%;left:5%;animation-duration:5.5s;animation-delay:.5s;"></div>
            <div class="bubble w-20 h-20" style="bottom:8%;right:22%;animation-duration:7s;animation-delay:2s;"></div>
            <div class="bubble w-8 h-8"  style="top:10%;right:40%;animation-duration:9s;animation-delay:1.5s;"></div>

            <div class="relative z-10 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="space-y-2">
                    <div class="flex items-center gap-4">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white/20 backdrop-blur-sm border border-white/30 shadow-lg">
                            <svg class="h-7 w-7 text-white drop-shadow" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-3xl font-black text-white tracking-tight drop-shadow-md lg:text-4xl">Monitoring Patrol</h1>
                            <p class="text-white/80 text-sm font-medium mt-0.5">Distribusi aktivitas patrol harian</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2 mt-1">
                        <span class="inline-flex items-center gap-1.5 bg-white/20 backdrop-blur-sm border border-white/30 text-white text-xs font-bold px-3 py-1.5 rounded-full">
                            <span class="h-2 w-2 rounded-full bg-emerald-300 animate-pulse"></span>{{ $totalPatrolMonth }} Patrol Selesai
                        </span>
                        <span class="inline-flex items-center gap-1.5 bg-white/20 backdrop-blur-sm border border-white/30 text-white text-xs font-bold px-3 py-1.5 rounded-full">
                            <span class="h-2 w-2 rounded-full bg-rose-300"></span>{{ $totalMissedMonth }} Missed
                        </span>
                        <span class="inline-flex items-center gap-1.5 bg-white/20 backdrop-blur-sm border border-white/30 text-white text-xs font-bold px-3 py-1.5 rounded-full">
                            <span class="h-2 w-2 rounded-full bg-amber-300"></span>{{ $ratePercent }}% Rate
                        </span>
                    </div>
                </div>

                <div class="flex items-center">
                    <div class="flex items-center gap-2 rounded-2xl bg-white/20 backdrop-blur-md border border-white/30 shadow-lg p-2">
                        <select wire:model.live="selectedMonth"
                            class="rounded-xl border-0 bg-white/90 dark:bg-gray-800/90 text-gray-800 dark:text-white px-4 py-2 text-sm font-bold focus:ring-2 focus:ring-white/50 cursor-pointer shadow-sm">
                            @foreach($this->getMonths() as $num => $name)
                                <option value="{{ $num }}" @selected($num == $data['month'])>{{ $name }}</option>
                            @endforeach
                        </select>
                        <div class="h-6 w-px bg-white/40"></div>
                        <select wire:model.live="selectedYear"
                            class="rounded-xl border-0 bg-white/90 dark:bg-gray-800/90 text-gray-800 dark:text-white px-4 py-2 text-sm font-bold focus:ring-2 focus:ring-white/50 cursor-pointer shadow-sm">
                            @foreach($this->getYears() as $year)
                                <option value="{{ $year }}" @selected($year == $data['year'])>{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- ══ STAT CARDS ════════════════════════════════════════════════ --}}
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">

            {{-- User Aktif --}}
            <div class="stat-card rounded-2xl p-5 shadow-xl" style="background:linear-gradient(135deg,#3b82f6,#06b6d4)">
                @php
                    $activeUsers = count(array_filter($data['table_data'], fn($row) => !empty(array_filter($row['daily_data'], fn($day) => in_array(1, $day['shifts_status'])))));
                    $totalUsers  = count($data['users']);
                    $activeRate  = $totalUsers > 0 ? round(($activeUsers / $totalUsers) * 100) : 0;
                @endphp
                <div class="flex items-start justify-between mb-3">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-blue-100 mb-2">User Aktif</p>
                        <div class="flex items-baseline gap-2">
                            <span class="text-4xl font-black text-white">{{ $activeUsers }}</span>
                            <span class="text-xs font-semibold text-blue-200">dari {{ $totalUsers }}</span>
                        </div>
                    </div>
                    <div class="stat-icon flex h-12 w-12 items-center justify-center rounded-xl shadow-md">
                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/></svg>
                    </div>
                </div>
                <p class="text-xs text-blue-200 font-semibold mb-2">User yang sudah melakukan patrol</p>
                <div class="h-1.5 w-full rounded-full bg-white/20 overflow-hidden">
                    <div class="h-full bg-white/60 rounded-full progress-bar" style="width:{{ $activeRate }}%"></div>
                </div>
            </div>

            {{-- Total Petugas --}}
            <div class="stat-card rounded-2xl p-5 shadow-xl" style="background:linear-gradient(135deg,#8b5cf6,#a855f7)">
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
                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z"/></svg>
                    </div>
                </div>
            </div>

            {{-- Titik Patrol --}}
            <div class="stat-card rounded-2xl p-5 shadow-xl" style="background:linear-gradient(135deg,#10b981,#14b8a6)">
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
                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                    </div>
                </div>
            </div>

            {{-- Kelengkapan --}}
            <div class="stat-card rounded-2xl p-5 shadow-xl" style="background:linear-gradient(135deg,#f43f5e,#fb923c)">
                @php
                    $avgCompletion = 0;
                    if (count($data['users']) > 0) {
                        $tot = 0;
                        foreach ($data['table_data'] as $row) {
                            if ($row['row_span'] > 0)
                                $tot += ($row['locations_patrolled'] / $row['total_locations']) * 100;
                        }
                        $avgCompletion = round($tot / count($data['users']));
                    }
                @endphp
                <p class="text-xs font-bold uppercase tracking-wider text-rose-100 mb-3">Rata-rata Kelengkapan</p>
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <div class="flex items-baseline gap-2 mb-2">
                            <span class="text-3xl font-black text-white">{{ $avgCompletion }}%</span>
                        </div>
                        <div class="h-2 w-full rounded-full bg-white/25 overflow-hidden">
                            <div class="h-full bg-white/70 rounded-full progress-bar" style="width:{{ $avgCompletion }}%"></div>
                        </div>
                    </div>
                    <div class="stat-icon flex h-12 w-12 items-center justify-center rounded-xl shadow-md ml-3">
                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- ══ PIC SUMMARY TABLE ══════════════════════════════════════════ --}}
        @php
            $picSummary = [];
            foreach ($data['users'] as $user) {
                $picSummary[$user->id] = [
                    'name'        => $user->name,
                    'color_idx'   => $picColors[$user->name] ?? 0,
                    'total_locations' => count($data['locations']),
                    'locations_visited'  => 0,
                    'patrols_completed'  => 0,
                    'patrols_pending'    => 0,
                    'patrols_total'      => 0,
                ];
            }
            foreach ($data['users'] as $user) {
                $range = [
                    \Carbon\Carbon::create($data['year'], $data['month'], 1),
                    \Carbon\Carbon::create($data['year'], $data['month'], $data['days_in_month'], 23, 59, 59),
                ];
                $lv = \App\Models\Patrol::where('user_id', $user->id)->whereBetween('patrol_time', $range)->whereNotNull('qr_scanned_at')->distinct('location_id')->count('location_id');
                $pc = \App\Models\Patrol::where('user_id', $user->id)->whereBetween('patrol_time', $range)->whereNotNull('qr_scanned_at')->count();
                $pt = \App\Models\Patrol::where('user_id', $user->id)->whereBetween('patrol_time', $range)->count();
                $picSummary[$user->id]['locations_visited'] = $lv;
                $picSummary[$user->id]['patrols_completed'] = $pc;
                $picSummary[$user->id]['patrols_pending']   = $pt - $pc;
                $picSummary[$user->id]['patrols_total']     = $pt;
            }
        @endphp

        <div class="overflow-hidden rounded-2xl shadow-xl border border-indigo-100 dark:border-indigo-900/40">
            {{-- header --}}
            <div class="sec-header px-6 py-4 border-b border-indigo-100 dark:border-indigo-900/40">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl shadow-md" style="background:linear-gradient(135deg,#6366f1,#8b5cf6)">
                        <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-black text-gray-900 dark:text-white text-base">Ringkasan Petugas Patrol</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Performa individual per bulan</p>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto summary-surface">
                <table class="min-w-full">
                    <thead>
                        <tr class="tbl-head-row">
                            <th class="px-6 py-4 text-left text-xs font-bold text-indigo-700 dark:text-indigo-300 uppercase tracking-wider">Petugas</th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-violet-700 dark:text-violet-300 uppercase tracking-wider">Lokasi</th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-emerald-700 dark:text-emerald-300 uppercase tracking-wider">Selesai</th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-amber-700 dark:text-amber-300 uppercase tracking-wider">Tertunda</th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-blue-700 dark:text-blue-300 uppercase tracking-wider">Progres</th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-rose-700 dark:text-rose-300 uppercase tracking-wider">Kelengkapan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-indigo-50 dark:divide-gray-700/60 bg-white dark:bg-gray-800">
                        @foreach($picSummary as $userId => $pic)
                            @php
                                $ck  = $badgeKeys[$pic['color_idx'] % count($badgeKeys)];
                                $cs  = $badgeBg[$ck];
                                $ini = collect(explode(' ', $pic['name']))->take(2)->map(fn($w) => strtoupper($w[0] ?? ''))->join('');
                                $prg = $pic['total_locations'] > 0 ? round(($pic['locations_visited'] / $pic['total_locations']) * 100) : 0;
                                $lp  = $pic['total_locations'] > 0 ? ($pic['locations_visited'] / $pic['total_locations']) * 100 : 0;
                                $cp  = $pic['patrols_total'] > 0 ? ($pic['patrols_completed'] / $pic['patrols_total']) * 100 : 0;
                                $ov  = round(($lp + $cp) / 2);
                                $sg  = $ov >= 80 ? 'from-emerald-500 to-teal-500' : ($ov >= 50 ? 'from-amber-400 to-orange-500' : 'from-rose-500 to-pink-500');
                                $si  = $ov >= 80 ? '✓' : ($ov >= 50 ? '⟳' : '✗');
                            @endphp
                            <tr class="transition-colors duration-150 hover:bg-indigo-50/50 dark:hover:bg-indigo-900/10">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-11 w-11 items-center justify-center rounded-xl {{ $avatarSolid[$ck] }} text-white text-sm font-black shadow-lg">{{ $ini }}</div>
                                        <div>
                                            <div class="font-bold text-gray-900 dark:text-white">{{ $pic['name'] }}</div>
                                            <div class="text-xs font-semibold {{ $cs['text'] }}">Petugas Patrol</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <div class="text-xl font-black {{ $cs['text'] }}">{{ $pic['locations_visited'] }}</div>
                                    <div class="text-xs text-gray-400 dark:text-gray-500">dari {{ $pic['total_locations'] }}</div>
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <div class="text-xl font-black text-emerald-600 dark:text-emerald-400">{{ $pic['patrols_completed'] }}</div>
                                    <div class="text-xs text-gray-400 dark:text-gray-500">dari {{ $pic['patrols_total'] }}</div>
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <div class="text-xl font-black text-amber-600 dark:text-amber-400">{{ $pic['patrols_pending'] }}</div>
                                    <div class="text-xs text-gray-400 dark:text-gray-500">belum selesai</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="w-32">
                                        <div class="flex justify-between mb-1">
                                            <span class="text-xs font-bold {{ $cs['text'] }}">{{ $prg }}%</span>
                                        </div>
                                        <div class="h-2.5 w-full rounded-full bg-gray-100 dark:bg-gray-700 overflow-hidden shadow-inner">
                                            <div class="h-full rounded-full bg-gradient-to-r {{ $cs['gradient'] }} progress-bar" style="width:{{ $prg }}%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-black text-white shadow-md bg-gradient-to-r {{ $sg }}">
                                        {{ $si }} {{ $ov }}%
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="tbl-head-row">
                            <td colspan="6" class="px-6 py-3">
                                <div class="flex items-center justify-between text-xs">
                                    <div class="flex items-center gap-4">
                                        <span class="flex items-center gap-1.5 font-semibold text-gray-600 dark:text-gray-300">
                                            <span class="w-2.5 h-2.5 rounded-full bg-gradient-to-r from-emerald-500 to-teal-500 shadow-sm"></span>≥80%
                                        </span>
                                        <span class="flex items-center gap-1.5 font-semibold text-gray-600 dark:text-gray-300">
                                            <span class="w-2.5 h-2.5 rounded-full bg-gradient-to-r from-amber-400 to-orange-500 shadow-sm"></span>50–79%
                                        </span>
                                        <span class="flex items-center gap-1.5 font-semibold text-gray-600 dark:text-gray-300">
                                            <span class="w-2.5 h-2.5 rounded-full bg-gradient-to-r from-rose-500 to-pink-500 shadow-sm"></span>&lt;50%
                                        </span>
                                    </div>
                                    <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ count($picSummary) }} Petugas</span>
                                </div>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- ══ LOCATION PERFORMANCE ══════════════════════════════════════ --}}
        @php
            $locationPerformance = [];
            $totalUsers = count($data['users']);
            foreach ($data['locations'] as $location) {
                $range = [
                    \Carbon\Carbon::create($data['year'], $data['month'], 1),
                    \Carbon\Carbon::create($data['year'], $data['month'], $data['days_in_month'], 23, 59, 59),
                ];
                $up  = \App\Models\Patrol::where('location_id', $location->id)->whereBetween('patrol_time', $range)->whereNotNull('qr_scanned_at')->distinct('user_id')->count('user_id');
                $tp  = \App\Models\Patrol::where('location_id', $location->id)->whereBetween('patrol_time', $range)->whereNotNull('qr_scanned_at')->count();
                $pct = $totalUsers > 0 ? round(($up / $totalUsers) * 100) : 0;
                $locationPerformance[$location->id] = ['name' => $location->name, 'users_patrolled' => $up, 'total_users' => $totalUsers, 'total_patrols' => $tp, 'performance_percent' => $pct];
            }
            usort($locationPerformance, fn($a,$b) => $b['performance_percent'] <=> $a['performance_percent']);
        @endphp

        <div class="overflow-hidden rounded-2xl shadow-xl border border-indigo-100 dark:border-indigo-900/40">
            <div class="sec-header px-6 py-4 border-b border-indigo-100 dark:border-indigo-900/40">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl shadow-md" style="background:linear-gradient(135deg,#10b981,#14b8a6)">
                        <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-black text-gray-900 dark:text-white text-base">Performa Lokasi Patrol</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Diurutkan dari performa tertinggi</p>
                    </div>
                </div>
            </div>

            <div class="p-5 page-surface">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach($locationPerformance as $li => $loc)
                        @php
                            $i      = $li % count($locGradients);
                            $lg     = $locGradients[$i];
                            $lbgl   = $locBgLight[$i];
                            $lbdl   = $locBorderLight[$i];
                            $lbdd   = $locBorderDark[$i];
                            $ltl    = $locTextLight[$i];
                            $ltd    = $locTextDark[$i];
                            $rem    = $loc['total_users'] - $loc['users_patrolled'];
                        @endphp
                        <div class="loc-card group relative flex flex-col rounded-xl border {{ $lbdl }} {{ $lbdd }} {{ $lbgl }} dark:bg-gray-800/70 p-4 shadow-md loc-card-surface">
                            <div class="absolute top-3 right-3">
                                <span class="inline-flex h-7 w-7 items-center justify-center rounded-full text-xs font-black text-white shadow-md bg-gradient-to-br {{ $lg }}">{{ $li+1 }}</span>
                            </div>
                            <div class="mb-3 pr-8">
                                <h4 class="font-black text-gray-900 dark:text-white text-sm">{{ $loc['name'] }}</h4>
                                <p class="text-[10px] font-bold {{ $ltl }} {{ $ltd }} uppercase tracking-wider mt-0.5">{{ $loc['users_patrolled'] }}/{{ $loc['total_users'] }} PIC</p>
                            </div>
                            <div class="mb-2">
                                <span class="text-2xl font-black bg-gradient-to-r {{ $lg }} bg-clip-text text-transparent">{{ $loc['performance_percent'] }}%</span>
                            </div>
                            <div class="w-full h-3 rounded-full bg-gray-200 dark:bg-gray-700 overflow-hidden mb-4 shadow-inner">
                                <div class="h-full rounded-full bg-gradient-to-r {{ $lg }} shadow progress-bar" style="width:{{ $loc['performance_percent'] }}%"></div>
                            </div>
                            <div class="grid grid-cols-3 gap-2">
                                <div class="rounded-lg bg-white/80 dark:bg-gray-900/60 p-2 shadow-sm border border-white/60 dark:border-gray-700/60 text-center">
                                    <p class="text-sm font-black {{ $ltl }} {{ $ltd }}">{{ $loc['users_patrolled'] }}</p>
                                    <p class="text-[9px] font-bold text-gray-400 dark:text-gray-500 uppercase mt-0.5">Aktif</p>
                                </div>
                                <div class="rounded-lg bg-white/80 dark:bg-gray-900/60 p-2 shadow-sm border border-white/60 dark:border-gray-700/60 text-center">
                                    <p class="text-sm font-black {{ $ltl }} {{ $ltd }}">{{ $loc['total_patrols'] }}</p>
                                    <p class="text-[9px] font-bold text-gray-400 dark:text-gray-500 uppercase mt-0.5">Patrol</p>
                                </div>
                                <div class="rounded-lg bg-white/80 dark:bg-gray-900/60 p-2 shadow-sm border border-white/60 dark:border-gray-700/60 text-center">
                                    <p class="text-sm font-black {{ $rem > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}">{{ $rem }}</p>
                                    <p class="text-[9px] font-bold text-gray-400 dark:text-gray-500 uppercase mt-0.5">Sisa</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ══ PIC LEGEND ════════════════════════════════════════════════ --}}
        @if(count($picColors) > 0)
        <div class="overflow-hidden rounded-2xl border border-indigo-100 dark:border-indigo-900/40 shadow-xl">
            <div class="sec-header px-5 py-3 border-b border-indigo-100 dark:border-indigo-900/40">
                <div class="flex items-center gap-2">
                    <div class="flex h-7 w-7 items-center justify-center rounded-lg shadow-sm" style="background:linear-gradient(135deg,#f43f5e,#fb923c)">
                        <svg class="h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/></svg>
                    </div>
                    <h3 class="text-sm font-black text-gray-700 dark:text-gray-200">Daftar Petugas</h3>
                    <span class="text-xs font-bold text-white px-2.5 py-0.5 rounded-full shadow-sm" style="background:linear-gradient(90deg,#6366f1,#8b5cf6)">
                        {{ count($picColors) }} petugas
                    </span>
                </div>
            </div>
            <div class="flex flex-wrap gap-2.5 p-4 page-surface">
                @foreach($picColors as $picName => $colorIdx)
                    @php
                        $ck  = $badgeKeys[$colorIdx % count($badgeKeys)];
                        $cs  = $badgeBg[$ck];
                        $ini = collect(explode(' ', $picName))->take(2)->map(fn($w) => strtoupper($w[0] ?? ''))->join('');
                    @endphp
                    <div class="pic-badge flex items-center gap-2.5 rounded-xl border {{ $cs['border'] }} {{ $cs['bg'] }} pl-2 pr-4 py-1.5 shadow-md">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg {{ $avatarSolid[$ck] }} text-white text-[11px] font-black shadow-md">{{ $ini }}</span>
                        <span class="text-xs font-bold {{ $cs['text'] }}">{{ $picName }}</span>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- ══ CALENDAR GRID ═════════════════════════════════════════════ --}}
        <div class="overflow-hidden rounded-2xl shadow-xl border border-indigo-100 dark:border-indigo-800/40">

            {{-- Calendar header --}}
            <div class="cal-header-bg flex flex-col gap-3 border-b border-indigo-100 dark:border-indigo-900/40 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl shadow-lg" style="background:linear-gradient(135deg,#6366f1,#8b5cf6)">
                        <span class="text-xl font-black text-white">{{ $data['month'] }}</span>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-gray-900 dark:text-white tracking-tight">{{ $monthNameId[$data['month']] }} {{ $data['year'] }}</h3>
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400">{{ $data['days_in_month'] }} hari · {{ count($calendarData) }} hari aktif</p>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-2 text-xs font-semibold">
                    <span class="flex items-center gap-1.5 rounded-lg bg-white dark:bg-gray-800 px-3 py-1.5 shadow border border-emerald-200 dark:border-emerald-800">
                        <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                        <span class="text-emerald-700 dark:text-emerald-300">Patrol</span>
                    </span>
                    <span class="flex items-center gap-1.5 rounded-lg bg-white dark:bg-gray-800 px-3 py-1.5 shadow border border-rose-200 dark:border-rose-800">
                        <span class="h-2.5 w-2.5 rounded-full bg-rose-500"></span>
                        <span class="text-rose-700 dark:text-rose-300">Missed</span>
                    </span>
                    <span class="flex items-center gap-1.5 rounded-lg bg-white dark:bg-gray-800 px-3 py-1.5 shadow border border-fuchsia-200 dark:border-fuchsia-800">
                        <span class="h-4 w-5 rounded bg-fuchsia-50 dark:bg-fuchsia-900/40 border border-fuchsia-200 dark:border-fuchsia-700"></span>
                        <span class="text-fuchsia-700 dark:text-fuchsia-300">Weekend</span>
                    </span>
                </div>
            </div>

            {{-- Day name headers --}}
            @php
                $dayColors = [
                    'bg-rose-50 dark:bg-rose-900/25 text-rose-600 dark:text-rose-400',
                    'bg-blue-50 dark:bg-blue-900/25 text-blue-700 dark:text-blue-400',
                    'bg-violet-50 dark:bg-violet-900/25 text-violet-700 dark:text-violet-400',
                    'bg-emerald-50 dark:bg-emerald-900/25 text-emerald-700 dark:text-emerald-400',
                    'bg-amber-50 dark:bg-amber-900/25 text-amber-700 dark:text-amber-400',
                    'bg-indigo-50 dark:bg-indigo-900/25 text-indigo-700 dark:text-indigo-400',
                    'bg-rose-50 dark:bg-rose-900/25 text-rose-600 dark:text-rose-400',
                ];
            @endphp
            <div class="grid grid-cols-7 border-b border-indigo-100 dark:border-gray-700">
                @foreach($dayNames as $i => $dn)
                    <div class="py-3 text-center {{ $dayColors[$i] }}">
                        <span class="text-xs font-black uppercase tracking-widest">{{ $dn }}</span>
                    </div>
                @endforeach
            </div>

            {{-- Day cells --}}
            <div class="grid grid-cols-7 bg-white dark:bg-gray-800">
                @for($b = 0; $b < $startBlank; $b++)
                    <div class="min-h-[110px] border-b border-r border-indigo-50 dark:border-gray-700/40 cal-cell-blank"></div>
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
                        $health     = $hasData ? ($dayMissed === 0 ? 'ok' : ($dayPatrol === 0 ? 'bad' : 'warn')) : 'none';
                    @endphp
                    <div
                        onclick="selectDay({{ $day }})"
                        id="cal-day-{{ $day }}"
                        tabindex="0"
                        role="button"
                        aria-label="Tanggal {{ $day }}, {{ $dayPatrol }} patrol"
                        class="cal-cell group min-h-[110px] border-b border-r border-indigo-50 dark:border-gray-700/40 p-3 cursor-pointer select-none focus:outline-none
                            {{ $isWeekend ? 'cal-cell-weekend' : 'cal-cell-normal' }}">

                        <div class="mb-2 flex items-center justify-between">
                            <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg text-xs font-black transition-all duration-200
                                {{ $isToday ? 'today-num' : ($isWeekend ? 'text-rose-500 dark:text-rose-400 group-hover:bg-rose-50 dark:group-hover:bg-rose-900/25' : 'text-gray-700 dark:text-gray-300 group-hover:bg-indigo-50 dark:group-hover:bg-indigo-900/25') }}">
                                {{ $day }}
                            </span>
                            @if($hasData)
                                <div class="relative flex items-center">
                                    <span class="inline-flex h-2.5 w-2.5 rounded-full shadow-sm
                                        {{ $health === 'ok' ? 'bg-emerald-500' : ($health === 'bad' ? 'bg-rose-500 animate-pulse' : 'bg-amber-400') }}">
                                    </span>
                                    @if($health === 'bad')
                                        <span class="absolute inset-0 h-2.5 w-2.5 rounded-full bg-rose-400 opacity-60 animate-ping"></span>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <div class="space-y-1.5">
                            @foreach($showPics as $picName => $picData)
                                @php
                                    $ck  = $badgeKeys[$picData['color_index'] % count($badgeKeys)];
                                    $cs  = $badgeBg[$ck];
                                    $sn  = \Str::limit($picName, 9, '..');
                                    $pct = $picData['total_assigned'] > 0 ? round(($picData['patrol_count'] / $picData['total_assigned']) * 100) : 0;
                                @endphp
                                <div class="flex items-center justify-between gap-1 rounded-lg border {{ $cs['border'] }} {{ $cs['bg'] }} px-2 py-1.5 transition-all duration-150 hover:shadow-sm hover:-translate-y-px">
                                    <div class="flex items-center gap-1.5 min-w-0">
                                        <span class="h-2 w-2 rounded-full flex-shrink-0 {{ $cs['dot'] }} {{ $picData['missed_count'] > 0 ? 'ring-2 ring-rose-400/50' : '' }}"></span>
                                        <span class="text-[10px] font-bold truncate {{ $cs['text'] }}">{{ $sn }}</span>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <span class="text-[10px] font-black {{ $cs['text'] }} px-1 py-0.5 rounded bg-white/60 dark:bg-black/20">{{ $picData['patrol_count'] }}</span>
                                        @if($pct < 100)
                                            <span class="text-[9px] text-gray-400 dark:text-gray-500">{{ $pct }}%</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach

                            @if($extraCount > 0)
                                <div class="flex items-center gap-1 px-2 py-1 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-200 dark:border-indigo-700">
                                    <svg class="h-3 w-3 text-indigo-400 dark:text-indigo-500" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                    <span class="text-[10px] font-bold text-indigo-600 dark:text-indigo-400">+{{ $extraCount }} lainnya</span>
                                </div>
                            @endif

                            @if(!$hasData && !$isWeekend)
                                <div class="px-2 py-1 text-center">
                                    <span class="text-[10px] italic text-gray-300 dark:text-gray-600">Tidak ada data</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endfor

                @php $trail = (7 - (($startBlank + $data['days_in_month']) % 7)) % 7; @endphp
                @for($t = 0; $t < $trail; $t++)
                    <div class="min-h-[110px] border-b border-r border-indigo-50 dark:border-gray-700/40 cal-cell-blank"></div>
                @endfor
            </div>
        </div>

        {{-- ══ DETAIL PANEL ══════════════════════════════════════════════ --}}
        <div id="cal-detail-panel" class="hidden overflow-hidden rounded-2xl shadow-2xl border border-indigo-200 dark:border-indigo-800/60">
            <div style="background:linear-gradient(135deg,#6366f1,#8b5cf6,#a855f7)" class="px-5 py-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-white/20 backdrop-blur-sm border border-white/30 shadow-md">
                            <div id="cal-detail-dot" class="h-3.5 w-3.5 rounded-full bg-emerald-400"></div>
                        </div>
                        <div>
                            <h3 id="cal-detail-title" class="text-base font-black text-white tracking-tight"></h3>
                            <p class="text-xs text-white/70">Detail aktivitas patrol per petugas</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span id="cal-detail-badge-patrol"
                            class="inline-flex items-center gap-1.5 rounded-xl bg-white/20 backdrop-blur-sm border border-white/30 px-3 py-1.5 text-xs font-bold text-white shadow">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                            <span id="cal-detail-patrol-count"></span>
                        </span>
                        <span id="cal-detail-badge-missed"
                            class="hidden inline-flex items-center gap-1.5 rounded-xl bg-rose-500/80 border border-rose-400/50 px-3 py-1.5 text-xs font-bold text-white shadow">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                            <span id="cal-detail-missed-count"></span>
                        </span>
                        <button onclick="closeDetail()"
                            class="flex h-8 w-8 items-center justify-center rounded-lg text-white/70 hover:bg-white/20 hover:text-white transition-all duration-200 active:scale-95">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>
            </div>
            <div id="cal-detail-body"
                class="grid gap-4 p-6 sm:grid-cols-2 lg:grid-cols-3 custom-scroll max-h-[500px] overflow-y-auto detail-body-bg">
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
        const calendarData  = @json($calendarData);
        const colorPalette  = @json($badgeKeys);
        const monthYear     = { month: {{ $data['month'] }}, year: {{ $data['year'] }} };
        const daysFull      = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
        const monthsFull    = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        const isDark        = () => document.documentElement.classList.contains('dark');

        const avatarColors = {
            sky:'from-sky-400 to-sky-600', emerald:'from-emerald-400 to-emerald-600',
            violet:'from-violet-400 to-violet-600', amber:'from-amber-400 to-amber-600',
            rose:'from-rose-400 to-rose-600', teal:'from-teal-400 to-teal-600',
            indigo:'from-indigo-400 to-indigo-600', orange:'from-orange-400 to-orange-600',
        };

        // chip backgrounds — both light and dark variants as Tailwind classes
        const chipBg = [
            'bg-blue-50 dark:bg-blue-900/40 border-blue-200 dark:border-blue-800',
            'bg-violet-50 dark:bg-violet-900/40 border-violet-200 dark:border-violet-800',
            'bg-emerald-50 dark:bg-emerald-900/40 border-emerald-200 dark:border-emerald-800',
            'bg-rose-50 dark:bg-rose-900/40 border-rose-200 dark:border-rose-800',
            'bg-amber-50 dark:bg-amber-900/40 border-amber-200 dark:border-amber-800',
            'bg-indigo-50 dark:bg-indigo-900/40 border-indigo-200 dark:border-indigo-800',
            'bg-teal-50 dark:bg-teal-900/40 border-teal-200 dark:border-teal-800',
            'bg-fuchsia-50 dark:bg-fuchsia-900/40 border-fuchsia-200 dark:border-fuchsia-800',
        ];

        let selectedDay = null, animating = false;

        function selectDay(day) {
            if (animating) return;
            if (selectedDay !== null)
                document.getElementById('cal-day-' + selectedDay)?.classList.remove('selected');
            if (selectedDay === day) { selectedDay = null; closeDetail(); return; }

            selectedDay = day;
            document.getElementById('cal-day-' + day)?.classList.add('selected');
            if (navigator.vibrate) navigator.vibrate(40);

            const panel   = document.getElementById('cal-detail-panel');
            const title   = document.getElementById('cal-detail-title');
            const dot     = document.getElementById('cal-detail-dot');
            const body    = document.getElementById('cal-detail-body');
            const bPatrol = document.getElementById('cal-detail-badge-patrol');
            const bMissed = document.getElementById('cal-detail-badge-missed');
            const cPatrol = document.getElementById('cal-detail-patrol-count');
            const cMissed = document.getElementById('cal-detail-missed-count');

            const d   = new Date(monthYear.year, monthYear.month - 1, day);
            title.textContent = `${daysFull[d.getDay()]}, ${day} ${monthsFull[monthYear.month - 1]} ${monthYear.year}`;

            const dayData = calendarData[day] ?? {};
            const pics    = Object.entries(dayData);
            const totP    = pics.reduce((s,[,v]) => s + v.patrol_count, 0);
            const totM    = pics.reduce((s,[,v]) => s + v.missed_count, 0);

            cPatrol.textContent = totP + ' patrol';
            cMissed.textContent = totM + ' missed';
            if (totM === 0) { bMissed.classList.add('hidden'); bMissed.classList.remove('inline-flex'); }
            else            { bMissed.classList.remove('hidden'); bMissed.classList.add('inline-flex'); }

            dot.className = 'h-3.5 w-3.5 rounded-full transition-all duration-300 ' +
                (!pics.length ? 'bg-gray-400' : totM === 0 ? 'bg-emerald-400' : totP === 0 ? 'bg-rose-400 animate-pulse' : 'bg-amber-400');

            body.innerHTML = '';
            body.style.opacity = '0';

            if (!pics.length) {
                body.innerHTML = `
                    <div class="col-span-full flex flex-col items-center justify-center py-16 text-center">
                        <div class="w-20 h-20 rounded-full bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center mb-4">
                            <svg class="h-10 w-10 text-indigo-300 dark:text-indigo-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/>
                            </svg>
                        </div>
                        <p class="text-sm font-bold text-indigo-400 dark:text-indigo-500">Tidak ada data patrol</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">untuk hari ini</p>
                    </div>`;
            } else {
                pics.forEach(([name, dv], idx) => {
                    const ck  = colorPalette[dv.color_index % colorPalette.length];
                    const av  = avatarColors[ck] ?? 'from-gray-400 to-gray-600';
                    const cb  = chipBg[dv.color_index % chipBg.length];
                    const ini = name.split(' ').slice(0,2).map(w=>(w[0]||'').toUpperCase()).join('');
                    const rt  = dv.total_assigned > 0 ? Math.round((dv.patrol_count/dv.total_assigned)*100) : 0;
                    const rg  = rt===100 ? 'from-emerald-500 to-teal-500' : rt>50 ? 'from-amber-400 to-orange-500' : 'from-rose-500 to-pink-500';
                    const rc  = rt===100 ? 'text-emerald-600 dark:text-emerald-400' : rt>50 ? 'text-amber-600 dark:text-amber-400' : 'text-rose-500 dark:text-rose-400';

                    const chip = document.createElement('div');
                    chip.className = `detail-chip fade-scale flex flex-col gap-3 rounded-2xl border ${cb} p-4 shadow-md`;
                    chip.style.animationDelay = `${idx*0.06}s`;
                    chip.style.animationFillMode = 'backwards';
                    chip.innerHTML = `
                        <div class="flex items-center gap-3">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br ${av} text-white text-base font-black shadow-lg flex-shrink-0">${esc(ini)}</div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-bold text-gray-900 dark:text-white truncate mb-1">${esc(name)}</p>
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center gap-1 text-xs font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/30 px-2 py-0.5 rounded-full border border-emerald-200 dark:border-emerald-700">
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>${dv.patrol_count}
                                    </span>
                                    ${dv.missed_count > 0 ? `
                                    <span class="inline-flex items-center gap-1 text-xs font-bold text-rose-500 dark:text-rose-400 bg-rose-50 dark:bg-rose-900/30 px-2 py-0.5 rounded-full border border-rose-200 dark:border-rose-700">
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>${dv.missed_count}
                                    </span>` : ''}
                                </div>
                            </div>
                            <span class="flex-shrink-0 text-sm font-black text-white px-2.5 py-1 rounded-full shadow-md bg-gradient-to-r ${rg}">${rt}%</span>
                        </div>
                        <div class="space-y-1.5">
                            <div class="flex justify-between text-xs">
                                <span class="text-gray-500 dark:text-gray-400 font-semibold">Progress Patrol</span>
                                <span class="font-bold ${rc}">${rt}%</span>
                            </div>
                            <div class="h-2.5 w-full rounded-full bg-gray-200 dark:bg-gray-700 overflow-hidden shadow-inner">
                                <div class="progress-bar h-full rounded-full bg-gradient-to-r ${rg}" style="width:0%"></div>
                            </div>
                        </div>`;
                    body.appendChild(chip);
                    requestAnimationFrame(() => {
                        const pb = chip.querySelector('.progress-bar');
                        if (pb) pb.style.width = rt + '%';
                    });
                });
            }

            body.style.opacity = '1';

            if (panel.classList.contains('hidden')) {
                panel.classList.remove('hidden','slide-down');
                panel.classList.add('slide-up');
                setTimeout(() => panel.scrollIntoView({ behavior:'smooth', block:'nearest' }), 50);
            }
        }

        function esc(t) { const d = document.createElement('div'); d.textContent = t; return d.innerHTML; }

        function closeDetail() {
            if (animating) return;
            animating = true;
            if (selectedDay !== null) {
                document.getElementById('cal-day-' + selectedDay)?.classList.remove('selected');
                selectedDay = null;
            }
            const panel = document.getElementById('cal-detail-panel');
            panel.classList.remove('slide-up');
            panel.classList.add('slide-down');
            setTimeout(() => { panel.classList.add('hidden'); panel.classList.remove('slide-down'); animating = false; }, 260);
        }

        document.addEventListener('keydown', e => {
            if (e.key === 'Escape' && selectedDay !== null) closeDetail();
            if (selectedDay !== null && (e.key === 'ArrowLeft' || e.key === 'ArrowRight')) {
                e.preventDefault();
                const n = selectedDay + (e.key === 'ArrowRight' ? 1 : -1);
                if (n >= 1 && n <= {{ $data['days_in_month'] }}) selectDay(n);
            }
        });

        document.addEventListener('click', e => {
            if (selectedDay !== null
                && !e.target.closest('.cal-cell')
                && !e.target.closest('#cal-detail-panel')) closeDetail();
        });
    </script>
    @endpush

</x-filament-panels::page>