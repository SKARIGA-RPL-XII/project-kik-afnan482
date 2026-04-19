// ============================================
// DASHBOARD REAL-TIME MONITORING SYSTEM
// Version 4.0 - Fixed Real-Time + 10s Polling
// ============================================

let refreshInterval = null;
let lastOrderStates = {};
let audioContext = null;
let isMonitoring = false;
let apiAvailable = true;
let audioUnlocked = false;
let isFetching = false;

document.addEventListener('DOMContentLoaded', function () {
    console.log('Dashboard v4.0 initialized');
    initializeOrderStates();
    setupAudioContext();
    setupUserMenuToggle();
    setupMobileMenu();
    setupScrollFunctions();

    // Mulai monitoring langsung tanpa delay cek API dulu
    startRealTimeMonitoring();
});

// ============================================
// AUDIO
// ============================================

function setupAudioContext() {
    try {
        audioContext = new (window.AudioContext || window.webkitAudioContext)();
    } catch (e) {
        audioContext = null;
        return;
    }

    const unlockAudio = () => {
        if (audioUnlocked) return;
        if (audioContext.state === 'suspended') {
            audioContext.resume().then(() => { audioUnlocked = true; });
        } else {
            audioUnlocked = true;
        }
        document.removeEventListener('click', unlockAudio);
        document.removeEventListener('touchstart', unlockAudio);
    };

    document.addEventListener('click', unlockAudio);
    document.addEventListener('touchstart', unlockAudio);
}

function playNotificationSound() {
    if (!audioContext) return;

    const resume = audioContext.state === 'suspended'
        ? audioContext.resume()
        : Promise.resolve();

    resume.then(() => {
        try {
            const now = audioContext.currentTime;

            const o1 = audioContext.createOscillator();
            const g1 = audioContext.createGain();
            o1.connect(g1); g1.connect(audioContext.destination);
            o1.type = 'sine'; o1.frequency.value = 880;
            g1.gain.setValueAtTime(0.3, now);
            g1.gain.exponentialRampToValueAtTime(0.001, now + 0.25);
            o1.start(now); o1.stop(now + 0.25);

            const o2 = audioContext.createOscillator();
            const g2 = audioContext.createGain();
            o2.connect(g2); g2.connect(audioContext.destination);
            o2.type = 'sine'; o2.frequency.value = 1100;
            g2.gain.setValueAtTime(0.3, now + 0.3);
            g2.gain.exponentialRampToValueAtTime(0.001, now + 0.55);
            o2.start(now + 0.3); o2.stop(now + 0.55);
        } catch (e) {
            console.error('Sound error:', e);
        }
    });
}

// ============================================
// FILTER pesanan valid
// ============================================

function isOrderVisible(order) {
    // Cash selalu tampil
    if (!order.payment_method || order.payment_method === 'cash') return true;
    // Midtrans: sembunyikan jika payment_status masih 'unpaid' (belum mulai bayar)
    if (order.payment_method === 'midtrans') return order.payment_status !== 'unpaid';
    return true;
}

// ============================================
// MONITORING — start/stop/fetch
// ============================================

function startRealTimeMonitoring() {
    if (isMonitoring) return;

    isMonitoring = true;

    // Fetch pertama langsung
    fetchOrderUpdates();

    // Lalu setiap 10 detik
    refreshInterval = setInterval(fetchOrderUpdates, 10000);

    showMonitoringStatus();
    console.log('Real-time monitoring started (10s interval)');
}

function stopRealTimeMonitoring() {
    if (refreshInterval) {
        clearInterval(refreshInterval);
        refreshInterval = null;
    }
    isMonitoring = false;
}

function fetchOrderUpdates() {
    // Hindari request tumpang tindih
    if (isFetching) return;
    isFetching = true;

    fetch('/api/user/orders-status', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        credentials: 'same-origin'
    })
    .then(res => {
        if (res.status === 404) {
            // API belum tersedia, hentikan monitoring
            apiAvailable = false;
            stopRealTimeMonitoring();
            console.warn('API /api/user/orders-status tidak ditemukan (404). Monitoring dihentikan.');
            return null;
        }
        if (!res.ok) throw new Error('HTTP ' + res.status);
        apiAvailable = true;
        return res.json();
    })
    .then(data => {
        if (data && data.success && Array.isArray(data.data)) {
            processOrderUpdates(data.data);
        }
    })
    .catch(err => {
        console.error('Fetch error:', err.message);
    })
    .finally(() => {
        isFetching = false;
    });
}

