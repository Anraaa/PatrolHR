<?php

namespace App\Filament\Admin\Pages;

use App\Events\PatrolQrScanned;
use App\Models\Location;
use App\Models\Patrol;
use App\Models\Shift;
use App\Models\User;
use Carbon\Carbon;
use Filament\Pages\Page;
use Livewire\Attributes\On;

class Dashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $navigationLabel = 'Dashboard';
    protected static ?string $title = 'Dashboard';
    protected static string $view = 'filament.admin.pages.dashboard';
    protected static ?int $navigationSort = -2;
    protected static string $routePath = '/';

    public ?int $selectedMonth = null;
    public ?int $selectedYear = null;

    public function mount(): void
    {
        $this->selectedMonth = now()->month;
        $this->selectedYear = now()->year;
    }

    public function updatedSelectedMonth(): void
    {
        // Trigger re-render
    }

    public function updatedSelectedYear(): void
    {
        // Trigger re-render
    }

    #[On('patrolQrScanned')]
    public function onPatrolQrScanned(): void
    {
        // Refresh data ketika ada QR scan - component akan re-render
        $this->dispatch('refresh-component');
    }

    public function getWidgets(): array
    {
        return [];
    }

    public function getVisibleWidgets(): array
    {
        return [];
    }

    public function getData(): array
    {
        return $this->getMonitoringPatrolData();
    }

    /**
     * Get monitoring patrol data for the selected month/year
     * Data diambil dari SEMUA akun user, termasuk yang belum melakukan patrol
     */
    public function getMonitoringPatrolData(): array
    {
        $month = $this->selectedMonth ?? now()->month;
        $year = $this->selectedYear ?? now()->year;

        $monthStart = Carbon::create($year, $month, 1);
        $monthEnd = $monthStart->copy()->endOfMonth();
        $daysInMonth = $monthEnd->day;

        // Ambil SEMUA user, bahkan yang belum melakukan patrol
        $users = User::orderBy('name')->get();

        $locations = Location::orderBy('name')->get();
        $shifts = Shift::orderBy('id')->get();

        $tableData = [];
        
        foreach ($users as $user) {
            $rowSpan = $locations->count();

            foreach ($locations as $index => $location) {
                $rowKey = $user->id . '_' . $location->id;
                
                // Ambil patrols yang sudah ter-validasi via QR code scan
                $patrols = Patrol::where('user_id', $user->id)
                    ->where('location_id', $location->id)
                    ->whereBetween('patrol_time', [$monthStart, $monthEnd])
                    ->whereNotNull('qr_scanned_at')  // Hanya patrol yang sudah di-scan QR code
                    ->get();

                $shiftsUsed = $patrols->pluck('shift_id')->unique()->values()->toArray();
                
                // Hitung total locations yang harus di-patrol per user
                $totalLocations = $locations->count();
                // Hitung berapa lokasi yang sudah di-patrol (minimum 1 patrol QR-validated per lokasi)
                $locationsPatrolledCount = Patrol::where('user_id', $user->id)
                    ->whereBetween('patrol_time', [$monthStart, $monthEnd])
                    ->whereNotNull('qr_scanned_at')
                    ->distinct('location_id')
                    ->count('location_id');

                $dailyData = [];
                for ($day = 1; $day <= $daysInMonth; $day++) {
                    $date = Carbon::create($year, $month, $day);

                    // Get patrol status untuk setiap shift pada hari ini
                    $shiftsStatus = [];
                    foreach ($shifts as $shift) {
                        // Check jika user sudah scan QR untuk location ini di shift ini pada hari ini
                        $validatedPatrol = $patrols->firstWhere(fn ($p) => 
                            $p->patrol_time->toDateString() === $date->toDateString() &&
                            $p->shift_id === $shift->id &&
                            $p->isValidated()  // Gunakan method untuk check validasi
                        );
                        
                        // Status: 
                        // 1 = patrol sudah dilakukan + QR code ter-scan (hijau ✓)
                        // 0 = shift punya tapi belum scan QR (merah ✗)
                        // -1 = shift tidak ditugaskan (dash —)
                        if ($validatedPatrol) {
                            $shiftsStatus[$shift->id] = 1;  // QR code validated
                        } elseif (in_array($shift->id, $shiftsUsed)) {
                            $shiftsStatus[$shift->id] = 0;  // Patrol exists but not validated
                        } else {
                            $shiftsStatus[$shift->id] = -1; // Not assigned
                        }
                    }
                    
                    $dailyData[$day] = [
                        'date' => $date,
                        'shifts_status' => $shiftsStatus,
                    ];
                }

                $tableData[$rowKey] = [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'user_email' => $user->email,
                    'show_user_name' => $index === 0,
                    'row_span' => $index === 0 ? $rowSpan : 0,
                    'location_id' => $location->id,
                    'location_name' => $location->name,
                    'shifts_used' => $shiftsUsed,
                    'total_locations' => $totalLocations,
                    'locations_patrolled' => $locationsPatrolledCount,
                    'daily_data' => $dailyData,
                ];
            }
        }

        return [
            'table_data' => $tableData,
            'month' => $month,
            'year' => $year,
            'days_in_month' => $daysInMonth,
            'month_name' => $monthStart->translatedFormat('F Y'),
            'users' => $users,
            'locations' => $locations,
            'shifts' => $shifts,
        ];
    }

    /**
     * Get months list in Indonesian
     */
    public function getMonths(): array
    {
        return [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
    }

    /**
     * Get years for filter
     */
    public function getYears(): array
    {
        $currentYear = now()->year;
        return array_combine(
            range($currentYear - 2, $currentYear + 1),
            range($currentYear - 2, $currentYear + 1)
        );
    }
}
