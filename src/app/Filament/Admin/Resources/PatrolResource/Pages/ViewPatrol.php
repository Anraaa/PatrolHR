<?php

namespace App\Filament\Admin\Resources\PatrolResource\Pages;

use App\Filament\Admin\Resources\PatrolResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPatrol extends ViewRecord
{
    protected static string $resource = PatrolResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
