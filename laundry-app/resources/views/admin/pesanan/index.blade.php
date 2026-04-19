<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pesanan - Laundry System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <!-- Leaflet Geocoder CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
    
    <style>
        .swal2-container.swal2-top-end {
            top: 1rem !important;
            right: 1rem !important;
        }
        .swal2-toast {
            padding: 1rem !important;
        }
        
        /* Fix map dalam modal */
        #adminMap {
            height: 250px;
            width: 100%;
            border-radius: 0.5rem;
            border: 2px solid #E5E7EB;
            z-index: 1;
        }
        
        .leaflet-control-geocoder {
            border-radius: 0.5rem !important;
            border: 2px solid #3B82F6 !important;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 to-gray-100 min-h-screen flex">
    <!-- Data untuk JavaScript -->
    <div id="app-data" 
         data-get-data-url="{{ route('admin.pesanan.getData') }}"
         data-store-url="{{ url('/admin/pesanan') }}"
         style="display: none;">
    </div>

    <!-- Mobile Menu Button -->
    <button onclick="toggleSidebar()" class="lg:hidden fixed top-4 left-4 z-50 bg-blue-600 text-white p-2 rounded-lg shadow-lg">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
        </svg>
    </button>

    @include('admin.sidebar')

    <!-- Overlay -->
    <div id="overlay" onclick="toggleSidebar()" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden lg:hidden"></div>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col min-h-screen">
        <header class="bg-white shadow-sm">
            <div class="px-4 lg:px-8 py-4 flex items-center justify-between">
                <div class="ml-12 lg:ml-0">
                    <h2 class="text-xl lg:text-2xl font-bold text-gray-800">Data Pesanan</h2>
                    <p class="text-xs lg:text-sm text-gray-600">Kelola semua pesanan laundry</p>
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
                        <input type="text" id="searchInput" placeholder="Cari pesanan..." class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm lg:w-64">
                        <select id="filterStatus" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                            <option value="">Semua Status</option>
                            <option value="pending">Pending</option>
                            <option value="proses">Proses</option>
                            <option value="selesai">Selesai</option>
                            <option value="diambil">Diambil</option>
                        </select>
                    </div>
                    <button onclick="openModal('create')" class="bg-blue-600 hover:bg-blue-700 text-white px-4 lg:px-6 py-2 rounded-lg flex items-center justify-center space-x-2 text-sm transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        <span>Tambah Pesanan</span>
                    </button>
                </div>
            </div>

            <!-- Desktop Table -->
            <div class="hidden lg:block bg-white rounded-xl shadow-sm overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">No Invoice</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Pelanggan</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Layanan</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Berat</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Total</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody" class="divide-y divide-gray-200">
                        <tr><td colspan="7" class="px-6 py-8 text-center text-gray-500">Memuat data...</td></tr>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Cards -->
            <div id="mobileCards" class="lg:hidden space-y-3"></div>
        </main>
    </div>

    <!-- Modal Form Pesanan -->
    <div id="modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center p-4">
        <div class="bg-white rounded-2xl p-6 lg:p-8 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-6">
                <h3 id="modalTitle" class="text-xl lg:text-2xl font-bold text-gray-800">Tambah Pesanan</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <form id="orderForm" onsubmit="saveOrder(event)" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama Pelanggan <span class="text-red-500">*</span></label>
                    <input type="text" id="customerName" name="customer_name" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">No. Telepon <span class="text-red-500">*</span></label>
                    <input type="text" id="customerPhone" name="customer_phone" oninput="this.value=this.value.replace(/[^0-9]/g,'')" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                
                <!-- Field Alamat dengan OpenStreetMap -->
                <div id="addressDiv">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Alamat Penjemputan</label>
                    
                    <!-- OpenStreetMap -->
                    <div class="relative mb-3">
                        <div id="adminMap"></div>
                        <button type="button" id="adminUseMyLocation" class="absolute top-2 right-2 bg-white border-2 border-gray-300 rounded-lg p-2 cursor-pointer shadow hover:bg-gray-50 z-[1000]" title="Gunakan lokasi saya">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Koordinat (hidden) -->
                    <input type="hidden" id="latitude" name="latitude">
                    <input type="hidden" id="longitude" name="longitude">

                    <!-- Alamat Lengkap (readonly) -->
                    <textarea id="address" name="address" rows="2" readonly class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none text-sm" placeholder="Klik pada peta untuk menentukan lokasi..."></textarea>
                    
                    <p class="text-xs text-gray-500 mt-1">
                        <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Klik pada peta atau geser marker untuk menentukan lokasi penjemputan
                    </p>
                </div>

                <!-- Field Catatan -->
                <div id="notesDiv">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Catatan Khusus <span class="text-gray-500 font-normal">(Opsional)</span></label>
                    <textarea id="notes" name="notes" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none" placeholder="Catatan untuk pesanan ini..."></textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Layanan <span class="text-red-500">*</span></label>
                    <select id="layananId" name="layanan_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" onchange="calculateTotal()" required>
                        <option value="">-- Pilih Layanan --</option>
                        @foreach($layanans as $layanan)
                        <option value="{{ $layanan->id }}" data-price="{{ $layanan->tarif }}">
                            {{ $layanan->nama_layanan }} - Rp {{ number_format($layanan->tarif, 0, ',', '.') }}/{{ $layanan->satuan }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div id="expressDiv">
                    <label class="flex items-center cursor-pointer bg-yellow-50 border border-yellow-200 p-3 rounded-lg">
                        <input type="checkbox" id="isExpress" name="is_express" value="1" class="w-5 h-5 text-yellow-600 rounded" onchange="calculateTotal()">
                        <div class="ml-3">
                            <span class="text-sm font-bold text-gray-800">Layanan Express</span>
                            <p class="text-xs text-gray-600">Selesai dalam 24 jam (+Rp 10.000/kg)</p>
                        </div>
                    </label>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Berat (Kg) <span class="text-red-500">*</span></label>
                    <input type="number" id="weight" name="weight" step="0.1" min="0.1" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" oninput="calculateTotal()" required>
                    <p id="weightNote" class="text-xs text-gray-500 mt-1">Masukkan berat cucian</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Total Harga</label>
                    <input type="text" id="totalPrice" class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100" readonly>
                    <p class="text-xs text-gray-500 mt-1">Sudah termasuk biaya antar Rp 5.000</p>
                </div>
                <!-- Field Status hanya muncul saat create -->
                <div id="statusDiv">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select id="status" name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <option value="pending">Pending</option>
                        <option value="proses">Proses</option>
                        <option value="selesai">Selesai</option>
                        <option value="diambil">Diambil</option>
                    </select>
                </div>
                <div class="flex gap-3 pt-4">
                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-lg font-medium">Simpan</button>
                    <button type="button" onclick="closeModal()" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 py-3 rounded-lg font-medium">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Update Status -->
    <div id="statusModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center p-4">
        <div class="bg-white rounded-2xl p-6 lg:p-8 w-full max-w-md">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-gray-800">Update Status</h3>
                <button onclick="closeStatusModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <form onsubmit="updateStatus(event)">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status Baru</label>
                    <select id="newStatus" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <option value="pending">Pending</option>
                        <option value="proses">Proses</option>
                        <option value="selesai">Selesai</option>
                        <option value="diambil">Diambil</option>
                    </select>
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-lg font-medium">Update</button>
                    <button type="button" onclick="closeStatusModal()" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 py-3 rounded-lg font-medium">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <!-- Leaflet Geocoder JS -->
    <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>

    <!-- Load JavaScript Terpisah -->
    <script src="{{ asset('js/pesanan.js') }}"></script>
</body>
</html>