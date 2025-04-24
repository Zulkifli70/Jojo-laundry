<?php

namespace App\Filament\Resources\StokFuzzyResource\Pages;

use App\Filament\Resources\StokFuzzyResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStokFuzzy extends EditRecord
{
    protected static string $resource = StokFuzzyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
