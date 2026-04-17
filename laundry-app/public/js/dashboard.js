// ============================================
// DASHBOARD REAL-TIME MONITORING SYSTEM
// Version 3.2 - Fixed: Filter midtrans unpaid orders
// ============================================

let refreshInterval;
let lastOrderStates = {};
let notificationSound;
let isMonitoring = false;
let apiAvailable = true;

document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Dashboard Real-time Monitoring v3.2 initialized');

    initializeOrderStates();
    setupNotificationSound();
    setupUserMenuToggle();
    setupMobileMenu();
    setupScrollFunctions();
    setupDetailButtonHandlers();

    setTimeout(() => {
        checkApiAvailability();
    }, 2000);
});

// ============================================
// FILTER: Hanya tampilkan pesanan yang valid
// Cash selalu tampil, Midtrans hanya jika sudah paid/pending/success
// ============================================

function isOrderVisible(order) {
    if (!order.payment_method || order.payment_method === 'cash') return true;
    return ['paid', 'pending', 'success'].includes(order.payment_status);
}

// ============================================
// API AVAILABILITY CHECK
// ============================================

function checkApiAvailability() {
    console.log('🔍 Checking API availability...');

    fetch('/api/user/orders-status', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        credentials: 'same-origin'
    })
    .then(response => {
        if (response.status === 404) {
            apiAvailable = false;
            console.warn('⚠️ API endpoint not available. Real-time monitoring disabled.');
            return null;
        }
        apiAvailable = true;
        return response.json();
    })
    .then(data => {
        if (data && apiAvailable) {
            console.log('✅ API is available. Starting real-time monitoring...');
            startRealTimeMonitoring();
        }
    })
    .catch(error => {
        console.error('❌ API check failed:', error);
        apiAvailable = false;
    });
}

// ============================================
// REAL-TIME MONITORING CORE
// ============================================

function startRealTimeMonitoring() {
    if (!apiAvailable || isMonitoring) return;

    console.log('⏰ Starting real-time monitoring...');
    isMonitoring = true;

    fetchOrderUpdates();
    refreshInterval = setInterval(fetchOrderUpdates, 10000);
    showMonitoringStatus();
}

function stopRealTimeMonitoring() {
    if (refreshInterval) {
        clearInterval(refreshInterval);
        refreshInterval = null;
        isMonitoring = false;
        console.log('⏹️ Real-time monitoring stopped');
    }
}

function fetchOrderUpdates() {
    if (!apiAvailable) return;

    fetch('/api/user/orders-status', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        credentials: 'same-origin'
    })
    .then(response => {
        if (!response.ok) {
            if (response.status === 404) {
                apiAvailable = false;
                stopRealTimeMonitoring();
            }
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success && data.data) {
            processOrderUpdates(data.data);
        }
    })
    .catch(error => console.error('❌ Error fetching updates:', error));
}

function processOrderUpdates(orders) {
    let hasChanges = false;

    orders.forEach(order => {
        const orderId = order.id;
        const currentStatus = order.status;
        const currentPaymentStatus = order.payment_status || 'unpaid';
        const previousState = lastOrderStates[orderId];

        if (previousState) {
            if (previousState.status !== currentStatus) {
                hasChanges = true;
                showStatusChangeNotification(order, previousState.status, currentStatus);
                playNotificationSound();
                updateNotificationBadge();
            }
            if (previousState.payment_status !== currentPaymentStatus) {
                hasChanges = true;
                showPaymentStatusNotification(order, previousState.payment_status, currentPaymentStatus);
                playNotificationSound();
                updateNotificationBadge();
            }
        }

        lastOrderStates[orderId] = {
            status: currentStatus,
            payment_status: currentPaymentStatus,
            invoice: order.invoice,
            timestamp: Date.now()
        };
    });

    updateDashboardUI(orders);

    if (hasChanges) setupDetailButtonHandlers();
}

// ============================================
// NOTIFICATION SYSTEM
// ============================================

function showStatusChangeNotification(order, oldStatus, newStatus) {
    const statusMessages = {
        'pending': 'Pesanan Baru',
        'proses':  'Sedang Diproses',
        'selesai': 'Siap Diambil',
        'diambil': 'Sudah Diambil'
    };
    const statusEmojis = { 'pending': '⏳', 'proses': '🧺', 'selesai': '✨', 'diambil': '📦' };

    if (typeof Swal === 'undefined') return;

    Swal.mixin({
        toast: true, position: 'top-end', showConfirmButton: false,
        timer: 5000, timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
    }).fire({
        icon: 'info',
        title: `${statusEmojis[newStatus]} Status Update`,
        html: `<div class="text-left"><p class="font-bold text-gray-800">${order.invoice}</p>
               <p class="text-sm text-gray-600 mt-1">${statusMessages[oldStatus]} → ${statusMessages[newStatus]}</p></div>`
    });
}

