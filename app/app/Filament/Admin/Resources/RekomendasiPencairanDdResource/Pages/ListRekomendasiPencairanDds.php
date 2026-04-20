<?php

namespace App\Filament\Admin\Resources\RekomendasiPencairanDdResource\Pages;

use App\Filament\Admin\Resources\RekomendasiPencairanDdResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRekomendasiPencairanDds extends ListRecords
{
    protected static string $resource = RekomendasiPencairanDdResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Ajukan Pencairan'),
        ];
    }
}
