<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pesanan - LaundryKu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gradient-to-br from-blue-50 via-white to-blue-50 min-h-screen">

<!-- ================= NAVBAR (SAMA PERSIS) ================= -->
<nav class="bg-white/90 backdrop-blur-lg shadow-lg sticky top-0 z-50 border-b border-blue-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">

            <!-- Logo -->
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-blue-700 rounded-xl flex items-center justify-center shadow-lg">
                    <i class="fas fa-tshirt text-white"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold bg-gradient-to-r from-blue-600 to-blue-700 bg-clip-text text-transparent">
                        LaundryKu
                    </h1>
                    <p class="text-xs text-gray-500 hidden sm:block">
                        Layanan Laundry Terpercaya
                    </p>
                </div>
            </div>

            <!-- Desktop Menu -->
            <div class="hidden md:flex items-center space-x-1">
                <a href="{{ route('user.dashboard') }}" 
                   class="px-4 py-2 rounded-lg text-gray-700 hover:bg-blue-50 font-medium transition">
                    <i class="fas fa-home mr-2"></i>Dashboard
                </a>

                <a href="{{ route('user.pemesanan.index') }}" 
                   class="px-4 py-2 rounded-lg text-gray-700 hover:bg-blue-50 font-medium transition">
                    <i class="fas fa-plus-circle mr-2"></i>Pesan Laundry
                </a>

                <a href="{{ route('user.riwayat.index') }}" 
                   class="px-4 py-2 rounded-lg bg-gradient-to-r from-blue-600 to-blue-700 text-white font-medium shadow-md">
                    <i class="fas fa-history mr-2"></i>Riwayat
                </a>
            </div>

            <!-- User -->
            <div class="flex items-center space-x-3">
                <div class="flex items-center space-x-2">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center text-white font-bold">
                        {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                    </div>
                    <span class="hidden sm:block text-sm font-medium text-gray-700">
                        {{ auth()->user()->name ?? 'User' }}
                    </span>
                </div>
            </div>

        </div>
    </div>
</nav>

<!-- ================= MAIN ================= -->
<main class="max-w-7xl mx-auto px-4 py-8">

    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-2xl shadow-lg p-6 text-white mb-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold mb-2">Riwayat Pesanan</h1>
                <p class="text-blue-100">Daftar semua pesanan laundry Anda</p>
            </div>
            <div class="hidden sm:block">
                <i class="fas fa-history text-5xl opacity-80"></i>
            </div>
        </div>
    </div>

    <!-- List Riwayat -->
    <div class="space-y-6">

        @forelse($pesanans as $pesanan)

        <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-6 hover:shadow-xl transition">

            <div class="flex flex-col md:flex-row md:justify-between md:items-center">

                <!-- Info Kiri -->
                <div>
                    <h2 class="text-lg font-bold text-gray-800 mb-1">
                        #{{ $pesanan->kode_pesanan }}
                    </h2>

                    <p class="text-sm text-gray-600">
                        {{ $pesanan->layanan->nama_layanan }}
                        • {{ $pesanan->berat }} Kg
                    </p>

                    <p class="text-sm text-gray-500 mt-1">
                        {{ $pesanan->created_at->format('d M Y H:i') }}
                    </p>
                </div>

                <!-- Status & Harga -->
                <div class="mt-4 md:mt-0 text-right">

                    <!-- Status -->
                    @if($pesanan->status == 'menunggu')
                        <span class="px-4 py-1 bg-yellow-100 text-yellow-700 text-sm font-bold rounded-full">
                            Menunggu
                        </span>
                    @elseif($pesanan->status == 'diproses')
                        <span class="px-4 py-1 bg-blue-100 text-blue-700 text-sm font-bold rounded-full">
                            Diproses
                        </span>
                    @elseif($pesanan->status == 'selesai')
                        <span class="px-4 py-1 bg-green-100 text-green-700 text-sm font-bold rounded-full">
                            Selesai
                        </span>
                    @else
                        <span class="px-4 py-1 bg-red-100 text-red-700 text-sm font-bold rounded-full">
                            Dibatalkan
                        </span>
                    @endif

                    <!-- Harga -->
                    <p class="text-xl font-bold text-blue-600 mt-3">
                        Rp {{ number_format($pesanan->total_harga,0,',','.') }}
                    </p>

                    <a href="{{ route('user.pemesanan.detail', $pesanan->id) }}"
                       class="inline-block mt-3 text-sm text-blue-600 hover:underline">
                        Lihat Detail →
                    </a>

                </div>
            </div>

        </div>

        @empty

        <!-- Jika kosong -->
        <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-12 text-center">
            <i class="fas fa-box-open text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-bold text-gray-700 mb-2">Belum Ada Pesanan</h3>
            <p class="text-gray-500 mb-6">Anda belum pernah melakukan pemesanan laundry.</p>
            <a href="{{ route('user.pemesanan.index') }}"
               class="bg-gradient-to-r from-blue-600 to-blue-700 text-white px-6 py-3 rounded-xl shadow-md hover:shadow-xl transition">
                Pesan Sekarang
            </a>
        </div>

        @endforelse

    </div>
</main>

</body>
</html>
