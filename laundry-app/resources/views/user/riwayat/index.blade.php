<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pesanan - LaundryKu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @keyframes scaleIn {
            from { opacity: 0; transform: scale(0.95) translateY(10px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }
        .modal-animate {
            animation: scaleIn 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">

<nav class="bg-white shadow-sm sticky top-0 z-50 border-b">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                    <i class="fas fa-tshirt text-white"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-800">LaundryKu</h1>
                    <p class="text-xs text-gray-500 hidden sm:block">Layanan Laundry Terpercaya</p>
                </div>
            </div>
            <div class="hidden md:flex items-center space-x-1">
                <a href="{{ route('user.dashboard') }}" class="px-4 py-2 rounded-lg text-gray-600 hover:bg-gray-100 font-medium transition"><i class="fas fa-home mr-2"></i>Dashboard</a>
                <a href="{{ route('user.pemesanan.index') }}" class="px-4 py-2 rounded-lg text-gray-600 hover:bg-gray-100 font-medium transition"><i class="fas fa-plus-circle mr-2"></i>Pesan Laundry</a>
                <a href="{{ route('user.riwayat.index') }}" class="px-4 py-2 rounded-lg bg-blue-600 text-white font-medium"><i class="fas fa-history mr-2"></i>Riwayat</a>
            </div>
            <div class="flex items-center space-x-3">
                <button class="p-2 hover:bg-gray-100 rounded-lg transition"><i class="fas fa-bell text-gray-600"></i></button>
                <div class="relative">
                    @php
                        $profileImageUrl = null;
                        if(auth()->user()->profile_image) {
                            $p = public_path(auth()->user()->profile_image);
                            if(file_exists($p)) $profileImageUrl = asset(auth()->user()->profile_image).'?v='.time();
                        }
                    @endphp
                    <button id="userMenuBtn" class="flex items-center space-x-2 hover:bg-gray-100 rounded-lg p-2 transition">
                        @if($profileImageUrl)
                            <img src="{{ $profileImageUrl }}" class="w-9 h-9 rounded-full object-cover border-2 border-blue-200">
                        @else
                            <div class="w-9 h-9 bg-blue-600 rounded-full flex items-center justify-center text-white font-semibold text-sm">{{ strtoupper(substr(auth()->user()->name??'U',0,1)) }}</div>
                        @endif
                        <span class="hidden sm:block text-sm font-medium text-gray-700">{{ auth()->user()->name??'User' }}</span>
                        <i class="fas fa-chevron-down text-gray-500 text-xs" id="dropdownIcon"></i>
                    </button>
                    <div id="userDropdown" class="hidden absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border py-2 z-50">
                        <div class="px-4 py-3 border-b border-gray-100 flex items-center space-x-3">
                            @if($profileImageUrl)
                                <img src="{{ $profileImageUrl }}" class="w-10 h-10 rounded-full object-cover border-2 border-blue-200">
                            @else
                                <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center text-white font-semibold">{{ strtoupper(substr(auth()->user()->name??'U',0,1)) }}</div>
                            @endif
                            <div>
                                <p class="text-sm font-semibold text-gray-800">{{ auth()->user()->name??'User' }}</p>
                                <p class="text-xs text-gray-500">{{ auth()->user()->email??'' }}</p>
                            </div>
                        </div>
                        <a href="{{ route('user.profile') }}" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 transition"><i class="fas fa-user-circle w-5 mr-3 text-gray-500"></i>Profil Saya</a>
                        <div class="border-t border-gray-100 pt-2">
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="flex items-center w-full px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition"><i class="fas fa-sign-out-alt w-5 mr-3"></i>Logout</button>
                            </form>
                        </div>
                    </div>
                </div>
                <button id="mobileMenuBtn" class="md:hidden p-2 hover:bg-gray-100 rounded-lg transition"><i class="fas fa-bars text-gray-700"></i></button>
            </div>
        </div>
    </div>
    <div id="mobileMenu" class="hidden md:hidden border-t bg-white px-4 py-3 space-y-1">
        <a href="{{ route('user.dashboard') }}" class="block px-4 py-2 rounded-lg text-gray-700 hover:bg-gray-50 font-medium"><i class="fas fa-home mr-2"></i>Dashboard</a>
        <a href="{{ route('user.pemesanan.index') }}" class="block px-4 py-2 rounded-lg text-gray-700 hover:bg-gray-50 font-medium"><i class="fas fa-plus-circle mr-2"></i>Pesan Laundry</a>
        <a href="{{ route('user.riwayat.index') }}" class="block px-4 py-2 rounded-lg bg-blue-50 text-blue-700 font-medium"><i class="fas fa-history mr-2"></i>Riwayat</a>
        <a href="{{ route('user.profile') }}" class="block px-4 py-2 rounded-lg text-gray-700 hover:bg-gray-50 font-medium"><i class="fas fa-user-circle mr-2"></i>Profil Saya</a>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="w-full text-left px-4 py-2 rounded-lg text-red-600 hover:bg-red-50 font-medium"><i class="fas fa-sign-out-alt mr-2"></i>Logout</button>
        </form>
    </div>
</nav>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">

    <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-xl p-6 text-white mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold mb-1">Riwayat Pesanan</h1>
            <p class="text-blue-100 text-sm">Daftar semua pesanan laundry Anda</p>
        </div>
        <i class="fas fa-history text-5xl opacity-80 hidden sm:block"></i>
    </div>

    <form method="GET" action="{{ route('user.riwayat.index') }}" class="bg-white rounded-xl border p-4 mb-6 flex flex-col sm:flex-row gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nomor invoice..." class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Semua Status</option>
            <option value="pending"  {{ request('status')=='pending'  ?'selected':'' }}>Menunggu</option>
            <option value="proses"   {{ request('status')=='proses'   ?'selected':'' }}>Diproses</option>
            <option value="selesai"  {{ request('status')=='selesai'  ?'selected':'' }}>Siap Diambil</option>
            <option value="diambil"  {{ request('status')=='diambil'  ?'selected':'' }}>Selesai</option>
        </select>
        <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition"><i class="fas fa-search mr-1"></i>Cari</button>
        @if(request('search') || request('status'))
            <a href="{{ route('user.riwayat.index') }}" class="px-5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium transition text-center"><i class="fas fa-times mr-1"></i>Reset</a>
        @endif
    </form>

    <div class="space-y-4">
        @forelse($pesanans as $pesanan)
        @php
            $labels  = ['cuci_kering'=>'Cuci Kering','cuci_setrika'=>'Cuci & Setrika','setrika_saja'=>'Setrika Saja','cuci-kering'=>'Cuci Kering','cuci-setrika'=>'Cuci & Setrika','setrika-saja'=>'Setrika Saja'];
            $layanan = $labels[$pesanan->service_type] ?? $pesanan->service_type ?? '-';
            $berat   = $pesanan->final_weight ?? $pesanan->weight ?? 0;
            $online  = ($pesanan->payment_method ?? '') === 'midtrans';
            $paid    = in_array($pesanan->payment_status ?? '', ['paid','success']);
            $sCfg    = [
                'pending' => ['label'=>'Pesanan Baru','icon'=>'fa-clock',       'class'=>'bg-yellow-100 text-yellow-800'],
                'proses'  => ['label'=>'Sedang Diproses',   'icon'=>'fa-sync',        'class'=>'bg-blue-100 text-blue-800'],
                'selesai' => ['label'=>'Siap Diambil',      'icon'=>'fa-check-circle','class'=>'bg-green-100 text-green-800'],
                'diambil' => ['label'=>'Selesai',           'icon'=>'fa-check-double','class'=>'bg-gray-200 text-gray-700'],
            ];
            $badge  = $sCfg[$pesanan->status] ?? ['label'=>ucfirst($pesanan->status),'icon'=>'fa-circle','class'=>'bg-red-100 text-red-700'];
            $modal  = [
                'invoice' => $pesanan->invoice ?? '-',
                'date'    => $pesanan->created_at->format('d M Y, H:i'),
                'service' => $layanan,
                'weight'  => number_format($berat, 1),
                'address' => $pesanan->address ?? '-',
                'total'   => 'Rp ' . number_format($pesanan->total ?? 0, 0, ',', '.'),
                'payment' => $online ? 'Online' : 'Tunai',
                'paid'    => $paid,
                'status'  => $badge['label'],
                'express' => (bool) $pesanan->is_express,
                'note'    => $pesanan->note ?? '',
            ];
        @endphp

        <div class="bg-white border-2 border-gray-200 rounded-xl p-5 hover:shadow-lg hover:border-blue-300 transition">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <div class="flex items-center gap-2 flex-wrap mb-1">
                        <p class="font-bold text-lg text-gray-800">{{ $pesanan->invoice ?? '-' }}</p>
                        @if($pesanan->is_express)
                            <span class="px-2 py-0.5 bg-yellow-100 text-yellow-700 text-xs font-bold rounded-full"><i class="fas fa-bolt mr-1"></i>Express</span>
                        @endif
                    </div>
                    <p class="text-xs text-gray-500"><i class="far fa-calendar mr-1"></i>{{ $pesanan->created_at->format('d M Y, H:i') }}</p>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $badge['class'] }} whitespace-nowrap">
                    <i class="fas {{ $badge['icon'] }} mr-1"></i>{{ $badge['label'] }}
                </span>
            </div>

            <div class="grid grid-cols-2 gap-3 text-sm mb-4">
                <div class="bg-gray-50 rounded-lg p-3 border">
                    <p class="text-xs text-gray-500 mb-1"><i class="fas fa-tshirt mr-1"></i>Layanan</p>
                    <p class="font-semibold text-gray-800">{{ $layanan }}</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-3 border">
                    <p class="text-xs text-gray-500 mb-1"><i class="fas fa-weight mr-1"></i>Berat</p>
                    <p class="font-semibold text-gray-800">{{ number_format($berat,1) }} kg
                        @if($pesanan->final_weight && $pesanan->final_weight != $pesanan->weight)
                            <span class="text-xs text-gray-400 block">(est: {{ number_format($pesanan->weight,1) }} kg)</span>
                        @endif
                    </p>
                </div>
            </div>

            @if(!empty($pesanan->address) && $pesanan->address !== 'Walk-in')
                <p class="text-xs text-gray-400 mb-3"><i class="fas fa-map-marker-alt mr-1"></i>{{ Str::limit($pesanan->address,70) }}</p>
            @endif

            <div class="flex justify-between items-center pt-4 border-t-2 border-gray-100">
                <div>
                    <p class="text-xs text-gray-500 mb-1">Total Pembayaran</p>
                    <p class="font-bold text-xl text-blue-600">Rp {{ number_format($pesanan->total??0,0,',','.') }}</p>
                    <p class="text-xs text-gray-500 mt-1">
                        <i class="fas fa-{{ $online ? 'credit-card' : 'money-bill-wave' }} mr-1"></i>{{ $online ? 'Online' : 'Tunai' }}
                        @if($paid) <span class="text-green-600 font-semibold">• Lunas</span> @endif
                    </p>
                </div>
                <button data-pesanan='@json($modal)'
                        onclick="openModal(JSON.parse(this.dataset.pesanan))"
                        class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-lg transition shadow-md">
                    <i class="fas fa-eye mr-2"></i>Detail
                </button>
            </div>
        </div>

        @empty
        <div class="bg-white rounded-xl border p-16 text-center">
            <i class="fas fa-box-open text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-bold text-gray-600 mb-2">{{ request('search')||request('status') ? 'Tidak Ada Hasil' : 'Belum Ada Pesanan' }}</h3>
            <p class="text-gray-400 mb-6">{{ request('search')||request('status') ? 'Coba ubah filter pencarian Anda.' : 'Anda belum pernah melakukan pemesanan laundry.' }}</p>
            <div class="flex justify-center gap-3 flex-wrap">
                @if(request('search')||request('status'))
                    <a href="{{ route('user.riwayat.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-3 rounded-xl font-medium transition"><i class="fas fa-times mr-2"></i>Reset Filter</a>
                @endif
                <a href="{{ route('user.pemesanan.index') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-xl font-semibold transition"><i class="fas fa-plus-circle mr-2"></i>Pesan Sekarang</a>
            </div>
        </div>
        @endforelse
    </div>

    @if($pesanans->hasPages())
        <div class="mt-6 flex justify-center">{{ $pesanans->links() }}</div>
    @endif
</main>

<!-- Modal -->
<div id="detailModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4 transition-opacity">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-sm overflow-hidden modal-animate">
        
        <!-- Header -->
        <div class="bg-blue-600 px-5 py-4 flex justify-between items-center text-white relative">
            <div>
                <h3 class="font-bold text-lg leading-tight" id="m-invoice"></h3>
                <p class="text-blue-100 text-xs mt-0.5" id="m-date"></p>
            </div>
            <div id="m-express" class="hidden absolute top-4 right-12">
                <span class="px-2 py-0.5 bg-yellow-400 text-yellow-900 text-[10px] font-bold rounded-full shadow-sm">
                    <i class="fas fa-bolt mr-1"></i>Express
                </span>
            </div>
            <button onclick="closeModal()" class="text-white/80 hover:text-white transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <!-- Content -->
        <div class="p-5 space-y-4">
            
            <div class="grid grid-cols-2 gap-3">
                <div class="bg-gray-50 rounded-lg p-3 text-center border border-gray-100">
                    <p class="text-[11px] text-gray-500 uppercase tracking-wider mb-1">Status</p>
                    <p class="font-bold text-gray-800 text-sm" id="m-status"></p>
                </div>
                <div class="bg-gray-50 rounded-lg p-3 text-center border border-gray-100">
                    <p class="text-[11px] text-gray-500 uppercase tracking-wider mb-1">Pembayaran</p>
                    <p class="font-bold text-gray-800 text-sm" id="m-payment"></p>
                </div>
            </div>

            <div class="space-y-3 text-sm border-t border-gray-100 pt-3">
                <div class="flex justify-between items-center">
                    <span class="text-gray-500"><i class="fas fa-tshirt w-5 text-center text-gray-400"></i> Layanan</span>
                    <span class="font-semibold text-gray-800" id="m-service"></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-500"><i class="fas fa-weight-hanging w-5 text-center text-gray-400"></i> Berat</span>
                    <span class="font-semibold text-gray-800" id="m-weight"></span>
                </div>
                
                <div id="m-address-wrap" class="hidden flex justify-between items-start">
                    <span class="text-gray-500 whitespace-nowrap"><i class="fas fa-map-marker-alt w-5 text-center text-gray-400"></i> Alamat</span>
                    <span class="font-semibold text-gray-800 text-right max-w-[65%] pl-2" id="m-address"></span>
                </div>

                <div id="m-note-wrap" class="hidden flex flex-col pt-1">
                    <span class="text-gray-500 mb-1"><i class="fas fa-comment-dots w-5 text-center text-gray-400"></i> Catatan</span>
                    <p class="font-medium text-gray-700 bg-yellow-50/50 p-2.5 rounded-lg border border-yellow-100 text-[13px]" id="m-note"></p>
                </div>
            </div>

            <!-- Footer -->
            <div class="border-t border-gray-100 pt-4 mt-2">
                <div class="flex justify-between items-end">
                    <div>
                        <p class="text-[11px] text-gray-500 uppercase tracking-wider mb-0.5">Total Harga</p>
                        <p class="font-bold text-xl text-blue-600 leading-none" id="m-total"></p>
                    </div>
                    <span id="m-paid" class="hidden px-2 py-1 bg-green-100 text-green-700 text-[11px] font-bold rounded">Lunas</span>
                </div>
            </div>

            <button onclick="closeModal()" class="w-full mt-3 py-2.5 bg-gray-100 text-gray-700 font-semibold rounded-lg hover:bg-gray-200 transition-colors text-sm">
                Tutup Ringkasan
            </button>
        </div>
    </div>
</div>

<script>
const modal = document.getElementById('detailModal');

function openModal(d) {
    document.getElementById('m-invoice').textContent  = d.invoice;
    document.getElementById('m-date').textContent     = d.date;
    document.getElementById('m-status').textContent   = d.status;
    document.getElementById('m-service').textContent  = d.service;
    document.getElementById('m-weight').textContent   = d.weight + ' kg';
    document.getElementById('m-total').textContent    = d.total;
    document.getElementById('m-payment').textContent  = d.payment;
    const showAddr = d.address && d.address !== '-' && d.address !== 'Walk-in';
    document.getElementById('m-address-wrap').classList.toggle('hidden', !showAddr);
    if (showAddr) document.getElementById('m-address').textContent = d.address;
    document.getElementById('m-note-wrap').classList.toggle('hidden', !d.note);
    if (d.note) document.getElementById('m-note').textContent = d.note;
    document.getElementById('m-express').classList.toggle('hidden', !d.express);
    document.getElementById('m-paid').classList.toggle('hidden', !d.paid);
    modal.classList.replace('hidden', 'flex');
}

function closeModal() { modal.classList.replace('flex', 'hidden'); }
modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });

const userMenuBtn  = document.getElementById('userMenuBtn');
const userDropdown = document.getElementById('userDropdown');
const dropdownIcon = document.getElementById('dropdownIcon');
userMenuBtn.addEventListener('click', e => { e.stopPropagation(); userDropdown.classList.toggle('hidden'); dropdownIcon.classList.toggle('rotate-180'); });
document.addEventListener('click', () => { userDropdown.classList.add('hidden'); dropdownIcon.classList.remove('rotate-180'); });
document.getElementById('mobileMenuBtn').addEventListener('click', () => document.getElementById('mobileMenu').classList.toggle('hidden'));
</script>
</body>
</html>