// ============================================
// PROSES UPDATE — deteksi perubahan status
// ============================================

function processOrderUpdates(orders) {
    let hasChanges = false;

    orders.forEach(order => {
        const orderId        = String(order.id);
        const currentStatus  = order.status;
        const currentPayment = order.payment_status || 'unpaid';
        const prev           = lastOrderStates[orderId];

        if (prev) {
            if (prev.status !== currentStatus) {
                hasChanges = true;
                showStatusNotif(order, prev.status, currentStatus);
                playNotificationSound();
                updateNotificationBadge();
                console.log('Status berubah:', order.invoice, prev.status, '->', currentStatus);
            }
            if (prev.payment_status !== currentPayment) {
                hasChanges = true;
                showPaymentNotif(order, prev.payment_status, currentPayment);
                playNotificationSound();
                updateNotificationBadge();
                console.log('Payment berubah:', order.invoice, prev.payment_status, '->', currentPayment);
            }
        }

        // Simpan state terbaru
        lastOrderStates[orderId] = {
            status:         currentStatus,
            payment_status: currentPayment,
            invoice:        order.invoice
        };
    });

    updateDashboardUI(orders);
}

// ============================================
// NOTIFIKASI TOAST
// ============================================

function showStatusNotif(order, oldStatus, newStatus) {
    if (typeof Swal === 'undefined') return;

    const labels = { pending: 'Pending', proses: 'Proses', selesai: 'Selesai', diambil: 'Diambil' };
    const emojis  = { pending: '⏳', proses: '🧺', selesai: '✨', diambil: '📦' };

    Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 5000,
        timerProgressBar: true,
        didOpen: toast => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
    }).fire({
        icon: 'info',
        title: (emojis[newStatus] || '') + ' Status Pesanan Diperbarui',
        html: '<p style="font-weight:bold;color:#1f2937">' + escapeHtml(order.invoice) + '</p>' +
              '<p style="font-size:13px;color:#6b7280;margin-top:4px">' +
              (labels[oldStatus] || oldStatus) + ' &rarr; <strong>' +
              (labels[newStatus] || newStatus) + '</strong></p>'
    });
}

function showPaymentNotif(order, oldStatus, newStatus) {
    if (typeof Swal === 'undefined') return;

    const labels = {
        unpaid: 'Belum Dibayar', pending: 'Menunggu Pembayaran',
        paid: 'Lunas', success: 'Pembayaran Berhasil',
        failed: 'Gagal', expired: 'Kadaluarsa'
    };
    const emojis = { unpaid: '⏳', pending: '💳', paid: '✅', success: '✅', failed: '❌', expired: '⏰' };

    Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 5000,
        timerProgressBar: true
    }).fire({
        icon: ['paid', 'success'].includes(newStatus) ? 'success' : 'info',
        title: (emojis[newStatus] || '') + ' Status Pembayaran',
        html: '<p style="font-weight:bold;color:#1f2937">' + escapeHtml(order.invoice) + '</p>' +
              '<p style="font-size:13px;color:#6b7280;margin-top:4px">' +
              (labels[oldStatus] || oldStatus) + ' &rarr; <strong>' +
              (labels[newStatus] || newStatus) + '</strong></p>'
    });
}

function updateNotificationBadge() {
    const badge = document.getElementById('notificationBadge');
    if (badge) {
        badge.classList.remove('hidden');
        setTimeout(() => badge.classList.add('hidden'), 5000);
    }
}

// ============================================
// UI UPDATE — update kartu & stat
// ============================================

function updateDashboardUI(orders) {
    const visible = orders.filter(isOrderVisible);

    // Update stat cards
    updateStatCard('pending-orders', visible.filter(o => o.status === 'pending').length);
    updateStatCard('proses-orders',  visible.filter(o => o.status === 'proses').length);
    updateStatCard('selesai-orders', visible.filter(o => o.status === 'selesai').length);
    updateStatCard('diambil-orders', visible.filter(o => o.status === 'diambil').length);

    // Active orders = semua kecuali cancelled, tampilkan semua status
    const activeOrders = visible.filter(o => o.status !== 'cancelled');
    updateActiveOrdersSection(activeOrders);

    // Update counter badge di header "Pesanan Aktif"
    const badge = document.querySelector('.active-orders-count');
    if (badge) badge.textContent = activeOrders.length + ' Pesanan';
}