function showPaymentStatusNotification(order, oldPaymentStatus, newPaymentStatus) {
    const paymentMessages = {
        'unpaid': 'Belum Dibayar', 'pending': 'Belum Dibayar',
        'paid': 'Sudah Dibayar', 'success': 'Pembayaran Berhasil',
        'failed': 'Pembayaran Gagal', 'expired': 'Pembayaran Kadaluarsa'
    };
    const paymentEmojis = {
        'unpaid': '⏳', 'pending': '💳', 'paid': '✅',
        'success': '✅', 'failed': '❌', 'expired': '⏰'
    };

    if (typeof Swal === 'undefined') return;

    Swal.mixin({
        toast: true, position: 'top-end', showConfirmButton: false, timer: 5000, timerProgressBar: true
    }).fire({
        icon: ['paid', 'success'].includes(newPaymentStatus) ? 'success' : 'info',
        title: `${paymentEmojis[newPaymentStatus]} Status Pembayaran`,
        html: `<div class="text-left"><p class="font-bold text-gray-800">${order.invoice}</p>
               <p class="text-sm text-gray-600 mt-1">${paymentMessages[oldPaymentStatus]} → ${paymentMessages[newPaymentStatus]}</p></div>`
    });
}

function playNotificationSound() {
    if (notificationSound) notificationSound.play().catch(() => {});
}

function setupNotificationSound() {
    try {
        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
        notificationSound = {
            play: function() {
                return new Promise((resolve, reject) => {
                    try {
                        const oscillator = audioContext.createOscillator();
                        const gainNode = audioContext.createGain();
                        oscillator.connect(gainNode);
                        gainNode.connect(audioContext.destination);
                        oscillator.frequency.value = 800;
                        oscillator.type = 'sine';
                        gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
                        gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);
                        oscillator.start(audioContext.currentTime);
                        oscillator.stop(audioContext.currentTime + 0.5);
                        resolve();
                    } catch (error) { reject(error); }
                });
            }
        };
    } catch (error) {
        notificationSound = null;
    }
}

function updateNotificationBadge() {
    const badge = document.getElementById('notificationBadge');
    if (badge) {
        badge.classList.remove('hidden');
        setTimeout(() => badge.classList.add('hidden'), 3000);
    }
}

// ============================================
// UI UPDATE FUNCTIONS
// ============================================

function updateDashboardUI(orders) {
    // Filter: hanya hitung & tampilkan pesanan yang valid
    const visibleOrders = orders.filter(isOrderVisible);

    updateStatCard('total-orders', visibleOrders.length);
    updateStatCard('processing-orders',
        visibleOrders.filter(o => ['pending', 'proses'].includes(o.status)).length);
    updateStatCard('ready-orders',
        visibleOrders.filter(o => o.status === 'selesai').length);
    updateStatCard('total-spent',
        `Rp ${formatNumber(visibleOrders.reduce((s, o) => s + parseFloat(o.total || 0), 0))}`);

    const activeOrders = visibleOrders.filter(o => !['diambil', 'cancelled'].includes(o.status));
    updateActiveOrdersSection(activeOrders);
}

function updateStatCard(statType, value) {
    const element = document.querySelector(`[data-stat="${statType}"]`);
    if (element && element.textContent.trim() !== value.toString()) {
        element.classList.add('scale-110', 'text-blue-600');
        element.textContent = value;
        setTimeout(() => element.classList.remove('scale-110', 'text-blue-600'), 500);
    }
}

function updateActiveOrdersSection(activeOrders) {
    const container = document.getElementById('active-orders-container');
    if (!container) return;

    if (activeOrders.length === 0) {
        container.innerHTML = `
            <div class="text-center py-12 md:py-16">
                <i class="fas fa-inbox text-gray-300 text-5xl md:text-6xl mb-4"></i>
                <p class="text-gray-500 text-base md:text-lg mb-4 font-medium">Belum ada pesanan aktif</p>
                <a href="/user/pemesanan" class="inline-block px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-bold rounded-lg hover:from-blue-700 hover:to-blue-800 transition shadow-lg transform hover:scale-105">
                    <i class="fas fa-plus-circle mr-2"></i>Buat Pesanan Baru
                </a>
            </div>`;
        return;
    }

    container.innerHTML = activeOrders.map(order => createOrderCard(order)).join('');
    setupDetailButtonHandlers();
}

