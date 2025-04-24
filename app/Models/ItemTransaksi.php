<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemTransaksi extends Model
{
    use HasFactory;

    protected $fillable = [

        'item_id',
        'Jumlah', 
        'harga_item',
        'transaksi_id',
        'updated_at',
        'created_at'
        
        
    ];

    protected $table = 'item_transaksis';

    public function transaksi()
    {
        return $this->belongsTo(transaksi::class, 'transaksi_id');
    }

    public function item()
{
    return $this->belongsTo(Item::class, 'item_id');
}

    protected static function boot()
    {
        parent::boot();

        // Update total price when item is created/updated/deleted
        static::saved(function ($item) {
            if ($item->transaksi) {
                $item->transaksi->updateTotalPrice();
            }
        });

        static::deleted(function ($item) {
            if ($item->transaksi) {
                $item->transaksi->updateTotalPrice();
            }
        });
    }
}
