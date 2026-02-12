<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use App\Models\Layanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PemesananController extends Controller
{
    /**
     * Form pemesanan
     */
    public function index()
    {
        $layanans = Layanan::all();
        return view('user.pemesanan.index', compact('layanans'));
    }

    /**
     * Store pesanan
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'layanan_id'     => 'required|exists:layanans,id',
            'weight'         => 'required|numeric|min:1|max:50',
            'is_express'     => 'nullable|boolean',
            'address'        => 'required|string|max:500',
            'notes'          => 'nullable|string|max:500',
            'payment_method' => 'required|in:midtrans,cash',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $user = Auth::guard('web')->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User belum login'
                ], 401);
            }

            $layanan = Layanan::findOrFail($request->layanan_id);

            $weight      = $request->weight;
            $isExpress   = $request->boolean('is_express');

            $pricePerKg  = $layanan->tarif;
            $expressFee  = $isExpress ? 10000 : 0;
            $deliveryFee = 5000;

            $subtotal = ($pricePerKg * $weight) + ($expressFee * $weight);
            $total    = $subtotal + $deliveryFee;

            $baseDuration = $layanan->estimasi_hari ?? 3;
            $estimatedDuration = $isExpress ? '1 hari (24 jam)' : $baseDuration . ' hari';

            $invoice = Pesanan::generateInvoice();
            $orderId = 'LDRY-' . time() . '-' . uniqid();

            $pesanan = Pesanan::create([
                'invoice'            => $invoice,
                'order_id'           => $orderId,
                'user_id'            => $user->id,
                'layanan_id'         => $layanan->id,
                'customer_name'      => $user->name,
                'customer_phone'     => $user->phone ?? '-',
                'service_type'       => $layanan->nama_layanan,
                'weight'             => $weight,
                'is_express'         => $isExpress,
                'price_per_kg'       => $pricePerKg,
                'express_fee'        => $expressFee,
                'delivery_fee'       => $deliveryFee,
                'subtotal'           => $subtotal,
                'total'              => $total,
                'address'            => $request->address,
                'notes'              => $request->notes,
                'payment_method'     => $request->payment_method,
                'status'             => 'pending',
                'payment_status'     => 'pending',
                'estimated_duration' => $estimatedDuration,
            ]);

            $snapToken = null;

            // Generate Snap Token HANYA jika payment method adalah Midtrans
            if ($request->payment_method === 'midtrans') {
                try {
                    // Konfigurasi Midtrans
                    \Midtrans\Config::$serverKey = config('midtrans.server_key');
                    \Midtrans\Config::$isProduction = config('midtrans.is_production', false);
                    \Midtrans\Config::$isSanitized = true;
                    \Midtrans\Config::$is3ds = true;

                    $params = [
                        'transaction_details' => [
                            'order_id' => $orderId,
                            'gross_amount' => (int)$total,
                        ],
                        'customer_details' => [
                            'first_name' => $user->name,
                            'email' => $user->email,
                            'phone' => $user->phone ?? '081234567890',
                        ],
                        'item_details' => [
                            [
                                'id' => 'LAYANAN-' . $layanan->id,
                                'price' => (int)$pricePerKg,
                                'quantity' => (int)$weight,
                                'name' => $layanan->nama_layanan,
                            ],
                        ],
                    ];

                    // Tambahkan express fee jika ada
                    if ($isExpress) {
                        $params['item_details'][] = [
                            'id' => 'EXPRESS-FEE',
                            'price' => (int)$expressFee,
                            'quantity' => (int)$weight,
                            'name' => 'Layanan Express (24 jam)',
                        ];
                    }

                    // Tambahkan delivery fee
                    $params['item_details'][] = [
                        'id' => 'DELIVERY-FEE',
                        'price' => (int)$deliveryFee,
                        'quantity' => 1,
                        'name' => 'Biaya Antar-Jemput',
                    ];

                    // Generate Snap Token
                    $snapToken = \Midtrans\Snap::getSnapToken($params);
                    
                    // Update pesanan dengan snap token
                    $pesanan->update(['snap_token' => $snapToken]);

                    Log::info('Midtrans Snap Token Generated', [
                        'order_id' => $orderId,
                        'snap_token' => $snapToken
                    ]);

                } catch (\Exception $e) {
                    DB::rollBack();
                    
                    Log::error('Midtrans Snap Token Error', [
                        'error' => $e->getMessage(),
                        'order_id' => $orderId
                    ]);

                    return response()->json([
                        'success' => false,
                        'message' => 'Gagal membuat token pembayaran. Silakan coba lagi.',
                        'error_detail' => $e->getMessage()
                    ], 500);
                }
            } else {
                // Untuk cash payment, langsung set status confirmed
                $pesanan->update([
                    'status' => 'confirmed',
                ]);
            }

            DB::commit();

            // Return response dengan payment_method
            return response()->json([
                'success' => true,
                'message' => 'Pesanan berhasil dibuat!',
                'data'    => [
                    'id'             => $pesanan->id,
                    'invoice'        => $invoice,
                    'order_id'       => $orderId,
                    'total'          => $total,
                    'payment_method' => $request->payment_method, // PENTING: Tambahkan ini
                    'snap_token'     => $snapToken,
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Order Creation Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat pesanan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * My Orders (JSON)
     */
    public function myOrders()
    {
        $userId = Auth::guard('web')->id();

        $orders = Pesanan::with('layanan')
            ->where('user_id', $userId)
            ->latest()
            ->get();

        return response()->json($orders);
    }

    /**
     * Detail Order
     */
    public function show($id)
    {
        $userId = Auth::guard('web')->id();

        $order = Pesanan::with('layanan')
            ->where('user_id', $userId)
            ->where('id', $id)
            ->firstOrFail();

        return response()->json($order);
    }

    /**
     * Riwayat (VIEW)
     */
    public function riwayat()
    {
        $userId = Auth::guard('web')->id();

        $pesanans = Pesanan::with('layanan')
            ->where('user_id', $userId)
            ->latest()
            ->get();

        return view('user.riwayat.index', compact('pesanans'));
    }

    /**
     * Callback Midtrans (Notification Handler)
     */
    public function callback(Request $request)
    {
        try {
            $serverKey = config('midtrans.server_key');
            
            // Verifikasi signature key
            $hashed = hash("sha512",
                $request->order_id .
                $request->status_code .
                $request->gross_amount .
                $serverKey
            );

            // Log untuk debugging
            Log::info('Midtrans Callback Received', [
                'order_id' => $request->order_id,
                'transaction_status' => $request->transaction_status,
                'payment_type' => $request->payment_type,
                'signature_valid' => ($hashed === $request->signature_key)
            ]);

            if ($hashed !== $request->signature_key) {
                Log::warning('Invalid Midtrans Signature', [
                    'order_id' => $request->order_id,
                    'expected' => $hashed,
                    'received' => $request->signature_key
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid signature'
                ], 403);
            }

            $pesanan = Pesanan::where('order_id', $request->order_id)->first();

            if (!$pesanan) {
                Log::error('Order not found', ['order_id' => $request->order_id]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            $transactionStatus = $request->transaction_status;
            $fraudStatus = $request->fraud_status ?? null;

            // Handle transaction status
            if ($transactionStatus == 'capture') {
                if ($fraudStatus == 'accept') {
                    $pesanan->update([
                        'payment_status' => 'success',
                        'status' => 'confirmed',
                        'paid_at' => now(),
                    ]);
                }
            } elseif ($transactionStatus == 'settlement') {
                $pesanan->update([
                    'payment_status' => 'success',
                    'status' => 'confirmed',
                    'paid_at' => now(),
                ]);
            } elseif ($transactionStatus == 'pending') {
                $pesanan->update([
                    'payment_status' => 'pending',
                ]);
            } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
                $pesanan->update([
                    'payment_status' => 'failed',
                    'status' => 'cancelled',
                ]);
            }

            Log::info('Payment Status Updated', [
                'order_id' => $request->order_id,
                'payment_status' => $pesanan->payment_status,
                'status' => $pesanan->status
            ]);

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('Midtrans Callback Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }
}