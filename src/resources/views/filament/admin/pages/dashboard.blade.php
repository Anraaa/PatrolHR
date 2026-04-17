<x-filament-panels::page>
    @php
        $data = $this->getData();
    @endphp

    <div class="space-y-4">
        {{-- MONITORING PATROL TABLE --}}
        <div class="space-y-4">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Monitoring Patrol</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Rekap harian patrol 1 bulan</p>
                </div>
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                    <select wire:model.live="selectedMonth" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        @foreach($this->getMonths() as $num => $name)
                            <option value="{{ $num }}" @selected($num == $data['month'])>{{ $name }}</option>
                        @endforeach
                    </select>
                    <select wire:model.live="selectedYear" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        @foreach($this->getYears() as $year)
                            <option value="{{ $year }}" @selected($year == $data['year'])>{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Table --}}
            <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
                <table class="w-full border-collapse text-sm">
                    {{-- Header Row 1: Date Numbers --}}
                    <thead>
                        <tr class="bg-gray-100 dark:bg-gray-700">
                            <th class="sticky left-0 z-20 border border-gray-200 dark:border-gray-600 px-3 py-2 text-left text-xs font-semibold text-gray-900 dark:text-white bg-gray-100 dark:bg-gray-700" style="min-width: 140px" rowspan="2">
                                Nama PIC
                            </th>
                            <th class="sticky left-[140px] z-20 border border-gray-200 dark:border-gray-600 px-3 py-2 text-left text-xs font-semibold text-gray-900 dark:text-white bg-gray-100 dark:bg-gray-700" style="min-width: 160px" rowspan="2">
                                Checkpoint
                            </th>
                            @for($day = 1; $day <= $data['days_in_month']; $day++)
                                @php 
                                    $firstRowKey = array_key_first($data['table_data']) ?? null;
                                    $date = $firstRowKey && isset($data['table_data'][$firstRowKey]['daily_data'][$day]) 
                                        ? $data['table_data'][$firstRowKey]['daily_data'][$day]['date'] 
                                        : null;
                                @endphp
                                <th colspan="{{ count($data['shifts']) }}" class="border border-gray-200 dark:border-gray-600 px-1 py-2 text-center text-xs font-semibold text-gray-900 dark:text-white bg-gray-100 dark:bg-gray-700">
                                    <span class="block text-sm font-bold leading-tight">{{ $day }}</span>
                                    <span class="text-[10px] font-normal text-gray-400 dark:text-gray-500">{{ $date?->format('D') ?? '' }}</span>
                                </th>
                            @endfor
                        </tr>
                        
                        {{-- Header Row 2: Shift Numbers --}}
                        <tr class="bg-gray-100 dark:bg-gray-700 border-b-2 border-gray-300 dark:border-gray-500">
                            @for($day = 1; $day <= $data['days_in_month']; $day++)
                                @foreach($data['shifts'] as $shift)
                                    <th class="border border-gray-200 dark:border-gray-600 px-1 py-1.5 text-center text-[10px] font-semibold text-gray-500 dark:text-gray-400" style="min-width: 38px">
                                        {{ $shift->name }}
                                    </th>
                                @endforeach
                            @endfor
                        </tr>
                    </thead>

                    {{-- Body --}}
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($data['table_data'] as $rowKey => $row)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                {{-- User Name --}}
                                <td class="sticky left-0 z-10 border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-2 text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $row['user_name'] }}
                                </td>

                                {{-- Location/Checkpoint --}}
                                <td class="sticky left-[140px] z-10 border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-2">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $row['location_name'] }}</p>
                                        @if(count($row['shifts_used']) > 0)
                                            <div class="mt-1 flex gap-1 flex-wrap">
                                                @foreach($data['shifts'] as $shift)
                                                    <span class="inline-block px-1.5 py-0.5 text-[10px] font-medium rounded
                                                        {{ in_array($shift->id, $row['shifts_used'])
                                                            ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300'
                                                            : 'bg-gray-100 text-gray-400 line-through dark:bg-gray-700 dark:text-gray-500' }}">
                                                        {{ $shift->name }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </td>

                                {{-- Daily Cells - One cell per shift per day --}}
                                @for($day = 1; $day <= $data['days_in_month']; $day++)
                                    @php 
                                        $dayInfo = $row['daily_data'][$day];
                                        $isWeekend = in_array($dayInfo['date']->dayOfWeek, [0, 6]);
                                    @endphp
                                    @foreach($data['shifts'] as $shift)
                                        @php $status = $dayInfo['shifts_status'][$shift->id] ?? -1; @endphp
                                        <td class="border border-gray-200 dark:border-gray-600 px-1 py-2 text-center
                                            {{ $isWeekend ? 'bg-gray-50 dark:bg-gray-700/40' : 'bg-white dark:bg-gray-800' }}">
                                            @if($status === 1)
                                                {{-- HIJAU - Patrol ada --}}
                                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-md
                                                    bg-green-700 dark:bg-green-600
                                                    text-white text-xs font-bold
                                                    ring-1 ring-green-800/30 dark:ring-green-500/30
                                                    shadow-sm">
                                                    ✓
                                                </span>
                                            @elseif($status === 0)
                                                {{-- MERAH - Shift punya tapi ga ada patrol --}}
                                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-md
                                                    bg-red-700 dark:bg-red-600
                                                    text-white text-xs font-bold
                                                    ring-1 ring-red-800/30 dark:ring-red-500/30
                                                    shadow-sm">
                                                    ✗
                                                </span>
                                            @else
                                                {{-- EM DASH - Shift tidak ditugaskan --}}
                                                <span class="text-base font-medium text-gray-300 dark:text-gray-600 select-none">—</span>
                                            @endif
                                        </td>
                                    @endforeach
                                @endfor
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ 2 + ($data['days_in_month'] * count($data['shifts'])) }}"
                                    class="border border-gray-200 dark:border-gray-600 px-4 py-10 text-center text-sm text-gray-400 dark:text-gray-500">
                                    Tidak ada data untuk periode ini
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Legend --}}
            <div class="flex flex-wrap gap-6 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/60 p-4">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-green-700 dark:bg-green-600 text-white text-xs font-bold ring-1 ring-green-800/30 shadow-sm">✓</span>
                    <span class="text-sm text-gray-600 dark:text-gray-300">Patrol dilakukan</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-red-700 dark:bg-red-600 text-white text-xs font-bold ring-1 ring-red-800/30 shadow-sm">✗</span>
                    <span class="text-sm text-gray-600 dark:text-gray-300">Shift tidak patrol</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-base font-medium text-gray-300 dark:text-gray-600 select-none">—</span>
                    <span class="text-sm text-gray-600 dark:text-gray-300">Shift tidak ditugaskan</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="h-5 w-10 rounded bg-gray-50 dark:bg-gray-700/40 border border-gray-200 dark:border-gray-600"></div>
                    <span class="text-sm text-gray-600 dark:text-gray-300">Akhir pekan</span>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>