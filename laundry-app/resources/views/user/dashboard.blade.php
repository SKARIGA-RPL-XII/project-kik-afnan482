<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard Pelanggan - LaundryKu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
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

        .modal-content {
            animation: slideUp 0.4s ease-out;
        }
        
        .order-card {
            cursor: pointer;
        }
        
        .order-card:hover {
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
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
                    <a href="{{ route('user.dashboard') }}" class="px-4 py-2 rounded-lg bg-blue-600 text-white font-medium">
                        <i class="fas fa-home mr-2"></i>Dashboard
                    </a>
                    <a href="{{ route('user.pemesanan.index') }}" class="px-4 py-2 rounded-lg text-gray-600 hover:bg-gray-100 font-medium transition">
                        <i class="fas fa-plus-circle mr-2"></i>Pesan Laundry
                    </a>
                    <a href="{{ route('user.riwayat.index') }}" class="px-4 py-2 rounded-lg text-gray-600 hover:bg-gray-100 font-medium transition">
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
                            @php
                                $profileImageUrl = null;
                                if(auth()->user()->profile_image) {
                                    $imagePath = public_path(auth()->user()->profile_image);
                                    if(file_exists($imagePath)) {
                                        $profileImageUrl = asset(auth()->user()->profile_image) . '?v=' . time();
                                    }
                                }
                            @endphp

                            @if($profileImageUrl)
                                <img src="{{ $profileImageUrl }}" 
                                     alt="{{ auth()->user()->name }}"
                                     class="w-9 h-9 rounded-full object-cover border-2 border-blue-200">
                            @else
                                <div class="w-9 h-9 bg-blue-600 rounded-full flex items-center justify-center text-white font-semibold text-sm">
                                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                                </div>
                            @endif
                            
                            <span class="hidden sm:block text-sm font-medium text-gray-700">{{ auth()->user()->name ?? 'User' }}</span>
                            <i class="fas fa-chevron-down text-gray-500 text-xs transition-transform" id="dropdownIcon"></i>
                        </button>

                        <div id="userDropdown" class="hidden absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border py-2 z-50">
                            <div class="px-4 py-3 border-b border-gray-100">
                                <div class="flex items-center space-x-3 mb-2">
                                    @if($profileImageUrl)
                                        <img src="{{ $profileImageUrl }}" 
                                             alt="{{ auth()->user()->name }}"
                                             class="w-12 h-12 rounded-full object-cover border-2 border-blue-200">
                                    @else
                                        <div class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center text-white font-semibold text-lg">
                                            {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                                        </div>
                                    @endif
                                    <div class="flex-1">
                                        <p class="text-sm font-semibold text-gray-800">{{ auth()->user()->name ?? 'User' }}</p>
                                        <p class="text-xs text-gray-500 mt-0.5">{{ auth()->user()->email ?? 'user@email.com' }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="py-2">
                                <a href="{{ route('user.profile') }}" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 transition">
                                    <i class="fas fa-user-circle w-5 mr-3 text-gray-500"></i>
                                    Profil Saya
                                </a>
                               
                            </div>
                            <div class="border-t border-gray-100 pt-2">
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="flex items-center w-full px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition">
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
                <a href="{{ route('user.dashboard') }}" class="block px-4 py-2 rounded-lg bg-blue-50 text-blue-700 font-medium">
                    <i class="fas fa-home mr-2"></i>Dashboard
                </a>
                <a href="{{ route('user.pemesanan.index') }}" class="block px-4 py-2 rounded-lg text-gray-700 hover:bg-gray-50 font-medium transition">
                    <i class="fas fa-plus-circle mr-2"></i>Pesan Laundry
                </a>
                <a href="#riwayat" class="block px-4 py-2 rounded-lg text-gray-700 hover:bg-gray-50 font-medium transition">
                    <i class="fas fa-history mr-2"></i>Riwayat
                </a>
                <a href="{{ route('user.profile') }}" class="block px-4 py-2 rounded-lg text-gray-700 hover:bg-gray-50 font-medium transition">
                    <i class="fas fa-user-circle mr-2"></i>Profil Saya
                </a>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full text-left px-4 py-2 rounded-lg text-red-600 hover:bg-red-50 font-medium transition">
                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
        <!-- Welcome Section -->
        <div class="bg-white border rounded-xl p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-1">Selamat Datang, {{ auth()->user()->name }}! 👋</h2>
                    <p class="text-sm text-gray-600">Kelola pesanan laundry Anda dengan mudah dan praktis</p>
                </div>
                <div class="hidden sm:block">
                    @php
                        $profileImageUrl = null;
                        if(auth()->user()->profile_image) {
                            $imagePath = public_path(auth()->user()->profile_image);
                            if(file_exists($imagePath)) {
                                $profileImageUrl = asset(auth()->user()->profile_image) . '?v=' . time();
                            }
                        }
                    @endphp

                    @if($profileImageUrl)
                        <img src="{{ $profileImageUrl }}" 
                             alt="{{ auth()->user()->name }}"
                             class="w-16 h-16 rounded-lg object-cover border-2 border-blue-200">
                    @else
                        <div class="w-16 h-16 bg-blue-50 rounded-lg flex items-center justify-center">
                            <i class="fas fa-user-circle text-blue-600 text-4xl"></i>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl border p-5 hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Total Pesanan</p>
                        <p class="text-3xl font-bold text-gray-800" data-stat="total-orders">{{ $totalOrders ?? 0 }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-50 rounded-lg flex items-center justify-center">
                        <i class="fas fa-shopping-bag text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl border p-5 hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Sedang Proses</p>
                        <p class="text-3xl font-bold text-gray-800" data-stat="processing-orders">{{ $processingOrders ?? 0 }}</p>
                    </div>
                    <div class="w-12 h-12 bg-yellow-50 rounded-lg flex items-center justify-center">
                        <i class="fas fa-clock text-yellow-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl border p-5 hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Siap Diambil</p>
                        <p class="text-3xl font-bold text-gray-800" data-stat="ready-orders">{{ $readyOrders ?? 0 }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-50 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl border p-5 hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Total Belanja</p>
                        <p class="text-2xl font-bold text-gray-800" data-stat="total-spent">Rp {{ number_format($totalSpent ?? 0, 0, ',', '.') }}</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-50 rounded-lg flex items-center justify-center">
                        <i class="fas fa-money-bill-wave text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
            <a href="{{ route('user.pemesanan.index') }}" class="bg-white rounded-xl border p-5 hover:shadow-md transition group">
                <div class="flex items-center space-x-4">
                    <div class="w-14 h-14 bg-blue-50 rounded-lg flex items-center justify-center group-hover:bg-blue-600 transition">
                        <i class="fas fa-plus text-blue-600 group-hover:text-white transition text-2xl"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-lg text-gray-800 mb-1">Pesan Laundry</h3>
                        <p class="text-sm text-gray-600">Buat pesanan baru</p>
                    </div>
                </div>
            </a>

            <button onclick="scrollToHistory()" class="bg-white rounded-xl border p-5 hover:shadow-md transition group text-left">
                <div class="flex items-center space-x-4">
                    <div class="w-14 h-14 bg-purple-50 rounded-lg flex items-center justify-center group-hover:bg-purple-600 transition">
                        <i class="fas fa-history text-purple-600 group-hover:text-white transition text-2xl"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-lg text-gray-800 mb-1">Riwayat</h3>
                        <p class="text-sm text-gray-600">Lihat pesanan selesai</p>
                    </div>
                </div>
            </button>
        </div>

        <!-- Active Orders -->
        <div class="bg-white rounded-xl border p-6 mb-6">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-xl font-bold text-gray-800 flex items-center">
                    <i class="fas fa-list-alt text-blue-600 mr-3"></i>
                    Pesanan Aktif
                </h3>
                <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm font-semibold">{{ $activeOrders->count() ?? 0 }} Pesanan</span>
            </div>
            
            <div id="active-orders-container" class="space-y-4">
                @if(isset($activeOrders) && $activeOrders->count() > 0)
                    @foreach($activeOrders as $order)
                        @php
                            // Badge pembayaran tidak perlu ditampilkan karena untuk Midtrans sudah dibayar saat pemesanan
                            $showPaymentBadge = false;
                        @endphp
                        
                        <div class="order-card border-2 border-gray-200 rounded-xl p-4 md:p-5 hover:shadow-lg hover:border-blue-300 transition transform hover:-translate-y-1" 
                             data-order-id="{{ $order->id }}"
                             onclick="viewDetail({{ $order->id }})">
                            <div class="flex justify-between items-start mb-3 md:mb-4">
                                <div>
                                    <p class="font-bold text-base md:text-lg text-gray-800">{{ $order->invoice }}</p>
                                    <p class="text-xs md:text-sm text-gray-500 mt-1">
                                        <i class="far fa-calendar mr-1"></i>
                                        {{ $order->created_at->format('d M Y, H:i') }}
                                    </p>
                                </div>
                                <div class="flex flex-col gap-1">
                                    {{-- Status Pesanan Badge --}}
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold {{ 
                                        $order->status == 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                        ($order->status == 'proses' ? 'bg-blue-100 text-blue-800' : 
                                        ($order->status == 'selesai' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'))
                                    }}" data-status="{{ $order->status }}">
                                        <i class="fas fa-{{ $order->status == 'pending' ? 'clock' : ($order->status == 'proses' ? 'sync' : 'check-double') }} mr-1"></i>
                                        @if($order->status == 'pending') Pesanan Baru
                                        @elseif($order->status == 'proses') Sedang Diproses
                                        @elseif($order->status == 'selesai') Siap Diambil
                                        @else {{ ucfirst($order->status) }}
                                        @endif
                                    </span>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-3 md:gap-4 text-sm mb-4">
                                <div class="bg-gray-50 rounded-lg p-3 border">
                                    <p class="text-xs text-gray-500 mb-1"><i class="fas fa-spray-can mr-1"></i>Layanan</p>
                                    <p class="font-semibold text-gray-800">
                                        @if($order->service_type == 'cuci_kering') Cuci Kering
                                        @elseif($order->service_type == 'cuci_setrika') Cuci & Setrika
                                        @elseif($order->service_type == 'setrika_saja') Setrika Saja
                                        @else {{ $order->service_type }}
                                        @endif
                                    </p>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-3 border">
                                    <p class="text-xs text-gray-500 mb-1"><i class="fas fa-weight mr-1"></i>Berat</p>
                                    <p class="font-semibold text-gray-800">{{ number_format($order->weight, 1) }} kg</p>
                                </div>
                            </div>
                            
                            <div class="flex justify-between items-center pt-3 md:pt-4 border-t-2 border-gray-100">
                                <div>
                                    <p class="text-xs text-gray-500 mb-1">Total Pembayaran</p>
                                    <p class="font-bold text-lg md:text-xl text-blue-600">Rp {{ number_format($order->total, 0, ',', '.') }}</p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        <i class="fas fa-{{ ($order->payment_method ?? 'cash') == 'midtrans' ? 'credit-card' : 'money-bill-wave' }} mr-1"></i>
                                        {{ ($order->payment_method ?? 'cash') == 'midtrans' ? 'Online' : 'Tunai' }}
                                        @if(in_array(($order->payment_status ?? 'unpaid'), ['paid', 'success']))
                                            <span class="text-green-600 font-semibold">• Lunas</span>
                                        @endif
                                    </p>
                                </div>
                                <button onclick="event.stopPropagation(); viewDetail({{ $order->id }})" 
                                        class="px-4 md:px-6 py-2 md:py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white text-sm font-bold rounded-lg hover:from-blue-700 hover:to-blue-800 transition shadow-md hover:shadow-lg transform hover:scale-105">
                                    <i class="fas fa-eye mr-2"></i>Detail
                                </button>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-16">
                        <i class="fas fa-inbox text-gray-300 text-6xl mb-4"></i>
                        <p class="text-gray-500 text-lg mb-4 font-medium">Belum ada pesanan aktif</p>
                        <a href="{{ route('user.pemesanan.index') }}" class="inline-block px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition">
                            <i class="fas fa-plus-circle mr-2"></i>Buat Pesanan Baru
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Order History -->
        <div id="riwayat" class="bg-white rounded-xl border p-6">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-xl font-bold text-gray-800 flex items-center">
                    <i class="fas fa-history text-purple-600 mr-3"></i>
                    Riwayat Pesanan
                </h3>
            </div>
            <div class="space-y-4">
                @if(isset($orderHistory) && $orderHistory->count() > 0)
                    @foreach($orderHistory as $order)
                        <div class="order-card border rounded-xl p-5 hover:shadow-md hover:border-purple-300 transition bg-gray-50 cursor-pointer"
                             data-order-id="{{ $order->id }}"
                             onclick="viewDetail({{ $order->id }})">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <p class="font-bold text-lg text-gray-800">{{ $order->invoice }}</p>
                                    <p class="text-sm text-gray-500 mt-1">{{ $order->created_at->format('d M Y, H:i') }}</p>
                                </div>
                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-gray-200 text-gray-700">
                                    <i class="fas fa-check-circle mr-1"></i>Selesai
                                </span>
                            </div>
                            <div class="grid grid-cols-2 gap-4 text-sm mb-4">
                                <div class="bg-white rounded-lg p-3 border">
                                    <p class="text-xs text-gray-500 mb-1">Layanan</p>
                                    <p class="font-semibold text-gray-800">
                                        @if($order->service_type == 'cuci_kering') Cuci Kering
                                        @elseif($order->service_type == 'cuci_setrika') Cuci & Setrika
                                        @elseif($order->service_type == 'setrika_saja') Setrika Saja
                                        @else {{ $order->service_type }}
                                        @endif
                                    </p>
                                </div>
                                <div class="bg-white rounded-lg p-3 border">
                                    <p class="text-xs text-gray-500 mb-1">Total</p>
                                    <p class="font-semibold text-blue-600">Rp {{ number_format($order->total, 0, ',', '.') }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-16">
                        <i class="fas fa-history text-gray-300 text-6xl mb-4"></i>
                        <p class="text-gray-500 text-lg font-medium">Belum ada riwayat pesanan</p>
                    </div>
                @endif
            </div>
        </div>
    </main>

    <!-- Modal Detail -->
    <div id="detailModal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4">
        <div class="modal-content bg-white rounded-xl p-6 lg:p-8 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-2xl font-bold text-gray-800">Detail Pesanan</h3>
                <button onclick="closeDetailModal()" class="text-gray-400 hover:text-gray-600 transition">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
            <div id="detailContent">
                <div class="text-center py-8">
                    <i class="fas fa-spinner fa-spin text-blue-600 text-4xl mb-2"></i>
                    <p class="text-gray-600">Memuat data...</p>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/dashboard.js') }}"></script>
</body>
</html>