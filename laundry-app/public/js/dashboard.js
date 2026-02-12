// ============================================
// DASHBOARD REAL-TIME MONITORING SYSTEM
// ============================================

let refreshInterval;
let lastOrderStates = {};
let notificationSound;

// Initialize real-time monitoring saat halaman load
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Real-time monitoring initialized');
    
    // Setup initial data
    initializeOrderStates();
    
    // Setup refresh interval (setiap 10 detik)
    startRealTimeMonitoring();
    
    // Setup notification sound
    setupNotificationSound();
    
    // Setup user interactions
    setupUserMenuToggle();
    setupMobileMenu();
    
    // Setup scroll functions
    setupScrollFunctions();
});

// ============================================
// REAL-TIME MONITORING CORE
// ============================================

function startRealTimeMonitoring() {
    console.log('⏰ Starting real-time monitoring...');
    
    // Initial fetch
    fetchOrderUpdates();
    
    // Set interval untuk auto-refresh (10 detik)
    refreshInterval = setInterval(() => {
        fetchOrderUpdates();
    }, 10000); // 10 detik
    
    // Visual indicator bahwa monitoring aktif
    showMonitoringStatus();
}

function fetchOrderUpdates() {
    console.log('🔄 Fetching order updates...');
    
    fetch('/api/user/orders-status', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('✅ Order updates received:', data);
            processOrderUpdates(data.data);
        }
    })
    .catch(error => {
        console.error('❌ Error fetching updates:', error);
    });
}

function processOrderUpdates(orders) {
    orders.forEach(order => {
        const orderId = order.id;
        const currentStatus = order.status;
        const previousState = lastOrderStates[orderId];
        
        // Cek apakah ada perubahan status
        if (previousState && previousState.status !== currentStatus) {
            console.log(`📢 Status changed for order #${order.invoice}: ${previousState.status} → ${currentStatus}`);
            
            // Show notification
            showStatusChangeNotification(order, previousState.status, currentStatus);
            
            // Play sound
            playNotificationSound();
            
            // Update badge
            updateNotificationBadge();
        }
        
        // Update state
        lastOrderStates[orderId] = {
            status: currentStatus,
            invoice: order.invoice,
            timestamp: Date.now()
        };
    });
    
    // Update UI
    updateDashboardUI(orders);
}

// ============================================
// NOTIFICATION SYSTEM
// ============================================

function showStatusChangeNotification(order, oldStatus, newStatus) {
    const statusMessages = {
        'pending': 'Menunggu Konfirmasi',
        'confirmed': 'Dikonfirmasi',
        'proses': 'Sedang Diproses',
        'selesai': 'Siap Diambil',
        'diambil': 'Sudah Diambil'
    };
    
    const statusEmojis = {
        'pending': '⏳',
        'confirmed': '✅',
        'proses': '🧺',
        'selesai': '✨',
        'diambil': '📦'
    };
    
    // Toast notification
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 5000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
    });
    
    Toast.fire({
        icon: 'info',
        title: `${statusEmojis[newStatus]} Status Update`,
        html: `
            <div class="text-left">
                <p class="font-bold text-gray-800">${order.invoice}</p>
                <p class="text-sm text-gray-600 mt-1">
                    ${statusMessages[oldStatus]} → ${statusMessages[newStatus]}
                </p>
            </div>
        `
    });
    
    // Update notification badge
    updateNotificationBadge();
}

function playNotificationSound() {
    if (notificationSound) {
        notificationSound.play().catch(e => console.log('Sound play prevented:', e));
    }
}

function setupNotificationSound() {
    // Create simple notification beep using Web Audio API
    const audioContext = new (window.AudioContext || window.webkitAudioContext)();
    
    notificationSound = {
        play: function() {
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
            
            return Promise.resolve();
        }
    };
}

function updateNotificationBadge() {
    const badge = document.getElementById('notificationBadge');
    if (badge) {
        badge.classList.remove('hidden');
        
        // Auto-hide after 3 seconds
        setTimeout(() => {
            badge.classList.add('hidden');
        }, 3000);
    }
}

// ============================================
// UI UPDATE FUNCTIONS
// ============================================

function updateDashboardUI(orders) {
    // Update stats
    const totalOrders = orders.length;
    const processingOrders = orders.filter(o => o.status === 'proses' || o.status === 'confirmed').length;
    const readyOrders = orders.filter(o => o.status === 'selesai').length;
    const totalSpent = orders.reduce((sum, o) => sum + parseFloat(o.total || 0), 0);
    
    // Update stat cards dengan animasi
    updateStatCard('total-orders', totalOrders);
    updateStatCard('processing-orders', processingOrders);
    updateStatCard('ready-orders', readyOrders);
    updateStatCard('total-spent', `Rp ${formatNumber(totalSpent)}`);
    
    // Update active orders section
    updateActiveOrdersSection(orders.filter(o => o.status !== 'diambil'));
}

