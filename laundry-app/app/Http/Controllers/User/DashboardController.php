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
        // Cash: selalu tampil
        // Midtrans: HANYA tampil jika payment_status sudah bukan 'unpaid'
        //           (pending = Midtrans sedang proses, success/paid = berhasil)
        // Juga exclude pesanan yang sudah cancelled
        $visible = function ($q) {
            $q->where('status', '!=', 'cancelled')
              ->where(function ($sub) {
                  $sub->where('payment_method', 'cash')
                      ->orWhere(function ($m) {
                          // Midtrans hanya jika sudah ada interaksi nyata dengan gateway
                          $m->where('payment_method', 'midtrans')
                            ->where('payment_status', '!=', 'unpaid');
                      });
              });
        };

        $pendingOrders = Pesanan::where('user_id', $userId)
            ->where('status', 'pending')
            ->where($visible)
            ->count();

        $prosesOrders = Pesanan::where('user_id', $userId)
            ->where('status', 'proses')
            ->where($visible)
            ->count();

        $selesaiOrders = Pesanan::where('user_id', $userId)
            ->where('status', 'selesai')
            ->where($visible)
            ->count();

        $diambilOrders = Pesanan::where('user_id', $userId)
            ->where('status', 'diambil')
            ->where($visible)
            ->count();

        $activeOrders = Pesanan::where('user_id', $userId)
            ->whereIn('status', ['pending', 'proses', 'selesai', 'diambil'])
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
            'pendingOrders',
            'prosesOrders',
            'selesaiOrders',
            'diambilOrders',
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
            'pending' => 'Pending',
            'proses'  => 'Proses',
            'selesai' => 'Selesai',
            'diambil' => 'Diambil',
        ];
        return $labels[$status] ?? ucfirst($status);
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