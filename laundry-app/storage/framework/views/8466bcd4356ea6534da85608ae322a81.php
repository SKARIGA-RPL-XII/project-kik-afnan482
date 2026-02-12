<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>Pesanan - Laundry System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
    <!-- Data untuk JavaScript -->
    <div id="app-data" 
         data-get-data-url="<?php echo e(route('admin.pesanan.getData')); ?>"
         data-store-url="<?php echo e(url('/admin/pesanan')); ?>"
         style="display: none;">
    </div>

    <!-- Mobile Menu Button -->
    <button onclick="toggleSidebar()" class="lg:hidden fixed top-4 left-4 z-50 bg-blue-600 text-white p-2 rounded-lg shadow-lg">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
        </svg>
    </button>

    <?php echo $__env->make('admin.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

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
                    <?php echo e(substr(auth()->user()->name ?? 'A', 0, 1)); ?>

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
                <!-- Field Alamat - TAMBAHAN BARU -->
                <div id="addressDiv">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Alamat Penjemputan</label>
                    <textarea id="address" name="address" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none" placeholder="Masukkan alamat lengkap..."></textarea>
                </div>

                <!-- Field Catatan - TAMBAHAN BARU (Letakkan setelah field weight) -->
                <div id="notesDiv">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Catatan Khusus <span class="text-gray-500 font-normal">(Opsional)</span></label>
                    <textarea id="notes" name="notes" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none" placeholder="Catatan untuk pesanan ini..."></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Layanan <span class="text-red-500">*</span></label>
                    <select id="layananId" name="layanan_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" onchange="calculateTotal()" required>
                        <option value="">-- Pilih Layanan --</option>
                        <?php $__currentLoopData = $layanans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $layanan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($layanan->id); ?>" data-price="<?php echo e($layanan->tarif); ?>">
                            <?php echo e($layanan->nama_layanan); ?> - Rp <?php echo e(number_format($layanan->tarif, 0, ',', '.')); ?>/<?php echo e($layanan->satuan); ?>

                        </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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

    <!-- Modal Input Berat Akhir -->
    <div id="weightModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center p-4">
        <div class="bg-white rounded-2xl p-6 lg:p-8 w-full max-w-md">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-gray-800">Input Berat Akhir</h3>
                <button onclick="closeWeightModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <form onsubmit="updateFinalWeight(event)">
                <div class="mb-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Berat Estimasi</label>
                    <input type="text" id="estimatedWeight" class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100" readonly>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Berat Akhir (Kg) <span class="text-red-500">*</span></label>
                    <input type="number" id="newFinalWeight" step="0.1" min="0.1" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    <p class="text-xs text-gray-500 mt-1">Total biaya akan dihitung ulang berdasarkan berat akhir</p>
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-lg font-medium">Simpan</button>
                    <button type="button" onclick="closeWeightModal()" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 py-3 rounded-lg font-medium">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Load JavaScript Terpisah -->
    <script src="<?php echo e(asset('js/pesanan.js')); ?>"></script>
</body>
</html><?php /**PATH C:\laragon\www\laundry-app\resources\views/admin/pesanan/index.blade.php ENDPATH**/ ?>