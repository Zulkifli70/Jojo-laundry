<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StokFuzzy extends Model
{
    use HasFactory;

    protected $fillable = [
        'periode',
        'detergen_stok_minimum',
        'detergen_stok_sekarang',
        'detergen_interval_rendah_min',
        'detergen_interval_rendah_max',
        'detergen_interval_sedang_min',
        'detergen_interval_sedang_max',
        'detergen_interval_tinggi_min',
        'detergen_interval_tinggi_max',
        'pewangi_stok_minimum',
        'pewangi_stok_sekarang',
        'pewangi_interval_rendah_min',
        'pewangi_interval_rendah_max',
        'pewangi_interval_sedang_min',
        'pewangi_interval_sedang_max',
        'pewangi_interval_tinggi_min',
        'pewangi_interval_tinggi_max',
        'parfum_stok_minimum',
        'parfum_stok_sekarang',
        'parfum_interval_rendah_min',
        'parfum_interval_rendah_max',
        'parfum_interval_sedang_min',
        'parfum_interval_sedang_max',
        'parfum_interval_tinggi_min',
        'parfum_interval_tinggi_max',
    ];

    protected $casts = [
        'periode' => 'date',
    ];

    public function getNamaBulanAttribute()
    {
        $bulanIndonesia = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];
        
        return $bulanIndonesia[$this->periode->month];
    }

    const USAGE_PER_qty = [
        'detergen' => 0.05,
        'pewangi' => 0.03,
        'parfum' => 0.02,
    ];

    private function getHistoricalData($product_type)
    {
        $data = self::where('periode', '<', $this->periode)
            ->orderBy('periode', 'desc')
            ->limit(6)
            ->pluck($product_type . '_stok_sekarang')
            ->toArray();
            
        // Jika tidak ada data historis, gunakan stok sekarang
        if (empty($data)) {
            $data = [$this->{$product_type . '_stok_sekarang'}];
        }
        
        return $data;
    }

    private function getMonthlyUsage($product_type)
    {
        $startDate = Carbon::now()->subMonth();
        $endDate = Carbon::now();

        $totalqty = DB::table('transaksis')
            ->join('item_transaksis', 'transaksis.id', '=', 'item_transaksis.transaksi_id')
            ->whereBetween('transaksis.created_at', [$startDate, $endDate])
            ->where('transaksis.status', '!=', 'batal')
            ->sum('item_transaksis.Jumlah');

        return $totalqty * self::USAGE_PER_qty[$product_type];
    }

    private function getDailyUsageRate($product_type)
    {
        $monthlyUsage = $this->getMonthlyUsage($product_type);
        return $monthlyUsage > 0 ? $monthlyUsage / 30 : 0.1; // menggunakan stok minimum default jika tidak ada data yang tersedia
    }

    private function getIntervals($product_type)
    {
        return [
            'rendah' => [
                'min' => (float) $this->{$product_type . '_interval_rendah_min'},
                'max' => (float) $this->{$product_type . '_interval_rendah_max'},
                'mid' => ((float) $this->{$product_type . '_interval_rendah_min'} + 
                         (float) $this->{$product_type . '_interval_rendah_max'}) / 2
            ],
            'sedang' => [
                'min' => (float) $this->{$product_type . '_interval_sedang_min'},
                'max' => (float) $this->{$product_type . '_interval_sedang_max'},
                'mid' => ((float) $this->{$product_type . '_interval_sedang_min'} + 
                         (float) $this->{$product_type . '_interval_sedang_max'}) / 2
            ],
            'tinggi' => [
                'min' => (float) $this->{$product_type . '_interval_tinggi_min'},
                'max' => (float) $this->{$product_type . '_interval_tinggi_max'},
                'mid' => ((float) $this->{$product_type . '_interval_tinggi_min'} + 
                         (float) $this->{$product_type . '_interval_tinggi_max'}) / 2
            ]
        ];
    }

    private function getFuzzyState($value, $intervals)
    {
        $memberships = [];
        foreach ($intervals as $state => $range) {
            if ($value <= $range['min']) {
                $memberships[$state] = ($state === 'rendah') ? 1 : 0;
            } elseif ($value >= $range['max']) {
                $memberships[$state] = ($state === 'tinggi') ? 1 : 0;
            } else {
                // Hitung derajat keanggotaan
                if ($value <= $range['mid']) {
                    $memberships[$state] = ($value - $range['min']) / ($range['mid'] - $range['min']);
                } else {
                    $memberships[$state] = ($range['max'] - $value) / ($range['max'] - $range['mid']);
                }
            }
        }

        // Mengembalikan state dengan nilai keanggotaan tertinggi
        arsort($memberships);
        return key($memberships);
    }

    private function calculateFLR($historicalData, $intervals)
    {
        $relationships = [];
        for ($i = 0; $i < count($historicalData) - 1; $i++) {
            $currentState = $this->getFuzzyState($historicalData[$i], $intervals);
            $nextState = $this->getFuzzyState($historicalData[$i + 1], $intervals);
            $relationships[] = [$currentState => $nextState];
        }
        return $relationships;
    }

    private function groupFLR($relationships)
    {
        $groups = [];
        foreach ($relationships as $relationship) {
            foreach ($relationship as $current => $next) {
                if (!isset($groups[$current])) {
                    $groups[$current] = [];
                }
                $groups[$current][] = $next;
            }
        }
        return $groups;
    }

    private function predictWithTimeSeriesAndUsage($product_type)
    {
        $historicalData = $this->getHistoricalData($product_type);
        $intervals = $this->getIntervals($product_type);
        $dailyUsage = $this->getDailyUsageRate($product_type);
        
        // Level Stok saat ini
        $currentStock = $this->{$product_type . '_stok_sekarang'};
        
        // Stok minimum yang dibutuhkan
        $minStock = $this->{$product_type . '_stok_minimum'};

        // Menghitung prediksi FTS 
        $relationships = $this->calculateFLR($historicalData, $intervals);
        $groups = $this->groupFLR($relationships);
        $currentState = $this->getFuzzyState($currentStock, $intervals);
        
        // dapatkan prediksi state berikutnya 
        $nextStates = $groups[$currentState] ?? array_keys($intervals);
        
        // Hitung nilai prediksi
        $predictedValue = 0;
        foreach ($nextStates as $state) {
            $predictedValue += $intervals[$state]['mid'];
        }
        $predictedValue = $predictedValue / count($nextStates);

        // Menyesuaikan prediksi berdasarkan tingkat penggunaan dan stok aman
        $daysUntilMin = ($currentStock - $minStock) / ($dailyUsage ?: 0.1);
        $safetyStock = $minStock * 1.2; // 20% tambahan

        if ($daysUntilMin < 7) { // Jika stok akan bertahan kurang dari 7 hari
            $recommendedOrder = max(
                $safetyStock - $currentStock + ($dailyUsage * 30), // Order for 30 days
                $minStock * 0.5 // Minimum order size
            );
            return $currentStock + $recommendedOrder;
        }

        // Mengembalikan nilai prediksi maksimum dan tingkat stok minimum
        return max($predictedValue, $minStock);
    }

    public function getPrediksiStok($forceRecalculate = false)
    {
        $currentMonth = Carbon::now()->format('Y-m');
        $lastPredictionMonth = $this->prediksi_bulan;

        // Paksa penghitungan ulang jika:
        // 1. Tidak ada prediksi sebelumnya
        // 2. Prediksi sebelumnya untuk bulan yang berbeda
        // 3. Perhitungan ulang secara paksa diminta
        $shouldRecalculate = $forceRecalculate || 
                             !$lastPredictionMonth || 
                             $lastPredictionMonth !== $currentMonth;

        // Jika tidak diperlukan penghitungan ulang, kembalikan prediksi yang sudah ada
        if (!$shouldRecalculate) {
            return [
                'detergen' => $this->prediksi_detergen,
                'pewangi' => $this->prediksi_pewangi,
                'parfum' => $this->prediksi_parfum
            ];
        }

        try {
            // Hitung prediksi baru
            $predictions = [
                'detergen' => max($this->predictWithTimeSeriesAndUsage('detergen'), $this->detergen_stok_minimum),
                'pewangi' => max($this->predictWithTimeSeriesAndUsage('pewangi'), $this->pewangi_stok_minimum),
                'parfum' => max($this->predictWithTimeSeriesAndUsage('parfum'), $this->parfum_stok_minimum)
            ];

            // Menyimpan prediksi baru
            $this->prediksi_detergen = $predictions['detergen'];
            $this->prediksi_pewangi = $predictions['pewangi'];
            $this->prediksi_parfum = $predictions['parfum'];
            $this->prediksi_bulan = $currentMonth;
            $this->save();

            return $predictions;
        } catch (\Exception $e) {
            // Log the error
            \Illuminate\Support\Facades\Log::error('Error in stock prediction: ' . $e->getMessage());
            
            // Return current stock levels as a fallback
            return [
                'detergen' => $this->detergen_stok_sekarang,
                'pewangi' => $this->pewangi_stok_sekarang,
                'parfum' => $this->parfum_stok_sekarang
            ];
        }
    }

    // Method to manually trigger prediction recalculation
    public function recalculatePrediksi()
    {
        return $this->getPrediksiStok(true);
    }
}