<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Struk Transaksi {{ $transaksi->number }}</title>
    <style>
        body {
            font-family: monospace;
            font-size: 10px;
            width: 250px;
            margin: 0 auto;
            padding: 10px;
            line-height: 1.3;
        }
        .header {
            text-align: center;
            border-bottom: 1px dashed #000;
            padding-bottom: 5px;
            margin-bottom: 5px;
        }
        .header h1 {
            margin: 0;
            font-size: 14px;
        }
        .info-section {
            border-bottom: 1px dashed #000;
            padding-bottom: 5px;
            margin-bottom: 5px;
        }
        .info-section p {
            margin: 2px 0;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }
        .items-table th, .items-table td {
            padding: 2px;
            text-align: left;
        }
        .items-table th {
            border-bottom: 1px solid #000;
        }
        .total-section {
            text-align: right;
            border-top: 1px dashed #000;
            padding-top: 5px;
        }
        .total-section p {
            margin: 2px 0;
        }
        .footer {
            text-align: center;
            font-size: 8px;
            border-top: 1px dashed #000;
            padding-top: 5px;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>JOJO LAUNDRY</h1>
        <p>Jl. Joyo Utomo No. 463</p>
        <p>Telp: (021) 123-4567</p>
    </div>

    <div class="info-section">
        <p>No. Transaksi: #{{ $transaksi->number }}</p>
        <p>Tgl: {{ $transaksi->created_at->format('d/m/Y H:i') }}</p>
        <p>Pelanggan: {{ $pelanggan->Nama ?? '-' }}</p>
        <p>No. HP: {{ $pelanggan->No_hp ?? '-' }}</p>
        <p>Alamat: {{ $pelanggan->Alamat ?? '-' }}</p>
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th>Item</th>
                <th>Qty</th>
                <th>Harga</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
            @php
                $itemModel = App\Models\Item::find($item->item_id);
            @endphp
            <tr>
                <td>{{ $itemModel ? $itemModel->Nama : 'Item tidak tersedia' }}</td>
                <td>{{ $item->Jumlah }} {{ $itemModel ? $itemModel->Satuan_berat : 'Kg' }}</td>
                <td>Rp {{ number_format(($item->harga_item ?? 0) * ($item->Jumlah ?? 0), 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total-section">
        <p>Layanan: {{ $transaksi->service_type === 'regular' ? 'Regular (3 Hari)' : 'Express (1 Hari)' }}</p>
        <p>Pengiriman: {{ $transaksi->shipping_method === 'pickup' ? 'Pick Up' : 'Delivery' }}</p>
        @if($transaksi->shipping_method === 'delivery')
        <p>Biaya Kirim: Rp 5.000</p>
        @endif
        <p><strong>TOTAL: Rp {{ number_format($transaksi->total_harga ?? 0, 0, ',', '.') }}</strong></p>
    </div>

    <div class="footer">
        <p>Terima kasih</p>
        <p>Simpan struk ini sebagai bukti transaksi</p>
    </div>
</body>
</html>