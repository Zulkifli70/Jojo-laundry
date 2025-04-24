<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum OrderStatus: string implements HasColor, HasIcon, HasLabel
{
    case Baru = 'baru';

    case Proses = 'proses';

    case Dikemas = 'dikemas';

    case Selesai = 'selesai';

    case Batal = 'batal';

    public static function toArray(): array
{
    return [
        self::Baru->value => self::Baru->getLabel(),
        self::Proses->value => self::Proses->getLabel(),
        self::Dikemas->value => self::Dikemas->getLabel(),
        self::Selesai->value => self::Selesai->getLabel(),
        self::Batal->value => self::Batal->getLabel(),
    ];
}

    public function getLabel(): string
    {
        return match ($this) {
            self::Baru => 'Baru',
            self::Proses => 'Proses',
            self::Dikemas => 'Dikemas',
            self::Selesai => 'Selesai',
            self::Batal => 'Batal',
        };
    }



    public function getColor(): string |  null
    {
        return match ($this) {
            self::Baru => 'info',
            self::Proses => 'warning',
            self::Selesai, self::Dikemas => 'success',
            self::Batal => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Baru => 'heroicon-m-sparkles',
            self::Proses => 'heroicon-m-arrow-path',
            self::Dikemas => 'heroicon-m-cube',
            self::Selesai => 'heroicon-m-check-badge',
            self::Batal => 'heroicon-m-x-circle',
        };
    }
}