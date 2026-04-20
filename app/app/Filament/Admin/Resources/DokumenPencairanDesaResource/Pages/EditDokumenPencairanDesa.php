<?php

namespace App\Filament\Admin\Resources\DokumenPencairanDesaResource\Pages;

use App\Filament\Admin\Resources\DokumenPencairanDesaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDokumenPencairanDesa extends EditRecord
{
    protected static string $resource = DokumenPencairanDesaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
