/**
 * Pemesanan Laundry - Enhanced Version
 * Features: OpenStreetMap, Payment Integration, Better Validation
 * Version: 2.0
 */

let map, marker, geocoder;

document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Pemesanan Enhanced v2.0');
    
    const state = { selectedService: null, pricePerKg: 0, satuan: 'kg', duration: '2-3' };
    const FEES = { delivery: 5000, express: 10000 };
    const el = {
        form: document.getElementById('orderForm'),
        serviceOptions: document.querySelectorAll('.service-option'),
        weightInput: document.getElementById('weight'),
        decreaseBtn: document.getElementById('decreaseWeight'),
        increaseBtn: document.getElementById('increaseWeight'),
        expressCheckbox: document.getElementById('expressService'),
        addressInput: document.getElementById('address'),
        orderBtn: document.getElementById('orderBtn'),
        summaryService: document.getElementById('summaryService'),
        summaryWeight: document.getElementById('summaryWeight'),
        summaryPricePerKg: document.getElementById('summaryPricePerKg'),
        summaryExpress: document.getElementById('summaryExpress'),
        expressInfo: document.getElementById('expressInfo'),
        subtotal: document.getElementById('subtotal'),
        total: document.getElementById('total'),
        estimatedTime: document.getElementById('estimatedTime'),
        successModal: document.getElementById('successModal'),
        invoiceNumber: document.getElementById('invoiceNumber'),
        closeModal: document.getElementById('closeModal')
    };
    
    const hasSwal = typeof Swal !== 'undefined';
    const Alert = {
        loading: (msg = 'Memproses...') => hasSwal && Swal.fire({ title: 'Mohon Tunggu', html: msg, allowOutsideClick: false, showConfirmButton: false, didOpen: () => Swal.showLoading() }),
        error: (title, msg) => hasSwal ? Swal.fire({ icon: 'error', title, text: msg, confirmButtonColor: '#EF4444' }) : alert(`${title}: ${msg}`),
        warning: (title, msg) => hasSwal ? Swal.fire({ icon: 'warning', title, text: msg, confirmButtonColor: '#F59E0B' }) : alert(`${title}: ${msg}`),
        success: (title, msg) => hasSwal ? Swal.fire({ icon: 'success', title, text: msg, confirmButtonColor: '#10B981' }) : alert(`${title}: ${msg}`),
        close: () => hasSwal && Swal.isVisible() && Swal.close()
    };
    
    const fmt = n => n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    const formatDate = d => {
        const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        return `${days[d.getDay()]}, ${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear()}`;
    };
    
    // Initialize Map
    const defaultLat = -7.9666, defaultLng = 112.6326;
    console.log('🗺️ Init map...');
    
    try {
        map = L.map('map').setView([defaultLat, defaultLng], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap', maxZoom: 19 }).addTo(map);
        
        const customIcon = L.divIcon({
            className: 'custom-marker',
            html: '<div style="background: #3B82F6; width: 30px; height: 30px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 8px rgba(0,0,0,0.3);"></div>',
            iconSize: [30, 30],
            iconAnchor: [15, 15]
        });
        
        marker = L.marker([defaultLat, defaultLng], { draggable: true, icon: customIcon }).addTo(map);
        
        geocoder = L.Control.Geocoder.nominatim({ geocodingQueryParams: { countrycodes: 'id', limit: 5 } });
        L.Control.geocoder({
            geocoder,
            defaultMarkGeocode: false,
            placeholder: 'Cari alamat...',
            errorMessage: 'Alamat tidak ditemukan'
        })
        .on('markgeocode', e => {
            const latlng = e.geocode.center;
            map.setView(latlng, 16);
            marker.setLatLng(latlng);
            marker.bindPopup(e.geocode.name).openPopup();
            updateAddress(latlng.lat, latlng.lng, e.geocode.name);
        })
        .addTo(map);
        
        marker.on('dragend', e => {
            const pos = marker.getLatLng();
            updateAddressFromCoords(pos.lat, pos.lng);
        });
        
        map.on('click', e => {
            marker.setLatLng(e.latlng);
            updateAddressFromCoords(e.latlng.lat, e.latlng.lng);
        });
        
        console.log('✅ Map initialized');
    } catch(e) {
        console.error('❌ Map init failed:', e);
        Alert.error('Gagal Memuat Peta', 'Refresh halaman');
    }
    
    // Use My Location
    const useLocBtn = document.getElementById('useMyLocation');
    if (useLocBtn) {
        useLocBtn.addEventListener('click', function() {
            if (!navigator.geolocation) {
                Alert.error('Browser Tidak Mendukung', 'Geolocation tidak tersedia');
                return;
            }
            
            const btn = this, icon = btn.querySelector('i');
            icon.classList.remove('fa-crosshairs');
            icon.classList.add('fa-spinner', 'fa-spin');
            btn.disabled = true;
            
            navigator.geolocation.getCurrentPosition(
                pos => {
                    const {latitude: lat, longitude: lng} = pos.coords;
                    console.log('📍 Location:', lat, lng);
                    map.setView([lat, lng], 17);
                    marker.setLatLng([lat, lng]);
                    updateAddressFromCoords(lat, lng);
                    icon.classList.remove('fa-spinner', 'fa-spin');
                    icon.classList.add('fa-crosshairs');
                    btn.disabled = false;
                    Alert.success('Lokasi Ditemukan', 'Berhasil!');
                },
                err => {
                    let msg = 'Tidak dapat mengakses lokasi';
                    if (err.code === err.PERMISSION_DENIED) msg = 'Izin lokasi ditolak';
                    else if (err.code === err.POSITION_UNAVAILABLE) msg = 'Lokasi tidak tersedia';
                    else if (err.code === err.TIMEOUT) msg = 'Request timeout';
                    Alert.error('Gagal', msg);
                    icon.classList.remove('fa-spinner', 'fa-spin');
                    icon.classList.add('fa-crosshairs');
                    btn.disabled = false;
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
            );
        });
    }
    
    function updateAddressFromCoords(lat, lng) {
        console.log('🔄 Reverse geocoding:', lat, lng);
        document.getElementById('latitude').value = lat;
        document.getElementById('longitude').value = lng;
        el.addressInput.value = 'Mengambil alamat...';
        
        fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`, {
            headers: { 'User-Agent': 'LaundryKu App' }
        })
        .then(r => r.json())
        .then(data => {
            const addr = data.display_name || `Lokasi: ${lat.toFixed(6)}, ${lng.toFixed(6)}`;
            updateAddress(lat, lng, addr);
            console.log('✅ Address:', addr);
        })
        .catch(e => {
            console.error('❌ Geocoding error:', e);
            updateAddress(lat, lng, `Lokasi: ${lat.toFixed(6)}, ${lng.toFixed(6)}`);
        });
    }
    
    function updateAddress(lat, lng, addr) {
        document.getElementById('latitude').value = lat;
        document.getElementById('longitude').value = lng;
        el.addressInput.value = addr;
        updateButton();
    }
    
    // Service Selection
    el.serviceOptions.forEach(opt => {
        opt.addEventListener('click', () => {
            el.serviceOptions.forEach(o => {
                o.classList.remove('border-blue-500', 'bg-blue-50', 'shadow-lg', '-translate-y-1');
                o.classList.add('border-gray-200');
                o.querySelector('.check-mark')?.classList.add('hidden');
            });
            
            opt.classList.remove('border-gray-200');
            opt.classList.add('border-blue-500', 'bg-blue-50', 'shadow-lg', '-translate-y-1');
            opt.querySelector('.check-mark')?.classList.remove('hidden');
            
            state.selectedService = opt.dataset.layananId;
            state.pricePerKg = parseFloat(opt.dataset.price);
            state.satuan = opt.dataset.satuan;
            state.duration = opt.dataset.duration;
            
            document.getElementById('layanan_id').value = state.selectedService;
            el.summaryService.textContent = opt.querySelector('h3').textContent;
            el.summaryPricePerKg.textContent = 'Rp ' + fmt(state.pricePerKg);
            
            console.log('✅ Service selected:', state.selectedService);
            updateAll();
        });
    });
    
    // Weight Controls
    el.decreaseBtn?.addEventListener('click', () => { el.weightInput.value = Math.max(1, parseInt(el.weightInput.value) - 1); updateAll(); });
    el.increaseBtn?.addEventListener('click', () => { el.weightInput.value = Math.min(50, parseInt(el.weightInput.value) + 1); updateAll(); });
    el.weightInput?.addEventListener('input', () => { 
        let val = parseInt(el.weightInput.value);
        el.weightInput.value = Math.max(1, Math.min(50, isNaN(val) ? 1 : val));
        updateAll();
    });
    
    el.expressCheckbox?.addEventListener('change', () => { el.expressInfo?.classList.toggle('hidden', !el.expressCheckbox.checked); updateAll(); });
    el.addressInput?.addEventListener('input', () => updateButton());
    
    function updateAll() { updateTotal(); updateTime(); updateButton(); }
    
    function updateTotal() {
        if (!state.selectedService) {
            el.subtotal.textContent = 'Rp 0';
            el.total.textContent = 'Rp 0';
            return;
        }
        
        const weight = parseInt(el.weightInput.value);
        const subtotal = state.pricePerKg * weight;
        const expressCharge = el.expressCheckbox.checked ? (FEES.express * weight) : 0;
        const total = subtotal + FEES.delivery + expressCharge;
        
        if (el.expressCheckbox.checked) el.summaryExpress.textContent = 'Rp ' + fmt(expressCharge);
        
        el.summaryWeight.textContent = weight + ' ' + state.satuan;
        el.subtotal.textContent = 'Rp ' + fmt(subtotal + expressCharge);
        el.total.textContent = 'Rp ' + fmt(total);
    }
    
    function updateTime() {
        if (!state.selectedService) {
            el.estimatedTime.textContent = 'Pilih layanan';
            return;
        }
        
        const today = new Date();
        const days = el.expressCheckbox.checked ? 1 : 2;
        const future = new Date(today);
        future.setDate(today.getDate() + days);
        
        el.estimatedTime.textContent = formatDate(future) + (el.expressCheckbox.checked ? ' (24 jam)' : ` (${state.duration} hari)`);
    }
    
    function updateButton() {
        const hasService = state.selectedService !== null;
        const hasAddress = el.addressInput.value.trim().length > 10;
        const hasCoords = document.getElementById('latitude').value && document.getElementById('longitude').value;
        const enabled = hasService && hasAddress && hasCoords;
        
        let btnText = 'Pilih Layanan';
        if (hasService && !hasAddress) btnText = 'Pilih Lokasi di Peta';
        if (hasService && hasAddress && !hasCoords) btnText = 'Pilih Lokasi di Peta';
        if (enabled) btnText = 'Buat Pesanan';
        
        el.orderBtn.disabled = !enabled;
        document.getElementById('btnText').textContent = btnText;
        
        if (enabled) {
            el.orderBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            el.orderBtn.classList.add('hover:scale-105');
        } else {
            el.orderBtn.classList.add('opacity-50', 'cursor-not-allowed');
            el.orderBtn.classList.remove('hover:scale-105');
        }
    }
    
    async function cancelOrder(id, invoice) {
        console.log('❌ Canceling:', invoice);
        try {
            const r = await fetch(`/user/pemesanan/${id}/cancel`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
            });
            const data = await r.json();
            if (r.ok && data.success) console.log('✅ Cancelled');
        } catch(e) { console.error('❌ Cancel error:', e); }
    }
    
    // Form Submit
    el.form?.addEventListener('submit', async e => {
        e.preventDefault();
        console.log('📝 Form submit');
        
        if (!state.selectedService) {
            Alert.warning('Layanan Belum Dipilih', 'Pilih layanan terlebih dahulu');
            return;
        }
        
        const address = el.addressInput.value.trim();
        if (!address || address.length < 10) {
            Alert.warning('Alamat Tidak Valid', 'Pilih lokasi di peta dengan benar');
            return;
        }
        
        const latitude = document.getElementById('latitude').value;
        const longitude = document.getElementById('longitude').value;
        
        if (!latitude || !longitude) {
            Alert.warning('Koordinat Tidak Valid', 'Pilih lokasi dengan benar');
            return;
        }
        
        const paymentMethod = document.querySelector('input[name="payment_method"]:checked')?.value || 'cash';
        console.log('💳 Payment:', paymentMethod);
        
        Alert.loading('Membuat pesanan...');
        
        try {
            const orderData = {
                layanan_id: state.selectedService,
                weight: parseInt(el.weightInput.value),
                is_express: el.expressCheckbox.checked ? 1 : 0,
                address,
                latitude,
                longitude,
                notes: document.getElementById('notes')?.value.trim() || null,
                payment_method: paymentMethod
            };
            
            console.log('📤 Sending:', orderData);
            
            const r = await fetch(el.form.getAttribute('data-store-url'), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify(orderData)
            });
            
            const data = await r.json();
            console.log('📥 Response:', data);
            Alert.close();
            
            if (!r.ok) throw new Error(data.message || 'Server error');
            
            if (data.success && data.data) {
                const order = data.data;
                console.log('✅ Order created:', order.invoice);
                
                if (paymentMethod === 'midtrans') {
                    handleMidtransPayment(order, cancelOrder);
                } else {
                    showModal(order.invoice, 'cash');
                }
            } else {
                throw new Error(data.message || 'Gagal membuat pesanan');
            }
        } catch(e) {
            Alert.close();
            console.error('❌ Error:', e);
            Alert.error('Gagal Membuat Pesanan', e.message || 'Terjadi kesalahan');
        }
    });
    
    function handleMidtransPayment(order, cancelOrderFn) {
        if (!order.snap_token) {
            Alert.error('Gagal Membuat Pembayaran', 'Token tidak tersedia');
            return;
        }
        
        if (typeof window.snap === 'undefined') {
            Alert.error('Midtrans Tidak Tersedia', 'Refresh halaman atau gunakan tunai');
            cancelOrderFn(order.id, order.invoice);
            return;
        }
        
        console.log('💳 Opening payment:', order.invoice);
        let paymentCompleted = false;
        
        window.snap.pay(order.snap_token, {
            onSuccess: r => {
                console.log('✅ Payment success:', r);
                paymentCompleted = true;
                showModal(order.invoice, 'success');
            },
            onPending: r => {
                console.log('⏳ Payment pending:', r);
                paymentCompleted = true;
                if (hasSwal) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Belum Dibayar',
                        html: `<p>Pembayaran sedang diproses.</p><p class="text-sm mt-2">Invoice: <strong>${order.invoice}</strong></p>`,
                        confirmButtonText: 'OK'
                    }).then(() => window.location.href = '/user/dashboard');
                }
            },
            onError: r => {
                console.error('❌ Payment error:', r);
                paymentCompleted = true;
                cancelOrderFn(order.id, order.invoice);
                Alert.error('Pembayaran Gagal', r.status_message || 'Error');
            },
            onClose: () => {
                console.log('🔴 Payment closed');
                if (!paymentCompleted) {
                    console.log('⚠️ User closed without completing');
                    cancelOrderFn(order.id, order.invoice);
                    if (hasSwal) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Pembayaran Dibatalkan',
                            html: `<p>Anda menutup halaman pembayaran.</p><p class="text-sm mt-2">Invoice: <strong>${order.invoice}</strong></p><p class="text-xs text-gray-500 mt-2">Pesanan dibatalkan otomatis.</p>`,
                            confirmButtonText: 'OK'
                        });
                    }
                }
            }
        });
    }
    
    function showModal(invoice, status) {
        el.invoiceNumber.textContent = invoice || '#LND-2026-XXX';
        
        const msg = {
            pending: { title: 'Belum Dibayar', desc: 'Selesaikan pembayaran Anda.' },
            cash: { title: 'Pesanan Berhasil!', desc: 'Driver akan menghubungi Anda.' },
            success: { title: 'Pembayaran Berhasil!', desc: 'Terima kasih! Driver akan menghubungi Anda.' }
        };
        
        const m = msg[status] || msg.success;
        el.successModal.querySelector('h3').textContent = m.title;
        el.successModal.querySelector('.bg-blue-50 p').textContent = m.desc;
        el.successModal.classList.add('show');
        document.body.classList.add('modal-open');
        
        console.log('✅ Modal shown:', status);
        
        // Reset form
        el.form.reset();
        state.selectedService = null;
        el.serviceOptions.forEach(opt => {
            opt.classList.remove('border-blue-500', 'bg-blue-50', 'shadow-lg');
            opt.classList.add('border-gray-200');
            opt.querySelector('.check-mark')?.classList.add('hidden');
        });
        updateAll();
    }
    
    el.closeModal?.addEventListener('click', () => {
        el.successModal.classList.remove('show');
        document.body.classList.remove('modal-open');
        window.location.href = el.closeModal.getAttribute('data-dashboard-url') || '/user/dashboard';
    });
    
    // Dropdown Menu
    document.getElementById('userMenuBtn')?.addEventListener('click', e => { e.stopPropagation(); document.getElementById('userDropdown')?.classList.toggle('hidden'); });
    document.getElementById('mobileMenuBtn')?.addEventListener('click', e => { e.stopPropagation(); document.getElementById('mobileMenu')?.classList.toggle('hidden'); });
    document.addEventListener('click', () => {
        document.getElementById('userDropdown')?.classList.add('hidden');
        document.getElementById('mobileMenu')?.classList.add('hidden');
    });
    
    console.log('✅ Pemesanan Enhanced v2.0 loaded');
    updateButton();
});