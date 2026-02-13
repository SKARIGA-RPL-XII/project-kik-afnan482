<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>Pemesanan Laundry - LaundryKu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php if(config('midtrans.is_production')): ?>
        <script src="https://app.midtrans.com/snap/snap.js" data-client-key="<?php echo e(config('midtrans.client_key')); ?>"></script>
    <?php else: ?>
        <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="<?php echo e(config('midtrans.client_key')); ?>"></script>
    <?php endif; ?>
    <style>
        #successModal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }
        
        #successModal.show {
            display: flex !important;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px) scale(0.9);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        
        #successModal.show .modal-content {
            animation: slideUp 0.4s ease-out;
        }
        
        body.modal-open {
            overflow: hidden;
        }

        /* Sticky summary on desktop */
        @media (min-width: 1024px) {
            .sticky-summary {
                position: sticky;
                top: 5rem;
            }
        }

        /* Mobile bottom summary */
        @media (max-width: 1023px) {
            .mobile-summary {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                z-index: 40;
                transform: translateY(0);
                transition: transform 0.3s ease;
            }
            
            .mobile-summary.hidden-mobile {
                transform: translateY(100%);
            }

            .mobile-summary-detail {
                max-height: 0;
                overflow: hidden;
                transition: max-height 0.3s ease;
            }

            .mobile-summary-detail.expanded {
                max-height: 500px;
            }

            body.modal-open {
                padding-bottom: 0;
            }

            body:not(.modal-open) {
                padding-bottom: 140px;
            }
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Navbar -->
    <nav class="bg-white shadow-sm sticky top-0 z-50 border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
               <!-- Logo -->
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                    <i class="fas fa-tshirt text-white"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-800">LaundryKu</h1>
                    <p class="text-xs text-gray-500 hidden sm:block">Layanan Laundry Terpercaya</p>
                </div>
            </div>

                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center space-x-1">
                    <a href="<?php echo e(route('user.dashboard')); ?>" class="px-4 py-2 rounded-lg text-gray-600 hover:bg-gray-100 font-medium transition">
                        <i class="fas fa-home mr-2"></i>Dashboard
                    </a>
                    <a href="<?php echo e(route('user.pemesanan.index')); ?>" class="px-4 py-2 rounded-lg bg-blue-600 text-white font-medium">
                        <i class="fas fa-plus-circle mr-2"></i>Pesan Laundry
                    </a>
                    <a href="<?php echo e(route('user.riwayat.index')); ?>" class="px-4 py-2 rounded-lg text-gray-600 hover:bg-gray-100 font-medium transition">
                        <i class="fas fa-history mr-2"></i>Riwayat
                    </a>
                </div>

                <!-- Right Section -->
                <div class="flex items-center space-x-3">
                    <button class="relative p-2 hover:bg-gray-100 rounded-lg transition">
                        <i class="fas fa-bell text-gray-600"></i>
                        <span id="notificationBadge" class="hidden absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                    </button>

                    <div class="relative">
                        <button id="userMenuBtn" class="flex items-center space-x-2 cursor-pointer hover:bg-gray-100 rounded-lg p-2 transition">
                            <div class="w-9 h-9 bg-blue-600 rounded-full flex items-center justify-center text-white font-semibold text-sm">
                                <?php echo e(substr(auth()->user()->name ?? 'U', 0, 1)); ?>

                            </div>
                            <span class="hidden sm:block text-sm font-medium text-gray-700"><?php echo e(auth()->user()->name ?? 'User'); ?></span>
                            <i class="fas fa-chevron-down text-gray-500 text-xs transition-transform" id="dropdownIcon"></i>
                        </button>

                        <div id="userDropdown" class="hidden absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border py-2 z-50">
                            <div class="px-4 py-3 border-b">
                                <p class="text-sm font-semibold text-gray-800"><?php echo e(auth()->user()->name ?? 'User'); ?></p>
                                <p class="text-xs text-gray-500 mt-1"><?php echo e(auth()->user()->email ?? 'user@email.com'); ?></p>
                            </div>
                            <div class="py-2">
                                <a href="<?php echo e(route('user.profile')); ?>" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition">
                                    <i class="fas fa-user-circle w-5 mr-3 text-gray-500"></i>
                                    Profil Saya
                                </a>
                                <a href="#pengaturan" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition">
                                    <i class="fas fa-cog w-5 mr-3 text-gray-500"></i>
                                    Pengaturan
                                </a>
                            </div>
                            <div class="border-t pt-2">
                                <form action="<?php echo e(route('logout')); ?>" method="POST">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition">
                                        <i class="fas fa-sign-out-alt w-5 mr-3"></i>
                                        Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <button id="mobileMenuBtn" class="md:hidden p-2 hover:bg-gray-100 rounded-lg transition">
                        <i class="fas fa-bars text-gray-700"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div id="mobileMenu" class="hidden md:hidden border-t bg-white">
            <div class="px-4 py-3 space-y-1">
                <a href="<?php echo e(route('user.dashboard')); ?>" class="block px-4 py-2 rounded-lg text-gray-700 hover:bg-gray-50 font-medium transition">
                    <i class="fas fa-home mr-2"></i>Dashboard
                </a>
                <a href="<?php echo e(route('user.pemesanan.index')); ?>" class="block px-4 py-2 rounded-lg bg-blue-50 text-blue-700 font-medium">
                    <i class="fas fa-plus-circle mr-2"></i>Pesan Laundry
                </a>
                <a href="<?php echo e(route('user.riwayat.index')); ?>" class="block px-4 py-2 rounded-lg text-gray-700 hover:bg-gray-50 font-medium transition">
                    <i class="fas fa-history mr-2"></i>Riwayat
                </a>
                <a href="<?php echo e(route('user.profile')); ?>" class="block px-4 py-2 rounded-lg text-gray-700 hover:bg-gray-50 font-medium transition">
                    <i class="fas fa-user-circle mr-2"></i>Profil Saya
                </a>
                <form action="<?php echo e(route('logout')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="w-full text-left px-4 py-2 rounded-lg text-red-600 hover:bg-red-50 font-medium transition">
                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <!-- Main -->
    <main class="max-w-7xl mx-auto px-4 py-6 sm:py-8">
        <!-- Header -->
        <div class="mb-6">
            <div class="bg-white border rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800 mb-1">Buat Pesanan Baru</h1>
                        <p class="text-gray-600 text-sm">Isi detail pesanan laundry Anda dengan lengkap</p>
                    </div>
                    <div class="hidden sm:block">
                        <div class="w-16 h-16 bg-blue-50 rounded-lg flex items-center justify-center">
                            <i class="fas fa-tshirt text-blue-600 text-3xl"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid lg:grid-cols-3 gap-6">
            <!-- Form Section -->
            <div class="lg:col-span-2 space-y-4">
                <form id="orderForm" data-store-url="<?php echo e(route('user.pemesanan.store')); ?>">
                    <!-- Pilih Layanan -->
                    <div class="bg-white rounded-xl border p-5">
                        <label class="flex items-center text-lg font-semibold text-gray-800 mb-4">
                            <i class="fas fa-concierge-bell text-blue-600 mr-2"></i>
                            Pilih Layanan
                        </label>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <?php $__empty_1 = true; $__currentLoopData = $layanans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $layanan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div class="service-option bg-white border-2 border-gray-200 rounded-lg p-4 cursor-pointer hover:border-blue-600 transition"
                                data-layanan-id="<?php echo e($layanan->id); ?>"
                                data-price="<?php echo e($layanan->tarif); ?>"
                                data-satuan="<?php echo e($layanan->satuan); ?>"
                                data-duration="2-3">
                                <div class="flex justify-between items-start mb-3">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center mr-3">
                                            <i class="fas fa-spray-can text-blue-600"></i>
                                        </div>
                                        <h3 class="font-semibold text-gray-800"><?php echo e($layanan->nama_layanan); ?></h3>
                                    </div>
                                    <div class="w-5 h-5 rounded-full border-2 border-gray-300 flex items-center justify-center service-check">
                                        <div class="w-3 h-3 rounded-full bg-blue-600 hidden check-mark"></div>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <p class="text-blue-600 font-bold text-xl">Rp <?php echo e(number_format($layanan->tarif, 0, ',', '.')); ?></p>
                                    <p class="text-xs text-gray-500">per <?php echo e($layanan->satuan); ?></p>
                                </div>
                                <?php if($layanan->deskripsi): ?>
                                <p class="text-sm text-gray-600"><?php echo e(Str::limit($layanan->deskripsi, 50)); ?></p>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="col-span-2 text-center py-8 text-gray-500">
                                <i class="fas fa-inbox text-4xl mb-3 text-gray-300"></i>
                                <p class="font-medium">Belum ada layanan tersedia</p>
                            </div>
                            <?php endif; ?>
                        </div>

                        <input type="hidden" name="layanan_id" id="layanan_id" required>
                    </div>

                    <!-- Layanan Express -->
                    <div class="bg-white rounded-xl border p-5">
                        <label class="flex items-start cursor-pointer">
                            <input type="checkbox" id="expressService" class="w-5 h-5 text-blue-600 rounded mt-1 cursor-pointer">
                            <div class="ml-3">
                                <div class="flex items-center mb-1">
                                    <i class="fas fa-bolt text-yellow-600 mr-2"></i>
                                    <span class="text-base font-semibold text-gray-800">Layanan Express</span>
                                    <span class="ml-2 px-2 py-0.5 bg-yellow-100 text-yellow-700 text-xs font-semibold rounded-full">24 JAM</span>
                                </div>
                                <p class="text-sm text-gray-600">Cucian selesai dalam 24 jam dengan biaya tambahan Rp 10.000/kg</p>
                            </div>
                        </label>
                    </div>

                    <!-- Berat & Detail -->
                    <div class="bg-white rounded-xl border p-5 space-y-5">
                        <!-- Berat Cucian -->
                        <div>
                            <label class="flex items-center text-base font-semibold text-gray-800 mb-3">
                                <i class="fas fa-weight text-blue-600 mr-2"></i>
                                Berat Cucian
                            </label>
                            <div class="flex items-center space-x-3">
                                <button type="button" id="decreaseWeight" class="w-12 h-12 bg-gray-100 hover:bg-gray-200 border rounded-lg font-semibold text-gray-700 transition">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <div class="flex-1 relative">
                                    <input type="number" id="weight" name="weight" value="1" min="1" max="50" 
                                        class="w-full text-center bg-blue-50 border-2 border-blue-300 rounded-lg py-3 text-2xl font-bold text-blue-600 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200" required>
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-base font-semibold text-blue-600">KG</span>
                                </div>
                                <button type="button" id="increaseWeight" class="w-12 h-12 bg-gray-100 hover:bg-gray-200 border rounded-lg font-semibold text-gray-700 transition">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                            <p class="text-xs text-gray-500 mt-2 text-center">
                                <i class="fas fa-info-circle mr-1"></i>
                                Minimal 1 kg, maksimal 50 kg per pesanan
                            </p>
                        </div>

                        <div class="border-t pt-5">
                            <!-- Alamat -->
                            <div class="mb-5">
                                <label class="flex items-center text-sm font-semibold text-gray-800 mb-2">
                                    <i class="fas fa-map-marker-alt text-blue-600 mr-2"></i>
                                    Alamat Penjemputan
                                </label>
                                <textarea id="address" name="address" rows="3" 
                                    class="w-full border-2 border-gray-200 rounded-lg p-3 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 resize-none" 
                                    placeholder="Masukkan alamat lengkap penjemputan..." required></textarea>
                            </div>

                            <!-- Catatan -->
                            <div>
                                <label class="flex items-center text-sm font-semibold text-gray-800 mb-2">
                                    <i class="fas fa-sticky-note text-blue-600 mr-2"></i>
                                    Catatan Khusus <span class="text-gray-500 font-normal ml-1">(Opsional)</span>
                                </label>
                                <textarea id="notes" name="notes" rows="3" 
                                    class="w-full border-2 border-gray-200 rounded-lg p-3 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 resize-none" 
                                    placeholder="Contoh: Pisahkan pakaian putih, jangan gunakan pewangi..."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Metode Pembayaran -->
                    <div class="bg-white rounded-xl border p-5">
                        <label class="flex items-center text-base font-semibold text-gray-800 mb-4">
                            <i class="fas fa-credit-card text-blue-600 mr-2"></i>
                            Metode Pembayaran
                        </label>
                        <div class="space-y-3">
                            <label class="flex items-center p-4 bg-blue-50 border-2 border-blue-300 rounded-lg cursor-pointer hover:bg-blue-100 transition">
                                <input type="radio" name="payment_method" value="midtrans" checked class="w-4 h-4 text-blue-600">
                                <div class="ml-3">
                                    <div class="flex items-center mb-1">
                                        <i class="fas fa-wallet text-blue-600 mr-2"></i>
                                        <span class="font-semibold text-gray-800">Pembayaran Online</span>
                                        <span class="ml-2 px-2 py-0.5 bg-blue-600 text-white text-xs font-semibold rounded-full">REKOMENDASI</span>
                                    </div>
                                    <p class="text-xs text-gray-600">Transfer Bank, E-Wallet, QRIS</p>
                                </div>
                            </label>
                            <label class="flex items-center p-4 bg-white border-2 border-gray-200 rounded-lg cursor-pointer hover:border-green-500 transition">
                                <input type="radio" name="payment_method" value="cash" class="w-4 h-4 text-green-600">
                                <div class="ml-3">
                                    <div class="flex items-center mb-1">
                                        <i class="fas fa-money-bill-wave text-green-600 mr-2"></i>
                                        <span class="font-semibold text-gray-800">Bayar Tunai</span>
                                    </div>
                                    <p class="text-xs text-gray-600">Bayar saat cucian diambil</p>
                                </div>
                            </label>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Summary Section - Desktop -->
            <div class="lg:col-span-1 hidden lg:block">
                <div class="sticky-summary bg-white rounded-xl border overflow-hidden">
                    <!-- Header -->
                    <div class="bg-blue-600 p-5 text-white">
                        <h3 class="font-semibold text-lg flex items-center">
                            <i class="fas fa-receipt mr-2"></i>
                            Ringkasan Pesanan
                        </h3>
                    </div>

                    <div class="p-5 space-y-4">
                        <!-- Service Info -->
                        <div class="space-y-3">
                            <div class="flex justify-between items-start pb-3 border-b">
                                <span class="text-sm text-gray-600">Layanan:</span>
                                <span id="summaryService" class="font-semibold text-gray-800 text-right">Pilih layanan</span>
                            </div>
                            
                            <div class="flex justify-between items-center pb-3 border-b">
                                <span class="text-sm text-gray-600">Berat:</span>
                                <span id="summaryWeight" class="font-semibold text-gray-800">1 kg</span>
                            </div>
                            
                            <div class="flex justify-between items-center pb-3 border-b">
                                <span class="text-sm text-gray-600">Harga/kg:</span>
                                <span id="summaryPricePerKg" class="font-semibold text-gray-800">-</span>
                            </div>
                        </div>

                        <!-- Express Info -->
                        <div id="expressInfo" class="hidden">
                            <div class="bg-yellow-50 border-2 border-yellow-200 rounded-lg p-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-semibold text-gray-700">
                                        <i class="fas fa-bolt mr-1 text-yellow-600"></i>
                                        Express:
                                    </span>
                                    <span id="summaryExpress" class="font-semibold text-yellow-700">-</span>
                                </div>
                            </div>
                        </div>

                        <!-- Calculation -->
                        <div class="space-y-2 bg-gray-50 rounded-lg p-3">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Subtotal:</span>
                                <span id="subtotal" class="font-semibold text-gray-800">Rp 0</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Antar-Jemput:</span>
                                <span class="font-semibold text-gray-800">Rp 5.000</span>
                            </div>
                        </div>

                        <!-- Total -->
                        <div class="bg-blue-600 rounded-lg p-4">
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-blue-100 text-xs">Total Pembayaran</p>
                                    <p class="font-semibold text-white text-sm">yang harus dibayar:</p>
                                </div>
                                <span id="total" class="font-bold text-2xl text-white">Rp 0</span>
                            </div>
                        </div>

                        <!-- Estimated Time -->
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                            <div class="flex items-start">
                                <i class="fas fa-clock text-blue-600 mr-2 mt-0.5"></i>
                                <div>
                                    <p class="text-sm font-semibold text-gray-800">Estimasi Selesai</p>
                                    <p id="estimatedTime" class="text-xs text-gray-600 mt-1">Pilih layanan terlebih dahulu</p>
                                </div>
                            </div>
                        </div>

                        <!-- Info Box -->
                        <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                            <div class="flex items-start">
                                <i class="fas fa-check-circle text-green-600 mr-2 mt-0.5"></i>
                                <div>
                                    <p class="text-sm font-semibold text-green-900">Gratis Penjemputan</p>
                                    <p class="text-xs text-green-700 mt-1">Driver akan menjemput di alamat Anda</p>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" form="orderForm" id="orderBtn" disabled 
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg transition disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-shopping-cart mr-2"></i>
                            <span id="btnText">Pilih Layanan</span>
                        </button>

                        <p class="text-xs text-center text-gray-500">
                            <i class="fas fa-shield-alt mr-1"></i>
                            Transaksi aman & terpercaya
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Floating Summary -->
        <div class="lg:hidden mobile-summary bg-white border-t-2 border-blue-600 shadow-lg">
            <!-- Expandable Summary -->
            <div class="mobile-summary-detail bg-gray-50">
                <div class="p-4 space-y-3">
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-gray-600">Layanan:</span>
                        <span id="summaryServiceMobile" class="font-semibold text-gray-800">Pilih layanan</span>
                    </div>
                    
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-gray-600">Berat:</span>
                        <span id="summaryWeightMobile" class="font-semibold text-gray-800">1 kg</span>
                    </div>
                    
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-gray-600">Harga/kg:</span>
                        <span id="summaryPricePerKgMobile" class="font-semibold text-gray-800">-</span>
                    </div>

                    <div id="expressInfoMobile" class="hidden bg-yellow-50 border border-yellow-200 rounded-lg p-2">
                        <div class="flex justify-between items-center text-sm">
                            <span class="font-semibold text-gray-700">Express:</span>
                            <span id="summaryExpressMobile" class="font-semibold text-yellow-700">-</span>
                        </div>
                    </div>

                    <div class="border-t pt-3 space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Subtotal:</span>
                            <span id="subtotalMobile" class="font-semibold text-gray-800">Rp 0</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Antar-Jemput:</span>
                            <span class="font-semibold text-gray-800">Rp 5.000</span>
                        </div>
                    </div>

                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-2">
                        <div class="flex items-start text-sm">
                            <i class="fas fa-clock text-blue-600 mr-2 mt-0.5"></i>
                            <div>
                                <p class="font-semibold text-gray-800">Estimasi Selesai</p>
                                <p id="estimatedTimeMobile" class="text-xs text-gray-600">Pilih layanan terlebih dahulu</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Bar -->
            <div class="bg-white p-4">
                <div class="flex items-center justify-between mb-3">
                    <button type="button" id="toggleDetailMobile" class="text-sm text-blue-600 font-semibold flex items-center">
                        <i class="fas fa-chevron-up mr-1" id="chevronIcon"></i>
                        <span id="toggleText">Lihat Detail</span>
                    </button>
                    <div class="text-right">
                        <p class="text-xs text-gray-500">Total</p>
                        <p id="totalMobile" class="text-xl font-bold text-blue-600">Rp 0</p>
                    </div>
                </div>
                <button type="submit" form="orderForm" id="orderBtnMobile" disabled 
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg transition disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-shopping-cart mr-2"></i>
                    <span id="btnTextMobile">Pilih Layanan</span>
                </button>
            </div>
        </div>
    </main>

    <!-- Success Modal -->
    <div id="successModal" class="bg-black/50">
        <div class="modal-content bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">
            <div class="bg-green-600 p-6 text-white text-center">
                <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-check text-green-600 text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold">Pesanan Berhasil!</h3>
                <p class="text-green-100 text-sm mt-1">Terima kasih telah memesan</p>
            </div>
            
            <div class="p-6">
                <div class="text-center mb-5">
                    <p class="text-gray-600 text-sm mb-2">Nomor Pesanan:</p>
                    <p id="invoiceNumber" class="text-2xl font-bold text-blue-600 bg-blue-50 py-2 rounded-lg">#LND-2026-004</p>
                </div>
                
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-5">
                    <p class="text-sm text-gray-700">Driver akan segera menghubungi Anda untuk konfirmasi penjemputan</p>
                </div>
                
                <button id="closeModal" data-dashboard-url="<?php echo e(route('user.dashboard')); ?>" 
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg transition">
                    <i class="fas fa-list-alt mr-2"></i>
                    Lihat Detail Pesanan
                </button>
            </div>
        </div>
    </div>

    <script src="<?php echo e(asset('js/pemesanan.js')); ?>"></script>
    
</body>
</html><?php /**PATH C:\laragon\www\project-kik-afnan482\laundry-app\resources\views/user/pemesanan/index.blade.php ENDPATH**/ ?>