<?php

namespace App\Filament\Admin\Pages;

use App\Models\Location;
use App\Models\Patrol;
use App\Models\Shift;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;

class ChecksheetPatrol extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon  = 'heroicon-o-table-cells';
    protected static ?string $navigationGroup = 'Patroli';
    protected static ?string $navigationLabel = 'Checksheet Patrol';
    protected static ?int    $navigationSort  = 4;
    protected static ?string $slug            = 'patrols/checksheet';
    protected static ?string $title           = 'Checksheet Patrol';
    protected static string  $view            = 'filament.admin.pages.checksheet-patrol';

    // ── Filter state ──────────────────────────────────────────────────────────
    public ?string $date_from   = null;
    public ?string $date_until  = null;
    public ?int    $shift_id    = null;
    public ?int    $location_id = null;

    // ── Data ─────────────────────────────────────────────────────────────────
    public array $patrols = [];
    public int   $total   = 0;

    public function mount(): void
    {
        // Default: hari ini
        $this->date_from  = now()->toDateString();
        $this->date_until = now()->toDateString();
        $this->form->fill([
            'date_from'  => $this->date_from,
            'date_until' => $this->date_until,
        ]);
        $this->loadData();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('date_from')
                    ->label('Dari Tanggal')
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->closeOnDateSelection()
                    ->live(),

                DatePicker::make('date_until')
                    ->label('Sampai Tanggal')
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->closeOnDateSelection()
                    ->live(),

                Select::make('shift_id')
                    ->label('Shift')
                    ->options(fn () => Shift::pluck('name', 'id'))
                    ->placeholder('Semua Shift')
                    ->native(false)
                    ->live(),

                Select::make('location_id')
                    ->label('Area Patrol')
                    ->options(fn () => Location::pluck('name', 'id'))
                    ->placeholder('Semua Area')
                    ->searchable()
                    ->native(false)
                    ->live(),
            ])
            ->statePath('');
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['date_from', 'date_until', 'shift_id', 'location_id'])) {
            $this->loadData();
        }
    }

    public function loadData(): void
    {
        $query = Patrol::with(['shift', 'user', 'employee.department', 'location'])
            ->orderBy('patrol_time');

        if ($this->date_from) {
            $query->whereDate('patrol_time', '>=', $this->date_from);
        }
        if ($this->date_until) {
            $query->whereDate('patrol_time', '<=', $this->date_until);
        }
        if ($this->shift_id) {
            $query->where('shift_id', $this->shift_id);
        }
        if ($this->location_id) {
            $query->where('location_id', $this->location_id);
        }

        $collection    = $query->get();
        $this->total   = $collection->count();
        $this->patrols = $collection->toArray();
    }

    public function exportPdf(): void
    {
        $params = http_build_query(array_filter([
            'date_from'   => $this->date_from,
            'date_until'  => $this->date_until,
            'shift_id'    => $this->shift_id,
            'location_id' => $this->location_id,
        ], fn ($v) => $v !== null && $v !== ''));

        $this->dispatch('open-url', url: '/admin/patrols/checksheet/export-pdf?' . $params);
    }

    public function exportExcel(): void
    {
        $params = http_build_query(array_filter([
            'date_from'   => $this->date_from,
            'date_until'  => $this->date_until,
            'shift_id'    => $this->shift_id,
            'location_id' => $this->location_id,
        ], fn ($v) => $v !== null && $v !== ''));

        $this->dispatch('open-url', url: '/admin/patrols/checksheet/export-excel?' . $params);
    }
}
