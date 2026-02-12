<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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
            'alamat' => 'nullable|string|max:255',
            'password' => 'required|string|min:8|confirmed',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'alamat' => $request->alamat,
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
            'alamat' => 'nullable|string|max:255',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $data = $request->only('name', 'email', 'phone', 'alamat');
        
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
}