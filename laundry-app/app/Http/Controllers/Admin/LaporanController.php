<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pesanan;
use App\Models\Layanan;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        // 1. Ambil input filter tanggal (default: bulan ini)
        $startDate = $request->start_date 
            ? Carbon::parse($request->start_date)->startOfDay() 
            : Carbon::now()->startOfMonth();
            
        $endDate = $request->end_date 
            ? Carbon::parse($request->end_date)->endOfDay() 
            : Carbon::now()->endOfDay();

        // 2. Query data pesanan dengan filter - DISESUAIKAN DENGAN DATABASE ANDA
        $query = Pesanan::with(['layanan', 'user'])
            ->whereBetween('created_at', [$startDate, $endDate]);
        
        // TAMBAHAN: Filter hanya pesanan yang sudah dibayar/dikonfirmasi
        // Sesuaikan dengan kolom status di database Anda
        // Uncomment salah satu yang sesuai:
        
        // Option 1: Jika ada kolom payment_status
        // $query->where('payment_status', 'success');
        
        // Option 2: Jika menggunakan status pesanan saja
        // $query->whereIn('status', ['proses', 'selesai', 'diambil']);
        
        // Option 3: Exclude pending_payment
        // $query->where('status', '!=', 'pending_payment');

        // 3. Ambil data untuk tabel
        $laporans = $query->latest()->get();

        // 4. Hitung statistik untuk summary cards
        $totalPendapatan = $laporans->sum('total');
        $totalTransaksi = $laporans->count();
        
        // Total berat (gunakan final_weight jika ada, jika tidak gunakan weight)
        $totalBerat = $laporans->sum(function($item) {
            return $item->final_weight ?? $item->weight;
        });
        
        $rataRataPerTransaksi = $totalTransaksi > 0 ? $totalPendapatan / $totalTransaksi : 0;

        // 5. Data untuk grafik pendapatan harian
        $dailyIncome = $this->getDailyIncome($startDate, $endDate);

        // 6. Data untuk grafik layanan terlaris
        $topServices = $this->getTopServices($startDate, $endDate);

        // 7. Persentase pertumbuhan (bandingkan dengan periode sebelumnya)
        $previousPeriodStart = $startDate->copy()->subDays($endDate->diffInDays($startDate) + 1);
        $previousPeriodEnd = $startDate->copy()->subDay();
        
        $previousIncome = Pesanan::whereBetween('created_at', [$previousPeriodStart, $previousPeriodEnd])
            ->sum('total');
        
        $growthPercentage = $previousIncome > 0 
            ? (($totalPendapatan - $previousIncome) / $previousIncome) * 100 
            : 0;

        // 8. Kirim semua variabel ke view
        return view('admin.laporan.index', compact(
            'laporans',
            'totalPendapatan',
            'totalTransaksi',
            'totalBerat',
            'rataRataPerTransaksi',
            'dailyIncome',
            'topServices',
            'growthPercentage',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Get daily income data for chart
     */
    private function getDailyIncome($startDate, $endDate)
    {
        $days = [];
        $incomes = [];
        
        $currentDate = $startDate->copy();
        
        // Batasi max 30 hari untuk performa
        $maxDays = 30;
        $dayCount = 0;
        
        while ($currentDate <= $endDate && $dayCount < $maxDays) {
            $dayIncome = Pesanan::whereDate('created_at', $currentDate)
                ->sum('total');
            
            $days[] = $currentDate->format('d/m');
            $incomes[] = $dayIncome;
            
            $currentDate->addDay();
            $dayCount++;
        }
        
        return [
            'labels' => $days,
            'data' => $incomes
        ];
    }

    /**
     * Get top services data for chart
     */
    private function getTopServices($startDate, $endDate)
    {
        $services = Pesanan::with('layanan')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get()
            ->groupBy('layanan_id')
            ->map(function($group) {
                return [
                    'name' => $group->first()->layanan->nama_layanan ?? 'Layanan Dihapus',
                    'count' => $group->count(),
                    'total' => $group->sum('total')
                ];
            })
            ->sortByDesc('count')
            ->take(5)
            ->values();

        return [
            'labels' => $services->pluck('name')->toArray(),
            'data' => $services->pluck('count')->toArray()
        ];
    }

    /**
     * Export to Excel
     */
   public function exportExcel(Request $request)
{
    $startDate = $request->start_date 
        ? Carbon::parse($request->start_date)->startOfDay() 
        : Carbon::now()->startOfMonth();
        
    $endDate = $request->end_date 
        ? Carbon::parse($request->end_date)->endOfDay() 
        : Carbon::now()->endOfDay();

    $laporans = Pesanan::with(['layanan', 'user'])
        ->whereBetween('created_at', [$startDate, $endDate])
        ->latest()
        ->get();

    $filename = 'Laporan_Keuangan_' . $startDate->format('Y-m-d') . '_sd_' . $endDate->format('Y-m-d') . '.csv';

    $headers = [
        'Content-Type'        => 'text/csv; charset=UTF-8',
        'Content-Disposition' => "attachment; filename=\"$filename\"",
    ];

    $callback = function() use ($laporans) {
        $file = fopen('php://output', 'w');
        fputs($file, "\xEF\xBB\xBF"); // BOM agar terbaca benar di Excel

        fputcsv($file, ['Tanggal', 'Invoice', 'Pelanggan', 'Layanan', 'Express', 'Berat (Kg)', 'Total (Rp)']);

        foreach ($laporans as $row) {
            fputcsv($file, [
                $row->created_at->format('d/m/Y'),
                $row->invoice ?? '-',
                $row->customer_name ?? ($row->user->name ?? '-'),
                $row->layanan->nama_layanan ?? '-',
                isset($row->is_express) ? ($row->is_express ? 'Ya' : 'Tidak') : '-',
                number_format($row->final_weight ?? $row->weight ?? 0, 1, ',', '.'),
                number_format($row->total, 0, ',', '.'),
            ]);
        }

        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}
    /**
     * Export to PDF
     */
    public function exportPdf(Request $request)
    {
        $startDate = $request->start_date 
            ? Carbon::parse($request->start_date)->startOfDay() 
            : Carbon::now()->startOfMonth();
            
        $endDate = $request->end_date 
            ? Carbon::parse($request->end_date)->endOfDay() 
            : Carbon::now()->endOfDay();

        // Ambil data
        $laporans = Pesanan::with(['layanan', 'user'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->latest()
            ->get();

        // Hitung statistik
        $totalPendapatan = $laporans->sum('total');
        $totalTransaksi = $laporans->count();
        $totalBerat = $laporans->sum(function($item) {
            return $item->final_weight ?? $item->weight;
        });
        $rataRataPerTransaksi = $totalTransaksi > 0 ? $totalPendapatan / $totalTransaksi : 0;

        $pdf = Pdf::loadView('admin.laporan.pdf', compact(
            'laporans',
            'totalPendapatan',
            'totalTransaksi',
            'totalBerat',
            'rataRataPerTransaksi',
            'startDate',
            'endDate'
        ));

        $filename = 'Laporan_Keuangan_' . $startDate->format('Y-m-d') . '_to_' . $endDate->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Print view (optimized for printing)
     */
    public function print(Request $request)
    {
        $startDate = $request->start_date 
            ? Carbon::parse($request->start_date)->startOfDay() 
            : Carbon::now()->startOfMonth();
            
        $endDate = $request->end_date 
            ? Carbon::parse($request->end_date)->endOfDay() 
            : Carbon::now()->endOfDay();

        $laporans = Pesanan::with(['layanan', 'user'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->latest()
            ->get();

        $totalPendapatan = $laporans->sum('total');
        $totalTransaksi = $laporans->count();
        $totalBerat = $laporans->sum(function($item) {
            return $item->final_weight ?? $item->weight;
        });
        $rataRataPerTransaksi = $totalTransaksi > 0 ? $totalPendapatan / $totalTransaksi : 0;

        return view('admin.laporan.print', compact(
            'laporans',
            'totalPendapatan',
            'totalTransaksi',
            'totalBerat',
            'rataRataPerTransaksi',
            'startDate',
            'endDate'
        ));
    }
}