function updateStatCard(statType, value) {
    const el = document.querySelector('[data-stat="' + statType + '"]');
    if (!el) return;
    const current = parseInt(el.textContent.trim(), 10);
    if (current !== value) {
        el.textContent = value;
        el.classList.add('scale-110', 'text-blue-600');
        setTimeout(() => el.classList.remove('scale-110', 'text-blue-600'), 500);
    }
}

function updateActiveOrdersSection(activeOrders) {
    const container = document.getElementById('active-orders-container');
    if (!container) return;

    if (activeOrders.length === 0) {
        container.innerHTML =
            '<div class="text-center py-16">' +
            '<i class="fas fa-inbox text-gray-300 text-6xl mb-4"></i>' +
            '<p class="text-gray-500 text-lg mb-4 font-medium">Belum ada pesanan aktif</p>' +
            '<a href="/user/pemesanan" class="inline-block px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition">' +
            '<i class="fas fa-plus-circle mr-2"></i>Buat Pesanan Baru</a></div>';
        return;
    }

    // Hanya re-render jika ada perbedaan agar tidak flicker
    const currentIds = Array.from(container.querySelectorAll('[data-order-id]'))
        .map(el => el.dataset.orderId).sort().join(',');
    const newIds = activeOrders.map(o => String(o.id)).sort().join(',');

    if (currentIds !== newIds) {
        // Ada pesanan baru/hilang — render ulang semua
        container.innerHTML = activeOrders.map(order => createOrderCard(order)).join('');
    } else {
        // Update status setiap kartu yang berubah saja
        activeOrders.forEach(order => {
            const card = container.querySelector('[data-order-id="' + order.id + '"]');
            if (!card) return;

            const prevStatus = card.dataset.status;
            if (prevStatus !== order.status) {
                // Ganti kartu ini saja
                card.outerHTML = createOrderCard(order);
            }
        });
    }
}

// ============================================
// ORDER CARD HTML BUILDER
// ============================================

const SERVICE_LABELS = {
    'cuci_kering':  'Cuci Kering',
    'cuci_setrika': 'Cuci & Setrika',
    'setrika_saja': 'Setrika Saja'
};

const STATUS_CFG = {
    pending: { bg: 'bg-yellow-100', text: 'text-yellow-800', label: 'Pending',  icon: 'clock' },
    proses:  { bg: 'bg-blue-100',   text: 'text-blue-800',   label: 'Proses',   icon: 'sync' },
    selesai: { bg: 'bg-green-100',  text: 'text-green-800',  label: 'Selesai',  icon: 'check-double' },
    diambil: { bg: 'bg-gray-100',   text: 'text-gray-800',   label: 'Diambil',  icon: 'box' }
};

