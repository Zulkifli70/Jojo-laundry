<?php

namespace App\Filament\Widgets;

use App\Models\StokFuzzy;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;
use Filament\Support\Colors\Color;
use Filament\Notifications\Notification;

class PerencanaanWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    protected static ?string $pollingInterval = '60s';
    
    protected function getStats(): array
    {
        $now = Carbon::now();
        $endOfMonth = Carbon::now()->endOfMonth()->endOfDay();
        
        // Hitung perbedaan waktu
        $days = $now->diffInDays($endOfMonth);
        $hours = $now->copy()->addDays($days)->diffInHours($endOfMonth);
        $minutes = $now->copy()->addDays($days)->addHours($hours)->diffInMinutes($endOfMonth);
        
        // Format timer dengan label hari, jam, dan menit
        $timerDisplay = sprintf(
            '%d hari',
            $days,
            $hours,
            $minutes
        );
        
        // Menentukan warna dan pesan berdasarkan sisa waktu
        $color = Color::Green;
        $description = 'Waktu tersisa untuk perencanaan stok';
        
        if ($days <= 3) {
            $color = Color::Red;
            $description = 'Segera lakukan perencanaan stok! Waktu hampir habis';
            
            // Tambahkan notifikasi jika waktunya sudah kritis
            if ($days <= 2) {
                Notification::make()
                    ->title('Peringatan Perencanaan Stok')
                    ->body('Waktu perencanaan stok akan berakhir dalam ' . $days . ' hari')
                    ->danger()
                    ->persistent()
                    ->send();
            }
        } elseif ($days <= 7) {
            $color = Color::Amber;
            $description = 'Perencanaan stok perlu segera dilakukan';
        }

        return [
            Stat::make('Timer Perencanaan Stok', $timerDisplay)
                ->description($description . "\nBulan " . $endOfMonth->format('F Y'))
                ->color($color)
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->descriptionIcon('heroicon-m-clock')
        ];
    }

    protected function getPollingInterval(): ?string
    {
        return '60s'; // Update setiap 1 menit
    }

    public static function canView(): bool
    {
        return true;
    }
}