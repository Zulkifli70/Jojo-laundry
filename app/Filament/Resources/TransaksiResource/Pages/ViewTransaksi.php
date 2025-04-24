<?php

namespace App\Filament\Resources\TransaksiResource\Pages;

use App\Filament\Resources\TransaksiResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\View;

class ViewTransaksi extends ViewRecord
{
    protected static string $resource = TransaksiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
            ->label('Ubah'),
            Actions\Action::make('generateReceipt')
                ->label('Cetak Struk')
                ->icon('heroicon-o-printer')
                ->action(function () {
                    $transaksi = $this->record;
                    $items = $transaksi->items()->with('item')->get();
                    
                    // Generate PDF using view
                    $pdf = Pdf::loadView('receipts.transaction', [
                        'transaksi' => $transaksi,
                        'items' => $items,
                        'pelanggan' => $transaksi->pelanggan,
                    ]);
                    
                    // Download PDF with custom filename
                    return response()->streamDownload(function () use ($pdf) {
                        echo $pdf->output();
                    }, 'struk-' . $transaksi->number . '.pdf');
                })
                ->color('success'),
        ];
        
    }
    protected function afterSave(): void
    {
        $this->redirect(static::getResource()::getUrl('index'), navigate: true);
    }
}