function updateStatCard(elementId, value) {
    const element = document.querySelector(`[data-stat="${elementId}"]`);
    if (element) {
        const currentValue = element.textContent;
        if (currentValue !== value.toString()) {
            // Animate change
            element.classList.add('scale-110', 'text-blue-600');
            element.textContent = value;
            
            setTimeout(() => {
                element.classList.remove('scale-110', 'text-blue-600');
            }, 500);
        }
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
            </div>
        `;
        return;
    }
    
    container.innerHTML = activeOrders.map(order => createOrderCard(order)).join('');
}

function createOrderCard(order) {
    const statusConfig = {
        'pending': { bg: 'bg-yellow-100', text: 'text-yellow-800', label: 'Menunggu Konfirmasi' },
        'confirmed': { bg: 'bg-blue-100', text: 'text-blue-800', label: 'Dikonfirmasi' },
        'proses': { bg: 'bg-blue-100', text: 'text-blue-800', label: 'Sedang Diproses' },
        'selesai': { bg: 'bg-green-100', text: 'text-green-800', label: 'Siap Diambil' }
    };
    
    const status = statusConfig[order.status] || statusConfig['pending'];
    
    return `
        <div class="border-2 border-gray-200 rounded-xl p-4 md:p-5 hover:shadow-lg hover:border-blue-300 transition transform hover:-translate-y-1 animate-fadeIn" data-order-id="${order.id}">
            <div class="flex justify-between items-start mb-3 md:mb-4">
                <div>
                    <p class="font-bold text-base md:text-lg text-gray-800">${order.invoice}</p>
                    <p class="text-xs md:text-sm text-gray-500 mt-1">${formatDate(order.created_at)}</p>
                </div>
                <span class="px-3 py-1.5 rounded-full text-xs font-bold ${status.bg} ${status.text}">
                    ${status.label}
                </span>
            </div>
            <div class="grid grid-cols-2 gap-3 md:gap-4 text-sm mb-4">
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="text-xs text-gray-500 mb-1">Layanan</p>
                    <p class="font-bold text-gray-800">${order.service_type}</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="text-xs text-gray-500 mb-1">Berat</p>
                    <p class="font-bold text-gray-800">${order.weight} kg</p>
                </div>
            </div>
            <div class="flex justify-between items-center pt-3 md:pt-4 border-t-2 border-gray-100">
                <div>
                    <p class="text-xs text-gray-500 mb-1">Total Pembayaran</p>
                    <p class="font-bold text-lg md:text-xl text-blue-600">Rp ${formatNumber(order.total)}</p>
                </div>
                <button onclick="viewDetail(${order.id})" class="px-4 md:px-6 py-2 md:py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white text-sm font-bold rounded-lg hover:from-blue-700 hover:to-blue-800 transition shadow-md hover:shadow-lg transform hover:scale-105">
                    <i class="fas fa-eye mr-2"></i>Detail
                </button>
            </div>
        </div>
    `;
}

// ============================================
// MONITORING STATUS INDICATOR
// ============================================


    document.body.appendChild(indicator);
    
    // Auto-hide after 3 seconds
    setTimeout(() => {
        indicator.style.opacity = '0';
        indicator.style.transform = 'translateY(100px)';
        indicator.style.transition = 'all 0.5s ease';
        
        setTimeout(() => {
            indicator.remove();
        }, 500);
    }, 3000);


// ============================================
// HELPER FUNCTIONS
// ============================================

function initializeOrderStates() {
    // Get initial order states from page data
    const orderElements = document.querySelectorAll('[data-order-id]');
    orderElements.forEach(element => {
        const orderId = element.dataset.orderId;
        const statusElement = element.querySelector('[data-status]');
        if (statusElement) {
            lastOrderStates[orderId] = {
                status: statusElement.dataset.status,
                timestamp: Date.now()
            };
        }
    });
}

function formatNumber(num) {
    return new Intl.NumberFormat('id-ID').format(num);
}

function formatDate(dateString) {
    const date = new Date(dateString);
    const options = { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    };
    return date.toLocaleDateString('id-ID', options);
}

// ============================================
// EXISTING FUNCTIONS (dari dashboard.js)
// ============================================

function setupUserMenuToggle() {
    const userMenuBtn = document.getElementById('userMenuBtn');
    const userDropdown = document.getElementById('userDropdown');
    const dropdownIcon = document.getElementById('dropdownIcon');

    if (userMenuBtn && userDropdown) {
        userMenuBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            userDropdown.classList.toggle('hidden');
            if (dropdownIcon) {
                dropdownIcon.style.transform = userDropdown.classList.contains('hidden') 
                    ? 'rotate(0deg)' 
                    : 'rotate(180deg)';
            }
        });

        document.addEventListener('click', (e) => {
            if (!userMenuBtn.contains(e.target) && !userDropdown.contains(e.target)) {
                userDropdown.classList.add('hidden');
                if (dropdownIcon) {
                    dropdownIcon.style.transform = 'rotate(0deg)';
                }
            }
        });
    }
}

function setupMobileMenu() {
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const mobileMenu = document.getElementById('mobileMenu');

    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });
    }
}

function setupScrollFunctions() {
    window.scrollToOrders = function() {
        document.querySelector('#active-orders-container')?.parentElement.scrollIntoView({ 
            behavior: 'smooth' 
        });
    };

    window.scrollToHistory = function() {
        document.querySelector('#riwayat')?.scrollIntoView({ 
            behavior: 'smooth' 
        });
    };
}

function viewDetail(orderId) {
    const modal = document.getElementById('detailModal');
    const content = document.getElementById('detailContent');
    
    if (!modal || !content) return;
    
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    
    // Fetch detail
    fetch(`/api/user/orders/${orderId}`, {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        content.innerHTML = createDetailModalContent(data);
    })
    .catch(error => {
        content.innerHTML = `
            <div class="text-center py-8 text-red-600">
                <i class="fas fa-exclamation-circle text-4xl mb-2"></i>
                <p>Gagal memuat detail pesanan</p>
            </div>
        `;
    });
}

function createDetailModalContent(order) {
    const statusConfig = {
        'pending': { icon: 'clock', color: 'yellow', label: 'Menunggu Konfirmasi' },
        'confirmed': { icon: 'check-circle', color: 'blue', label: 'Dikonfirmasi' },
        'proses': { icon: 'sync', color: 'blue', label: 'Sedang Diproses' },
        'selesai': { icon: 'check-double', color: 'green', label: 'Siap Diambil' },
        'diambil': { icon: 'box', color: 'gray', label: 'Sudah Diambil' }
    };
    
    const status = statusConfig[order.status] || statusConfig['pending'];
    
    return `
        <div class="space-y-6">
            <!-- Status Badge -->
            <div class="text-center bg-${status.color}-50 rounded-xl p-4 border-2 border-${status.color}-200">
                <i class="fas fa-${status.icon} text-${status.color}-600 text-3xl mb-2"></i>
                <p class="font-bold text-${status.color}-800">${status.label}</p>
            </div>
            
            <!-- Invoice -->
            <div class="text-center">
                <p class="text-sm text-gray-500 mb-1">Nomor Invoice</p>
                <p class="text-2xl font-bold text-gray-800">${order.invoice}</p>
            </div>
            
            <!-- Details -->
            <div class="space-y-3">
                <div class="flex justify-between py-3 border-b">
                    <span class="text-gray-600">Layanan:</span>
                    <span class="font-bold text-gray-800">${order.service_type}</span>
                </div>
                <div class="flex justify-between py-3 border-b">
                    <span class="text-gray-600">Berat:</span>
                    <span class="font-bold text-gray-800">${order.weight} kg</span>
                </div>
                ${order.is_express ? `
                <div class="flex justify-between py-3 border-b">
                    <span class="text-gray-600">Express:</span>
                    <span class="font-bold text-yellow-600"><i class="fas fa-bolt mr-1"></i>24 Jam</span>
                </div>
                ` : ''}
                <div class="flex justify-between py-3 border-b">
                    <span class="text-gray-600">Alamat:</span>
                    <span class="font-semibold text-gray-800 text-right max-w-xs">${order.address || '-'}</span>
                </div>
                ${order.notes ? `
                <div class="py-3 border-b">
                    <p class="text-gray-600 mb-2">Catatan:</p>
                    <p class="font-semibold text-gray-800">${order.notes}</p>
                </div>
                ` : ''}
                <div class="flex justify-between py-3 border-b">
                    <span class="text-gray-600">Tanggal Pesan:</span>
                    <span class="font-semibold text-gray-800">${formatDate(order.created_at)}</span>
                </div>
            </div>
            
            <!-- Price Breakdown -->
            <div class="bg-gray-50 rounded-xl p-4 space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Subtotal:</span>
                    <span class="font-semibold">Rp ${formatNumber(order.subtotal)}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Biaya Antar:</span>
                    <span class="font-semibold">Rp ${formatNumber(order.delivery_fee)}</span>
                </div>
                <div class="flex justify-between pt-2 border-t-2 border-gray-200">
                    <span class="font-bold text-gray-800">Total:</span>
                    <span class="font-bold text-xl text-blue-600">Rp ${formatNumber(order.total)}</span>
                </div>
            </div>
        </div>
    `;
}

function closeDetailModal() {
    const modal = document.getElementById('detailModal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
}

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
});