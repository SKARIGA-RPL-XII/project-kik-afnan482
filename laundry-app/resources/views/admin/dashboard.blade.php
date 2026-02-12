<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Laundry System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body class="bg-gradient-to-br from-blue-50 to-gray-100 min-h-screen">
    
    <div class="flex">
        @include('admin.sidebar')

        <div class="flex-1 flex flex-col min-h-screen">
            <header class="bg-white shadow-sm sticky top-0 z-20">
                <div class="px-4 sm:px-8 py-4 flex items-center justify-between">
                    <div class="ml-14 lg:ml-0">
                        <h2 class="text-xl sm:text-2xl font-bold text-gray-800">Dashboard</h2>
                        <p class="text-xs sm:text-sm text-gray-600">Selamat datang di sistem laundry</p>
                    </div>
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center text-white font-bold">
                        {{ substr(auth()->user()->name ?? 'A', 0, 1) }}
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
                <!-- Welcome Section -->
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-2xl p-6 sm:p-8 mb-6 sm:mb-8 text-white">
                    <h2 class="text-2xl sm:text-3xl font-bold mb-2">Selamat Datang, {{ auth()->user()->name }}! 👋</h2>
                    <p class="text-sm sm:text-base text-blue-100">Kelola sistem laundry Anda dengan mudah dan efisien</p>
                    <div class="mt-4 flex items-center gap-4 text-sm">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span>{{ \Carbon\Carbon::now()->isoFormat('dddd, D MMMM Y') }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            <span>{{ $pesananHariIni ?? 0 }} Pesanan Hari Ini</span>
                        </div>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6 sm:mb-8">
                    <!-- Total Pesanan -->
                    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-blue-500 hover:shadow-md transition">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600 font-medium">Total Pesanan</p>
                                <p class="text-3xl font-bold text-gray-800 mt-1">{{ $totalPesanan ?? 0 }}</p>
                                <p class="text-xs text-gray-500 mt-2">
                                    <span class="text-blue-600 font-semibold">{{ $pesananHariIni ?? 0 }}</span> hari ini
                                </p>
                            </div>
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Pesanan Selesai -->
                    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-green-500 hover:shadow-md transition">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600 font-medium">Selesai</p>
                                <p class="text-3xl font-bold text-gray-800 mt-1">{{ $pesananSelesai ?? 0 }}</p>
                                <p class="text-xs text-gray-500 mt-2">
                                    <span class="text-green-600 font-semibold">{{ ($totalPesanan ?? 0) > 0 ? round((($pesananSelesai ?? 0) / $totalPesanan) * 100) : 0 }}%</span> dari total
                                </p>
                            </div>
                            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Pesanan Proses -->
                    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-yellow-500 hover:shadow-md transition">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600 font-medium">Dalam Proses</p>
                                <p class="text-3xl font-bold text-gray-800 mt-1">{{ ($pesananProses ?? 0) + ($pesananPending ?? 0) }}</p>
                                <p class="text-xs text-gray-500 mt-2">
                                    <span class="text-yellow-600 font-semibold">{{ $pesananPending ?? 0 }}</span> pending
                                </p>
                            </div>
                            <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Total Pendapatan -->
                    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-purple-500 hover:shadow-md transition">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600 font-medium">Pendapatan Total</p>
                                <p class="text-2xl font-bold text-gray-800 mt-1">Rp {{ number_format(($totalPendapatan ?? 0) / 1000000, 1) }}M</p>
                                <p class="text-xs text-gray-500 mt-2">
                                    Bulan ini: <span class="text-purple-600 font-semibold">Rp {{ number_format(($pendapatanBulanIni ?? 0) / 1000, 0) }}K</span>
                                </p>
                            </div>
                            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <!-- Grafik Pesanan -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">Grafik Pesanan (12 Bulan Terakhir)</h3>
                        <div style="position: relative; height: 300px;">
                            <canvas id="pesananChart"></canvas>
                        </div>
                    </div>

                    <!-- Grafik Pendapatan -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">Grafik Pendapatan (12 Bulan Terakhir)</h3>
                        <div style="position: relative; height: 300px;">
                            <canvas id="pendapatanChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Tables Section -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Layanan Terpopuler -->
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                        <div class="px-6 py-4 bg-gradient-to-r from-blue-500 to-blue-600 text-white">
                            <h3 class="text-lg font-bold">Layanan Terpopuler</h3>
                        </div>
                        <div class="p-6">
                            @forelse($layananTerpopuler ?? [] as $layanan)
                            <div class="flex items-center justify-between py-3 border-b last:border-0">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-800">{{ $layanan->layanan->nama_layanan ?? 'Unknown' }}</p>
                                        <p class="text-xs text-gray-500">{{ $layanan->total }} pesanan</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="w-16 bg-gray-200 rounded-full h-2">
                                        <div class="bg-blue-600 h-2 rounded-full" style="width: {{ ($totalPesanan ?? 0) > 0 ? ($layanan->total / $totalPesanan) * 100 : 0 }}%"></div>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">{{ ($totalPesanan ?? 0) > 0 ? round(($layanan->total / $totalPesanan) * 100) : 0 }}%</p>
                                </div>
                            </div>
                            @empty
                            <p class="text-center text-gray-500 py-8">Belum ada data</p>
                            @endforelse
                        </div>
                    </div>

                    <!-- Pesanan Terbaru -->
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                        <div class="px-6 py-4 bg-gradient-to-r from-green-500 to-green-600 text-white">
                            <h3 class="text-lg font-bold">Pesanan Terbaru</h3>
                        </div>
                        <div class="p-6">
                            @forelse($pesananTerbaru ?? [] as $pesanan)
                            <div class="flex items-center justify-between py-3 border-b last:border-0">
                                <div>
                                    <p class="font-semibold text-gray-800">{{ $pesanan->invoice }}</p>
                                    <p class="text-xs text-gray-500">{{ $pesanan->customer_name ?? 'Guest' }}</p>
                                    <p class="text-xs text-gray-500 mt-1">{{ $pesanan->created_at->diffForHumans() }}</p>
                                </div>
                                <div class="text-right">
                                    @php
                                        $statusColors = [
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'proses' => 'bg-blue-100 text-blue-800',
                                            'selesai' => 'bg-green-100 text-green-800',
                                            'diambil' => 'bg-gray-100 text-gray-800'
                                        ];
                                    @endphp
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $statusColors[$pesanan->status] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ ucfirst($pesanan->status) }}
                                    </span>
                                    <p class="text-xs text-gray-600 mt-1 font-semibold">Rp {{ number_format($pesanan->total, 0, ',', '.') }}</p>
                                </div>
                            </div>
                            @empty
                            <p class="text-center text-gray-500 py-8">Belum ada pesanan</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 mt-6">
                    <a href="{{ route('admin.pesanan.index') }}" class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition group">
                        <div class="flex items-center space-x-4">
                            <div class="w-14 h-14 bg-blue-100 rounded-lg flex items-center justify-center group-hover:bg-blue-600 transition">
                                <svg class="w-7 h-7 text-blue-600 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                            </div>
                            <div class="text-left">
                                <h3 class="font-bold text-gray-800">Pesanan Baru</h3>
                                <p class="text-sm text-gray-600">Tambah pesanan</p>
                            </div>
                        </div>
                    </a>

                    <a href="{{ route('admin.pesanan.index') }}" class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition group">
                        <div class="flex items-center space-x-4">
                            <div class="w-14 h-14 bg-green-100 rounded-lg flex items-center justify-center group-hover:bg-green-600 transition">
                                <svg class="w-7 h-7 text-green-600 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                                </svg>
                            </div>
                            <div class="text-left">
                                <h3 class="font-bold text-gray-800">Daftar Pesanan</h3>
                                <p class="text-sm text-gray-600">Lihat semua</p>
                            </div>
                        </div>
                    </a>

                    <a href="{{ route('admin.pelanggan.index') }}" class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition group sm:col-span-2 lg:col-span-1">
                        <div class="flex items-center space-x-4">
                            <div class="w-14 h-14 bg-purple-100 rounded-lg flex items-center justify-center group-hover:bg-purple-600 transition">
                                <svg class="w-7 h-7 text-purple-600 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                            </div>
                            <div class="text-left">
                                <h3 class="font-bold text-gray-800">Pelanggan</h3>
                                <p class="text-sm text-gray-600">{{ $totalPelanggan ?? 0 }} terdaftar</p>
                            </div>
                        </div>
                    </a>
                </div>
            </main>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Data untuk Chart.js
            const months = {!! json_encode($months ?? []) !!};
            const pesananData = {!! json_encode($pesananData ?? []) !!};
            const pendapatanData = {!! json_encode($pendapatanData ?? []) !!};

            // Grafik Pesanan
            const ctxPesanan = document.getElementById('pesananChart');
            if (ctxPesanan) {
                new Chart(ctxPesanan, {
                    type: 'line',
                    data: {
                        labels: months,
                        datasets: [{
                            label: 'Jumlah Pesanan',
                            data: pesananData,
                            borderColor: 'rgb(59, 130, 246)',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            tension: 0.4,
                            fill: true,
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                }
                            }
                        }
                    }
                });
            }

            // Grafik Pendapatan
            const ctxPendapatan = document.getElementById('pendapatanChart');
            if (ctxPendapatan) {
                new Chart(ctxPendapatan, {
                    type: 'bar',
                    data: {
                        labels: months,
                        datasets: [{
                            label: 'Pendapatan (Rp)',
                            data: pendapatanData,
                            backgroundColor: 'rgba(168, 85, 247, 0.8)',
                            borderColor: 'rgb(168, 85, 247)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top'
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        let label = context.dataset.label || '';
                                        if (label) {
                                            label += ': ';
                                        }
                                        label += new Intl.NumberFormat('id-ID', {
                                            style: 'currency',
                                            currency: 'IDR',
                                            minimumFractionDigits: 0
                                        }).format(context.parsed.y);
                                        return label;
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        if (value >= 1000000) {
                                            return 'Rp ' + (value / 1000000).toFixed(1) + 'M';
                                        } else if (value >= 1000) {
                                            return 'Rp ' + (value / 1000).toFixed(0) + 'K';
                                        }
                                        return 'Rp ' + value;
                                    }
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>