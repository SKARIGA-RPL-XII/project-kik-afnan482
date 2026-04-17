<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Laporan Keuangan - Laundry System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gradient-to-br from-blue-50 to-gray-100 min-h-screen flex">

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
                    <h2 class="text-xl lg:text-2xl font-bold text-gray-800">Laporan Keuangan</h2>
                    <p class="text-xs lg:text-sm text-gray-600">Analisis pendapatan dan performa laundry</p>
                </div>
                <div class="w-8 h-8 lg:w-10 lg:h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center text-white font-bold text-sm">
                    {{ substr(auth()->user()->name ?? 'A', 0, 1) }}
                </div>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-4 lg:p-8">
            
            <!-- Filter Form -->
            <div class="bg-white rounded-xl shadow-sm p-4 lg:p-6 mb-6">
                <form id="filterForm" action="{{ route('admin.laporan.index') }}" method="GET" class="flex flex-col lg:flex-row lg:items-end gap-4 justify-between">
                    <div class="flex flex-col lg:flex-row gap-4 flex-1">
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Dari Tanggal</label>
                            <input type="date" name="start_date" id="start_date" value="{{ request('start_date', $startDate->format('Y-m-d')) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Sampai Tanggal</label>
                            <input type="date" name="end_date" id="end_date" value="{{ request('end_date', $endDate->format('Y-m-d')) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                        </div>
                        <div class="lg:self-end">
                            <button type="submit" class="w-full lg:w-auto bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg text-sm font-medium transition flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                                Filter
                            </button>
                        </div>
                    </div>
                    
                    <div class="flex gap-2 lg:self-end border-t lg:border-t-0 pt-4 lg:pt-0 mt-2 lg:mt-0">
                        <button type="button" onclick="printReport()" class="flex-1 lg:flex-none bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 px-4 py-2 rounded-lg text-sm font-medium transition flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                            Print
                        </button>
                        <button type="button" onclick="exportExcel()" class="flex-1 lg:flex-none bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            Excel
                        </button>
                        <button type="button" onclick="exportPdf()" class="flex-1 lg:flex-none bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                            PDF
                        </button>
                    </div>
                </form>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-blue-100 p-3 rounded-lg text-blue-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        @if($growthPercentage != 0)
                        <span class="text-xs font-semibold {{ $growthPercentage >= 0 ? 'text-green-500 bg-green-50' : 'text-red-500 bg-red-50' }} px-2 py-1 rounded-full">
                            {{ $growthPercentage >= 0 ? '+' : '' }}{{ number_format($growthPercentage, 1) }}%
                        </span>
                        @endif
                    </div>
                    <p class="text-sm text-gray-500">Total Pendapatan</p>
                    <h3 class="text-2xl font-bold text-gray-800">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</h3>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-purple-100 p-3 rounded-lg text-purple-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                        </div>
                    </div>
                    <p class="text-sm text-gray-500">Total Transaksi</p>
                    <h3 class="text-2xl font-bold text-gray-800">{{ number_format($totalTransaksi) }} Pesanan</h3>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-orange-100 p-3 rounded-lg text-orange-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path></svg>
                        </div>
                    </div>
                    <p class="text-sm text-gray-500">Total Berat</p>
                    <h3 class="text-2xl font-bold text-gray-800">{{ number_format($totalBerat, 1, ',', '.') }} Kg</h3>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-teal-100 p-3 rounded-lg text-teal-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path></svg>
                        </div>
                    </div>
                    <p class="text-sm text-gray-500">Rata-rata / Transaksi</p>
                    <h3 class="text-2xl font-bold text-gray-800">Rp {{ number_format($rataRataPerTransaksi, 0, ',', '.') }}</h3>
                </div>
            </div>

            <!-- Charts -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Grafik Pendapatan Harian</h3>
                    <div class="h-64">
                        <canvas id="incomeChart"></canvas>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Layanan Terlaris</h3>
                    <div class="h-64 flex justify-center">
                        <canvas id="serviceChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                    <h3 class="font-bold text-gray-700">Rincian Transaksi</h3>
                    <span class="text-sm text-gray-600">{{ $laporans->count() }} transaksi</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Tanggal</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Invoice</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Pelanggan</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Layanan</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Berat (Kg)</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($laporans as $row)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $row->created_at->format('d/m/Y') }}</td>
                                <td class="px-6 py-4 text-sm font-medium text-blue-600">{{ $row->invoice }}</td>
                                <td class="px-6 py-4 text-sm text-gray-800">{{ $row->customer_name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $row->layanan->nama_layanan ?? $row->service_type ?? 'Layanan Terhapus' }}
                                    </span>
                                    @if($row->is_express)
                                    <span class="ml-1 px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Express</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ number_format($row->final_weight ?? $row->weight, 1, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 text-sm font-bold text-gray-800">Rp {{ number_format($row->total, 0, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-500">Tidak ada data transaksi pada periode ini.</td>
                            </tr>
                            @endforelse
                        </tbody>
                        @if($laporans->count() > 0)
                        <tfoot class="bg-gray-50 border-t-2 border-gray-300">
                            <tr>
                                <th colspan="5" class="px-6 py-4 text-right text-sm font-bold text-gray-700 uppercase">TOTAL:</th>
                                <th class="px-6 py-4 text-sm font-bold text-blue-600">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</th>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>

        </main>
    </div>

    <script>
        // Toggle Sidebar
        function toggleSidebar() {
            const sidebar = document.querySelector('aside');
            const overlay = document.getElementById('overlay');
            if (sidebar.classList.contains('-translate-x-full')) {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
            } else {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            }
        }

        // Print Report
        function printReport() {
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            const url = `{{ route('admin.laporan.print') }}?start_date=${startDate}&end_date=${endDate}`;
            window.open(url, '_blank');
        }

        // Export Excel
        function exportExcel() {
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            window.location.href = `{{ route('admin.laporan.export.excel') }}?start_date=${startDate}&end_date=${endDate}`;
        }

        // Export PDF
        function exportPdf() {
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            window.location.href = `{{ route('admin.laporan.export.pdf') }}?start_date=${startDate}&end_date=${endDate}`;
        }

        // Initialize Charts
        document.addEventListener('DOMContentLoaded', function() {
            
            // 1. Income Chart (Line)
            const incomeCtx = document.getElementById('incomeChart').getContext('2d');
            new Chart(incomeCtx, {
                type: 'line',
                data: {
                    labels: @json($dailyIncome['labels']),
                    datasets: [{
                        label: 'Pendapatan (Rp)',
                        data: @json($dailyIncome['data']),
                        borderColor: '#2563EB',
                        backgroundColor: 'rgba(37, 99, 235, 0.1)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#2563EB',
                        pointBorderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { borderDash: [2, 4] },
                            ticks: {
                                callback: function(value) {
                                    return 'Rp ' + (value/1000) + 'k';
                                }
                            }
                        },
                        x: {
                            grid: { display: false }
                        }
                    }
                }
            });

            // 2. Service Chart (Doughnut)
            const serviceCtx = document.getElementById('serviceChart').getContext('2d');
            new Chart(serviceCtx, {
                type: 'doughnut',
                data: {
                    labels: @json($topServices['labels']),
                    datasets: [{
                        data: @json($topServices['data']),
                        backgroundColor: [
                            '#3B82F6',
                            '#10B981',
                            '#F59E0B',
                            '#6366F1',
                            '#EC4899'
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { usePointStyle: true, boxWidth: 8 }
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>