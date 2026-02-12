// admin-pesanan.js - JavaScript untuk halaman admin pesanan dengan ADDRESS & NOTES
(function() {
    'use strict';
    
    // Get CSRF token and routes from meta tags or data attributes
    const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const getDataUrl = document.getElementById('app-data')?.dataset.getDataUrl || '';
    const storeUrl = document.getElementById('app-data')?.dataset.storeUrl || '';
    
    const statusColors = {
        pending: 'bg-yellow-100 text-yellow-800', 
        proses: 'bg-blue-100 text-blue-800', 
        selesai: 'bg-green-100 text-green-800', 
        diambil: 'bg-gray-100 text-gray-800'
    };
    
    const paymentMethodLabels = {
        cash: 'Tunai',
        transfer: 'Transfer Bank',
        ewallet: 'E-Wallet'
    };
    
    let editingId = null;
    let ordersData = [];
    let statusEditingId = null;
    let weightEditingId = null;

    // Toggle Sidebar
    window.toggleSidebar = function() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
        if (sidebar && overlay) {
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }
    };

    // Load Orders
    async function loadOrders() {
        try {
            const url = new URL(getDataUrl);
            const search = document.getElementById('searchInput')?.value || '';
            const status = document.getElementById('filterStatus')?.value || '';
            
            if (search) url.searchParams.append('search', search);
            if (status) url.searchParams.append('status', status);
            
            const res = await fetch(url);
            ordersData = res.ok ? await res.json() : [];
        } catch(e) {
            console.error('Error loading orders:', e);
            ordersData = [];
        }
        
        renderTable();
        renderMobileCards();
    }

    // Render Desktop Table
    function renderTable() {
        const tableBody = document.getElementById('tableBody');
        if (!tableBody) return;
        
        if (ordersData.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="7" class="px-6 py-8 text-center text-gray-500">Tidak ada data</td></tr>';
            return;
        }
        
        tableBody.innerHTML = ordersData.map(o => `
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 font-medium">${o.invoice}</td>
                <td class="px-6 py-4">
                    <div class="font-medium">${o.customer}</div>
                    <div class="text-sm text-gray-500">${o.phone}</div>
                    ${o.address ? `<div class="text-xs text-blue-600 mt-1"><i class="fas fa-map-marker-alt mr-1"></i>${truncateText(o.address, 40)}</div>` : ''}
                </td>
                <td class="px-6 py-4">
                    <div>${o.service}</div>
                    ${o.is_express ? '<span class="inline-block mt-1 px-2 py-1 bg-yellow-100 text-yellow-800 text-xs font-semibold rounded">Express</span>' : ''}
                    ${o.notes ? `<div class="text-xs text-gray-500 mt-1" title="${o.notes}"><i class="fas fa-sticky-note mr-1"></i>${truncateText(o.notes, 30)}</div>` : ''}
                </td>
                <td class="px-6 py-4">
                    <div>${parseFloat(o.weight)} kg (estimasi)</div>
                    ${o.final_weight ? `<div class="text-sm text-green-600 font-medium">${parseFloat(o.final_weight)} kg (akhir)</div>` : '<div class="text-sm text-gray-400">Belum ada berat akhir</div>'}
                </td>
                <td class="px-6 py-4">
                    <div class="font-medium">Rp ${parseFloat(o.total).toLocaleString('id-ID')}</div>
                    ${o.is_express ? '<div class="text-xs text-yellow-600">+ Express Fee</div>' : ''}
                    ${o.payment_method ? `<div class="text-xs text-gray-500 mt-1">${paymentMethodLabels[o.payment_method] || o.payment_method}</div>` : ''}
                </td>
                <td class="px-6 py-4">
                    <span class="px-3 py-1 rounded-full text-xs font-semibold ${statusColors[o.status]}">${o.status.charAt(0).toUpperCase() + o.status.slice(1)}</span>
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center gap-2">
                        <button onclick="viewDetail(${o.id})" class="text-indigo-600 hover:text-indigo-800" title="Lihat Detail">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                        </button>
                        <button onclick="openStatusModal(${o.id}, '${o.status}')" class="text-purple-600 hover:text-purple-800" title="Update Status">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </button>
                        <button onclick="openWeightModal(${o.id}, ${o.weight}, ${o.final_weight || 'null'})" class="text-green-600 hover:text-green-800" title="Input Berat Akhir">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path></svg>
                        </button>
                        <button onclick="editOrder(${o.id})" class="text-blue-600 hover:text-blue-800" title="Edit">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        </button>
                        <button onclick="deleteOrder(${o.id})" class="text-red-600 hover:text-red-800" title="Hapus">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    // Render Mobile Cards
    function renderMobileCards() {
        const mobileCards = document.getElementById('mobileCards');
        if (!mobileCards) return;
        
        if (ordersData.length === 0) {
            mobileCards.innerHTML = '<div class="bg-white rounded-lg shadow-sm p-8 text-center text-gray-500 text-sm">Tidak ada data</div>';
            return;
        }
        
        mobileCards.innerHTML = ordersData.map(o => `
            <div class="bg-white rounded-lg shadow-sm p-4">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <div class="font-bold text-sm">${o.invoice}</div>
                        <div class="text-xs text-gray-500 mt-1">${o.customer}</div>
                        <div class="text-xs text-gray-500">${o.phone}</div>
                        ${o.address ? `<div class="text-xs text-blue-600 mt-1"><i class="fas fa-map-marker-alt mr-1"></i>${truncateText(o.address, 40)}</div>` : ''}
                    </div>
                    <span class="px-2 py-1 rounded-full text-xs font-semibold ${statusColors[o.status]}">${o.status.charAt(0).toUpperCase() + o.status.slice(1)}</span>
                </div>
                <div class="grid grid-cols-2 gap-2 text-xs mb-3">
                    <div>
                        <span class="text-gray-500">Layanan:</span> ${o.service}
                        ${o.is_express ? '<div class="text-yellow-600 font-semibold mt-1">Express</div>' : ''}
                    </div>
                    <div>
                        <span class="text-gray-500">Berat:</span> ${parseFloat(o.weight)} kg
                        ${o.final_weight ? `<div class="text-green-600 font-medium">${parseFloat(o.final_weight)} kg (akhir)</div>` : ''}
                    </div>
                </div>
                ${o.notes ? `<div class="text-xs text-gray-600 mb-3 bg-gray-50 p-2 rounded"><i class="fas fa-sticky-note mr-1"></i>${truncateText(o.notes, 60)}</div>` : ''}
                <div class="flex justify-between items-center pt-3 border-t">
                    <div>
                        <div class="font-bold text-sm">Rp ${parseFloat(o.total).toLocaleString('id-ID')}</div>
                        ${o.is_express ? '<div class="text-xs text-yellow-600">+ Express Fee</div>' : ''}
                        ${o.payment_method ? `<div class="text-xs text-gray-500">${paymentMethodLabels[o.payment_method] || o.payment_method}</div>` : ''}
                    </div>
                    <div class="flex gap-1">
                        <button onclick="viewDetail(${o.id})" class="text-indigo-600 p-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                        </button>
                        <button onclick="openStatusModal(${o.id}, '${o.status}')" class="text-purple-600 p-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </button>
                        <button onclick="openWeightModal(${o.id}, ${o.weight}, ${o.final_weight || 'null'})" class="text-green-600 p-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path></svg>
                        </button>
                        <button onclick="editOrder(${o.id})" class="text-blue-600 p-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        </button>
                        <button onclick="deleteOrder(${o.id})" class="text-red-600 p-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </div>
                </div>
            </div>
        `).join('');
    }

    // View Detail Modal
    window.viewDetail = function(id) {
        const o = ordersData.find(x => x.id === id);
        if (!o) return;
        
        Swal.fire({
            title: 'Detail Pesanan',
            html: `
                <div class="text-left space-y-3">
                    <div class="bg-blue-50 p-3 rounded">
                        <div class="font-bold text-lg text-blue-900">${o.invoice}</div>
                        <div class="text-sm text-blue-700">${o.created_at}</div>
                    </div>
                    
                    <div class="border-b pb-2">
                        <div class="font-semibold text-gray-700">Informasi Pelanggan</div>
                        <div class="text-sm mt-1">Nama: ${o.customer}</div>
                        <div class="text-sm">Telepon: ${o.phone}</div>
                        ${o.address ? `<div class="text-sm mt-2"><i class="fas fa-map-marker-alt text-blue-600 mr-1"></i><strong>Alamat:</strong><br/><span class="text-gray-700">${o.address}</span></div>` : ''}
                    </div>
                    
                    <div class="border-b pb-2">
                        <div class="font-semibold text-gray-700">Detail Layanan</div>
                        <div class="text-sm mt-1">Layanan: ${o.service}</div>
                        ${o.is_express ? '<div class="text-sm"><span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs">Express - 24 Jam</span></div>' : ''}
                        <div class="text-sm">Berat Estimasi: ${parseFloat(o.weight)} kg</div>
                        ${o.final_weight ? `<div class="text-sm text-green-600 font-medium">Berat Akhir: ${parseFloat(o.final_weight)} kg</div>` : ''}
                        ${o.notes ? `<div class="text-sm mt-2 bg-gray-50 p-2 rounded"><i class="fas fa-sticky-note text-gray-600 mr-1"></i><strong>Catatan:</strong><br/><span class="text-gray-700">${o.notes}</span></div>` : ''}
                    </div>
                    
                    <div class="border-b pb-2">
                        <div class="font-semibold text-gray-700">Rincian Biaya</div>
                        <div class="text-sm mt-1">Harga per kg: Rp ${parseFloat(o.price_per_kg).toLocaleString('id-ID')}</div>
                        ${o.is_express ? `<div class="text-sm">Express Fee: Rp ${parseFloat(o.express_fee).toLocaleString('id-ID')} x ${o.final_weight || o.weight} kg</div>` : ''}
                        <div class="text-sm">Biaya Antar-Jemput: Rp ${parseFloat(o.delivery_fee).toLocaleString('id-ID')}</div>
                        <div class="text-sm">Subtotal: Rp ${parseFloat(o.subtotal).toLocaleString('id-ID')}</div>
                        <div class="text-lg font-bold mt-2">Total: Rp ${parseFloat(o.total).toLocaleString('id-ID')}</div>
                    </div>
                    
                    <div>
                        <div class="font-semibold text-gray-700">Status & Pembayaran</div>
                        <div class="text-sm mt-1">Status: <span class="px-2 py-1 rounded-full text-xs font-semibold ${statusColors[o.status]}">${o.status.charAt(0).toUpperCase() + o.status.slice(1)}</span></div>
                        ${o.payment_method ? `<div class="text-sm">Metode Bayar: ${paymentMethodLabels[o.payment_method] || o.payment_method}</div>` : ''}
                    </div>
                </div>
            `,
            width: '600px',
            confirmButtonText: 'Tutup',
            confirmButtonColor: '#3b82f6'
        });
    };

    // Truncate Text Helper
    function truncateText(text, maxLength) {
        if (!text) return '';
        return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
    }

    // Open Modal
    window.openModal = function(mode, id = null) {
        editingId = id;
        const modalTitle = document.getElementById('modalTitle');
        const modal = document.getElementById('modal');
        const statusDiv = document.getElementById('statusDiv');
        const expressDiv = document.getElementById('expressDiv');
        const addressDiv = document.getElementById('addressDiv');
        const notesDiv = document.getElementById('notesDiv');
        const weightNote = document.getElementById('weightNote');
        
        if (modalTitle) {
            modalTitle.textContent = mode === 'create' ? 'Tambah Pesanan' : 'Edit Pesanan';
        }
        
        if (mode === 'create') {
            document.getElementById('orderForm')?.reset();
            document.getElementById('totalPrice').value = '';
            document.getElementById('isExpress').checked = false;
            
            if (statusDiv) statusDiv.style.display = 'block';
            if (expressDiv) expressDiv.style.display = 'block';
            if (addressDiv) addressDiv.style.display = 'block';
            if (notesDiv) notesDiv.style.display = 'block';
            if (weightNote) weightNote.textContent = 'Masukkan berat cucian';
            
        } else {
            const o = ordersData.find(x => x.id === id);
            if (o) {
                document.getElementById('customerName').value = o.customer;
                document.getElementById('customerPhone').value = o.phone;
                
                if (o.layanan_id) {
                    document.getElementById('layananId').value = o.layanan_id;
                }
                
                document.getElementById('weight').value = o.weight;
                
                if (document.getElementById('address')) {
                    document.getElementById('address').value = o.address || '';
                }
                
                if (document.getElementById('notes')) {
                    document.getElementById('notes').value = o.notes || '';
                }
                
                if (statusDiv) statusDiv.style.display = 'none';
                if (expressDiv) expressDiv.style.display = 'none';
                if (addressDiv) addressDiv.style.display = 'block';
                if (notesDiv) notesDiv.style.display = 'block';
                if (weightNote) weightNote.textContent = 'Ubah berat jika diperlukan';
                
                window.calculateTotal();
            }
        }
        
        modal?.classList.remove('hidden');
        modal?.classList.add('flex');
    };

    // Close Modal
    window.closeModal = function() {
        const modal = document.getElementById('modal');
        modal?.classList.add('hidden');
        modal?.classList.remove('flex');
        editingId = null;
    };

    // Calculate Total
    window.calculateTotal = function() {
        const layananSelect = document.getElementById('layananId');
        
        if (!layananSelect || !layananSelect.value) {
            document.getElementById('totalPrice').value = '';
            return;
        }
        
        const selectedOption = layananSelect.options[layananSelect.selectedIndex];
        const pricePerKg = parseFloat(selectedOption.getAttribute('data-price')) || 0;
        
        const w = parseFloat(document.getElementById('weight')?.value) || 0;
        const isExpress = document.getElementById('isExpress')?.checked || false;
        
        const expressFee = isExpress ? 10000 : 0;
        const deliveryFee = 5000;
        
        const subtotal = (pricePerKg * w) + (expressFee * w);
        const total = subtotal + deliveryFee;
        
        const totalPriceInput = document.getElementById('totalPrice');
        if (totalPriceInput) {
            totalPriceInput.value = total > 0 ? 'Rp ' + total.toLocaleString('id-ID') : '';
        }
    };

    // Save Order
    window.saveOrder = async function(e) {
        e.preventDefault();
        
        const layananId = document.getElementById('layananId')?.value;
        if (!layananId) {
            Swal.fire({icon: 'error', title: 'Gagal!', text: 'Silakan pilih layanan'});
            return;
        }
        
        const data = {
            customer_name: document.getElementById('customerName')?.value,
            customer_phone: document.getElementById('customerPhone')?.value,
            layanan_id: layananId,
            weight: parseFloat(document.getElementById('weight')?.value),
            address: document.getElementById('address')?.value || null,
            notes: document.getElementById('notes')?.value || null,
        };
        
        if (!editingId) {
            data.is_express = document.getElementById('isExpress')?.checked ? 1 : 0;
            data.status = document.getElementById('status')?.value;
        }
        
        try {
            const url = editingId ? `${storeUrl}/${editingId}` : storeUrl;
            const res = await fetch(url, {
                method: editingId ? 'PUT' : 'POST',
                headers: {
                    'Content-Type': 'application/json', 
                    'X-CSRF-TOKEN': token, 
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            });
            
            const result = await res.json();
            
            if (!res.ok) {
                Swal.fire({
                    icon: 'error', 
                    title: 'Gagal!', 
                    text: result.errors ? Object.values(result.errors).flat().join('\n') : result.message
                });
                return;
            }
            
            Swal.fire({
                icon: 'success', 
                title: 'Berhasil!', 
                text: result.message, 
                timer: 3000, 
                showConfirmButton: false,
                position: 'top-end',
                toast: true,
                timerProgressBar: true
            });
            
            window.closeModal();
            loadOrders();
        } catch(e) {
            Swal.fire({icon: 'error', title: 'Error!', text: e.message});
        }
    };

    // Edit Order
    window.editOrder = function(id) {
        window.openModal('edit', id);
    };

    // Status Modal
    window.openStatusModal = function(id, currentStatus) {
        statusEditingId = id;
        const statusModal = document.getElementById('statusModal');
        document.getElementById('newStatus').value = currentStatus;
        statusModal?.classList.remove('hidden');
        statusModal?.classList.add('flex');
    };

    window.closeStatusModal = function() {
        const statusModal = document.getElementById('statusModal');
        statusModal?.classList.add('hidden');
        statusModal?.classList.remove('flex');
        statusEditingId = null;
    };

    window.updateStatus = async function(e) {
        e.preventDefault();
        const newStatus = document.getElementById('newStatus')?.value;
        
        try {
            const res = await fetch(`${storeUrl}/${statusEditingId}/status`, {
                method: 'PUT',
                headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json'},
                body: JSON.stringify({status: newStatus})
            });
            
            const result = await res.json();
            
            if (!res.ok) {
                Swal.fire({icon: 'error', title: 'Gagal!', text: result.message});
                return;
            }
            
            Swal.fire({
                icon: 'success', 
                title: 'Berhasil!', 
                text: result.message, 
                timer: 3000, 
                showConfirmButton: false,
                position: 'top-end',
                toast: true,
                timerProgressBar: true
            });
            
            window.closeStatusModal();
            loadOrders();
        } catch(e) {
            Swal.fire({icon: 'error', title: 'Error!', text: e.message});
        }
    };

    // Weight Modal
    window.openWeightModal = function(id, estimatedWeight, finalWeight) {
        weightEditingId = id;
        const weightModal = document.getElementById('weightModal');
        document.getElementById('estimatedWeight').value = parseFloat(estimatedWeight) + ' kg';
        document.getElementById('newFinalWeight').value = finalWeight ? parseFloat(finalWeight) : '';
        weightModal?.classList.remove('hidden');
        weightModal?.classList.add('flex');
    };

    window.closeWeightModal = function() {
        const weightModal = document.getElementById('weightModal');
        weightModal?.classList.add('hidden');
        weightModal?.classList.remove('flex');
        weightEditingId = null;
    };

    window.updateFinalWeight = async function(e) {
        e.preventDefault();
        const finalWeight = parseFloat(document.getElementById('newFinalWeight')?.value);
        
        try {
            const res = await fetch(`${storeUrl}/${weightEditingId}/final-weight`, {
                method: 'PUT',
                headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json'},
                body: JSON.stringify({final_weight: finalWeight})
            });
            
            const result = await res.json();
            
            if (!res.ok) {
                Swal.fire({icon: 'error', title: 'Gagal!', text: result.message});
                return;
            }
            
            Swal.fire({
                icon: 'success', 
                title: 'Berhasil!', 
                text: result.message, 
                timer: 3000, 
                showConfirmButton: false,
                position: 'top-end',
                toast: true,
                timerProgressBar: true
            });
            
            window.closeWeightModal();
            loadOrders();
        } catch(e) {
            Swal.fire({icon: 'error', title: 'Error!', text: e.message});
        }
    };

    // Delete Order
    window.deleteOrder = async function(id) {
        const result = await Swal.fire({
            title: 'Yakin ingin menghapus?',
            text: "Data tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        });
        
        if (!result.isConfirmed) return;
        
        try {
            const res = await fetch(`${storeUrl}/${id}`, {
                method: 'DELETE',
                headers: {'X-CSRF-TOKEN': token, 'Accept': 'application/json'}
            });
            
            const data = await res.json();
            
            Swal.fire({
                icon: 'success', 
                title: 'Terhapus!', 
                text: data.message, 
                timer: 3000, 
                showConfirmButton: false,
                position: 'top-end',
                toast: true,
                timerProgressBar: true
            });
            
            loadOrders();
        } catch(e) {
            Swal.fire({icon: 'error', title: 'Error!', text: 'Gagal menghapus data'});
        }
    };

    // Event Listeners
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const filterStatus = document.getElementById('filterStatus');
        
        if (searchInput) {
            searchInput.addEventListener('input', loadOrders);
        }
        
        if (filterStatus) {
            filterStatus.addEventListener('change', loadOrders);
        }
        
        loadOrders();
    });

})();