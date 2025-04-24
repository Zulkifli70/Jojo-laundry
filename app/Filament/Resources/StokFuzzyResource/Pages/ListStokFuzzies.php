<?php

namespace App\Filament\Resources\StokFuzzyResource\Pages;

use App\Filament\Resources\StokFuzzyResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStokFuzzies extends ListRecords
{
    protected static string $resource = StokFuzzyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
            ->label('Perencanaan Baru')
            ->icon('heroicon-s-plus'),
        ];
    }
}
