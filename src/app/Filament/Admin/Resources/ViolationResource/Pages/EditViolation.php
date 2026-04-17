<?php

namespace App\Filament\Admin\Resources\ViolationResource\Pages;

use App\Filament\Admin\Resources\ViolationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditViolation extends EditRecord
{
    protected static string $resource = ViolationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
