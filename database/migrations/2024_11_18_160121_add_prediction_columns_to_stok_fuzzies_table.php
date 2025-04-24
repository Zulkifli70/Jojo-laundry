<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stok_fuzzies', function (Blueprint $table) {
            $table->float('prediksi_detergen')->nullable()->after('parfum_interval_tinggi');
            $table->float('prediksi_pewangi')->nullable()->after('prediksi_detergen');
            $table->float('prediksi_parfum')->nullable()->after('prediksi_pewangi');
        });
    }

    public function down(): void
    {
        Schema::table('stok_fuzzies', function (Blueprint $table) {
            $table->dropColumn(['prediksi_detergen', 'prediksi_pewangi', 'prediksi_parfum']);
        });
    }
};