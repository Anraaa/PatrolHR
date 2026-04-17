<?php

namespace App\Filament\Admin\Resources\PatrolResource\Pages;

use App\Filament\Admin\Resources\PatrolResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPatrol extends EditRecord
{
    protected static string $resource = PatrolResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
