<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pesanan; // Pastikan nama Model Anda benar
use Carbon\Carbon;

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

        // 2. Query data pesanan dengan filter (Hanya yang statusnya selesai/diambil biasanya)
        $query = Pesanan::with('layanan') // Eager load relasi layanan
            ->whereBetween('created_at', [$startDate, $endDate]);

        // 3. Ambil data untuk tabel
        $laporans = $query->latest()->get();

        // 4. Hitung statistik untuk summary cards
        $totalPendapatan = $laporans->sum('total_price');
        $totalPesanan = $laporans->count();
        $totalBerat = $laporans->sum(function($item) {
            return $item->final_weight ?? $item->weight; // Sesuaikan nama kolom di database Anda
        });
        $rataRata = $totalPesanan > 0 ? $totalPendapatan / $totalPesanan : 0;

        // 5. Kirim semua variabel ke view
        return view('admin.laporan.index', compact(
            'laporans', 
            'totalPendapatan', 
            'totalPesanan', 
            'totalBerat', 
            'rataRata'
        ));
    }
}