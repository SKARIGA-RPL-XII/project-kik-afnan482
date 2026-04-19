<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use App\Models\Layanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class PesananController extends Controller
{
    public function index()
    {
        $layanans = Layanan::all();
        return view('admin.pesanan.index', compact('layanans'));
    }

    public function getData(Request $request)
    {
        $query = Pesanan::with(['user', 'layanan'])
            ->where('status', '!=', 'cancelled')
            ->where(function ($q) {
                $q->where('payment_method', 'cash')
                  ->orWhere(function ($sub) {
                      $sub->where('payment_method', 'midtrans')
                          ->whereIn('payment_status', ['pending', 'success', 'paid']);
                  });
            })
            ->orderBy('created_at', 'desc');

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('invoice', 'like', "%{$request->search}%")
                  ->orWhere('customer_name', 'like', "%{$request->search}%")
                  ->orWhere('customer_phone', 'like', "%{$request->search}%");
            });
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        // Load semua layanan sekali untuk fallback matching
        $semuaLayanan = Layanan::all();

        $pesanan = $query->get()->map(function($item) use ($semuaLayanan) {
            $serviceName = $item->service_type;
            if ($item->is_express) {
                $serviceName .= ' (Express)';
            }

            // FIX: Jika layanan_id null, cari berdasarkan nama service_type
            $layananId = $item->layanan_id;
            if (!$layananId && $item->service_type) {
                $matched = $semuaLayanan->first(function ($l) use ($item) {
                    return strtolower(trim($l->nama_layanan)) === strtolower(trim($item->service_type));
                });
                $layananId = $matched?->id;
            }

            return [
                'id'             => $item->id,
                'invoice'        => $item->invoice,
                'customer'       => $item->customer_name,
                'phone'          => $item->customer_phone,
                'service'        => $serviceName,
                'layanan_id'     => $layananId,
                'is_express'     => $item->is_express,
                'weight'         => $item->weight,
                'final_weight'   => $item->final_weight,
                'price_per_kg'   => $item->price_per_kg,
                'express_fee'    => $item->express_fee,
                'delivery_fee'   => $item->delivery_fee,
                'subtotal'       => $item->subtotal,
                'total'          => $item->total,
                'address'        => $item->address,
                'latitude'       => $item->latitude,
                'longitude'      => $item->longitude,
                'notes'          => $item->notes,
                'payment_method' => $item->payment_method,
                'status'         => $item->status,
                'created_at'     => $item->created_at->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json($pesanan);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_name'  => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'layanan_id'     => 'required|exists:layanans,id',
            'weight'         => 'required|numeric|min:0.1',
            'is_express'     => 'nullable|boolean',
            'status'         => 'required|in:pending,proses,selesai,diambil',
            'address'        => 'nullable|string|max:500',
            'latitude'       => 'nullable|numeric|between:-90,90',
            'longitude'      => 'nullable|numeric|between:-180,180',
            'notes'          => 'nullable|string|max:500',
            'payment_method' => 'nullable|in:cash,transfer,ewallet',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validasi gagal', 'errors' => $validator->errors()], 422);
        }

        try {
            $layanan   = Layanan::findOrFail($request->layanan_id);
            $isExpress = $request->is_express == true || $request->is_express == 1;

            $pricePerKg  = $layanan->tarif;
            $expressFee  = $isExpress ? 10000 : 0;
            $deliveryFee = 5000;
            $subtotal    = ($pricePerKg * $request->weight) + ($expressFee * $request->weight);
            $total       = $subtotal + $deliveryFee;

            $baseDuration      = $layanan->estimasi_hari ?? 3;
            $estimatedDuration = $isExpress ? '1 hari (24 jam)' : $baseDuration . ' hari';

            $userId = Auth::check() ? Auth::id() : null;

            $pesanan = Pesanan::create([
                'invoice'            => Pesanan::generateInvoice(),
                'user_id'            => $userId,
                'layanan_id'         => $layanan->id,
                'customer_name'      => $request->customer_name,
                'customer_phone'     => $request->customer_phone,
                'service_type'       => $layanan->nama_layanan,
                'weight'             => $request->weight,
                'is_express'         => $isExpress,
                'price_per_kg'       => $pricePerKg,
                'express_fee'        => $expressFee,
                'delivery_fee'       => $deliveryFee,
                'subtotal'           => $subtotal,
                'total'              => $total,
                'address'            => $request->address ?? 'Walk-in',
                'latitude'           => $request->latitude,
                'longitude'          => $request->longitude,
                'notes'              => $request->notes,
                'payment_method'     => $request->payment_method ?? 'cash',
                'payment_status'     => 'success',
                'status'             => $request->status,
                'estimated_duration' => $estimatedDuration,
            ]);

            return response()->json(['success' => true, 'message' => 'Pesanan berhasil ditambahkan!', 'data' => $pesanan], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menambahkan pesanan: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'customer_name'  => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'layanan_id'     => 'required|exists:layanans,id',
            'weight'         => 'required|numeric|min:0.1',
            'final_weight'   => 'nullable|numeric|min:0.1',
            'address'        => 'nullable|string|max:500',
            'latitude'       => 'nullable|numeric|between:-90,90',
            'longitude'      => 'nullable|numeric|between:-180,180',
            'notes'          => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validasi gagal', 'errors' => $validator->errors()], 422);
        }

        try {
            $pesanan     = Pesanan::findOrFail($id);
            $layanan     = Layanan::findOrFail($request->layanan_id);
            $pricePerKg  = $layanan->tarif;
            $expressFee  = $pesanan->express_fee;
            $deliveryFee = 5000;

            $weightForCalc = $request->final_weight ?? $request->weight;
            $subtotal      = ($pricePerKg * $weightForCalc) + ($expressFee * $weightForCalc);
            $total         = $subtotal + $deliveryFee;

            $baseDuration      = $layanan->estimasi_hari ?? 3;
            $estimatedDuration = $pesanan->is_express ? '1 hari (24 jam)' : $baseDuration . ' hari';

            $pesanan->update([
                'customer_name'      => $request->customer_name,
                'customer_phone'     => $request->customer_phone,
                'layanan_id'         => $layanan->id,
                'service_type'       => $layanan->nama_layanan,
                'weight'             => $request->weight,
                'final_weight'       => $request->final_weight,
                'price_per_kg'       => $pricePerKg,
                'subtotal'           => $subtotal,
                'total'              => $total,
                'address'            => $request->address        ?? $pesanan->address,
                'latitude'           => $request->latitude       ?? $pesanan->latitude,
                'longitude'          => $request->longitude      ?? $pesanan->longitude,
                'notes'              => $request->notes          ?? $pesanan->notes,
                'estimated_duration' => $estimatedDuration,
            ]);

            return response()->json(['success' => true, 'message' => 'Pesanan berhasil diupdate!', 'data' => $pesanan]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal mengupdate pesanan: ' . $e->getMessage()], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,proses,selesai,diambil'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validasi gagal', 'errors' => $validator->errors()], 422);
        }

        try {
            $pesanan = Pesanan::findOrFail($id);
            $pesanan->update(['status' => $request->status]);
            return response()->json(['success' => true, 'message' => 'Status pesanan berhasil diupdate!', 'data' => $pesanan]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal mengupdate status: ' . $e->getMessage()], 500);
        }
    }

    public function updateFinalWeight(Request $request, $id)
    {
        $validator = Validator::make($request->all(), ['final_weight' => 'required|numeric|min:0.1']);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validasi gagal', 'errors' => $validator->errors()], 422);
        }

        try {
            $pesanan     = Pesanan::findOrFail($id);
            $finalWeight = $request->final_weight;
            $subtotal    = ($pesanan->price_per_kg * $finalWeight) + ($pesanan->express_fee * $finalWeight);
            $total       = $subtotal + $pesanan->delivery_fee;

            $pesanan->update([
                'final_weight' => $finalWeight,
                'subtotal'     => $subtotal,
                'total'        => $total,
            ]);

            return response()->json(['success' => true, 'message' => 'Berat akhir berhasil diupdate!', 'data' => $pesanan]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal mengupdate berat akhir: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $pesanan = Pesanan::findOrFail($id);
            $pesanan->delete();
            return response()->json(['success' => true, 'message' => 'Pesanan berhasil dihapus!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menghapus pesanan: ' . $e->getMessage()], 500);
        }
    }
}