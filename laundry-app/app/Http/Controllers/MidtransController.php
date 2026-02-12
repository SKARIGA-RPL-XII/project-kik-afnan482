<?php

namespace App\Http\Controllers;

use App\Models\Pesanan;
use Midtrans\Notification;

class MidtransController extends Controller
{
    public function callback()
    {
        $notif = new Notification();

        $orderId = $notif->order_id;
        $status  = $notif->transaction_status;

        $pesanan = Pesanan::where('order_id', $orderId)->first();

        if (!$pesanan) {
            return response()->json(['message' => 'Pesanan tidak ditemukan'], 404);
        }

        if (in_array($status, ['settlement', 'capture'])) {
            $pesanan->status = 'dibayar';
        } elseif ($status === 'pending') {
            $pesanan->status = 'menunggu_pembayaran';
        } else {
            $pesanan->status = 'batal';
        }

        $pesanan->save();

        return response()->json(['status' => 'ok']);
    }
}
