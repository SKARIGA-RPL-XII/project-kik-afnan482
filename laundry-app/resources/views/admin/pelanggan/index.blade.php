<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Data Pelanggan - Laundry System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .swal2-container.swal2-top-end { top: 1rem !important; right: 1rem !important; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        .map-container {
            height: 300px; width: 100%;
            border-radius: 0.5rem; border: 2px solid #E5E7EB;
            z-index: 1; margin-bottom: 1rem;
        }
        .leaflet-control-geocoder { border-radius: 0.5rem !important; border: 2px solid #3B82F6 !important; }
        .leaflet-control-geocoder-form input { border-radius: 0.5rem !important; padding: 8px 12px !important; font-family: inherit !important; }
        .location-button {
            position: absolute; top: 10px; right: 10px;
            background: white; border: 2px solid #E5E7EB;
            border-radius: 0.5rem; padding: 10px; cursor: pointer;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1); z-index: 1000;
        }
        .location-button:hover { background: #F3F4F6; }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 to-gray-100 min-h-screen flex">

    @include('admin.sidebar')

    <div id="overlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden lg:hidden"></div>

    <div class="flex-1 flex flex-col min-h-screen">
        <header class="bg-white shadow-sm">
            <div class="px-4 lg:px-8 py-4 flex items-center justify-between">
                <div class="ml-12 lg:ml-0">
                    <h2 class="text-xl lg:text-2xl font-bold text-gray-800">Data Pelanggan</h2>
                    <p class="text-xs lg:text-sm text-gray-600">Kelola data pelanggan laundry</p>
                </div>
                <div class="w-8 h-8 lg:w-10 lg:h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center text-white font-bold text-sm">
                    {{ substr(Auth::user()->name ?? 'A', 0, 1) }}
                </div>
            </div>
        </header>

        <main class="flex-1 p-4 lg:p-8">

            <!-- Action Bar -->
            <div class="bg-white rounded-xl shadow-sm p-4 lg:p-6 mb-4 lg:mb-6">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                    <h3 class="text-lg font-bold text-gray-800">Daftar Pelanggan</h3>
                    <button onclick="openModal('addModal')" class="bg-blue-600 hover:bg-blue-700 text-white px-4 lg:px-6 py-2 rounded-lg flex items-center justify-center space-x-2 text-sm transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        <span>Tambah Pelanggan</span>
                    </button>
                </div>
            </div>

            <!-- Desktop Table -->
            <div class="hidden lg:block bg-white rounded-xl shadow-sm overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">No</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Nama</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Email</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Telepon</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Alamat</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Total Pesanan</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($pelanggan as $i => $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-700">{{ $pelanggan->firstItem() + $i }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="w-9 h-9 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold text-sm mr-3 flex-shrink-0">
                                        {{ strtoupper(substr($item->name, 0, 1)) }}
                                    </div>
                                    <span class="font-semibold text-gray-900">{{ $item->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">{{ $item->email }}</td>
                            <td class="px-6 py-4 text-sm text-gray-700">{{ $item->phone ?? '-' }}</td>
                            {{-- ✅ Pakai $item->address (kolom DB dari AuthController) --}}
                            <td class="px-6 py-4 text-sm text-gray-700">{{ Str::limit($item->address ?? '-', 30) }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                                    {{ $item->pesanans_count ?? 0 }} Pesanan
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex gap-2">
                                    <button onclick='viewRiwayat({{ $item->id }})' class="p-1.5 text-purple-600 hover:text-purple-800 hover:bg-purple-50 rounded-lg transition" title="Riwayat Pesanan">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    </button>
                                    <button onclick='viewDetail(@json($item))' class="p-1.5 text-green-600 hover:text-green-800 hover:bg-green-50 rounded-lg transition" title="Detail">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    </button>
                                    <button onclick='editPelanggan(@json($item))' class="p-1.5 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition" title="Edit">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </button>
                                    <button onclick="deletePelanggan({{ $item->id }}, '{{ addslashes($item->name) }}')" class="p-1.5 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg transition" title="Hapus">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                Belum ada data pelanggan
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile Cards -->
            <div class="lg:hidden space-y-3">
                @forelse ($pelanggan as $item)
                <div class="bg-white rounded-xl shadow-sm p-4">
                    <div class="flex items-center mb-3">
                        <div class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold mr-3 flex-shrink-0">
                            {{ strtoupper(substr($item->name, 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-gray-900 truncate">{{ $item->name }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ $item->email }}</p>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 mt-1">
                                {{ $item->pesanans_count ?? 0 }} Pesanan
                            </span>
                        </div>
                    </div>
                    <div class="space-y-1 mb-3 text-sm">
                        <p><span class="font-medium text-gray-700">Telepon:</span> <span class="text-gray-600">{{ $item->phone ?? '-' }}</span></p>
                        {{-- ✅ Pakai $item->address --}}
                        <p><span class="font-medium text-gray-700">Alamat:</span> <span class="text-gray-600">{{ Str::limit($item->address ?? '-', 40) }}</span></p>
                    </div>
                    <div class="flex gap-2">
                        <button onclick='viewRiwayat({{ $item->id }})' class="flex-1 bg-purple-100 text-purple-700 px-3 py-2 rounded-lg text-xs font-medium hover:bg-purple-200 transition">Riwayat</button>
                        <button onclick='viewDetail(@json($item))' class="flex-1 bg-green-100 text-green-700 px-3 py-2 rounded-lg text-xs font-medium hover:bg-green-200 transition">Detail</button>
                        <button onclick='editPelanggan(@json($item))' class="flex-1 bg-blue-100 text-blue-700 px-3 py-2 rounded-lg text-xs font-medium hover:bg-blue-200 transition">Edit</button>
                        <button onclick="deletePelanggan({{ $item->id }}, '{{ addslashes($item->name) }}')" class="flex-1 bg-red-100 text-red-700 px-3 py-2 rounded-lg text-xs font-medium hover:bg-red-200 transition">Hapus</button>
                    </div>
                </div>
                @empty
                <div class="bg-white rounded-xl shadow-sm px-6 py-12 text-center text-gray-500">Belum ada data pelanggan</div>
                @endforelse
            </div>

            @if($pelanggan->hasPages())
            <div class="mt-4 bg-white rounded-xl shadow-sm px-6 py-4">
                {{ $pelanggan->links() }}
            </div>
            @endif

        </main>
    </div>

    <!-- ===== MODAL ADD ===== -->
    <div id="addModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center p-4" style="display:none;">
        <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto no-scrollbar">
            <div class="sticky top-0 bg-blue-600 text-white px-6 py-4 flex justify-between items-center rounded-t-xl z-10">
                <h3 class="text-xl font-bold">Tambah Pelanggan</h3>
                <button onclick="closeModal('addModal')" class="text-white hover:text-blue-200 text-2xl leading-none">&times;</button>
            </div>
            <form id="addForm" action="{{ route('admin.pelanggan.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="add_name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" id="add_email" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Telepon</label>
                    <input type="text" name="phone" id="add_phone" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Alamat dengan Peta</label>
                    <div class="relative">
                        <div id="addMap" class="map-container"></div>
                        <button type="button" id="addUseMyLocation" class="location-button" title="Gunakan lokasi saya">
                            <i class="fas fa-crosshairs text-blue-600"></i>
                        </button>
                    </div>
                    <input type="hidden" id="add_latitude"  name="latitude">
                    <input type="hidden" id="add_longitude" name="longitude">
                    {{-- ✅ name="address" sesuai kolom DB --}}
                    <textarea name="address" id="add_address" rows="2" readonly
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 text-sm"
                        placeholder="Alamat akan terisi otomatis setelah memilih lokasi di peta"></textarea>
                    <p class="text-xs text-gray-500 mt-1"><i class="fas fa-info-circle mr-1"></i>Klik pada peta atau geser marker untuk menentukan lokasi</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Password <span class="text-red-500">*</span></label>
                    <input type="password" name="password" id="add_password" required minlength="8" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-500 mt-1">Min 8 karakter</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Konfirmasi Password <span class="text-red-500">*</span></label>
                    <input type="password" name="password_confirmation" id="add_password_confirmation" required minlength="8" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-lg font-medium transition">Simpan</button>
                    <button type="button" onclick="closeModal('addModal')" class="flex-1 bg-gray-200 hover:bg-gray-300 px-4 py-2.5 rounded-lg font-medium transition">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===== MODAL VIEW ===== -->
    <div id="viewModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center p-4" style="display:none;">
        <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full">
            <div class="bg-green-600 text-white px-6 py-4 flex justify-between items-center rounded-t-xl">
                <h3 class="text-xl font-bold">Detail Pelanggan</h3>
                <button onclick="closeModal('viewModal')" class="text-white hover:text-green-200 text-2xl leading-none">&times;</button>
            </div>
            <div class="p-6" id="viewContent"></div>
        </div>
    </div>

    <!-- ===== MODAL EDIT ===== -->
    <div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center p-4" style="display:none;">
        <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto no-scrollbar">
            <div class="sticky top-0 bg-blue-600 text-white px-6 py-4 flex justify-between items-center rounded-t-xl z-10">
                <h3 class="text-xl font-bold">Edit Pelanggan</h3>
                <button onclick="closeModal('editModal')" class="text-white hover:text-blue-200 text-2xl leading-none">&times;</button>
            </div>
            <form id="editForm" method="POST" class="p-6 space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="edit_name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" id="edit_email" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Telepon</label>
                    <input type="text" name="phone" id="edit_phone" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Alamat dengan Peta</label>
                    <div class="relative">
                        <div id="editMap" class="map-container"></div>
                        <button type="button" id="editUseMyLocation" class="location-button" title="Gunakan lokasi saya">
                            <i class="fas fa-crosshairs text-blue-600"></i>
                        </button>
                    </div>
                    <input type="hidden" id="edit_latitude"  name="latitude">
                    <input type="hidden" id="edit_longitude" name="longitude">
                    {{-- ✅ name="address" sesuai kolom DB --}}
                    <textarea name="address" id="edit_address" rows="2" readonly
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 text-sm"
                        placeholder="Alamat akan terisi otomatis setelah memilih lokasi di peta"></textarea>
                    <p class="text-xs text-gray-500 mt-1"><i class="fas fa-info-circle mr-1"></i>Klik pada peta atau geser marker untuk menentukan lokasi</p>
                </div>
                <div class="border-t pt-4">
                    <p class="text-sm text-gray-500 mb-3">Kosongkan jika tidak ingin mengubah password</p>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Password Baru</label>
                            <input type="password" name="password" id="edit_password" minlength="8" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <p class="text-xs text-gray-500 mt-1">Min 8 karakter</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Konfirmasi Password</label>
                            <input type="password" name="password_confirmation" id="edit_password_confirmation" minlength="8" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-lg font-medium transition">Update</button>
                    <button type="button" onclick="closeModal('editModal')" class="flex-1 bg-gray-200 hover:bg-gray-300 px-4 py-2.5 rounded-lg font-medium transition">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===== MODAL DELETE ===== -->
    <div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center p-4" style="display:none;">
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full">
            <div class="p-6">
                <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full mb-4">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-center text-gray-800">Konfirmasi Hapus</h3>
                <p class="mt-2 text-sm text-gray-600 text-center">Yakin ingin menghapus <strong id="deleteName"></strong>?</p>
                <form id="deleteForm" method="POST" class="mt-6 flex gap-3">
                    @csrf
                    @method('DELETE')
                    <button type="button" onclick="closeModal('deleteModal')" class="flex-1 bg-gray-200 hover:bg-gray-300 px-4 py-2.5 rounded-lg font-medium transition">Batal</button>
                    <button type="submit" class="flex-1 bg-red-600 hover:bg-red-700 text-white px-4 py-2.5 rounded-lg font-medium transition">Ya, Hapus</button>
                </form>
            </div>
        </div>
    </div>

    <!-- ===== MODAL RIWAYAT ===== -->
    <div id="riwayatModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center p-4" style="display:none;">
        <div class="bg-white rounded-xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto no-scrollbar">
            <div class="sticky top-0 bg-purple-600 text-white px-6 py-4 flex justify-between items-center rounded-t-xl z-10">
                <div>
                    <h3 class="text-xl font-bold">Riwayat Pesanan</h3>
                    <p class="text-sm text-purple-100 mt-0.5" id="riwayatPelangganInfo">-</p>
                </div>
                <button onclick="closeModal('riwayatModal')" class="text-white hover:text-purple-200 text-2xl leading-none">&times;</button>
            </div>
            <div class="p-6" id="riwayatContent">
                <div class="text-center py-8">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-purple-600"></div>
                    <p class="text-gray-500 mt-2">Memuat data...</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
    <script src="{{ asset('js/pelanggan.js') }}"></script>

    <script>
        window.openModal = function(id) {
            const el = document.getElementById(id);
            el.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            if (id === 'addModal')  setTimeout(() => initAddMap(),  100);
            if (id === 'editModal') setTimeout(() => initEditMap(), 100);
        };
        window.closeModal = function(id) {
            const el = document.getElementById(id);
            el.style.display = 'none';
            document.body.style.overflow = 'auto';
            if (id === 'addModal') {
                document.getElementById('addForm').reset();
                if (typeof addMap !== 'undefined' && addMap) { addMap.remove(); addMap = null; addMarker = null; }
            }
            if (id === 'editModal') {
                document.getElementById('editForm').reset();
                if (typeof editMap !== 'undefined' && editMap) { editMap.remove(); editMap = null; editMarker = null; }
            }
        };
    </script>

    <script>
        @if(session('success'))
        Swal.fire({ icon: 'success', title: 'Berhasil!', text: '{{ session('success') }}', timer: 3000, toast: true, position: 'top-end', showConfirmButton: false, timerProgressBar: true });
        @endif
        @if(session('error'))
        Swal.fire({ icon: 'error', title: 'Gagal!', text: '{{ session('error') }}' });
        @endif
    </script>

</body>
</html>