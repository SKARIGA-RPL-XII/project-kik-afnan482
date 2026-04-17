<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = Auth::guard('web')->id();

        // Scope: hanya tampilkan pesanan yang valid
        // Cash selalu tampil, Midtrans hanya jika payment_status sudah paid/pending/success
        $visible = function ($q) {
            $q->where(function ($sub) {
                $sub->where('payment_method', 'cash')
                    ->orWhereIn('payment_status', ['paid', 'pending', 'success']);
            });
        };

        $totalOrders = Pesanan::where('user_id', $userId)
            ->where($visible)
            ->count();

        $processingOrders = Pesanan::where('user_id', $userId)
            ->whereIn('status', ['pending', 'proses'])
            ->where($visible)
            ->count();

        $readyOrders = Pesanan::where('user_id', $userId)
            ->where('status', 'selesai')
            ->where($visible)
            ->count();

        $totalSpent = Pesanan::where('user_id', $userId)
            ->where('status', 'diambil')
            ->where($visible)
            ->sum('total');

        $activeOrders = Pesanan::where('user_id', $userId)
            ->whereIn('status', ['pending', 'proses', 'selesai'])
            ->where($visible)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($order) {
                $order->setAttribute('service_name', $this->getServiceName($order->service_type));
                $order->setAttribute('formatted_weight', number_format($order->weight, 1) . ' kg');
                $order->setAttribute('formatted_total', 'Rp ' . number_format($order->total, 0, ',', '.'));
                $order->setAttribute('status_label', $this->getStatusLabel($order->status));
                $order->setAttribute('status_color', $this->getStatusColor($order->status));

                $paymentStatus = $order->payment_status ?? 'unpaid';
                $order->setAttribute('payment_status', $paymentStatus);
                $order->setAttribute('payment_method', $order->payment_method ?? 'cash');
                $order->setAttribute('show_payment_status',
                    $order->payment_method !== 'cash' && !in_array($paymentStatus, ['paid', 'success'])
                );

                return $order;
            });

        $orderHistory = Pesanan::where('user_id', $userId)
            ->where('status', 'diambil')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($order) {
                $order->setAttribute('service_name', $this->getServiceName($order->service_type));
                $order->setAttribute('formatted_weight', number_format($order->weight, 1) . ' kg');
                $order->setAttribute('formatted_total', 'Rp ' . number_format($order->total, 0, ',', '.'));
                $order->setAttribute('status_label', $this->getStatusLabel($order->status));
                $order->setAttribute('status_color', $this->getStatusColor($order->status));
                return $order;
            });

        return view('user.dashboard', compact(
            'totalOrders',
            'processingOrders',
            'readyOrders',
            'totalSpent',
            'activeOrders',
            'orderHistory'
        ));
    }

    private function getServiceName($serviceType)
    {
        $services = [
            'cuci_kering'   => 'Cuci Kering',
            'cuci_setrika'  => 'Cuci & Setrika',
            'setrika_saja'  => 'Setrika Saja',
        ];
        return $services[$serviceType] ?? $serviceType;
    }

    private function getStatusLabel($status)
    {
        $labels = [
            'pending' => 'Pesanan Baru',
            'proses'  => 'Sedang Diproses',
            'selesai' => 'Siap Diambil',
            'diambil' => 'Telah Diambil',
        ];
        return $labels[$status] ?? $status;
    }

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