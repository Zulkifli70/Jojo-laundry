<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\transaksi;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use GrahamCampbell\ResultType\Success;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Illuminate\Support\Carbon;

class PendapatanOverview extends BaseWidget
{
    use HasWidgetShield;
    
    protected function getStats(): array
    {
        $start = $this->filters['startDate'] ?? Carbon::now()->startOfMonth();
        $end = $this->filters['endDate'] ?? Carbon::now()->endOfMonth();

        // Get current month's total income
        $currentMonthIncome = transaksi::where('status', '!=', 'Batal')
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('total_harga');

        // Get previous month's total income
        $previousMonthIncome = transaksi::where('status', '!=', 'Batal')
            ->whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->whereYear('created_at', Carbon::now()->subMonth()->year)
            ->sum('total_harga');

        // Calculate percentage change
        $percentageChange = $previousMonthIncome > 0 
            ? (($currentMonthIncome - $previousMonthIncome) / $previousMonthIncome) * 100 
            : 0;

        // Get income breakdown by service type
        $regularIncome = transaksi::where('status', '!=', 'Batal')
            ->where('service_type', '=', 'regular')
            ->when($start, fn ($query) => $query->whereDate('created_at', '>=', $start))
            ->when($end, fn ($query) => $query->whereDate('created_at', '<=', $end))
            ->sum('total_harga');

        $expressIncome = transaksi::where('status', '!=', 'Batal')
            ->where('service_type', '=', 'express')
            ->when($start, fn ($query) => $query->whereDate('created_at', '>=', $start))
            ->when($end, fn ($query) => $query->whereDate('created_at', '<=', $end))
            ->sum('total_harga');

        return [
            Stat::make('Total Pendapatan Bulan Ini', 'Rp ' . number_format($currentMonthIncome, 0, ',', '.'))
                ->description($percentageChange >= 0 ? 'Naik ' . number_format(abs($percentageChange), 1) . '%' : 'Turun ' . number_format(abs($percentageChange), 1) . '%')
                ->descriptionIcon($percentageChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($percentageChange >= 0 ? 'success' : 'danger')
                ->chart([
                    $previousMonthIncome / 1000000, 
                    $currentMonthIncome / 1000000
                ]),

            Stat::make('Pendapatan Regular', 'Rp ' . number_format($regularIncome, 0, ',', '.'))
                ->description('Dari layanan regular')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success')
                ->chart([
                    $regularIncome / 1000000
                ]),

            Stat::make('Pendapatan Express', 'Rp ' . number_format($expressIncome, 0, ',', '.'))
                ->description('Dari layanan express')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success')
                ->chart([
                    $expressIncome / 1000000
                ]),
        ];
    }
}