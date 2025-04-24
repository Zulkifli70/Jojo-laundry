<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('Nama');
            $table->decimal('Harga_berat', 10, 2)->nullable()
                ->default(1)
                ->unsigned();
            $table->string('Satuan_berat')->default('kg')->nullable();
            $table->decimal('Harga_satuan', 10, 2)->nullable()
                ->default(1)
                ->unsigned();
            $table->decimal('Harga', 10, 2)
                ->default(1)
                ->unsigned();    
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