function createOrderCard(order) {
    const s           = STATUS_CFG[order.status] || STATUS_CFG['pending'];
    const isPaid      = ['paid', 'success'].includes(order.payment_status);
    const methodIcon  = order.payment_method === 'midtrans' ? 'credit-card' : 'money-bill-wave';
    const methodLabel = order.payment_method === 'midtrans' ? 'Online' : 'Tunai';
    const serviceName = SERVICE_LABELS[order.service_type] || order.service_type;

    return '<div class="order-card border-2 border-gray-200 rounded-xl p-4 md:p-5 hover:shadow-lg hover:border-blue-300 transition transform hover:-translate-y-1 cursor-pointer"' +
        ' data-order-id="' + order.id + '"' +
        ' data-status="' + escapeAttr(order.status) + '"' +
        ' data-payment-status="' + escapeAttr(order.payment_status || 'unpaid') + '"' +
        ' onclick="viewDetail(' + order.id + ')">' +

        '<div class="flex justify-between items-start mb-3 md:mb-4">' +
        '<div>' +
        '<p class="font-bold text-base md:text-lg text-gray-800">' + escapeHtml(order.invoice) + '</p>' +
        '<p class="text-xs md:text-sm text-gray-500 mt-1"><i class="far fa-calendar mr-1"></i>' + formatDate(order.created_at) + '</p>' +
        '</div>' +
        '<span class="px-3 py-1.5 rounded-full text-xs font-bold ' + s.bg + ' ' + s.text + '">' +
        '<i class="fas fa-' + s.icon + ' mr-1"></i>' + s.label + '</span>' +
        '</div>' +

        '<div class="grid grid-cols-2 gap-3 md:gap-4 text-sm mb-4">' +
        '<div class="bg-gray-50 rounded-lg p-3 border">' +
        '<p class="text-xs text-gray-500 mb-1"><i class="fas fa-spray-can mr-1"></i>Layanan</p>' +
        '<p class="font-semibold text-gray-800">' + escapeHtml(serviceName) + '</p></div>' +
        '<div class="bg-gray-50 rounded-lg p-3 border">' +
        '<p class="text-xs text-gray-500 mb-1"><i class="fas fa-weight mr-1"></i>Berat</p>' +
        '<p class="font-semibold text-gray-800">' + parseFloat(order.weight).toFixed(1) + ' kg</p></div>' +
        '</div>' +

        (order.is_express
            ? '<div class="mb-3 bg-yellow-50 border border-yellow-200 rounded-lg p-2">' +
              '<p class="text-xs text-yellow-800 font-semibold"><i class="fas fa-bolt text-yellow-600 mr-1"></i>Layanan Express (24 Jam)</p></div>'
            : '') +

        '<div class="flex justify-between items-center pt-3 md:pt-4 border-t-2 border-gray-100">' +
        '<div>' +
        '<p class="text-xs text-gray-500 mb-1">Total Pembayaran</p>' +
        '<p class="font-bold text-lg md:text-xl text-blue-600">Rp ' + formatNumber(order.total) + '</p>' +
        '<p class="text-xs text-gray-500 mt-1"><i class="fas fa-' + methodIcon + ' mr-1"></i>' + methodLabel +
        (isPaid ? ' <span class="text-green-600 font-semibold">• Lunas</span>' : '') + '</p>' +
        '</div>' +
        '<button onclick="event.stopPropagation(); viewDetail(' + order.id + ')" ' +
        'class="detail-btn px-4 md:px-6 py-2 md:py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white text-sm font-bold rounded-lg hover:from-blue-700 hover:to-blue-800 transition shadow-md">' +
        '<i class="fas fa-eye mr-2"></i>Detail</button>' +
        '</div></div>';
}

// ============================================
// DETAIL MODAL
// ============================================

function viewDetail(orderId) {
    const modal   = document.getElementById('detailModal');
    const content = document.getElementById('detailContent');
    if (!modal || !content) return;

    modal.classList.remove('hidden');
    modal.classList.add('flex');
    content.innerHTML =
        '<div class="text-center py-8">' +
        '<i class="fas fa-spinner fa-spin text-blue-600 text-4xl mb-2"></i>' +
        '<p class="text-gray-600">Memuat data...</p></div>';

    fetch('/api/user/orders/' + orderId, {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin'
    })
    .then(res => {
        if (!res.ok) throw new Error('HTTP ' + res.status);
        return res.json();
    })
    .then(data => {
        if (data.success && data.data) {
            content.innerHTML = createDetailModalContent(data.data);
        } else {
            throw new Error(data.message || 'Gagal memuat data');
        }
    })
    .catch(err => {
        content.innerHTML =
            '<div class="text-center py-8 text-red-600">' +
            '<i class="fas fa-exclamation-circle text-4xl mb-2"></i>' +
            '<p class="font-semibold">Gagal memuat detail pesanan</p>' +
            '<p class="text-sm text-gray-500 mt-1">' + escapeHtml(err.message) + '</p></div>';
    });
}

