<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class transaksi extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'transaksis';

    protected $fillable = [
        'number',
        'total_harga',
        'status',
        'shipping_price',
        'shipping_method',
        'service_type',
    ];

    protected $casts = [
        'status' => OrderStatus::class,
        'total_harga' => 'decimal:2',
        'shipping_price' => 'decimal:2',
    ];
    
    public function Pelanggan(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class, 'pelanggan_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ItemTransaksi::class, 'transaksi_id');
    }

    public function calculateTotalPrice(): float
    {
        // Reload items relationship to ensure we have fresh data
        $this->load('items');
        
        // Calculate items total
        $itemsTotal = $this->items->sum(function ($item) {
            return floatval($item->harga_item) * floatval($item->Jumlah);
        });

        // Apply service multiplier
        $multiplier = $this->service_type === 'express' ? 2 : 1;
        
        // Add shipping price
        $shippingPrice = $this->shipping_method === 'delivery' ? 5000 : 0;
        
        // Calculate final total
        $total = ($itemsTotal * $multiplier) + $shippingPrice;
        
        return round($total, 2);
    }

    public function updateTotalPrice(): void
    {
        $this->total_harga = $this->calculateTotalPrice();
        $this->saveQuietly(); // Use saveQuietly to prevent infinite loops
    }

    protected static function boot()
    {
        parent::boot();

        // When a new transaction is created
        static::created(function ($transaksi) {
            $transaksi->updateTotalPrice();
        });

        // After saving items relationship
        static::updated(function ($transaksi) {
            // Only update price if relevant fields changed
            if ($transaksi->isDirty([
                'shipping_method',
                'service_type',
                'shipping_price'
            ])) {
                $transaksi->updateTotalPrice();
            }
        });
    }
}           
