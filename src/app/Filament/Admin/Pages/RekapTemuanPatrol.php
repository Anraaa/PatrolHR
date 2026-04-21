<?php

namespace App\Filament\Admin\Pages;

use App\Models\Patrol;
use App\Models\Shift;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;

class RekapTemuanPatrol extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationGroup = 'Patroli';
    protected static ?string $navigationLabel = 'Rekap Temuan Patrol';
    protected static ?int    $navigationSort  = 3;
    protected static ?string $slug            = 'patrols/rekap-temuan';
    protected static ?string $title           = 'Rekap Temuan Patrol';
    protected static string  $view            = 'filament.admin.pages.rekap-temuan-patrol';

    // ── Filter state ──────────────────────────────────────────────────────────
    public ?string $date_from    = null;
    public ?string $date_until   = null;
    public ?int    $shift_id     = null;
    public ?string $shfgroup     = null;
    public bool    $only_violations = false;

    // ── Computed data ─────────────────────────────────────────────────────────
    public array $patrols = [];
    public int   $total   = 0;

    public function mount(): void
    {
        $this->form->fill([]);
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

                Select::make('shfgroup')
                    ->label('Shift Group')
                    ->options([
                        'A' => 'Group A',
                        'B' => 'Group B',
                        'C' => 'Group C',
                        'D' => 'Group D',
                    ])
                    ->placeholder('Semua Group')
                    ->native(false)
                    ->live(),

                Select::make('only_violations')
                    ->label('Tampilkan')
                    ->options([
                        0 => 'Semua (ada & tidak ada temuan)',
                        1 => 'Hanya yang ada pelanggaran',
                    ])
                    ->default(0)
                    ->native(false)
                    ->live(),
            ])
            ->statePath('');
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['date_from', 'date_until', 'shift_id', 'shfgroup', 'only_violations'])) {
            $this->loadData();
        }
    }

    public function loadData(): void
    {
        $query = Patrol::with(['employee', 'shift', 'location', 'violation', 'action', 'user'])
            ->orderByDesc('patrol_time');

        if ($this->date_from) {
            $query->whereDate('patrol_time', '>=', $this->date_from);
        }
        if ($this->date_until) {
            $query->whereDate('patrol_time', '<=', $this->date_until);
        }
        if ($this->shift_id) {
            $query->where('shift_id', $this->shift_id);
        }
        if ($this->shfgroup) {
            $query->whereHas('employee', fn ($q) => $q->where('shfgroup', $this->shfgroup));
        }
        if ($this->only_violations) {
            $query->whereNotNull('employee_id');
        }

        $collection = $query->get();
        $this->total   = $collection->count();
        $this->patrols = $collection->toArray();
    }

    public function exportPdf(): void
    {
        $params = http_build_query(array_filter([
            'date_from'       => $this->date_from,
            'date_until'      => $this->date_until,
            'shift_id'        => $this->shift_id,
            'shfgroup'        => $this->shfgroup,
            'only_violations' => $this->only_violations ? 1 : 0,
        ], fn ($v) => $v !== null && $v !== '' && $v !== false));

        $this->dispatch('open-url', url: '/admin/patrols/rekap-temuan/export-pdf?' . $params);
    }

    public function exportExcel(): void
    {
        $params = http_build_query(array_filter([
            'date_from'       => $this->date_from,
            'date_until'      => $this->date_until,
            'shift_id'        => $this->shift_id,
            'shfgroup'        => $this->shfgroup,
            'only_violations' => $this->only_violations ? 1 : 0,
        ], fn ($v) => $v !== null && $v !== '' && $v !== false));

        $this->dispatch('open-url', url: '/admin/patrols/rekap-temuan/export-excel?' . $params);
    }

    private function buildFilterLabel(): string
    {
        $parts = [];
        if ($this->date_from && $this->date_until) {
            $parts[] = 'Tgl: ' . \Carbon\Carbon::parse($this->date_from)->format('d/m/Y')
                . ' – ' . \Carbon\Carbon::parse($this->date_until)->format('d/m/Y');
        } elseif ($this->date_from) {
            $parts[] = 'Dari: ' . \Carbon\Carbon::parse($this->date_from)->format('d/m/Y');
        } elseif ($this->date_until) {
            $parts[] = 'Sampai: ' . \Carbon\Carbon::parse($this->date_until)->format('d/m/Y');
        }
        if ($this->shift_id) {
            $parts[] = 'Shift: ' . (Shift::find($this->shift_id)?->name ?? '-');
        }
        if ($this->shfgroup) {
            $parts[] = 'Group: ' . $this->shfgroup;
        }
        if ($this->only_violations) {
            $parts[] = 'Hanya pelanggaran';
        }
        return implode(' | ', $parts);
    }
}
