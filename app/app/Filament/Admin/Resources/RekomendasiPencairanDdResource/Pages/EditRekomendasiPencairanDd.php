<?php

namespace App\Filament\Admin\Resources\RekomendasiPencairanDdResource\Pages;

use App\Filament\Admin\Resources\RekomendasiPencairanDdResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRekomendasiPencairanDd extends EditRecord
{
    protected static string $resource = RekomendasiPencairanDdResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
