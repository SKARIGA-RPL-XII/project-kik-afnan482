<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>Data Pelanggan - Laundry System</title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .swal2-container.swal2-top-end {
            top: 1rem !important;
            right: 1rem !important;
        }
        .swal2-toast {
            padding: 1rem !important;
        }
        /* Hide scrollbar for Chrome, Safari and Opera */
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        /* Hide scrollbar for IE, Edge and Firefox */
        .no-scrollbar {
            -ms-overflow-style: none;  /* IE and Edge */
            scrollbar-width: none;  /* Firefox */
        }
        /* Modal display toggle */
        .modal:not(.hidden) {
            display: flex;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 to-gray-100 min-h-screen">
    
    <div class="flex">
        <?php echo $__env->make('admin.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <div class="flex-1 flex flex-col min-h-screen">
            <header class="bg-white shadow-sm sticky top-0 z-20">
                <div class="px-4 sm:px-8 py-4 flex items-center justify-between">
                    <div class="ml-14 lg:ml-0">
                        <h2 class="text-xl sm:text-2xl font-bold text-gray-800">Data Pelanggan</h2>
                        <p class="text-xs sm:text-sm text-gray-600">Kelola data pelanggan laundry</p>
                    </div>
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center text-white font-bold">
                        <?php echo e(substr(Auth::user()->name ?? 'A', 0, 1)); ?>

                    </div>
                </div>
            </header>

            <main class="flex-1 p-4 sm:p-8">
                <!-- Action Bar -->
                <div class="bg-white rounded-xl shadow-sm p-4 lg:p-6 mb-4 lg:mb-6">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                        <h3 class="text-lg font-bold text-gray-800">Daftar Pelanggan</h3>
                        <button onclick="openModal('addModal')" class="bg-blue-600 hover:bg-blue-700 text-white px-4 lg:px-6 py-2 rounded-lg flex items-center justify-center space-x-2 text-sm transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
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
                            <?php $__empty_1 = true; $__currentLoopData = $pelanggan; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm text-gray-700"><?php echo e($pelanggan->firstItem() + $i); ?></td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold mr-3"><?php echo e(substr($item->name, 0, 1)); ?></div>
                                        <span class="font-medium text-gray-900"><?php echo e($item->name); ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700"><?php echo e($item->email); ?></td>
                                <td class="px-6 py-4 text-sm text-gray-700"><?php echo e($item->phone ?? '-'); ?></td>
                                <td class="px-6 py-4 text-sm text-gray-700"><?php echo e($item->alamat ?? '-'); ?></td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                                        <?php echo e($item->pesanans_count ?? 0); ?> Pesanan
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex gap-2">
                                        <button onclick='viewRiwayat(<?php echo e($item->id); ?>)' class="text-purple-600 hover:text-purple-800" title="Riwayat Pesanan">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        </button>
                                        <button onclick='viewDetail(<?php echo json_encode($item, 15, 512) ?>)' class="text-green-600 hover:text-green-800" title="Detail">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        </button>
                                        <button onclick='editPelanggan(<?php echo json_encode($item, 15, 512) ?>)' class="text-blue-600 hover:text-blue-800" title="Edit">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </button>
                                        <button onclick="deletePelanggan(<?php echo e($item->id); ?>, '<?php echo e(addslashes($item->name)); ?>')" class="text-red-600 hover:text-red-800" title="Hapus">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="7" class="px-6 py-8 text-center text-gray-500">Belum ada data pelanggan</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Cards -->
                <div class="lg:hidden space-y-3">
                    <?php $__empty_1 = true; $__currentLoopData = $pelanggan; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="bg-white rounded-xl shadow-sm p-4">
                        <div class="flex items-center mb-3">
                            <div class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold mr-3"><?php echo e(substr($item->name, 0, 1)); ?></div>
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-gray-900 truncate"><?php echo e($item->name); ?></p>
                                <p class="text-xs text-gray-500 truncate"><?php echo e($item->email); ?></p>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 mt-1">
                                    <?php echo e($item->pesanans_count ?? 0); ?> Pesanan
                                </span>
                            </div>
                        </div>
                        <div class="space-y-1 mb-3 text-sm">
                            <p><span class="font-medium text-gray-700">Telepon:</span> <span class="text-gray-600"><?php echo e($item->phone ?? '-'); ?></span></p>
                            <p><span class="font-medium text-gray-700">Alamat:</span> <span class="text-gray-600"><?php echo e(Str::limit($item->alamat ?? '-', 30)); ?></span></p>
                        </div>
                        <div class="flex gap-2">
                            <button onclick='viewRiwayat(<?php echo e($item->id); ?>)' class="flex-1 bg-purple-100 text-purple-700 px-3 py-2 rounded-lg text-xs font-medium hover:bg-purple-200">Riwayat</button>
                            <button onclick='viewDetail(<?php echo json_encode($item, 15, 512) ?>)' class="flex-1 bg-green-100 text-green-700 px-3 py-2 rounded-lg text-xs font-medium hover:bg-green-200">Detail</button>
                            <button onclick='editPelanggan(<?php echo json_encode($item, 15, 512) ?>)' class="flex-1 bg-blue-100 text-blue-700 px-3 py-2 rounded-lg text-xs font-medium hover:bg-blue-200">Edit</button>
                            <button onclick="deletePelanggan(<?php echo e($item->id); ?>, '<?php echo e(addslashes($item->name)); ?>')" class="flex-1 bg-red-100 text-red-700 px-3 py-2 rounded-lg text-xs font-medium hover:bg-red-200">Hapus</button>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="bg-white rounded-xl shadow-sm px-6 py-12 text-center text-gray-500">Belum ada data pelanggan</div>
                    <?php endif; ?>
                </div>

                <?php if($pelanggan->hasPages()): ?>
                <div class="mt-4">
                    <div class="bg-white rounded-xl shadow-sm px-6 py-4">
                        <?php echo e($pelanggan->links()); ?>

                    </div>
                </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <!-- Modal Add -->
    <div id="addModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center p-4 modal" data-modal-type="add">
        <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto no-scrollbar">
            <div class="sticky top-0 bg-blue-600 text-white px-6 py-4 flex justify-between items-center rounded-t-xl">
                <h3 class="text-xl font-bold">Tambah Pelanggan</h3>
                <button onclick="closeModal('addModal')" class="text-white text-2xl">&times;</button>
            </div>
            <form id="addForm" action="<?php echo e(route('admin.pelanggan.store')); ?>" method="POST" class="p-6 space-y-4">
                <?php echo csrf_field(); ?>
                <div><label class="block text-sm font-medium mb-2">Nama *</label><input type="text" name="name" id="add_name" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
                <div><label class="block text-sm font-medium mb-2">Email *</label><input type="email" name="email" id="add_email" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
                <div><label class="block text-sm font-medium mb-2">Telepon</label><input type="text" name="phone" id="add_phone" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
                <div><label class="block text-sm font-medium mb-2">Alamat</label><textarea name="alamat" id="add_alamat" rows="2" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea></div>
                <div><label class="block text-sm font-medium mb-2">Password *</label><input type="password" name="password" id="add_password" required minlength="8" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"><p class="text-xs text-gray-500 mt-1">Min 8 karakter</p></div>
                <div><label class="block text-sm font-medium mb-2">Konfirmasi Password *</label><input type="password" name="password_confirmation" id="add_password_confirmation" required minlength="8" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
                <div class="flex gap-3 pt-4">
                    <button type="submit" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 font-medium">Simpan</button>
                    <button type="button" onclick="closeModal('addModal')" class="flex-1 bg-gray-200 px-4 py-2 rounded-lg hover:bg-gray-300 font-medium">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal View -->
    <div id="viewModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center p-4 modal" data-modal-type="view">
        <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full">
            <div class="bg-green-600 text-white px-6 py-4 flex justify-between items-center rounded-t-xl">
                <h3 class="text-xl font-bold">Detail Pelanggan</h3>
                <button onclick="closeModal('viewModal')" class="text-white text-2xl">&times;</button>
            </div>
            <div class="p-6" id="viewContent"></div>
        </div>
    </div>

    <!-- Modal Edit -->
    <div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center p-4 modal" data-modal-type="edit">
        <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto no-scrollbar">
            <div class="sticky top-0 bg-blue-600 text-white px-6 py-4 flex justify-between items-center rounded-t-xl">
                <h3 class="text-xl font-bold">Edit Pelanggan</h3>
                <button onclick="closeModal('editModal')" class="text-white text-2xl">&times;</button>
            </div>
            <form id="editForm" method="POST" class="p-6 space-y-4">
                <?php echo csrf_field(); ?> 
                <?php echo method_field('PUT'); ?>
                <div><label class="block text-sm font-medium mb-2">Nama *</label><input type="text" name="name" id="edit_name" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
                <div><label class="block text-sm font-medium mb-2">Email *</label><input type="email" name="email" id="edit_email" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
                <div><label class="block text-sm font-medium mb-2">Telepon</label><input type="text" name="phone" id="edit_phone" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
                <div><label class="block text-sm font-medium mb-2">Alamat</label><textarea name="alamat" id="edit_alamat" rows="2" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea></div>
                <div class="border-t pt-4">
                    <p class="text-sm text-gray-600 mb-3">Kosongkan jika tidak ingin ubah password</p>
                    <div class="space-y-4">
                        <div><label class="block text-sm font-medium mb-2">Password Baru</label><input type="password" name="password" id="edit_password" minlength="8" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"><p class="text-xs text-gray-500 mt-1">Min 8 karakter</p></div>
                        <div><label class="block text-sm font-medium mb-2">Konfirmasi Password</label><input type="password" name="password_confirmation" id="edit_password_confirmation" minlength="8" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
                    </div>
                </div>
                <div class="flex gap-3 pt-4">
                    <button type="submit" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 font-medium">Update</button>
                    <button type="button" onclick="closeModal('editModal')" class="flex-1 bg-gray-200 px-4 py-2 rounded-lg hover:bg-gray-300 font-medium">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Delete -->
    <div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center p-4 modal" data-modal-type="delete">
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full">
            <div class="p-6">
                <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
                <h3 class="mt-4 text-lg font-bold text-center">Konfirmasi Hapus</h3>
                <p class="mt-2 text-sm text-gray-600 text-center">Yakin hapus <strong id="deleteName"></strong>?</p>
                <form id="deleteForm" method="POST" class="mt-6 flex gap-3">
                    <?php echo csrf_field(); ?> 
                    <?php echo method_field('DELETE'); ?>
                    <button type="button" onclick="closeModal('deleteModal')" class="flex-1 bg-gray-200 px-4 py-2 rounded-lg hover:bg-gray-300 font-medium">Batal</button>
                    <button type="submit" class="flex-1 bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 font-medium">Ya, Hapus</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Riwayat Pesanan -->
    <div id="riwayatModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center p-4 modal" data-modal-type="riwayat">
        <div class="bg-white rounded-xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto no-scrollbar">
            <div class="sticky top-0 bg-purple-600 text-white px-6 py-4 flex justify-between items-center rounded-t-xl">
                <div>
                    <h3 class="text-xl font-bold">Riwayat Pesanan</h3>
                    <p class="text-sm text-purple-100 mt-1" id="riwayatPelangganInfo">-</p>
                </div>
                <button onclick="closeModal('riwayatModal')" class="text-white text-2xl">&times;</button>
            </div>
            <div class="p-6" id="riwayatContent">
                <div class="text-center py-8">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-purple-600"></div>
                    <p class="text-gray-500 mt-2">Memuat data...</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        const $ = id => document.getElementById(id);
        
        function openModal(id) { 
            $(id).classList.remove('hidden'); 
            document.body.style.overflow = 'hidden'; 
        }
        
        function closeModal(id) { 
            $(id).classList.add('hidden'); 
            document.body.style.overflow = 'auto'; 
            if(id === 'addModal') {
                $('addForm').reset();
            } else if(id === 'editModal') {
                $('editForm').reset();
            }
        }

        function viewDetail(data) {
            const totalPesanan = data.pesanans_count ?? 0;
            $('viewContent').innerHTML = `
                <div class="space-y-4">
                    <div class="flex items-center pb-4 border-b">
                        <div class="w-16 h-16 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold text-2xl mr-4">
                            ${data.name.charAt(0).toUpperCase()}
                        </div>
                        <div>
                            <h4 class="font-bold text-lg">${data.name}</h4>
                            <p class="text-sm text-gray-500">${totalPesanan} Total Pesanan</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs text-gray-500">Email</p>
                            <p class="font-semibold text-sm break-all">${data.email}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Telepon</p>
                            <p class="font-semibold text-sm">${data.phone || '-'}</p>
                        </div>
                        <div class="col-span-2">
                            <p class="text-xs text-gray-500">Alamat</p>
                            <p class="font-semibold text-sm">${data.alamat || '-'}</p>
                        </div>
                    </div>
                </div>
            `;
            openModal('viewModal');
        }

        function editPelanggan(data) {
            $('editForm').action = `/admin/pelanggan/${data.id}`;
            $('edit_name').value = data.name;
            $('edit_email').value = data.email;
            $('edit_phone').value = data.phone || '';
            $('edit_alamat').value = data.alamat || '';
            $('edit_password').value = '';
            $('edit_password_confirmation').value = '';
            openModal('editModal');
        }

        function deletePelanggan(id, name) {
            $('deleteForm').action = `/admin/pelanggan/${id}`;
            $('deleteName').textContent = name;
            openModal('deleteModal');
        }

        // Fungsi untuk melihat riwayat pesanan
        async function viewRiwayat(userId) {
            openModal('riwayatModal');
            $('riwayatContent').innerHTML = `
                <div class="text-center py-8">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-purple-600"></div>
                    <p class="text-gray-500 mt-2">Memuat data...</p>
                </div>
            `;

            try {
                const response = await fetch(`/admin/pelanggan/${userId}/riwayat`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                
                if (!response.ok) {
                    throw new Error('Gagal memuat data');
                }
                
                const data = await response.json();

                if (!data.success) {
                    throw new Error(data.message || 'Gagal memuat data');
                }

                const statusColors = {
                    'pending': 'bg-yellow-100 text-yellow-800',
                    'proses': 'bg-blue-100 text-blue-800',
                    'selesai': 'bg-green-100 text-green-800',
                    'diambil': 'bg-gray-100 text-gray-800'
                };

                const serviceNames = {
                    'cuci_kering': 'Cuci Kering',
                    'cuci_setrika': 'Cuci & Setrika',
                    'setrika_saja': 'Setrika Saja'
                };

                $('riwayatPelangganInfo').textContent = `${data.pelanggan.name} - ${data.pelanggan.email}`;

                if (data.pesanan.length === 0) {
                    $('riwayatContent').innerHTML = `
                        <div class="text-center py-12">
                            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                            </svg>
                            <p class="text-gray-500">Belum ada riwayat pesanan</p>
                        </div>
                    `;
                    return;
                }

                let html = '<div class="space-y-3">';
                data.pesanan.forEach(p => {
                    const serviceName = serviceNames[p.service_type] || p.service_type;
                    const expressBadge = p.is_express ? '<span class="ml-2 px-2 py-0.5 bg-yellow-100 text-yellow-800 rounded-full text-xs font-semibold">Express</span>' : '';
                    
                    html += `
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    <div class="font-bold text-blue-600">${p.invoice}</div>
                                    <div class="text-xs text-gray-500 mt-1">${p.created_at}</div>
                                </div>
                                <span class="px-3 py-1 rounded-full text-xs font-semibold ${statusColors[p.status] || 'bg-gray-100 text-gray-800'}">${p.status.charAt(0).toUpperCase() + p.status.slice(1)}</span>
                            </div>
                            <div class="grid grid-cols-2 gap-3 text-sm mb-3">
                                <div>
                                    <span class="text-gray-500">Layanan:</span>
                                    <div class="font-medium">${serviceName}${expressBadge}</div>
                                </div>
                                <div>
                                    <span class="text-gray-500">Berat:</span>
                                    <div class="font-medium">
                                        ${parseFloat(p.weight).toFixed(1)} kg (estimasi)
                                        ${p.final_weight ? `<div class="text-green-600 text-xs">${parseFloat(p.final_weight).toFixed(1)} kg (akhir)</div>` : ''}
                                    </div>
                                </div>
                            </div>
                            <div class="flex justify-between items-center pt-3 border-t border-gray-100">
                                <span class="text-gray-500 text-sm">Total:</span>
                                <span class="font-bold text-blue-600">Rp ${parseFloat(p.total).toLocaleString('id-ID')}</span>
                            </div>
                        </div>
                    `;
                });
                html += '</div>';

                $('riwayatContent').innerHTML = html;

            } catch (error) {
                console.error('Error:', error);
                $('riwayatContent').innerHTML = `
                    <div class="text-center py-12">
                        <svg class="w-16 h-16 mx-auto text-red-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-red-500">${error.message}</p>
                    </div>
                `;
            }
        }

        // Form validation
        $('addForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const p = $('add_password').value;
            const c = $('add_password_confirmation').value;
            
            if(p.length < 8) {
                Swal.fire({icon:'error', title:'Error', text:'Password minimal 8 karakter'});
                return false;
            }
            if(p !== c) {
                Swal.fire({icon:'error', title:'Error', text:'Password tidak cocok'});
                return false;
            }
            this.submit();
        });

        $('editForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const p = $('edit_password').value;
            const c = $('edit_password_confirmation').value;
            
            if(p && p.length < 8) {
                Swal.fire({icon:'error', title:'Error', text:'Password minimal 8 karakter'});
                return false;
            }
            if(p && p !== c) {
                Swal.fire({icon:'error', title:'Error', text:'Password tidak cocok'});
                return false;
            }
            this.submit();
        });

        // Show success/error messages
        <?php if(session('success')): ?>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '<?php echo e(session('success')); ?>',
            timer: 3000,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timerProgressBar: true
        });
        <?php endif; ?>
        
        <?php if(session('error')): ?>
        Swal.fire({
            icon:'error',
            title:'Gagal!',
            text:'<?php echo e(session('error')); ?>'
        });
        <?php endif; ?>
    </script>
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html><?php /**PATH C:\laragon\www\project-kik-afnan482\laundry-app\resources\views/admin/pelanggan/index.blade.php ENDPATH**/ ?>