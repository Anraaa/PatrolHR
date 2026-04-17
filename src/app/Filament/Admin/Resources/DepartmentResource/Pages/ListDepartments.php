<?php

namespace App\Filament\Admin\Resources\DepartmentResource\Pages;

use App\Filament\Admin\Resources\DepartmentResource;
use Filament\Resources\Pages\ListRecords;

class ListDepartments extends ListRecords
{
    protected static string $resource = DepartmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
