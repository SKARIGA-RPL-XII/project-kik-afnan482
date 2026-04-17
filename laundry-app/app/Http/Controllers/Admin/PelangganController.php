<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Pesanan;

class PelangganController extends Controller
{
    public function index()
    {
        // Ambil semua user dengan role 'user' (bukan admin)
        $pelanggan = User::where('role', 'user')
            ->withCount('pesanans')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.pelanggan.index', compact('pelanggan'));
    }

    public function create()
    {
        return view('admin.pelanggan.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'alamat' => 'nullable|string|max:500',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'password' => 'required|string|min:8|confirmed',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'alamat' => $request->alamat,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'password' => bcrypt($request->password),
            'role' => 'user',
        ]);

        return redirect()->route('admin.pelanggan.index')->with('success', 'Pelanggan berhasil ditambahkan');
    }

    public function edit($id)
    {
        $pelanggan = User::findOrFail($id);
        return view('admin.pelanggan.edit', compact('pelanggan'));
    }

    public function update(Request $request, $id)
    {
        $pelanggan = User::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $pelanggan->id,
            'phone' => 'nullable|string|max:20',
            'alamat' => 'nullable|string|max:500',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $data = $request->only('name', 'email', 'phone', 'alamat', 'latitude', 'longitude');
        
        // Update password hanya jika diisi
        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }

        $pelanggan->update($data);

        return redirect()->route('admin.pelanggan.index')->with('success', 'Pelanggan berhasil diperbarui');
    }

    public function destroy($id)
    {
        try {
            $pelanggan = User::findOrFail($id);
            
            // Cek apakah pelanggan memiliki pesanan
            if ($pelanggan->pesanans()->count() > 0) {
                return redirect()->route('admin.pelanggan.index')
                    ->with('error', 'Tidak dapat menghapus pelanggan yang memiliki riwayat pesanan');
            }
            
            $pelanggan->delete();
            return redirect()->route('admin.pelanggan.index')->with('success', 'Pelanggan berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('admin.pelanggan.index')->with('error', 'Gagal menghapus pelanggan');
        }
    }

    // Fungsi untuk melihat riwayat pesanan pelanggan
    public function getRiwayatPesanan($id)
    {
        try {
            $pelanggan = User::findOrFail($id);
            
            // Ambil pesanan berdasarkan user_id yang sesuai
            $pesanan = Pesanan::where('user_id', $id)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function($item) {
                    return [
                        'id' => $item->id,
                        'invoice' => $item->invoice,
                        'service_type' => $item->service_type,
                        'weight' => $item->weight,
                        'final_weight' => $item->final_weight,
                        'total' => $item->total,
                        'status' => $item->status,
                        'is_express' => $item->is_express ?? false,
                        'created_at' => $item->created_at->format('d M Y H:i'),
                    ];
                });

            return response()->json([
                'success' => true,
                'pelanggan' => [
                    'name' => $pelanggan->name,
                    'email' => $pelanggan->email,
                    'phone' => $pelanggan->phone ?? '-',
                ],
                'pesanan' => $pesanan
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat riwayat pesanan: ' . $e->getMessage()
            ], 500);
        }
    }

    // Proxy endpoint untuk reverse geocoding (mengatasi CORS)
    public function reverseGeocode(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
        ]);

        try {
            $lat = $request->lat;
            $lng = $request->lng;
            
            // Gunakan cURL untuk request ke Nominatim
            $url = "https://nominatim.openstreetmap.org/reverse?format=json&lat={$lat}&lon={$lng}&zoom=18&addressdetails=1";
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_USERAGENT, 'LaundryKu App/1.0');
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Accept: application/json',
                'Accept-Language: id'
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($httpCode === 200 && $response) {
                $data = json_decode($response, true);
                return response()->json([
                    'success' => true,
                    'address' => $data['display_name'] ?? null,
                    'data' => $data
                ]);
            } else {
                Log::warning('Geocoding failed', [
                    'http_code' => $httpCode,
                    'error' => $error
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Geocoding service unavailable',
                    'address' => null
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Geocoding exception', [
                'message' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'address' => null
            ]);
        }
    }
}