<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'Nama',
        'Harga_berat',
        'Satuan_berat',
        'Harga_satuan',
        'Harga'
        
    ];
    
    protected $table = 'items';
    
    public function transaksis()
{
    return $this->hasMany(ItemTransaksi::class);
}
}

