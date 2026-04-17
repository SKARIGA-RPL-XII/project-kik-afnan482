<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Print Laporan Keuangan</title>
    <style>
        @media print {
            @page {
                size: A4 landscape;
                margin: 15mm;
            }
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .no-print {
                display: none !important;
            }
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 3px solid #2563EB;
            padding-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            color: #2563EB;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .summary {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .summary-item {
            display: table-cell;
            width: 25%;
            padding: 12px;
            background: #F3F4F6;
            border: 1px solid #E5E7EB;
            text-align: center;
        }
        .summary-item h3 {
            margin: 0 0 5px 0;
            font-size: 9px;
            color: #666;
            text-transform: uppercase;
        }
        .summary-item p {
            margin: 0;
            font-size: 14px;
            font-weight: bold;
            color: #2563EB;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        thead th {
            background: #2563EB;
            color: white;
            padding: 8px;
            text-align: left;
            font-size: 9px;
            text-transform: uppercase;
            border: 1px solid #1D4ED8;
        }
        tbody td {
            padding: 6px 8px;
            border: 1px solid #E5E7EB;
            font-size: 10px;
        }
        tbody tr:nth-child(even) {
            background: #F9FAFB;
        }
        tfoot td {
            padding: 10px 8px;
            border: 2px solid #2563EB;
            font-weight: bold;
            background: #EFF6FF;
            font-size: 11px;
        }
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #E5E7EB;
            text-align: center;
            font-size: 9px;
            color: #999;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 8px;
            font-size: 8px;
            font-weight: 600;
        }
        .badge-blue {
            background: #DBEAFE;
            color: #1E40AF;
        }
        .badge-yellow {
            background: #FEF3C7;
            color: #92400E;
        }
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #2563EB;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .print-button:hover {
            background: #1D4ED8;
        }
    </style>
</head>
<body>
    <button onclick="window.print()" class="print-button no-print">
        🖨️ Cetak Laporan
    </button>

    <div class="header">
        <h1>LAPORAN KEUANGAN</h1>
        <p>Periode: {{ $startDate->format('d F Y') }} - {{ $endDate->format('d F Y') }}</p>
        <p style="font-size: 9px; color: #999;">Dicetak pada: {{ now()->format('d F Y H:i') }}</p>
    </div>

    <div class="summary">
        <div class="summary-item">
            <h3>Total Pendapatan</h3>
            <p>Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</p>
        </div>
        <div class="summary-item">
            <h3>Total Transaksi</h3>
            <p>{{ number_format($totalTransaksi) }}</p>
        </div>
        <div class="summary-item">
            <h3>Total Berat</h3>
            <p>{{ number_format($totalBerat, 1) }} Kg</p>
        </div>
        <div class="summary-item">
            <h3>Rata-rata/Transaksi</h3>
            <p>Rp {{ number_format($rataRataPerTransaksi, 0, ',', '.') }}</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 4%;">NO</th>
                <th style="width: 9%;">TANGGAL</th>
                <th style="width: 10%;">INVOICE</th>
                <th style="width: 14%;">PELANGGAN</th>
                <th style="width: 11%;">TELEPON</th>
                <th style="width: 18%;">LAYANAN</th>
                <th style="width: 8%;">BERAT (KG)</th>
                <th style="width: 10%;">METODE</th>
                <th style="width: 6%;">STATUS</th>
                <th style="width: 10%;">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @forelse($laporans as $index => $row)
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td>{{ $row->created_at->format('d/m/Y') }}</td>
                <td style="color: #2563EB; font-weight: 600;">{{ $row->invoice }}</td>
                <td>{{ $row->customer_name }}</td>
                <td>{{ $row->customer_phone }}</td>
                <td>
                    <span class="badge badge-blue">{{ $row->layanan->nama_layanan ?? 'Layanan Terhapus' }}</span>
                    @if($row->is_express)
                    <span class="badge badge-yellow">Express</span>
                    @endif
                </td>
                <td style="text-align: center;">{{ number_format($row->final_weight ?? $row->weight, 1, ',', '.') }}</td>
                <td style="font-size: 9px;">
                    @if($row->payment_method == 'cash')
                        Tunai
                    @elseif($row->payment_method == 'transfer')
                        Transfer
                    @elseif($row->payment_method == 'ewallet')
                        E-Wallet
                    @else
                        {{ ucfirst($row->payment_method) }}
                    @endif
                </td>
                <td style="text-align: center; font-size: 9px;">{{ ucfirst($row->status) }}</td>
                <td style="text-align: right; font-weight: 600;">Rp {{ number_format($row->total, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="10" style="text-align: center; padding: 30px; color: #999;">Tidak ada data transaksi pada periode ini.</td>
            </tr>
            @endforelse
        </tbody>
        @if($laporans->count() > 0)
        <tfoot>
            <tr>
                <td colspan="9" style="text-align: right; color: #1E40AF;">TOTAL PENDAPATAN:</td>
                <td style="text-align: right; color: #2563EB;">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
        @endif
    </table>

    <div class="footer">
        <p>Laporan ini dibuat secara otomatis oleh sistem LaundryKu</p>
        <p>&copy; {{ now()->year }} LaundryKu. All rights reserved.</p>
    </div>
</body>
</html>