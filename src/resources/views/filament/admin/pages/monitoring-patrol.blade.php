<x-filament-panels::page>

    <script>
        document.addEventListener('livewire:initialized', function () {
            Livewire.on('open-url', function (data) {
                window.open(data.url ?? data[0]?.url ?? data, '_blank');
            });
        });
    </script>

    {{-- ── Filter Panel ────────────────────────────────────────────────────── --}}
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
        <div class="flex items-center justify-between border-b border-gray-200 px-5 py-3.5 dark:border-gray-700">
            <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                </svg>
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200">Filter Monitoring</h3>
            </div>
        </div>
        <div class="p-5">
            <form wire:submit.prevent>
                {{ $this->form }}
            </form>
            <div class="mt-4 flex flex-wrap items-center gap-2">
                <button
                    wire:click="loadData"
                    wire:loading.attr="disabled"
                    wire:target="loadData"
                    type="button"
                    class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-500 disabled:opacity-60">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" wire:loading.remove wire:target="loadData">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z"/>
                    </svg>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24" wire:loading wire:target="loadData">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                    </svg>
                    Terapkan Filter
                </button>
                <span class="ml-auto inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Petugas: <strong class="font-semibold text-gray-700 dark:text-gray-200">{{ count($monitoring) }}</strong>
                </span>
            </div>
        </div>
    </div>

    {{-- ── Legend ───────────────────────────────────────────────────────────── --}}
    <div class="mt-4 flex items-center gap-4 rounded-lg border border-gray-200 bg-white px-4 py-2.5 dark:border-gray-700 dark:bg-gray-900">
        <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Keterangan:</span>
        <span class="inline-flex items-center gap-1.5 text-xs text-gray-600 dark:text-gray-300">
            <span class="inline-block h-4 w-4 rounded bg-green-500"></span> Patrol
        </span>
        <span class="inline-flex items-center gap-1.5 text-xs text-gray-600 dark:text-gray-300">
            <span class="inline-block h-4 w-4 rounded bg-red-500"></span> Tidak Patrol
        </span>
        <span class="inline-flex items-center gap-1.5 text-xs text-gray-600 dark:text-gray-300">
            <span class="inline-block h-4 w-4 rounded bg-gray-200 dark:bg-gray-700"></span> Belum (hari mendatang)
        </span>
    </div>

    {{-- ── Monitoring Table ────────────────────────────────────────────────── --}}
    <div class="mt-4 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
        <div class="flex items-center justify-between border-b border-gray-200 px-5 py-3.5 dark:border-gray-700">
            <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200">Monitoring Patrol Bulanan</h3>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-[11px] dark:divide-gray-800">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800/60">
                        <th class="sticky left-0 z-10 bg-gray-50 dark:bg-gray-800/60 px-2 py-2 text-center font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 border-r border-gray-200 dark:border-gray-700" style="min-width:40px">No</th>
                        <th class="sticky left-[40px] z-10 bg-gray-50 dark:bg-gray-800/60 px-2 py-2 text-left font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 border-r border-gray-200 dark:border-gray-700" style="min-width:120px">Nama</th>
                        <th class="sticky left-[160px] z-10 bg-gray-50 dark:bg-gray-800/60 px-2 py-2 text-left font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 border-r border-gray-200 dark:border-gray-700" style="min-width:80px">NIP</th>
                        <th class="sticky left-[240px] z-10 bg-gray-50 dark:bg-gray-800/60 px-2 py-2 text-left font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 border-r border-gray-200 dark:border-gray-700" style="min-width:100px">Lokasi</th>
                        <th class="sticky left-[340px] z-10 bg-gray-50 dark:bg-gray-800/60 px-2 py-2 text-center font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 border-r border-gray-200 dark:border-gray-700" style="min-width:70px">Shift</th>

                        {{-- Date columns --}}
                        @foreach ($dates as $d)
                            <th class="px-1 py-2 text-center font-semibold text-gray-400 dark:text-gray-500 border-r border-gray-100 dark:border-gray-800" style="min-width:28px">{{ $d }}</th>
                        @endforeach

                        <th class="px-2 py-2 text-center font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500" style="min-width:50px">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($monitoring as $i => $row)
                        @php
                            $today = now()->day;
                            $isCurrentMonth = \Carbon\Carbon::parse($month ?? now())->format('Y-m') === now()->format('Y-m');
                        @endphp
                        <tr class="group transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/40">
                            {{-- No --}}
                            <td class="sticky left-0 z-10 bg-white dark:bg-gray-900 group-hover:bg-gray-50 dark:group-hover:bg-gray-800/40 px-2 py-2 text-center text-xs font-medium text-gray-400 border-r border-gray-200 dark:border-gray-700">{{ $i + 1 }}</td>

                            {{-- Nama --}}
                            <td class="sticky left-[40px] z-10 bg-white dark:bg-gray-900 group-hover:bg-gray-50 dark:group-hover:bg-gray-800/40 px-2 py-2 text-xs font-medium text-gray-800 dark:text-gray-100 border-r border-gray-200 dark:border-gray-700 whitespace-nowrap">{{ $row['name'] }}</td>

                            {{-- NIP --}}
                            <td class="sticky left-[160px] z-10 bg-white dark:bg-gray-900 group-hover:bg-gray-50 dark:group-hover:bg-gray-800/40 px-2 py-2 text-xs text-gray-600 dark:text-gray-300 border-r border-gray-200 dark:border-gray-700 font-mono">{{ $row['nip'] }}</td>

                            {{-- Lokasi --}}
                            <td class="sticky left-[240px] z-10 bg-white dark:bg-gray-900 group-hover:bg-gray-50 dark:group-hover:bg-gray-800/40 px-2 py-2 text-xs text-gray-600 dark:text-gray-300 border-r border-gray-200 dark:border-gray-700 whitespace-nowrap">{{ $row['location'] }}</td>

                            {{-- Shift --}}
                            <td class="sticky left-[340px] z-10 bg-white dark:bg-gray-900 group-hover:bg-gray-50 dark:group-hover:bg-gray-800/40 px-2 py-2 text-center border-r border-gray-200 dark:border-gray-700">
                                <span class="inline-flex items-center rounded-full px-1.5 py-0.5 text-[10px] font-semibold bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">{{ $row['shift'] }}</span>
                            </td>

                            {{-- Daily indicators --}}
                            @foreach ($dates as $d)
                                @php
                                    $hasPatrol = $row['daily_status'][$d] ?? false;
                                    $isFuture  = $isCurrentMonth && $d > $today;
                                    $isPast    = !$isCurrentMonth || $d <= $today;
                                @endphp
                                <td class="px-0 py-2 text-center border-r border-gray-100 dark:border-gray-800">
                                    @if ($isFuture)
                                        <span class="inline-block h-4 w-4 rounded bg-gray-200 dark:bg-gray-700" title="Tgl {{ $d }} - Belum"></span>
                                    @elseif ($hasPatrol)
                                        <span class="inline-block h-4 w-4 rounded bg-green-500" title="Tgl {{ $d }} - Patrol ✓"></span>
                                    @else
                                        <span class="inline-block h-4 w-4 rounded bg-red-500" title="Tgl {{ $d }} - Tidak Patrol ✗"></span>
                                    @endif
                                </td>
                            @endforeach

                            {{-- Total --}}
                            <td class="px-2 py-2 text-center">
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold
                                    {{ $row['total_patrol'] > 0 ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' }}">
                                    {{ $row['total_patrol'] }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 6 + $daysInMonth }}" class="px-4 py-8 text-center text-gray-400 dark:text-gray-500">
                                Tidak ada data petugas patrol.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── Recent Alerts ───────────────────────────────────────────────────── --}}
    <div class="mt-4 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
        <div class="flex items-center justify-between border-b border-gray-200 px-5 py-3.5 dark:border-gray-700">
            <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200">Alert Patrol Terbaru</h3>
            </div>
            @if (count($alerts) > 0)
                <span class="rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-semibold text-amber-700 dark:bg-amber-900/30 dark:text-amber-300">
                    {{ count($alerts) }} alert
                </span>
            @endif
        </div>

        <div class="divide-y divide-gray-100 dark:divide-gray-800">
            @forelse ($alerts as $alert)
                <div class="flex items-start gap-3 px-5 py-3">
                    <div class="mt-0.5 flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full {{ ($alert['status'] ?? 'sent') === 'read' ? 'bg-gray-100 dark:bg-gray-800' : 'bg-amber-100 dark:bg-amber-900/30' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 {{ ($alert['status'] ?? 'sent') === 'read' ? 'text-gray-400' : 'text-amber-600 dark:text-amber-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs text-gray-700 dark:text-gray-200">{{ $alert['message'] }}</p>
                        <p class="mt-0.5 text-[11px] text-gray-400 dark:text-gray-500">
                            {{ \Carbon\Carbon::parse($alert['created_at'])->diffForHumans() }}
                            @if (($alert['status'] ?? 'sent') === 'sent')
                                <span class="ml-1 inline-flex items-center rounded-full bg-amber-50 px-1.5 py-0.5 text-[10px] font-semibold text-amber-600 dark:bg-amber-900/20 dark:text-amber-400">Baru</span>
                            @endif
                        </p>
                    </div>
                </div>
            @empty
                <div class="px-5 py-6 text-center text-xs text-gray-400 dark:text-gray-500">
                    Tidak ada alert bulan ini.
                </div>
            @endforelse
        </div>
    </div>

</x-filament-panels::page>