function createOrderCard(order) {
    const statusConfig = {
        'pending': { bg: 'bg-yellow-100', text: 'text-yellow-800', label: 'Pesanan Baru', icon: 'clock' },
        'proses':  { bg: 'bg-blue-100',   text: 'text-blue-800',   label: 'Sedang Diproses',    icon: 'sync' },
        'selesai': { bg: 'bg-green-100',  text: 'text-green-800',  label: 'Siap Diambil',        icon: 'check-double' }
    };
    const paymentStatusConfig = {
        'unpaid':  { bg: 'bg-red-100',    text: 'text-red-700',    label: 'Belum Bayar',    icon: 'exclamation-circle' },
        'pending': { bg: 'bg-yellow-100', text: 'text-yellow-700', label: 'Belum Bayar',       icon: 'clock' },
        'paid':    { bg: 'bg-green-100',  text: 'text-green-700',  label: 'Lunas',          icon: 'check-circle' },
        'success': { bg: 'bg-green-100',  text: 'text-green-700',  label: 'Lunas',          icon: 'check-circle' }
    };

    const status = statusConfig[order.status] || statusConfig['pending'];
    const paymentStatus = paymentStatusConfig[order.payment_status || 'unpaid'];
    const isPaidOrSuccess = ['paid', 'success'].includes(order.payment_status);
    const showPaymentStatus = false;
    const paymentMethodIcon  = order.payment_method === 'midtrans' ? 'credit-card' : 'money-bill-wave';
    const paymentMethodLabel = order.payment_method === 'midtrans' ? 'Online' : 'Tunai';

    return `
        <div class="border-2 border-gray-200 rounded-xl p-4 md:p-5 hover:shadow-lg hover:border-blue-300 transition transform hover:-translate-y-1 cursor-pointer order-card"
             data-order-id="${order.id}" onclick="viewDetail(${order.id})">
            <div class="flex justify-between items-start mb-3 md:mb-4">
                <div>
                    <p class="font-bold text-base md:text-lg text-gray-800">${order.invoice}</p>
                    <p class="text-xs md:text-sm text-gray-500 mt-1">
                        <i class="far fa-calendar mr-1"></i>${formatDate(order.created_at)}
                    </p>
                </div>
                <div class="flex flex-col gap-1">
                    <span class="px-3 py-1.5 rounded-full text-xs font-bold ${status.bg} ${status.text}" data-status="${order.status}">
                        <i class="fas fa-${status.icon} mr-1"></i>${status.label}
                    </span>
                    ${showPaymentStatus ? `
                    <span class="px-3 py-1.5 rounded-full text-xs font-bold ${paymentStatus.bg} ${paymentStatus.text}" data-payment-status="${order.payment_status}">
                        <i class="fas fa-${paymentStatus.icon} mr-1"></i>${paymentStatus.label}
                    </span>` : ''}
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 md:gap-4 text-sm mb-4">
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="text-xs text-gray-500 mb-1"><i class="fas fa-spray-can mr-1"></i>Layanan</p>
                    <p class="font-bold text-gray-800">${order.service_type}</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="text-xs text-gray-500 mb-1"><i class="fas fa-weight mr-1"></i>Berat</p>
                    <p class="font-bold text-gray-800">${order.weight} kg</p>
                </div>
            </div>

            ${order.is_express ? `
            <div class="mb-3 bg-yellow-50 border border-yellow-200 rounded-lg p-2">
                <p class="text-xs text-yellow-800 font-semibold">
                    <i class="fas fa-bolt text-yellow-600 mr-1"></i>Layanan Express (24 Jam)
                </p>
            </div>` : ''}

            <div class="flex justify-between items-center pt-3 md:pt-4 border-t-2 border-gray-100">
                <div>
                    <p class="text-xs text-gray-500 mb-1">Total Pembayaran</p>
                    <p class="font-bold text-lg md:text-xl text-blue-600">Rp ${formatNumber(order.total)}</p>
                    <p class="text-xs text-gray-500 mt-1">
                        <i class="fas fa-${paymentMethodIcon} mr-1"></i>${paymentMethodLabel}
                        ${isPaidOrSuccess ? '<span class="text-green-600 font-semibold ml-1">• Lunas</span>' : ''}
                    </p>
                </div>
                <button onclick="event.stopPropagation(); viewDetail(${order.id})"
                        class="detail-btn px-4 md:px-6 py-2 md:py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white text-sm font-bold rounded-lg hover:from-blue-700 hover:to-blue-800 transition shadow-md hover:shadow-lg transform hover:scale-105">
                    <i class="fas fa-eye mr-2"></i>Detail
                </button>
            </div>

        </div>`;
}

