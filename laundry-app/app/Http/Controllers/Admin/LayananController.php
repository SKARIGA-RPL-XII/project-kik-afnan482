<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Layanan;
use Illuminate\Http\Request;

class LayananController extends Controller
{
    public function index()
    {
        $layanans = Layanan::latest()->get();
        return view('admin.layanan.index', compact('layanans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_layanan' => 'required|string|max:255',
            'tarif' => 'required|numeric|min:0',
            'satuan' => 'required|string|max:50',
            'deskripsi' => 'nullable|string'
        ]);

        Layanan::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Layanan berhasil ditambahkan'
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_layanan' => 'required|string|max:255',
            'tarif' => 'required|numeric|min:0',
            'satuan' => 'required|string|max:50',
            'deskripsi' => 'nullable|string'
        ]);

        $layanan = Layanan::findOrFail($id);
        $layanan->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Layanan berhasil diperbarui'
        ]);
    }

    public function destroy($id)
    {
        $layanan = Layanan::findOrFail($id);
        $layanan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Layanan berhasil dihapus'
        ]);
    }
}