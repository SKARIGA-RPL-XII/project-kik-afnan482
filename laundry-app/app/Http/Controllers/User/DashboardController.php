<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display user dashboard
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        // Ambil ID user yang sedang login
        $userId = Auth::guard('web')->id();
        
        // --- Hitung Statistik ---
        
        // Total semua pesanan user
        $totalOrders = Pesanan::where('user_id', $userId)->count();
        
        // Pesanan yang sedang diproses (pending & proses)
        $processingOrders = Pesanan::where('user_id', $userId)
            ->whereIn('status', ['pending', 'proses'])
            ->count();
        
        // Pesanan yang sudah selesai dan siap diambil
        $readyOrders = Pesanan::where('user_id', $userId)
            ->where('status', 'selesai')
            ->count();
        
        // Total uang yang sudah dikeluarkan (hanya pesanan yang sudah diambil)
        $totalSpent = Pesanan::where('user_id', $userId)
            ->where('status', 'diambil')
            ->sum('total');
        
        // --- Siapkan Data untuk Ditampilkan ---
        
        // Pesanan aktif (status pending, proses, atau selesai)
        $activeOrders = Pesanan::where('user_id', $userId)
            ->whereIn('status', ['pending', 'proses', 'selesai'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($order) {
                // Tambahkan atribut dinamis untuk memudahkan di view
                $order->service_name = $this->getServiceName($order->service_type);
                $order->formatted_weight = number_format($order->weight, 1) . ' kg';
                $order->formatted_total = 'Rp ' . number_format($order->total, 0, ',', '.');
                $order->status_label = $this->getStatusLabel($order->status);
                $order->status_color = $this->getStatusColor($order->status);
                return $order;
            });
        
        // Riwayat pesanan (status diambil)
        $orderHistory = Pesanan::where('user_id', $userId)
            ->where('status', 'diambil')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($order) {
                // Tambahkan atribut dinamis juga untuk riwayat
                $order->service_name = $this->getServiceName($order->service_type);
                $order->formatted_weight = number_format($order->weight, 1) . ' kg';
                $order->formatted_total = 'Rp ' . number_format($order->total, 0, ',', '.');
                $order->status_label = $this->getStatusLabel($order->status);
                $order->status_color = $this->getStatusColor($order->status);
                return $order;
            });
        
        // Kirim semua variabel ke view dashboard.blade.php
        return view('user.dashboard', compact(
            'totalOrders',
            'processingOrders',
            'readyOrders',
            'totalSpent',
            'activeOrders',
            'orderHistory'
        ));
    }
    
    /**
     * Helper untuk mengubah nama layanan menjadi Bahasa Indonesia
     */
    private function getServiceName($serviceType)
    {
        $services = [
            'cuci_kering'   => 'Cuci Kering',
            'cuci_setrika' => 'Cuci & Setrika',
            'setrika_saja' => 'Setrika Saja',
        ];
        
        return $services[$serviceType] ?? $serviceType;
    }
    
    /**
     * Helper untuk mengubah status menjadi label yang lebih mudah dibaca
     */
    private function getStatusLabel($status)
    {
        $labels = [
            'pending' => 'Menunggu Konfirmasi',
            'proses'  => 'Sedang Diproses',
            'selesai' => 'Siap Diambil',
            'diambil' => 'Telah Diambil',
        ];
        
        return $labels[$status] ?? $status;
    }
    
    /**
     * Helper untuk mendapatkan warna badge status
     */
    private function getStatusColor($status)
    {
        $colors = [
            'pending' => 'bg-yellow-100 text-yellow-800',
            'proses'  => 'bg-blue-100 text-blue-800',
            'selesai' => 'bg-green-100 text-green-800',
            'diambil' => 'bg-gray-100 text-gray-800',
        ];
        
        return $colors[$status] ?? 'bg-gray-100 text-gray-800';
    }
}