// ============================================
// DETAIL BUTTON HANDLERS
// ============================================

function setupDetailButtonHandlers() {
    document.querySelectorAll('.detail-btn, .order-card').forEach(el => {
        el.style.cursor = 'pointer';
    });
}

// ============================================
// PAYMENT FUNCTIONS
// ============================================

function continuePayment(snapToken, invoice) {
    if (typeof window.snap === 'undefined') {
        Swal.fire({
            icon: 'error', title: 'Midtrans Tidak Tersedia',
            text: 'Sistem pembayaran tidak dapat dimuat. Silakan refresh halaman.',
            confirmButtonColor: '#3B82F6'
        });
        return;
    }

    window.snap.pay(snapToken, {
        onSuccess: function(result) {
            Swal.fire({
                icon: 'success', title: 'Pembayaran Berhasil!',
                html: `<p>Pesanan <strong>${invoice}</strong> telah dibayar.</p>`,
                confirmButtonColor: '#10B981'
            }).then(() => fetchOrderUpdates());
        },
        onPending: function(result) {
            Swal.fire({
                icon: 'info', title: 'Belum Dibayar',
                html: `<p>Pembayaran <strong>${invoice}</strong> sedang diproses.</p>`,
                confirmButtonColor: '#3B82F6'
            });
        },
        onError: function(result) {
            Swal.fire({
                icon: 'error', title: 'Pembayaran Gagal',
                text: result.status_message || 'Terjadi kesalahan',
                confirmButtonColor: '#EF4444'
            });
        }
    });
}

// ============================================
// VIEW DETAIL MODAL
// ============================================

function viewDetail(orderId) {
    const modal = document.getElementById('detailModal');
    const content = document.getElementById('detailContent');
    if (!modal || !content) return;

    modal.classList.remove('hidden');
    modal.classList.add('flex');

    content.innerHTML = `
        <div class="text-center py-8">
            <i class="fas fa-spinner fa-spin text-blue-600 text-4xl mb-2"></i>
            <p class="text-gray-600">Memuat data...</p>
        </div>`;

    if (!apiAvailable) {
        content.innerHTML = `
            <div class="text-center py-8 text-yellow-600">
                <i class="fas fa-exclamation-triangle text-4xl mb-2"></i>
                <p class="font-semibold">API Belum Tersedia</p>
            </div>`;
        return;
    }

    fetch(`/api/user/orders/${orderId}`, {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) throw new Error('Failed to fetch order details');
        return response.json();
    })
    .then(data => {
        if (data.success && data.data) content.innerHTML = createDetailModalContent(data.data);
        else throw new Error(data.message || 'Failed to load');
    })
    .catch(error => {
        content.innerHTML = `
            <div class="text-center py-8 text-red-600">
                <i class="fas fa-exclamation-circle text-4xl mb-2"></i>
                <p class="font-semibold">Gagal memuat detail pesanan</p>
                <p class="text-sm text-gray-600">${error.message}</p>
            </div>`;
    });
}

