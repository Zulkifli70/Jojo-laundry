<?php

namespace App\Filament\Resources\PelangganResource\Pages;

use App\Filament\Resources\PelangganResource;
use App\Filament\Widgets\PelangganWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPelanggans extends ListRecords
{
    protected static string $resource = PelangganResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
            ->label('Pelanggan Baru')
            ->icon('heroicon-s-plus'),
            
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            PelangganWidget::class,
        ];
    }
}
