<?php

namespace App\Filament\Resources\TransaksiResource\Pages;

use App\Filament\Resources\TransaksiResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListTransaksis extends ListRecords
{
    protected static string $resource = TransaksiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
            ->label('Transaksi Baru')
            ->icon('heroicon-s-plus'), 
        ];
    }

    public function getTabs(): array
    {
        return [
            null => Tab::make('Semua'),
            'Baru' => Tab::make()->query(fn ($query) => $query->where('status', 'baru')),
            'Proses' => Tab::make()->query(fn ($query) => $query->where('status', 'proses')),
            'Dikemas' => Tab::make()->query(fn ($query) => $query->where('status', 'dikemas')),
            'Selesai' => Tab::make()->query(fn ($query) => $query->where('status', 'selesai')),
            'Batal' => Tab::make()->query(fn ($query) => $query->where('status', 'batal')),
        ];
    }

}
