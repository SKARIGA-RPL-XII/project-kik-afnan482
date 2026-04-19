<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Scope pesanan valid: bukan cancelled, dan Midtrans hanya jika sudah ada pembayaran
        $validScope = function ($q) {
            $q->where('status', '!=', 'cancelled')
              ->where(function ($sub) {
                  $sub->where('payment_method', 'cash')
                      ->orWhere(function ($m) {
                          $m->where('payment_method', 'midtrans')
                            ->whereIn('payment_status', ['pending', 'success', 'paid']);
                      });
              });
        };

        // Hitung pesanan hari ini (hanya pesanan valid)
        $pesananHariIni = Pesanan::where($validScope)
            ->whereDate('created_at', Carbon::today())->count();
        
        // Statistik lainnya
        $totalPesanan   = Pesanan::where($validScope)->count();
        $pesananSelesai = Pesanan::where($validScope)->where('status', 'selesai')->count();
        $pesananProses  = Pesanan::where($validScope)->where('status', 'proses')->count();
        $pesananPending = Pesanan::where($validScope)->where('status', 'pending')->count();
        
        $totalPendapatan = Pesanan::where($validScope)
            ->whereIn('status', ['selesai', 'diambil'])->sum('total');
        
        $pendapatanBulanIni = Pesanan::where($validScope)
            ->whereIn('status', ['selesai', 'diambil'])
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('total');
        
        $totalPelanggan = User::where('role', 'user')->count();
        
        // Grafik Pesanan (hanya pesanan valid)
        $chartPesanan = Pesanan::select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('YEAR(created_at) as year'),
                DB::raw('COUNT(*) as total')
            )
            ->where($validScope)
            ->where('created_at', '>=', Carbon::now()->subMonths(12))
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();
        
        // Grafik Pendapatan (hanya pesanan valid)
        $chartPendapatan = Pesanan::select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('YEAR(created_at) as year'),
                DB::raw('SUM(total) as total')
            )
            ->where($validScope)
            ->whereIn('status', ['selesai', 'diambil'])
            ->where('created_at', '>=', Carbon::now()->subMonths(12))
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();
        
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        
        $pesananData = array_fill(0, 12, 0);
        $pendapatanData = array_fill(0, 12, 0);
        
        foreach ($chartPesanan as $data) {
            $index = $data->month - 1;
            $pesananData[$index] = $data->total;
        }
        
        foreach ($chartPendapatan as $data) {
            $index = $data->month - 1;
            $pendapatanData[$index] = $data->total;
        }
        
        $layananTerpopuler = Pesanan::select('layanan_id', DB::raw('COUNT(*) as total'))
            ->with('layanan')
            ->where($validScope)
            ->whereNotNull('layanan_id')
            ->groupBy('layanan_id')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get();
        
        $pesananTerbaru = Pesanan::with('user', 'layanan')
            ->where($validScope)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // KIRIM KE VIEW - Pastikan pesananHariIni ada di sini
        return view('admin.dashboard', [
            'totalPesanan' => $totalPesanan,
            'pesananSelesai' => $pesananSelesai,
            'pesananProses' => $pesananProses,
            'pesananPending' => $pesananPending,
            'totalPendapatan' => $totalPendapatan,
            'pendapatanBulanIni' => $pendapatanBulanIni,
            'totalPelanggan' => $totalPelanggan,
            'pesananHariIni' => $pesananHariIni,  // ← INI HARUS ADA
            'months' => $months,
            'pesananData' => $pesananData,
            'pendapatanData' => $pendapatanData,
            'layananTerpopuler' => $layananTerpopuler,
            'pesananTerbaru' => $pesananTerbaru
        ]);
    }
}