function createDetailModalContent(order) {
    const statusConfig = {
        'pending': { icon: 'clock',        color: 'yellow', label: 'Pesanan Baru' },
        'proses':  { icon: 'sync',         color: 'blue',   label: 'Sedang Diproses' },
        'selesai': { icon: 'check-double', color: 'green',  label: 'Siap Diambil' },
        'diambil': { icon: 'box',          color: 'gray',   label: 'Sudah Diambil' }
    };
    const paymentStatusConfig = {
        'unpaid':  { color: 'red',    label: 'Belum Dibayar',        icon: 'exclamation-circle' },
        'pending': { color: 'yellow', label: 'Belum Dibayar',  icon: 'clock' },
        'paid':    { color: 'green',  label: 'Sudah Dibayar',        icon: 'check-circle' },
        'success': { color: 'green',  label: 'Pembayaran Berhasil',  icon: 'check-circle' }
    };

    const status = statusConfig[order.status] || statusConfig['pending'];
    const paymentStatus = paymentStatusConfig[order.payment_status || 'unpaid'];
    const isPaidOrSuccess = ['paid', 'success'].includes(order.payment_status);
    const showPaymentStatus = false;
    const paymentMethodIcon  = order.payment_method === 'midtrans' ? 'credit-card' : 'money-bill-wave';
    const paymentMethodLabel = order.payment_method === 'midtrans' ? 'Pembayaran Online' : 'Bayar Tunai';

    return `
        <div class="space-y-6">
            <div class="grid grid-cols-1 ${showPaymentStatus ? 'md:grid-cols-2' : ''} gap-3">
                <div class="text-center bg-${status.color}-50 rounded-xl p-4 border-2 border-${status.color}-200">
                    <i class="fas fa-${status.icon} text-${status.color}-600 text-3xl mb-2"></i>
                    <p class="font-bold text-${status.color}-800">${status.label}</p>
                    <p class="text-xs text-${status.color}-600 mt-1">Status Pesanan</p>
                </div>
                ${showPaymentStatus ? `
                <div class="text-center bg-${paymentStatus.color}-50 rounded-xl p-4 border-2 border-${paymentStatus.color}-200">
                    <i class="fas fa-${paymentStatus.icon} text-${paymentStatus.color}-600 text-3xl mb-2"></i>
                    <p class="font-bold text-${paymentStatus.color}-800">${paymentStatus.label}</p>
                    <p class="text-xs text-${paymentStatus.color}-600 mt-1">Status Pembayaran</p>
                </div>` : ''}
            </div>

            <div class="text-center bg-gradient-to-r from-blue-50 to-blue-100 rounded-xl p-4">
                <p class="text-sm text-gray-600 mb-1">Nomor Invoice</p>
                <p class="text-2xl font-bold text-gray-800">${order.invoice}</p>
            </div>

            <div class="space-y-3">
                <div class="flex justify-between py-3 border-b">
                    <span class="text-gray-600"><i class="fas fa-spray-can mr-2 text-blue-600"></i>Layanan:</span>
                    <span class="font-bold text-gray-800">${order.service_type}</span>
                </div>
                <div class="flex justify-between py-3 border-b">
                    <span class="text-gray-600"><i class="fas fa-weight mr-2 text-blue-600"></i>Berat:</span>
                    <span class="font-bold text-gray-800">${order.weight} kg</span>
                </div>
                ${order.is_express ? `
                <div class="flex justify-between py-3 border-b">
                    <span class="text-gray-600"><i class="fas fa-bolt mr-2 text-yellow-600"></i>Express:</span>
                    <span class="font-bold text-yellow-600">24 Jam</span>
                </div>` : ''}
                <div class="flex justify-between py-3 border-b">
                    <span class="text-gray-600"><i class="fas fa-${paymentMethodIcon} mr-2 text-blue-600"></i>Pembayaran:</span>
                    <span class="font-bold text-gray-800">
                        ${paymentMethodLabel}
                        ${isPaidOrSuccess ? '<span class="text-green-600 ml-2">✓ Lunas</span>' : ''}
                    </span>
                </div>
                <div class="flex justify-between py-3 border-b">
                    <span class="text-gray-600"><i class="fas fa-map-marker-alt mr-2 text-blue-600"></i>Alamat:</span>
                    <span class="font-semibold text-gray-800 text-right max-w-xs">${order.address || '-'}</span>
                </div>
                ${order.notes ? `
                <div class="py-3 border-b">
                    <p class="text-gray-600 mb-2"><i class="fas fa-sticky-note mr-2 text-blue-600"></i>Catatan:</p>
                    <p class="font-semibold text-gray-800 bg-gray-50 rounded p-2">${order.notes}</p>
                </div>` : ''}
                <div class="flex justify-between py-3 border-b">
                    <span class="text-gray-600"><i class="far fa-calendar mr-2 text-blue-600"></i>Tanggal Pesan:</span>
                    <span class="font-semibold text-gray-800">${formatDate(order.created_at)}</span>
                </div>
            </div>

            <div class="bg-gradient-to-r from-gray-50 to-blue-50 rounded-xl p-4 space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Subtotal:</span>
                    <span class="font-semibold">Rp ${formatNumber(order.subtotal || order.total)}</span>
                </div>
                ${order.delivery_fee ? `
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Biaya Antar-Jemput:</span>
                    <span class="font-semibold">Rp ${formatNumber(order.delivery_fee)}</span>
                </div>` : ''}
                ${order.is_express ? `
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Biaya Express:</span>
                    <span class="font-semibold text-yellow-600">Rp ${formatNumber(order.weight * 10000)}</span>
                </div>` : ''}
                <div class="flex justify-between pt-2 border-t-2 border-gray-300">
                    <span class="font-bold text-gray-800">Total:</span>
                    <span class="font-bold text-xl text-blue-600">Rp ${formatNumber(order.total)}</span>
                </div>
            </div>

        </div>`;
}

