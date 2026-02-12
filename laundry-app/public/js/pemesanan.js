/**
 * Pemesanan Laundry Script - FIXED CANCEL HANDLING
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('Script loaded');
    
    const state = {
        selectedService: null,
        pricePerKg: 0,
        satuan: 'kg',
        duration: '2-3'
    };
    
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
        orderBtnMobile: document.getElementById('orderBtnMobile'),
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
        loading(msg = 'Memproses pesanan...') {
            if (hasSwal) {
                Swal.fire({
                    title: 'Mohon Tunggu',
                    html: msg,
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => Swal.showLoading()
                });
            }
        },
        error(title, msg) {
            if (hasSwal) {
                Swal.fire({ icon: 'error', title, text: msg, confirmButtonColor: '#EF4444' });
            } else {
                alert(title + ': ' + msg);
            }
        },
        warning(title, msg) {
            if (hasSwal) {
                Swal.fire({ icon: 'warning', title, text: msg, confirmButtonColor: '#F59E0B' });
            } else {
                alert(title + ': ' + msg);
            }
        },
        cancelled(invoice) {
            if (hasSwal) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Pembayaran Dibatalkan',
                    html: `
                        <div class="text-left">
                            <p class="mb-3 text-gray-700">Anda membatalkan proses pembayaran.</p>
                            <div class="bg-gray-100 p-3 rounded-lg mb-3">
                                <p class="text-sm text-gray-600">Nomor Pesanan:</p>
                                <p class="font-bold text-gray-800">${invoice}</p>
                            </div>
                            <p class="text-sm text-gray-600">Pesanan akan dibatalkan otomatis.</p>
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonText: '<i class="fas fa-redo mr-2"></i>Coba Lagi',
                    cancelButtonText: '<i class="fas fa-times mr-2"></i>Kembali',
                    confirmButtonColor: '#3B82F6',
                    cancelButtonColor: '#6B7280',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                        setTimeout(() => {
                            el.addressInput?.focus();
                        }, 500);
                    }
                });
            } else {
                if (confirm(`Pembayaran dibatalkan untuk pesanan ${invoice}. Coba lagi?`)) {
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            }
        },
        paymentError(invoice) {
            if (hasSwal) {
                Swal.fire({
                    icon: 'error',
                    title: 'Pembayaran Gagal',
                    html: `
                        <div class="text-left">
                            <p class="mb-3 text-gray-700">Transaksi pembayaran gagal diproses.</p>
                            <div class="bg-red-50 border border-red-200 p-3 rounded-lg mb-3">
                                <p class="text-sm text-gray-600">Nomor Pesanan:</p>
                                <p class="font-bold text-red-700">${invoice}</p>
                            </div>
                            <p class="text-sm text-gray-500">Silakan coba dengan metode pembayaran lain atau hubungi customer service.</p>
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonText: '<i class="fas fa-redo mr-2"></i>Coba Lagi',
                    cancelButtonText: '<i class="fas fa-home mr-2"></i>Ke Dashboard',
                    confirmButtonColor: '#3B82F6',
                    cancelButtonColor: '#6B7280'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    } else if (result.dismiss === Swal.DismissReason.cancel) {
                        window.location.href = '/user/dashboard';
                    }
                });
            } else {
                if (confirm(`Pembayaran gagal untuk ${invoice}. Coba lagi?`)) {
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            }
        },
        close() {
            if (hasSwal && Swal.isVisible()) {
                Swal.close();
            }
        }
    };

    const fmt = (num) => num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    const formatDate = (date) => {
        const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        return `${days[date.getDay()]}, ${date.getDate()} ${months[date.getMonth()]} ${date.getFullYear()}`;
    };

    // SERVICE SELECTION
    el.serviceOptions.forEach(option => {
        option.addEventListener('click', () => {
            el.serviceOptions.forEach(opt => {
                opt.classList.remove('border-blue-500', 'bg-blue-50', 'shadow-lg', '-translate-y-1');
                opt.classList.add('border-gray-200');
                opt.querySelector('.check-mark')?.classList.add('hidden');
            });
            
            option.classList.remove('border-gray-200');
            option.classList.add('border-blue-500', 'bg-blue-50', 'shadow-lg', '-translate-y-1');
            option.querySelector('.check-mark')?.classList.remove('hidden');
            
            state.selectedService = option.dataset.layananId;
            state.pricePerKg = parseFloat(option.dataset.price);
            state.satuan = option.dataset.satuan;
            state.duration = option.dataset.duration;
            
            document.getElementById('layanan_id').value = state.selectedService;
            el.summaryService.textContent = option.querySelector('h3').textContent;
            el.summaryPricePerKg.textContent = 'Rp ' + fmt(state.pricePerKg);
            
            updateAll();
        });
    });

    // WEIGHT CONTROLS
    el.decreaseBtn?.addEventListener('click', () => {
        const val = Math.max(1, parseInt(el.weightInput.value) - 1);
        el.weightInput.value = val;
        updateAll();
    });
    
    el.increaseBtn?.addEventListener('click', () => {
        const val = Math.min(50, parseInt(el.weightInput.value) + 1);
        el.weightInput.value = val;
        updateAll();
    });
    
    el.weightInput?.addEventListener('input', () => {
        let val = parseInt(el.weightInput.value);
        el.weightInput.value = Math.max(1, Math.min(50, isNaN(val) ? 1 : val));
        updateAll();
    });

    el.expressCheckbox?.addEventListener('change', () => {
        el.expressInfo?.classList.toggle('hidden', !el.expressCheckbox.checked);
        updateAll();
    });

    el.addressInput?.addEventListener('input', () => updateButton());

    function updateAll() {
        updateTotal();
        updateTime();
        updateButton();
        syncMobile();
    }

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
        
        if (el.expressCheckbox.checked) {
            el.summaryExpress.textContent = 'Rp ' + fmt(expressCharge);
        }
        
        el.summaryWeight.textContent = weight + ' ' + state.satuan;
        el.subtotal.textContent = 'Rp ' + fmt(subtotal + expressCharge);
        el.total.textContent = 'Rp ' + fmt(total);
    }

    function updateTime() {
        if (!state.selectedService) {
            el.estimatedTime.textContent = 'Pilih layanan terlebih dahulu';
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
        const hasAddress = el.addressInput.value.trim().length > 0;
        const enabled = hasService && hasAddress;
        
        let btnText = 'Pilih Layanan';
        if (hasService && !hasAddress) btnText = 'Masukkan Alamat';
        if (hasService && hasAddress) btnText = 'Buat Pesanan';
        
        el.orderBtn.disabled = !enabled;
        document.getElementById('btnText').textContent = btnText;
        
        if (el.orderBtnMobile) {
            el.orderBtnMobile.disabled = !enabled;
            document.getElementById('btnTextMobile').textContent = btnText;
        }
    }

    function syncMobile() {
        const pairs = [
            ['summaryServiceMobile', el.summaryService?.textContent],
            ['summaryWeightMobile', el.summaryWeight?.textContent],
            ['summaryPricePerKgMobile', el.summaryPricePerKg?.textContent],
            ['subtotalMobile', el.subtotal?.textContent],
            ['totalMobile', el.total?.textContent],
            ['estimatedTimeMobile', el.estimatedTime?.textContent]
        ];
        
        pairs.forEach(([id, val]) => {
            const elem = document.getElementById(id);
            if (elem && val) elem.textContent = val;
        });
        
        const expMobile = document.getElementById('expressInfoMobile');
        if (expMobile) {
            expMobile.classList.toggle('hidden', el.expressInfo.classList.contains('hidden'));
            if (!el.expressInfo.classList.contains('hidden')) {
                const exp = document.getElementById('summaryExpressMobile');
                if (exp) exp.textContent = el.summaryExpress.textContent;
            }
        }
    }

    // CANCEL ORDER - API CALL
    async function cancelOrder(orderId, invoice) {
        console.log('Canceling order:', orderId, invoice);
        
        try {
            const response = await fetch(`/user/pemesanan/${orderId}/cancel`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();
            console.log('Cancel response:', data);

            if (response.ok && data.success) {
                console.log('Order cancelled successfully');
            } else {
                console.error('Failed to cancel order:', data.message);
            }
        } catch (error) {
            console.error('Error canceling order:', error);
        }
    }

    // FORM SUBMIT
    el.form?.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        if (!state.selectedService) {
            Alert.warning('Layanan Belum Dipilih', 'Silakan pilih layanan laundry terlebih dahulu');
            return;
        }
        
        const address = el.addressInput.value.trim();
        if (!address) {
            Alert.warning('Alamat Belum Diisi', 'Silakan masukkan alamat penjemputan');
            return;
        }
        
        const paymentMethod = document.querySelector('input[name="payment_method"]:checked')?.value || 'cash';
        
        Alert.loading('Membuat pesanan Anda...');
        
        try {
            const response = await fetch(el.form.getAttribute('data-store-url'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    layanan_id: state.selectedService,
                    weight: parseInt(el.weightInput.value),
                    is_express: el.expressCheckbox.checked ? 1 : 0,
                    address: address,
                    notes: document.getElementById('notes')?.value.trim() || null,
                    payment_method: paymentMethod
                })
            });
            
            const data = await response.json();
            Alert.close();
            
            console.log('Order response:', data);
            
            if (!response.ok) {
                throw new Error(data.message || 'Terjadi kesalahan');
            }
            
            if (data.success) {
                const orderData = data.data;
                
                if (paymentMethod === 'midtrans' && orderData.snap_token && typeof window.snap !== 'undefined') {
                    console.log('Opening Midtrans payment popup...');
                    
                    // FLAG untuk track apakah payment berhasil
                    let paymentCompleted = false;
                    
                    window.snap.pay(orderData.snap_token, {
                        onSuccess: function(result) {
                            console.log('✅ Payment SUCCESS:', result);
                            paymentCompleted = true;
                            Alert.close();
                            showModal(orderData.invoice, 'success');
                        },
                        onPending: function(result) {
                            console.log('⏳ Payment PENDING:', result);
                            paymentCompleted = true;
                            Alert.close();
                            showModal(orderData.invoice, 'pending');
                        },
                        onError: function(result) {
                            console.error('❌ Payment ERROR:', result);
                            paymentCompleted = true;
                            Alert.close();
                            
                            // Cancel order
                            cancelOrder(orderData.id, orderData.invoice);
                            
                            // Show error alert
                            Alert.paymentError(orderData.invoice);
                        },
                        onClose: function() {
                            console.log('🔴 Payment popup CLOSED');
                            
                            // HANYA cancel jika payment belum completed
                            if (!paymentCompleted) {
                                Alert.close();
                                
                                console.log('User closed without completing payment - canceling order');
                                
                                // Cancel order di backend
                                cancelOrder(orderData.id, orderData.invoice);
                                
                                // Show cancelled alert (JANGAN show success modal!)
                                Alert.cancelled(orderData.invoice);
                            } else {
                                console.log('Payment already completed, not canceling');
                            }
                        }
                    });
                } else {
                    // CASH PAYMENT - langsung success
                    showModal(orderData.invoice, 'cash');
                }
            }
        } catch (error) {
            Alert.close();
            console.error('Error:', error);
            Alert.error('Gagal', error.message || 'Terjadi kesalahan saat membuat pesanan');
        }
    });

    // MODAL (hanya untuk SUCCESS/PENDING/CASH)
    function showModal(invoice, status) {
        console.log('Showing modal:', status, invoice);
        
        el.invoiceNumber.textContent = invoice || '#LND-2026-XXX';
        
        const messages = {
            pending: { 
                title: 'Menunggu Pembayaran', 
                desc: '<i class="fas fa-clock mr-2"></i>Silakan selesaikan pembayaran Anda. Cek email untuk instruksi.' 
            },
            cash: { 
                title: 'Pesanan Berhasil Dibuat!', 
                desc: '<i class="fas fa-money-bill-wave mr-2"></i>Driver akan menghubungi Anda. Bayar saat cucian diambil.' 
            },
            success: { 
                title: 'Pembayaran Berhasil!', 
                desc: '<i class="fas fa-check-circle mr-2"></i>Terima kasih! Driver akan menghubungi Anda dalam 15-30 menit.' 
            }
        };
        
        const msg = messages[status] || messages.success;
        el.successModal.querySelector('h3').textContent = msg.title;
        el.successModal.querySelector('.bg-blue-50 p').innerHTML = msg.desc;
        el.successModal.classList.add('show');
        document.body.classList.add('modal-open');
        
        // Reset form
        el.form.reset();
        state.selectedService = null;
        el.serviceOptions.forEach(opt => {
            opt.classList.remove('border-blue-500', 'bg-blue-50', 'shadow-lg', '-translate-y-1');
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

    // DROPDOWN MENU
    document.getElementById('userMenuBtn')?.addEventListener('click', (e) => {
        e.stopPropagation();
        document.getElementById('userDropdown')?.classList.toggle('hidden');
    });
    
    document.getElementById('mobileMenuBtn')?.addEventListener('click', (e) => {
        e.stopPropagation();
        document.getElementById('mobileMenu')?.classList.toggle('hidden');
    });
    
    document.addEventListener('click', () => {
        document.getElementById('userDropdown')?.classList.add('hidden');
        document.getElementById('mobileMenu')?.classList.add('hidden');
    });

    // MOBILE SUMMARY TOGGLE
    document.getElementById('toggleDetailMobile')?.addEventListener('click', () => {
        const detail = document.querySelector('.mobile-summary-detail');
        const icon = document.getElementById('chevronIcon');
        const text = document.getElementById('toggleText');
        
        detail?.classList.toggle('expanded');
        
        if (detail?.classList.contains('expanded')) {
            icon?.classList.replace('fa-chevron-up', 'fa-chevron-down');
            if (text) text.textContent = 'Sembunyikan Detail';
        } else {
            icon?.classList.replace('fa-chevron-down', 'fa-chevron-up');
            if (text) text.textContent = 'Lihat Detail';
        }
    });

    // INIT
    console.log('App initialized', { hasSwal });
    setTimeout(syncMobile, 100);
    updateButton();
});