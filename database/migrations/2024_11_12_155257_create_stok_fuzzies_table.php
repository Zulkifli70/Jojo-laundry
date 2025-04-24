<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stok_fuzzies', function (Blueprint $table) {
            $table->id();
            $table->date('periode');
            $table->float('detergen_stok_minimum');
            $table->float('detergen_stok_sekarang');
            $table->integer('detergen_interval_rendah_min');
            $table->integer('detergen_interval_rendah_max');
            $table->integer('detergen_interval_sedang_min');
            $table->integer('detergen_interval_sedang_max');
            $table->integer('detergen_interval_tinggi_min');
            $table->integer('detergen_interval_tinggi_max');
            $table->float('pewangi_stok_minimum');
            $table->float('pewangi_stok_sekarang');
            $table->integer('pewangi_interval_rendah_min');
            $table->integer('pewangi_interval_rendah_max');
            $table->integer('pewangi_interval_sedang_min');
            $table->integer('pewangi_interval_sedang_max');
            $table->integer('pewangi_interval_tinggi_min');
            $table->integer('pewangi_interval_tinggi_max');
            $table->float('parfum_stok_minimum');
            $table->float('parfum_stok_sekarang');
            $table->integer('parfum_interval_rendah_min');
            $table->integer('parfum_interval_rendah_max');
            $table->integer('parfum_interval_sedang_min');
            $table->integer('parfum_interval_sedang_max');
            $table->integer('parfum_interval_tinggi_min');
            $table->integer('parfum_interval_tinggi_max');
            $table->string('prediksi_bulan')->nullable();
            $table->float('prediksi_detergen')->nullable();
            $table->float('prediksi_pewangi')->nullable();
            $table->float('prediksi_parfum')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stok_fuzzies');
    }
};