function createDetailModalContent(order) {
    const s           = STATUS_CFG[order.status] || STATUS_CFG['pending'];
    const isPaid      = ['paid', 'success'].includes(order.payment_status);
    const methodIcon  = order.payment_method === 'midtrans' ? 'credit-card' : 'money-bill-wave';
    const methodLabel = order.payment_method === 'midtrans' ? 'Pembayaran Online' : 'Bayar Tunai';
    const serviceName = SERVICE_LABELS[order.service_type] || order.service_type;

    const colorMap = { yellow: 'yellow', blue: 'blue', green: 'green', gray: 'gray' };
    const c = colorMap[s.text.replace('text-', '').replace('-800', '')] || 'gray';

    return '<div class="space-y-6">' +

        '<div class="text-center bg-' + c + '-50 rounded-xl p-4 border-2 border-' + c + '-200">' +
        '<i class="fas fa-' + s.icon + ' text-' + c + '-600 text-3xl mb-2"></i>' +
        '<p class="font-bold text-' + c + '-800 text-lg">' + s.label + '</p>' +
        '<p class="text-xs text-' + c + '-600 mt-1">Status Pesanan</p></div>' +

        '<div class="text-center bg-blue-50 rounded-xl p-4">' +
        '<p class="text-sm text-gray-600 mb-1">Nomor Invoice</p>' +
        '<p class="text-2xl font-bold text-gray-800">' + escapeHtml(order.invoice) + '</p></div>' +

        '<div class="space-y-3">' +
        '<div class="flex justify-between py-3 border-b">' +
        '<span class="text-gray-600"><i class="fas fa-spray-can mr-2 text-blue-600"></i>Layanan:</span>' +
        '<span class="font-bold text-gray-800">' + escapeHtml(serviceName) + '</span></div>' +

        '<div class="flex justify-between py-3 border-b">' +
        '<span class="text-gray-600"><i class="fas fa-weight mr-2 text-blue-600"></i>Berat:</span>' +
        '<span class="font-bold text-gray-800">' + parseFloat(order.weight).toFixed(1) + ' kg</span></div>' +

        (order.is_express
            ? '<div class="flex justify-between py-3 border-b"><span class="text-gray-600"><i class="fas fa-bolt mr-2 text-yellow-600"></i>Express:</span><span class="font-bold text-yellow-600">24 Jam</span></div>'
            : '') +

        '<div class="flex justify-between py-3 border-b">' +
        '<span class="text-gray-600"><i class="fas fa-' + methodIcon + ' mr-2 text-blue-600"></i>Pembayaran:</span>' +
        '<span class="font-bold text-gray-800">' + methodLabel +
        (isPaid ? ' <span class="text-green-600 ml-2">&#10003; Lunas</span>' : '') + '</span></div>' +

        '<div class="flex justify-between py-3 border-b">' +
        '<span class="text-gray-600"><i class="fas fa-map-marker-alt mr-2 text-blue-600"></i>Alamat:</span>' +
        '<span class="font-semibold text-gray-800 text-right max-w-xs">' + escapeHtml(order.address || '-') + '</span></div>' +

        (order.notes
            ? '<div class="py-3 border-b"><p class="text-gray-600 mb-2"><i class="fas fa-sticky-note mr-2 text-blue-600"></i>Catatan:</p>' +
              '<p class="font-semibold text-gray-800 bg-gray-50 rounded p-2">' + escapeHtml(order.notes) + '</p></div>'
            : '') +

        '<div class="flex justify-between py-3 border-b">' +
        '<span class="text-gray-600"><i class="far fa-calendar mr-2 text-blue-600"></i>Tanggal Pesan:</span>' +
        '<span class="font-semibold text-gray-800">' + formatDate(order.created_at) + '</span></div>' +
        '</div>' +

        '<div class="bg-gray-50 rounded-xl p-4 space-y-2">' +
        '<div class="flex justify-between text-sm">' +
        '<span class="text-gray-600">Subtotal:</span>' +
        '<span class="font-semibold">Rp ' + formatNumber(order.subtotal || order.total) + '</span></div>' +

        (order.delivery_fee && order.delivery_fee > 0
            ? '<div class="flex justify-between text-sm"><span class="text-gray-600">Biaya Antar-Jemput:</span>' +
              '<span class="font-semibold">Rp ' + formatNumber(order.delivery_fee) + '</span></div>'
            : '') +

        (order.is_express
            ? '<div class="flex justify-between text-sm"><span class="text-gray-600">Biaya Express:</span>' +
              '<span class="font-semibold text-yellow-600">Rp ' + formatNumber(order.weight * 10000) + '</span></div>'
            : '') +

        '<div class="flex justify-between pt-2 border-t-2 border-gray-300">' +
        '<span class="font-bold text-gray-800">Total:</span>' +
        '<span class="font-bold text-xl text-blue-600">Rp ' + formatNumber(order.total) + '</span></div>' +
        '</div></div>';
}

