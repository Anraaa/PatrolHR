<x-filament-panels::page>

    {{-- Listener untuk open-url (export PDF) dari Livewire dispatch --}}
    <script>
        document.addEventListener('livewire:initialized', function () {
            Livewire.on('open-url', function (data) {
                window.open(data.url ?? data[0]?.url ?? data, '_blank');
            });
        });
    </script>

    {{-- ── Filter Panel ───────────────────────────────────────────────────── --}}
    <x-filament::section icon="heroicon-o-funnel" heading="Filter & Export Data">
        <form wire:submit.prevent>
            {{ $this->form }}
        </form>

        <div class="mt-6 flex flex-col md:flex-row items-center gap-4">
            <x-filament::button wire:click="loadData" icon="heroicon-m-magnifying-glass">
                Terapkan Filter
            </x-filament::button>

            <x-filament::button wire:click="exportPdf" color="danger" icon="heroicon-m-document-arrow-down">
                Export PDF
            </x-filament::button>

            <x-filament::button wire:click="exportExcel" color="success" icon="heroicon-m-table-cells">
                Export Excel
            </x-filament::button>

            <div class="ml-auto w-full md:w-auto flex items-center justify-between md:justify-start gap-3 rounded-lg bg-gray-50 px-4 py-2.5 text-sm text-gray-600 dark:bg-white/5 dark:text-gray-300 border border-gray-200 dark:border-white/10 transition-colors">
                <span class="flex items-center gap-2">
                    <x-filament::icon icon="heroicon-m-chart-bar" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                    <span class="font-medium">Total data:</span>
                </span>
                <strong class="text-gray-900 dark:text-white text-base">{{ $total }}</strong>
            </div>
        </div>
    </x-filament::section>

    {{-- ── Tabel Rekap ─────────────────────────────────────────────────────── --}}
    <x-filament::section>
        <div class="overflow-x-auto -mx-6 -my-6">
            <table class="w-full text-left divide-y divide-gray-200 dark:divide-white/5">
                <thead class="bg-gray-50 dark:bg-white/5">
                    <tr>
                        <th class="px-4 py-3 text-center text-sm font-semibold text-gray-900 dark:text-white">No</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">Tanggal</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">Shift</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">Group / Dept</th>
                        <th class="px-4 py-3 text-center text-sm font-semibold text-gray-900 dark:text-white">Jam</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white whitespace-nowrap">Area / Lokasi</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">Temuan / Pelanggaran</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">Identitas Karyawan</th>
                        <th class="px-4 py-3 text-center text-sm font-semibold text-gray-900 dark:text-white">Evidence</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">Sanksi / Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                    @forelse ($patrols as $i => $row)
                        @php
                            $patrolTime = $row['patrol_time'] ? \Carbon\Carbon::parse($row['patrol_time']) : null;
                            $employee   = $row['employee'] ?? null;
                            $shfgroup   = $employee['shfgroup'] ?? '—';
                            $shift      = $row['shift'] ?? null;
                            $location   = $row['location'] ?? null;
                            $violation  = $row['violation'] ?? null;
                            $action     = $row['action'] ?? null;
                            $photos     = $row['photos'] ?? [];
                            $actionName = $action['name'] ?? null;
                            $actionColor = match(true) {
                                $actionName && (str_contains($actionName, 'SP') || str_contains($actionName, 'PHK')) => 'danger',
                                $actionName && (str_contains($actionName, 'Peringatan') || str_contains($actionName, 'Teguran')) => 'warning',
                                $actionName && (str_contains($actionName, 'Pernyataan') || str_contains($actionName, 'Pembinaan')) => 'info',
                                $actionName => 'success',
                                default => 'gray',
                            };
                        @endphp
                        <tr class="transition hover:bg-gray-50 dark:hover:bg-white/5">
                            {{-- 1. No --}}
                            <td class="px-4 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                {{ $i + 1 }}
                            </td>

                            {{-- 2. Tanggal --}}
                            <td class="px-4 py-4 text-sm">
                                <div class="font-medium text-gray-900 dark:text-white whitespace-nowrap">
                                    {{ $patrolTime?->format('d M Y') ?? '—' }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 whitespace-nowrap">
                                    {{ $patrolTime?->diffForHumans() }}
                                </div>
                            </td>

                            {{-- 3. Shift --}}
                            <td class="px-4 py-4 text-sm">
                                @if ($shift)
                                    <x-filament::badge color="info">
                                        {{ $shift['name'] }}
                                    </x-filament::badge>
                                @else
                                    <span class="text-gray-500 dark:text-gray-400">—</span>
                                @endif
                            </td>

                            {{-- 4. Group / Dept --}}
                            <td class="px-4 py-4 text-sm text-gray-900 dark:text-white">
                                {{ $shfgroup }}
                            </td>

                            {{-- 5. Jam --}}
                            <td class="px-4 py-4 text-center text-sm font-mono text-gray-900 dark:text-white">
                                <x-filament::badge color="gray">
                                    {{ $patrolTime?->format('H:i') ?? '—' }}
                                </x-filament::badge>
                            </td>

                            {{-- 6. Area --}}
                            <td class="px-4 py-4 text-sm text-gray-900 dark:text-white min-w-[150px]">
                                {{ $location['name'] ?? '—' }}
                            </td>

                            {{-- 7. Temuan (Pelanggaran) --}}
                            <td class="px-4 py-4 text-sm min-w-[150px]">
                                @if ($violation)
                                    <x-filament::badge color="danger">
                                        {{ $violation['name'] }}
                                    </x-filament::badge>
                                @else
                                    <span class="inline-flex items-center gap-1.5 text-success-600 dark:text-success-400 font-medium">
                                        <x-filament::icon icon="heroicon-m-check-circle" class="h-5 w-5" />
                                        <span>Tidak ada temuan</span>
                                    </span>
                                @endif
                            </td>

                            {{-- 8. Identitas Karyawan --}}
                            <td class="px-4 py-4 text-sm min-w-[150px]">
                                @if ($employee)
                                    <div class="font-semibold text-gray-900 dark:text-white">{{ $employee['name'] }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">NIP: {{ $employee['nip'] }}</div>
                                @else
                                    <span class="text-gray-500 dark:text-gray-400">—</span>
                                @endif
                            </td>

                            {{-- 9. Evidence --}}
                            <td class="px-4 py-4 text-center">
                                @if (!empty($photos))
                                    <div class="flex flex-wrap justify-center gap-2">
                                        @foreach (array_slice((array) $photos, 0, 2) as $photo)
                                            @if ($photo)
                                                <a href="{{ Storage::url($photo) }}" target="_blank" class="block group">
                                                    <img src="{{ Storage::url($photo) }}"
                                                        alt="bukti"
                                                        class="h-12 w-12 rounded-lg border border-gray-200 object-cover shadow-sm transition-all duration-300 group-hover:scale-110 group-hover:shadow-md group-hover:border-primary-500 dark:border-white/10 dark:group-hover:border-primary-400">
                                                </a>
                                            @endif
                                        @endforeach
                                        @if(count((array) $photos) > 2)
                                            <div class="h-12 w-12 rounded-lg bg-gray-100 dark:bg-white/5 flex items-center justify-center text-xs font-semibold text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-white/10" title="{{ count((array) $photos) - 2 }} foto lainnya">
                                                +{{ count((array) $photos) - 2 }}
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-xs italic text-gray-500 dark:text-gray-400">Tidak ada</span>
                                @endif
                            </td>

                            {{-- 10. Sanksi --}}
                            <td class="px-4 py-4 text-sm min-w-[120px]">
                                @if ($actionName)
                                    <x-filament::badge :color="$actionColor">
                                        {{ $actionName }}
                                    </x-filament::badge>
                                @else
                                    <span class="text-gray-500 dark:text-gray-400">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-16 text-center">
                                <div class="mx-auto flex max-w-sm flex-col items-center justify-center gap-4">
                                    <div class="rounded-full bg-gray-100 p-4 dark:bg-white/5">
                                        <x-filament::icon icon="heroicon-o-document-magnifying-glass" class="h-8 w-8 text-gray-400 dark:text-gray-500" />
                                    </div>
                                    <div>
                                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                                            Tidak Ada Data
                                        </h3>
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                            Belum ada rekap temuan patroli yang sesuai dengan filter yang Anda tentukan. Silakan ubah filter untuk mencari ulang.
                                        </p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>

</x-filament-panels::page>
