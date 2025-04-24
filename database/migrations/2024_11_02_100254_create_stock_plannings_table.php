<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_plannings', function (Blueprint $table) {
            $table->id();
            $table->string('nama_bahan')->nullable(false); // Explicitly set NOT NULL
            $table->enum('satuan', ['liter', 'kg'])->nullable(false);
            $table->decimal('stok_minimum', 10, 2)->nullable(false);
            $table->decimal('stok_sekarang', 10, 2)->nullable(false);
            $table->string('interval_rendah')->nullable(false);
            $table->string('interval_sedang')->nullable(false);
            $table->string('interval_tinggi')->nullable(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_plannings');
    }
};