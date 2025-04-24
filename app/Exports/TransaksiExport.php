<?php

namespace App\Exports;

use App\Models\Transaksi;
use App\Models\Pelanggan;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Support\Carbon;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class TransaksiExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithEvents
{
    use Exportable;

    protected $startDate;
    protected $endDate;
    protected $totalTransactionAmount = 0;
    protected $totalTransactionCount = 0;
    protected $uniqueCustomerCount = 0;

    public function __construct($startDate = null, $endDate = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function query()
    {
        $query = Transaksi::query();

        if ($this->startDate) {
            $query->whereDate('created_at', '>=', Carbon::parse($this->startDate));
        }

        if ($this->endDate) {
            $query->whereDate('created_at', '<=', Carbon::parse($this->endDate));
        }

        // Calculate total transaction amount and count
        $this->totalTransactionAmount = (clone $query)->sum('total_harga');
        $this->totalTransactionCount = (clone $query)->count();
        
        // Calculate unique customers
        $this->uniqueCustomerCount = (clone $query)->distinct('pelanggan_id')->count('pelanggan_id');

        return $query;
    }

    public function headings(): array
    {
        return [
            'Nomor Transaksi',
            'Nama Pelanggan',
            'Total Harga',
            'Layanan',
            'Metode Pengiriman',
            'Waktu Transaksi'
        ];
    }

    public function map($transaksi): array
    {
        return [
            $transaksi->number,
            $transaksi->pelanggan ? $transaksi->pelanggan->Nama : 'Pelanggan Tidak Ditemukan',
            $transaksi->total_harga,
            $this->formatServiceType($transaksi->service_type),
            $this->formatShippingMethod($transaksi->shipping_method),
            $transaksi->created_at->format('Y-m-d H:i:s')
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Format total harga column as currency
        $sheet->getStyle('C:C')->getNumberFormat()
            ->setFormatCode('Rp #,##0.00');
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20,
            'B' => 25,
            'C' => 15,
            'D' => 20,
            'E' => 20,
            'F' => 20,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Add title at the top
                $startDateFormatted = Carbon::parse($this->startDate)->format('d M Y');
                $endDateFormatted = Carbon::parse($this->endDate)->format('d M Y');
                $title = "Laporan Bulanan Jojo Laundry per {$startDateFormatted} - {$endDateFormatted}";
                
                $sheet->insertNewRowBefore(1);
                $sheet->mergeCells('A1:F1');
                $sheet->setCellValue('A1', $title);

                $sheet->insertNewRowBefore(2);
                $sheet->mergeCells('A2:F2');
                $sheet->setCellValue('A2', 'Jl. Joyo Utomo no 463');

                // Style the title
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 14,
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                ]);

                // Style the address
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 12,
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                ]);
                
                // Ringkasan
                $lastRow = $sheet->getHighestRow() + 2;
                $sheet->setCellValue('A' . $lastRow, 'Ringkasan Transaksi');
                $sheet->mergeCells("A{$lastRow}:F{$lastRow}");
                $sheet->getStyle("A{$lastRow}")->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                ]);

                // Ringkasan
                $lastRow++;
                $sheet->setCellValue('A' . $lastRow, 'Total Transaksi');
                $sheet->setCellValue('C' . $lastRow, $this->totalTransactionCount);
                // Format total transaksi sebagai angka biasa
                $sheet->getStyle('C' . $lastRow)->getNumberFormat()
                    ->setFormatCode('0');

                $lastRow++;
                $sheet->setCellValue('A' . $lastRow, 'Jumlah Pelanggan');
                $sheet->setCellValue('C' . $lastRow, $this->uniqueCustomerCount);
                // Format jumlah pelanggan sebagai angka biasa
                $sheet->getStyle('C' . $lastRow)->getNumberFormat()
                    ->setFormatCode('0');

                $lastRow++;
                $sheet->setCellValue('A' . $lastRow, 'Total Pendapatan');
                $sheet->setCellValue('C' . $lastRow, $this->totalTransactionAmount);
                // Format total pendapatan tetap dalam mata uang Rupiah
                $sheet->getStyle('C' . $lastRow)->getNumberFormat()
                    ->setFormatCode('Rp #,##0');
            }
        ];
    }

    private function formatServiceType($type)
    {
        return match ($type) {
            'regular' => 'Regular (3 Hari)',
            'express' => 'Express (1 Hari)',
            default => $type,
        };
    }

    private function formatShippingMethod($method)
    {
        return match ($method) {
            'delivery' => 'Delivery',
            'pickup' => 'PickUp',
            default => $method,
        };
    }
}