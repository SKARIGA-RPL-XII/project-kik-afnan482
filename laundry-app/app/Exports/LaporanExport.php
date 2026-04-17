<?php

namespace App\Exports;

use App\Models\Pesanan;
use Maatwebsite\Excel\Facades\Excel;

class LaporanExport
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
     * Get data untuk export
     */
    public function getData()
    {
        $pesanans = Pesanan::with(['layanan', 'user'])
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->whereNotIn('status', ['pending_payment', 'cancelled'])
            ->where('payment_status', 'success')
            ->latest()
            ->get();

        $data = [];
        $no = 1;

        // Header
        $data[] = [
            'NO',
            'TANGGAL',
            'INVOICE',
            'PELANGGAN',
            'TELEPON',
            'LAYANAN',
            'BERAT (KG)',
            'TOTAL',
            'STATUS',
        ];

        // Data rows
        foreach ($pesanans as $pesanan) {
            // Safely get layanan name
            $layananName = 'Layanan Dihapus';
            try {
                if ($pesanan->layanan) {
                    $layananName = $pesanan->layanan->nama_layanan ?? $pesanan->service_type ?? 'Layanan Dihapus';
                } elseif ($pesanan->service_type) {
                    $layananName = $pesanan->service_type;
                }
            } catch (\Exception $e) {
                $layananName = $pesanan->service_type ?? 'Layanan Dihapus';
            }

        // Get weight
        $weight = $pesanan->final_weight ?? $pesanan->weight ?? 0;
        
        $data[] = [
            $no,
            $pesanan->created_at ? $pesanan->created_at->format('d/m/Y H:i') : '-',
            $pesanan->invoice ?? '-',
            ($pesanan->user ? $pesanan->user->name : 'N/A') ?? '-',
            ($pesanan->user ? $pesanan->user->phone : '') ?? '-',
            $layananName,
            number_format($weight, 1, ',', '.'),
            'Rp ' . number_format($pesanan->total ?? 0, 0, ',', '.'),
            ucfirst(str_replace('_', ' ', $pesanan->status ?? 'pending')),
        ];
        
        $no++;
        }

        return $data;
    }
}