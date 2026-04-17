<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Kelola Layanan - Laundry System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .swal2-container.swal2-top-end {
            top: 1rem !important;
            right: 1rem !important;
        }
        .swal2-toast {
            padding: 1rem !important;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 to-gray-100 min-h-screen flex">
    <div id="app-data" 
         data-store-url="{{ url('/admin/layanan') }}"
         style="display: none;">
    </div>

    <button onclick="toggleSidebar()" class="lg:hidden fixed top-4 left-4 z-50 bg-blue-600 text-white p-2 rounded-lg shadow-lg">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
        </svg>
    </button>

    @include('admin.sidebar')

    <div id="overlay" onclick="toggleSidebar()" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden lg:hidden"></div>

    <div class="flex-1 flex flex-col min-h-screen">
        <header class="bg-white shadow-sm">
            <div class="px-4 lg:px-8 py-4 flex items-center justify-between">
                <div class="ml-12 lg:ml-0">
                    <h2 class="text-xl lg:text-2xl font-bold text-gray-800">Kelola Layanan</h2>
                    <p class="text-xs lg:text-sm text-gray-600">Mengelola jenis dan tarif layanan laundry</p>
                </div>
                <div class="w-8 h-8 lg:w-10 lg:h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center text-white font-bold text-sm">
                    {{ substr(auth()->user()->name ?? 'A', 0, 1) }}
                </div>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-4 lg:p-8">
            
            <div class="bg-white rounded-xl shadow-sm p-4 lg:p-6 mb-4 lg:mb-6">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                    <div class="flex flex-col sm:flex-row gap-2 lg:gap-4">
                        <input type="text" id="searchInput" placeholder="Cari layanan..." 
                               class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm lg:w-64">
                    </div>
                    <button onclick="openModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 lg:px-6 py-2 rounded-lg flex items-center justify-center space-x-2 text-sm transition shadow-md">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        <span>Tambah Layanan</span>
                    </button>
                </div>
            </div>

            <div class="hidden lg:block bg-white rounded-xl shadow-sm overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">No</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Nama Layanan</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Tarif</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Satuan</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Deskripsi</th>
                            <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody" class="divide-y divide-gray-200">
                        @forelse($layanans as $index => $layanan)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $index + 1 }}</td>
                            <td class="px-6 py-4 font-semibold text-gray-800">{{ $layanan->nama_layanan }}</td>
                            <td class="px-6 py-4 text-green-600 font-bold">Rp {{ number_format($layanan->tarif, 0, ',', '.') }}</td>
                            <td class="px-6 py-4"><span class="px-2 py-1 bg-blue-50 text-blue-700 rounded text-xs font-medium">{{ $layanan->satuan }}</span></td>
                            <td class="px-6 py-4 text-gray-500 text-sm max-w-xs truncate">{{ $layanan->deskripsi ?? '-' }}</td>
                            <td class="px-6 py-4">
                                <div class="flex gap-3 justify-center">
                                    <button onclick='editLayanan({{ json_encode($layanan) }})' class="text-blue-600 hover:text-blue-800 transition">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <button onclick="deleteLayanan({{ $layanan->id }})" class="text-red-600 hover:text-red-800 transition">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-gray-500 italic">Belum ada data layanan</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div id="mobileCards" class="lg:hidden space-y-3">
                @foreach($layanans as $layanan)
                <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-blue-500">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <h4 class="font-bold text-gray-800">{{ $layanan->nama_layanan }}</h4>
                            <span class="text-[10px] uppercase tracking-wider text-gray-500 font-semibold">{{ $layanan->satuan }}</span>
                        </div>
                        <div class="text-blue-600 font-bold">Rp {{ number_format($layanan->tarif, 0, ',', '.') }}</div>
                    </div>
                    <p class="text-xs text-gray-600 mb-4 line-clamp-2 italic">"{{ $layanan->deskripsi ?? 'Tidak ada deskripsi' }}"</p>
                    <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                        <button onclick='editLayanan({{ json_encode($layanan) }})' class="flex items-center gap-1 text-xs font-medium text-blue-600 px-3 py-1.5 bg-blue-50 rounded-lg">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            Edit
                        </button>
                        <button onclick="deleteLayanan({{ $layanan->id }})" class="flex items-center gap-1 text-xs font-medium text-red-600 px-3 py-1.5 bg-red-50 rounded-lg">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            Hapus
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
        </main>
    </div>

    <div id="layananModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl p-6 lg:p-8 w-full max-w-md">
            <div class="flex items-center justify-between mb-6">
                <h3 id="modalTitle" class="text-xl lg:text-2xl font-bold text-gray-800">Tambah Layanan</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <form id="layananForm" class="space-y-4">
                <input type="hidden" id="layananId">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Nama Layanan <span class="text-red-500">*</span></label>
                    <input type="text" id="nama_layanan" placeholder="Masukan nama layanan" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition" required>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Tarif (Rp) <span class="text-red-500">*</span></label>
                        <input type="number" id="tarif" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition" required min="0">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Satuan <span class="text-red-500">*</span></label>
                        <input type="text" id="satuan" placeholder="kg/pcs" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition" required>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Deskripsi</label>
                    <textarea id="deskripsi" rows="3" placeholder="Tambahkan deskripsi singkat..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-lg font-bold transition">Simpan</button>
                    <button type="button" onclick="closeModal()" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 py-3 rounded-lg font-bold transition">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <script src="{{ asset('js/layanan.js') }}"></script>
</body>
</html>