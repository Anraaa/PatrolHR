<?php

namespace App\Filament\Admin\Resources\PatrolResource\Pages;

use App\Filament\Admin\Resources\PatrolResource;
use App\Models\Patrol;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;
use pxlrbt\FilamentExcel\Actions\Pages\ExportAction;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class ListPatrols extends ListRecords
{
    protected static string $resource = PatrolResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),

            // FR-2.03: Export Excel/CSV
            ExportAction::make()
                ->label('Export Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->exports([
                    ExcelExport::make('patrols')
                        ->fromTable()
                        ->withFilename(fn () => 'laporan-patroli-' . now()->format('Y-m-d'))
                        ->withColumns([
                            Column::make('patrol_time')->heading('Tanggal Patrol'),
                            Column::make('employee.nip')->heading('NIP'),
                            Column::make('employee.name')->heading('Nama Karyawan'),
                            Column::make('employee.department.name')->heading('Departemen'),
                            Column::make('shift.name')->heading('Grup-Shift'),
                            Column::make('description')->heading('Item Temuan'),
                            Column::make('violation.name')->heading('Jenis Pelanggaran'),
                            Column::make('action.name')->heading('Action Temuan'),
                            Column::make('location.name')->heading('Lokasi Patrol'),
                            Column::make('user.name')->heading('PIC'),
                        ]),
                ]),

            // FR-2.03: Export PDF
            Actions\Action::make('exportPdf')
                ->label('Export PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('danger')
                ->action(function () {
                    $query = Patrol::with(['employee.department', 'shift', 'location', 'violation', 'action', 'user'])
                        ->orderByDesc('patrol_time');

                    // Apply current table filters if they exist
                    $tableFilters = $this->tableFilters ?? [];

                    if (! empty($tableFilters['patrol_date']['from'] ?? null)) {
                        $query->whereDate('patrol_time', '>=', $tableFilters['patrol_date']['from']);
                    }
                    if (! empty($tableFilters['patrol_date']['until'] ?? null)) {
                        $query->whereDate('patrol_time', '<=', $tableFilters['patrol_date']['until']);
                    }
                    if (! empty($tableFilters['shift_id']['value'] ?? null)) {
                        $query->where('shift_id', $tableFilters['shift_id']['value']);
                    }
                    if (! empty($tableFilters['violation_id']['value'] ?? null)) {
                        $query->where('violation_id', $tableFilters['violation_id']['value']);
                    }

                    $patrols = $query->get();

                    $pdf = Pdf::loadView('exports.patrols-pdf', [
                        'patrols' => $patrols,
                        'generated_at' => now()->format('d/m/Y H:i'),
                    ])->setPaper('a4', 'landscape');

                    return response()->streamDownload(
                        fn () => print($pdf->output()),
                        'laporan-patroli-' . now()->format('Y-m-d') . '.pdf'
                    );
                }),

        ];
    }
}