function closeDetailModal() {
    const modal = document.getElementById('detailModal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
}

// ============================================
// MONITORING STATUS INDICATOR
// ============================================

function showMonitoringStatus() {
    const el = document.createElement('div');
    el.className = 'fixed bottom-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg flex items-center space-x-2 z-50';
    el.innerHTML = '<div class="w-2 h-2 bg-white rounded-full animate-pulse"></div>' +
                   '<span class="text-sm font-semibold">Monitoring Aktif</span>';
    document.body.appendChild(el);
    setTimeout(() => {
        el.style.transition = 'opacity 0.5s';
        el.style.opacity = '0';
        setTimeout(() => el.remove(), 500);
    }, 3000);
}

// ============================================
// HELPERS
// ============================================

function initializeOrderStates() {
    document.querySelectorAll('#active-orders-container [data-order-id]').forEach(el => {
        lastOrderStates[el.dataset.orderId] = {
            status:         el.dataset.status || 'pending',
            payment_status: el.dataset.paymentStatus || 'unpaid'
        };
    });
    console.log('Initialized', Object.keys(lastOrderStates).length, 'order states from DOM');
}

function formatNumber(num) {
    return new Intl.NumberFormat('id-ID').format(num || 0);
}

function formatDate(dateString) {
    if (!dateString) return '-';
    try {
        return new Date(dateString).toLocaleDateString('id-ID', {
            year: 'numeric', month: 'short', day: 'numeric',
            hour: '2-digit', minute: '2-digit'
        });
    } catch (e) { return dateString; }
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function escapeAttr(str) {
    if (!str) return '';
    return String(str).replace(/"/g, '&quot;');
}

function setupUserMenuToggle() {
    const btn  = document.getElementById('userMenuBtn');
    const drop = document.getElementById('userDropdown');
    const icon = document.getElementById('dropdownIcon');
    if (!btn || !drop) return;

    btn.addEventListener('click', e => {
        e.stopPropagation();
        drop.classList.toggle('hidden');
        if (icon) icon.style.transform = drop.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(180deg)';
    });
    document.addEventListener('click', e => {
        if (!btn.contains(e.target) && !drop.contains(e.target)) {
            drop.classList.add('hidden');
            if (icon) icon.style.transform = 'rotate(0deg)';
        }
    });
}

function setupMobileMenu() {
    const btn  = document.getElementById('mobileMenuBtn');
    const menu = document.getElementById('mobileMenu');
    if (btn && menu) btn.addEventListener('click', () => menu.classList.toggle('hidden'));
}

function setupScrollFunctions() {
    window.scrollToOrders = () => {
        document.getElementById('active-orders-container')
            ?.parentElement.scrollIntoView({ behavior: 'smooth' });
    };
    window.scrollToHistory = () => {
        document.getElementById('riwayat')?.scrollIntoView({ behavior: 'smooth' });
    };
}

// ============================================
// VISIBILITY CHANGE — resume saat tab aktif kembali
// ============================================

document.addEventListener('visibilitychange', () => {
    if (!document.hidden) {
        if (!isMonitoring && apiAvailable) {
            startRealTimeMonitoring();
        } else if (isMonitoring) {
            // Langsung fetch tanpa tunggu interval berikutnya
            fetchOrderUpdates();
        }
    }
});

// ============================================
// CLEANUP
// ============================================

window.addEventListener('beforeunload', stopRealTimeMonitoring);

// Debug di console browser
window.dashboardMonitoring = {
    start:     startRealTimeMonitoring,
    stop:      stopRealTimeMonitoring,
    fetchNow:  fetchOrderUpdates,
    testSound: playNotificationSound,
    status:    () => ({
        isMonitoring,
        apiAvailable,
        audioUnlocked,
        isFetching,
        orderCount: Object.keys(lastOrderStates).length,
        states: lastOrderStates
    })
};

console.log('Dashboard v4.0 loaded — monitoring will start automatically');