function closeDetailModal() {
    const modal = document.getElementById('detailModal');
    if (modal) { modal.classList.add('hidden'); modal.classList.remove('flex'); }
}

// ============================================
// MONITORING STATUS INDICATOR
// ============================================

function showMonitoringStatus() {
    const indicator = document.createElement('div');
    indicator.className = 'fixed bottom-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg flex items-center space-x-2 z-50';
    indicator.innerHTML = `<div class="w-2 h-2 bg-white rounded-full animate-pulse"></div><span class="text-sm font-semibold">Monitoring Aktif</span>`;
    document.body.appendChild(indicator);
    setTimeout(() => { indicator.style.opacity = '0'; indicator.style.transition = 'all 0.5s'; setTimeout(() => indicator.remove(), 500); }, 3000);
}

// ============================================
// HELPER FUNCTIONS
// ============================================

function initializeOrderStates() {
    document.querySelectorAll('[data-order-id]').forEach(element => {
        const orderId = element.dataset.orderId;
        const statusEl = element.querySelector('[data-status]');
        const paymentEl = element.querySelector('[data-payment-status]');
        if (statusEl) {
            lastOrderStates[orderId] = {
                status: statusEl.dataset.status,
                payment_status: paymentEl?.dataset.paymentStatus || 'unpaid',
                timestamp: Date.now()
            };
        }
    });
    console.log('📋 Initialized', Object.keys(lastOrderStates).length, 'order states');
}

function formatNumber(num) {
    return new Intl.NumberFormat('id-ID').format(num);
}

function formatDate(dateString) {
    try {
        return new Date(dateString).toLocaleDateString('id-ID', {
            year: 'numeric', month: 'short', day: 'numeric',
            hour: '2-digit', minute: '2-digit'
        });
    } catch (e) { return dateString; }
}

function setupUserMenuToggle() {
    const userMenuBtn = document.getElementById('userMenuBtn');
    const userDropdown = document.getElementById('userDropdown');
    const dropdownIcon = document.getElementById('dropdownIcon');

    if (userMenuBtn && userDropdown) {
        userMenuBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            userDropdown.classList.toggle('hidden');
            if (dropdownIcon) {
                dropdownIcon.style.transform = userDropdown.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(180deg)';
            }
        });
        document.addEventListener('click', (e) => {
            if (!userMenuBtn.contains(e.target) && !userDropdown.contains(e.target)) {
                userDropdown.classList.add('hidden');
                if (dropdownIcon) dropdownIcon.style.transform = 'rotate(0deg)';
            }
        });
    }
}

function setupMobileMenu() {
    const btn = document.getElementById('mobileMenuBtn');
    const menu = document.getElementById('mobileMenu');
    if (btn && menu) btn.addEventListener('click', () => menu.classList.toggle('hidden'));
}

function setupScrollFunctions() {
    window.scrollToOrders = function() {
        document.querySelector('#active-orders-container')?.parentElement.scrollIntoView({ behavior: 'smooth' });
    };
    window.scrollToHistory = function() {
        document.querySelector('#riwayat')?.scrollIntoView({ behavior: 'smooth' });
    };
}

// ============================================
// CLEANUP
// ============================================

document.addEventListener('visibilitychange', () => {
    if (!document.hidden && !isMonitoring && apiAvailable) startRealTimeMonitoring();
    else if (!document.hidden && apiAvailable) fetchOrderUpdates();
});

window.addEventListener('beforeunload', () => stopRealTimeMonitoring());

window.dashboardMonitoring = {
    start: startRealTimeMonitoring,
    stop: stopRealTimeMonitoring,
    fetchNow: fetchOrderUpdates,
    status: () => ({ isMonitoring, apiAvailable, orderCount: Object.keys(lastOrderStates).length })
};

console.log('✅ Dashboard v3.2 loaded');