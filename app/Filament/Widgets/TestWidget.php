<?php

namespace App\Filament\Widgets;

use App\Models\transaksi;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use GrahamCampbell\ResultType\Success;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class TestWidget extends BaseWidget
{

    use InteractsWithPageFilters;
    use HasWidgetShield;

    protected static ?int $sort = 1;

    protected function getStats(): array


    {       

        $start = $this->filters['startDate'];
        $end = $this->filters['endDate'];
        

        return [
            Stat::make('Jumlah Pesanan',transaksi::when(

            $start,
            fn ($query) => $query->whereDate('created_at','>', $start)
            )
                -> when(
                    $end,
                    fn ($query) => $query->whereDate('created_at','<', $end)
                )
                ->count()
            )
                ->description('Pesanan yang sedang berjalan saat ini')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([1, 3, 5, 10, 20, 50])
                ->color('success'),

            Stat::make('Jumlah Pesanan Regular', transaksi::where('service_type', '=', 'regular')
                ->when($start, fn ($query) => $query->whereDate('created_at', '>', $start))
                ->when($end, fn ($query) => $query->whereDate('created_at', '<', $end))
                ->count()
            )
                ->description('Pesanan regular yang sedang berjalan')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([1, 3, 5, 10, 20, 50])
                ->color('success'),

                Stat::make('Jumlah Pesanan Delivery', transaksi::where('shipping_method', '=', 'delivery')
                ->when($start, fn ($query) => $query->whereDate('created_at', '>', $start))
                ->when($end, fn ($query) => $query->whereDate('created_at', '<', $end))
                ->count()
            )
                ->description('Pesanan delivery yang sedang berjalan')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([1, 3, 5, 10, 20, 50])
                ->color('success'),
        ];

        
    }

    
}
