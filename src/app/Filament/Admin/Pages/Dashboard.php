<?php

namespace App\Filament\Admin\Pages;

use App\Models\Location;
use App\Models\Patrol;
use App\Models\Shift;
use App\Models\User;
use Carbon\Carbon;
use Filament\Pages\Page;

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
     */
    public function getMonitoringPatrolData(): array
    {
        $month = $this->selectedMonth ?? now()->month;
        $year = $this->selectedYear ?? now()->year;

        $monthStart = Carbon::create($year, $month, 1);
        $monthEnd = $monthStart->copy()->endOfMonth();
        $daysInMonth = $monthEnd->day;

        $users = User::where('role', 'pic')
            ->orderBy('name')
            ->get();

        $locations = Location::orderBy('name')->get();
        $shifts = Shift::orderBy('id')->get();

        $tableData = [];
        
        foreach ($users as $user) {
            foreach ($locations as $location) {
                $rowKey = $user->id . '_' . $location->id;
                
                $patrols = Patrol::where('user_id', $user->id)
                    ->where('location_id', $location->id)
                    ->whereBetween('patrol_time', [$monthStart, $monthEnd])
                    ->get();

                $shiftsUsed = $patrols->pluck('shift_id')->unique()->values()->toArray();

                $dailyData = [];
                for ($day = 1; $day <= $daysInMonth; $day++) {
                    $date = Carbon::create($year, $month, $day);

                    
                    // Get patrol status for each shift on this day
                    $shiftsStatus = [];
                    foreach ($shifts as $shift) {
                        // Check if user has patrol for this shift on this day
                        $hasPatrol = $patrols->contains(fn ($p) => 
                            $p->patrol_time->toDateString() === $date->toDateString() &&
                            $p->shift_id === $shift->id
                        );
                        
                        // Status: 1 = patrol ada (hijau), 0 = shift punya tapi ga ada patrol (merah), -1 = shift tidak ditugaskan (dash)
                        if ($hasPatrol) {
                            $shiftsStatus[$shift->id] = 1;  // Patrol exists - green checkmark
                        } elseif (in_array($shift->id, $shiftsUsed)) {
                            $shiftsStatus[$shift->id] = 0;  // Shift assigned but no patrol - red X
                        } else {
                            $shiftsStatus[$shift->id] = -1; // Shift not assigned - dash
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
                    'location_id' => $location->id,
                    'location_name' => $location->name,
                    'shifts_used' => $shiftsUsed,
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
