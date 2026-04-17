<?php

namespace App\Filament\Admin\Resources\ShiftResource\Pages;

use App\Filament\Admin\Resources\ShiftResource;
use Filament\Resources\Pages\ListRecords;

class ListShifts extends ListRecords
{
    protected static string $resource = ShiftResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
