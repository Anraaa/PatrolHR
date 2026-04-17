<?php

namespace App\Filament\Admin\Resources\ViolationResource\Pages;

use App\Filament\Admin\Resources\ViolationResource;
use Filament\Resources\Pages\ListRecords;

class ListViolations extends ListRecords
{
    protected static string $resource = ViolationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
