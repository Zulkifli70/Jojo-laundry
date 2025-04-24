<?php

namespace App\Filament\Widgets;

use App\Models\Pelanggan;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use GrahamCampbell\ResultType\Success;

class PelangganWidget extends BaseWidget
{
    
    protected function getStats(): array
    {
        return [
            Stat::make('Total Pelanggan', Pelanggan::count())
            ->icon('heroicon-o-user-group')
            ->color('Success'),
        ];
    }
}
