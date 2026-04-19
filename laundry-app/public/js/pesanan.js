// admin-pesanan.js
(function () {
    'use strict';

    const token      = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const getDataUrl = document.getElementById('app-data')?.dataset.getDataUrl   || '';
    const storeUrl   = document.getElementById('app-data')?.dataset.storeUrl     || '';
    const geocodeUrl = '/api/geocode/reverse';

    const statusColors = {
        pending: 'bg-yellow-100 text-yellow-800',
        proses:  'bg-blue-100 text-blue-800',
        selesai: 'bg-green-100 text-green-800',
        diambil: 'bg-gray-100 text-gray-800'
    };

    const paymentMethodLabels = {
        cash: 'Tunai', transfer: 'Transfer Bank', ewallet: 'E-Wallet'
    };

    let editingId = null, ordersData = [], statusEditingId = null;
    let adminMap = null, adminMarker = null;

    /* ── MAP ─────────────────────────────────────────────────────────── */
    async function reverseGeocode(lat, lng) {
        try {
            const r = await fetch(`${geocodeUrl}?lat=${lat}&lng=${lng}`, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': token }
            });
            if (r.ok) { const d = await r.json(); return d.address || `${lat.toFixed(6)}, ${lng.toFixed(6)}`; }
        } catch (e) { console.error(e); }
        return `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
    }

    function initAdminMap() {
        if (adminMap) { adminMap.remove(); adminMap = null; adminMarker = null; }
        const dlat = -7.9666, dlng = 112.6326;
        adminMap = L.map('adminMap').setView([dlat, dlng], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap', maxZoom: 19
        }).addTo(adminMap);
        L.Control.geocoder({
            defaultMarkGeocode: false, placeholder: 'Cari lokasi...',
            geocoder: L.Control.Geocoder.nominatim({ serviceUrl: 'https://nominatim.openstreetmap.org/' })
        }).on('markgeocode', function (e) {
            const ll = e.geocode.center;
            updateMapLoc(ll.lat, ll.lng, e.geocode.name);
            adminMap.setView(ll, 16);
        }).addTo(adminMap);
        adminMarker = L.marker([dlat, dlng], { draggable: true }).addTo(adminMap);
        adminMarker.on('dragend', e => { const p = e.target.getLatLng(); updateMapLoc(p.lat, p.lng); });
        adminMap.on('click', e => updateMapLoc(e.latlng.lat, e.latlng.lng));

        const btn = document.getElementById('adminUseMyLocation');
        if (btn) {
            btn.onclick = function (e) {
                e.preventDefault(); e.stopPropagation();
                if (!navigator.geolocation) { alert('Browser tidak mendukung geolocation'); return; }
                btn.innerHTML = '<svg class="w-5 h-5 text-blue-600 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>';
                const icon = '<svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>';
                navigator.geolocation.getCurrentPosition(
                    pos => { updateMapLoc(pos.coords.latitude, pos.coords.longitude); adminMap.setView([pos.coords.latitude, pos.coords.longitude], 16); btn.innerHTML = icon; },
                    err => { alert('Tidak dapat akses lokasi: ' + err.message); btn.innerHTML = icon; }
                );
            };
        }
        document.getElementById('address').value = 'Memuat alamat...';
        updateMapLoc(dlat, dlng);
    }

    async function updateMapLoc(lat, lng, addressName = null) {
        if (adminMarker) adminMarker.setLatLng([lat, lng]);
        document.getElementById('latitude').value  = lat;
        document.getElementById('longitude').value = lng;
        if (addressName) { document.getElementById('address').value = addressName; return; }
        document.getElementById('address').value = 'Memuat alamat...';
        document.getElementById('address').value = await reverseGeocode(lat, lng);
    }

    /* ── SIDEBAR ─────────────────────────────────────────────────────── */
    window.toggleSidebar = function () {
        document.getElementById('sidebar')?.classList.toggle('-translate-x-full');
        document.getElementById('overlay')?.classList.toggle('hidden');
    };

    /* ── LOAD DATA ───────────────────────────────────────────────────── */
    async function loadOrders() {
        try {
            const url = new URL(getDataUrl);
            const s = document.getElementById('searchInput')?.value || '';
            const f = document.getElementById('filterStatus')?.value || '';
            if (s) url.searchParams.append('search', s);
            if (f) url.searchParams.append('status', f);
            const res = await fetch(url);
            ordersData = res.ok ? await res.json() : [];
        } catch (e) { console.error(e); ordersData = []; }
        renderTable();
        renderMobileCards();
    }

    /* ── RENDER TABLE ────────────────────────────────────────────────── */
    function renderTable() {
        const tb = document.getElementById('tableBody');
        if (!tb) return;
        if (!ordersData.length) { tb.innerHTML = '<tr><td colspan="7" class="px-6 py-8 text-center text-gray-500">Tidak ada data</td></tr>'; return; }
        tb.innerHTML = ordersData.map(o => `
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 font-medium">${o.invoice}</td>
                <td class="px-6 py-4">
                    <div class="font-medium">${o.customer}</div>
                    <div class="text-sm text-gray-500">${o.phone}</div>
                    ${o.address ? `<div class="text-xs text-blue-600 mt-1">${trunc(o.address,40)}</div>` : ''}
                </td>
                <td class="px-6 py-4">
                    <div>${o.service}</div>
                    ${o.is_express ? '<span class="inline-block mt-1 px-2 py-1 bg-yellow-100 text-yellow-800 text-xs font-semibold rounded">Express</span>' : ''}
                    ${o.notes ? `<div class="text-xs text-gray-500 mt-1">${trunc(o.notes,30)}</div>` : ''}
                </td>
                <td class="px-6 py-4">
                    <div>${parseFloat(o.weight)} kg (estimasi)</div>
                    ${o.final_weight ? `<div class="text-sm text-green-600 font-medium">${parseFloat(o.final_weight)} kg (akhir)</div>` : '<div class="text-sm text-gray-400">Belum ada berat akhir</div>'}
                </td>
                <td class="px-6 py-4">
                    <div class="font-medium">Rp ${parseFloat(o.total).toLocaleString('id-ID')}</div>
                    ${o.is_express ? '<div class="text-xs text-yellow-600">+ Express Fee</div>' : ''}
                    ${o.payment_method ? `<div class="text-xs text-gray-500 mt-1">${paymentMethodLabels[o.payment_method]||o.payment_method}</div>` : ''}
                </td>
                <td class="px-6 py-4">
                    <span class="px-3 py-1 rounded-full text-xs font-semibold ${statusColors[o.status]}">${cap(o.status)}</span>
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center gap-2">
                        <button onclick="viewDetail(${o.id})" class="text-indigo-600 hover:text-indigo-800" title="Detail"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg></button>
                        <button onclick="openStatusModal(${o.id},'${o.status}')" class="text-purple-600 hover:text-purple-800" title="Status"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></button>
                        <button onclick="editOrder(${o.id})" class="text-blue-600 hover:text-blue-800" title="Edit"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg></button>
                        <button onclick="deleteOrder(${o.id})" class="text-red-600 hover:text-red-800" title="Hapus"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button>
                    </div>
                </td>
            </tr>`).join('');
    }

    /* ── RENDER MOBILE ───────────────────────────────────────────────── */
    function renderMobileCards() {
        const mc = document.getElementById('mobileCards');
        if (!mc) return;
        if (!ordersData.length) { mc.innerHTML = '<div class="bg-white rounded-lg shadow-sm p-8 text-center text-gray-500 text-sm">Tidak ada data</div>'; return; }
        mc.innerHTML = ordersData.map(o => `
            <div class="bg-white rounded-lg shadow-sm p-4">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <div class="font-bold text-sm">${o.invoice}</div>
                        <div class="text-xs text-gray-500 mt-1">${o.customer}</div>
                        <div class="text-xs text-gray-500">${o.phone}</div>
                        ${o.address ? `<div class="text-xs text-blue-600 mt-1">${trunc(o.address,40)}</div>` : ''}
                    </div>
                    <span class="px-2 py-1 rounded-full text-xs font-semibold ${statusColors[o.status]}">${cap(o.status)}</span>
                </div>
                <div class="grid grid-cols-2 gap-2 text-xs mb-3">
                    <div><span class="text-gray-500">Layanan:</span> ${o.service}${o.is_express?'<div class="text-yellow-600 font-semibold mt-1">Express</div>':''}</div>
                    <div><span class="text-gray-500">Berat:</span> ${parseFloat(o.weight)} kg${o.final_weight?`<div class="text-green-600 font-medium">${parseFloat(o.final_weight)} kg (akhir)</div>`:''}</div>
                </div>
                ${o.notes?`<div class="text-xs text-gray-600 mb-3 bg-gray-50 p-2 rounded">${trunc(o.notes,60)}</div>`:''}
                <div class="flex justify-between items-center pt-3 border-t">
                    <div>
                        <div class="font-bold text-sm">Rp ${parseFloat(o.total).toLocaleString('id-ID')}</div>
                        ${o.is_express?'<div class="text-xs text-yellow-600">+ Express Fee</div>':''}
                        ${o.payment_method?`<div class="text-xs text-gray-500">${paymentMethodLabels[o.payment_method]||o.payment_method}</div>`:''}
                    </div>
                    <div class="flex gap-1">
                        <button onclick="viewDetail(${o.id})" class="text-indigo-600 p-1"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg></button>
                        <button onclick="openStatusModal(${o.id},'${o.status}')" class="text-purple-600 p-1"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></button>
                        <button onclick="editOrder(${o.id})" class="text-blue-600 p-1"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg></button>
                        <button onclick="deleteOrder(${o.id})" class="text-red-600 p-1"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button>
                    </div>
                </div>
            </div>`).join('');
    }

    /* ── VIEW DETAIL ─────────────────────────────────────────────────── */
    window.viewDetail = function (id) {
        const o = ordersData.find(x => x.id === id);
        if (!o) return;
        Swal.fire({
            title: 'Detail Pesanan', width: '600px',
            confirmButtonText: 'Tutup', confirmButtonColor: '#3b82f6',
            html: `<div class="text-left space-y-3">
                <div class="bg-blue-50 p-3 rounded">
                    <div class="font-bold text-lg text-blue-900">${o.invoice}</div>
                    <div class="text-sm text-blue-700">${o.created_at}</div>
                </div>
                <div class="border-b pb-2">
                    <div class="font-semibold text-gray-700">Informasi Pelanggan</div>
                    <div class="text-sm mt-1">Nama: ${o.customer}</div>
                    <div class="text-sm">Telepon: ${o.phone}</div>
                    ${o.address?`<div class="text-sm mt-2"><strong>Alamat:</strong><br/><span class="text-gray-700">${o.address}</span></div>`:''}
                </div>
                <div class="border-b pb-2">
                    <div class="font-semibold text-gray-700">Detail Layanan</div>
                    <div class="text-sm mt-1">Layanan: ${o.service}</div>
                    ${o.is_express?'<div class="text-sm"><span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs">Express - 24 Jam</span></div>':''}
                    <div class="text-sm">Berat Estimasi: ${parseFloat(o.weight)} kg</div>
                    ${o.final_weight?`<div class="text-sm text-green-600 font-medium">Berat Akhir: ${parseFloat(o.final_weight)} kg</div>`:''}
                    ${o.notes?`<div class="text-sm mt-2 bg-gray-50 p-2 rounded"><strong>Catatan:</strong><br/><span class="text-gray-700">${o.notes}</span></div>`:''}
                </div>
                <div class="border-b pb-2">
                    <div class="font-semibold text-gray-700">Rincian Biaya</div>
                    <div class="text-sm mt-1">Harga per kg: Rp ${parseFloat(o.price_per_kg).toLocaleString('id-ID')}</div>
                    ${o.is_express?`<div class="text-sm">Express Fee: Rp ${parseFloat(o.express_fee).toLocaleString('id-ID')} x ${o.final_weight||o.weight} kg</div>`:''}
                    <div class="text-sm">Biaya Antar-Jemput: Rp ${parseFloat(o.delivery_fee).toLocaleString('id-ID')}</div>
                    <div class="text-sm">Subtotal: Rp ${parseFloat(o.subtotal).toLocaleString('id-ID')}</div>
                    <div class="text-lg font-bold mt-2">Total: Rp ${parseFloat(o.total).toLocaleString('id-ID')}</div>
                </div>
                <div>
                    <div class="font-semibold text-gray-700">Status & Pembayaran</div>
                    <div class="text-sm mt-1">Status: <span class="px-2 py-1 rounded-full text-xs font-semibold ${statusColors[o.status]}">${cap(o.status)}</span></div>
                    ${o.payment_method?`<div class="text-sm">Metode Bayar: ${paymentMethodLabels[o.payment_method]||o.payment_method}</div>`:''}
                </div>
            </div>`
        });
    };

    /* ── HELPERS ─────────────────────────────────────────────────────── */
    function trunc(t, n) { return t && t.length > n ? t.substring(0, n) + '...' : (t||''); }
    function cap(s)       { return s ? s.charAt(0).toUpperCase() + s.slice(1) : ''; }

    /* ── SET DROPDOWN LAYANAN ────────────────────────────────────────── */
    // Strategi 1: match by layanan_id
    // Strategi 2 (fallback): match by teks nama layanan di option
    function setLayananDropdown(layananId, serviceType) {
        const sel = document.getElementById('layananId');
        if (!sel) return;

        // Reset dulu
        sel.selectedIndex = 0;

        // Strategi 1: match by ID
        if (layananId) {
            const target = String(layananId);
            for (let i = 0; i < sel.options.length; i++) {
                if (String(sel.options[i].value) === target) {
                    sel.selectedIndex = i;
                    return;
                }
            }
        }

        // Strategi 2: match by nama layanan di teks option
        // (untuk pesanan lama yang layanan_id-nya null)
        if (serviceType) {
            // Bersihkan "(Express)" dari nama jika ada
            const cleanName = serviceType.replace(/\s*\(Express\)\s*$/i, '').trim().toLowerCase();
            for (let i = 0; i < sel.options.length; i++) {
                // Teks option format: "Cuci+Setrika - Rp 7.000/kg"
                // Ambil bagian sebelum " - "
                const optName = sel.options[i].text.split(' - ')[0].trim().toLowerCase();
                if (optName === cleanName) {
                    sel.selectedIndex = i;
                    return;
                }
            }
        }

        console.warn('[Pesanan] Layanan tidak ditemukan - id:', layananId, 'service_type:', serviceType);
    }

    /* ── FILL FORM EDIT ──────────────────────────────────────────────── */
    function fillEditForm(o) {
        const statusDiv     = document.getElementById('statusDiv');
        const expressDiv    = document.getElementById('expressDiv');
        const addressDiv    = document.getElementById('addressDiv');
        const notesDiv      = document.getElementById('notesDiv');
        const weightNote    = document.getElementById('weightNote');
        const totalPriceDiv = document.getElementById('totalPrice')?.closest('div');

        document.getElementById('customerName').value  = o.customer || '';
        document.getElementById('customerPhone').value = o.phone    || '';
        document.getElementById('weight').value        = o.weight   || '';

        if (document.getElementById('address'))   document.getElementById('address').value   = o.address   || '';
        if (document.getElementById('latitude'))  document.getElementById('latitude').value  = o.latitude  || '';
        if (document.getElementById('longitude')) document.getElementById('longitude').value = o.longitude || '';
        if (document.getElementById('notes'))     document.getElementById('notes').value     = o.notes     || '';

        if (statusDiv)      statusDiv.style.display      = 'none';
        if (expressDiv)     expressDiv.style.display     = 'none';
        if (addressDiv)     addressDiv.style.display     = 'block';
        if (notesDiv)       notesDiv.style.display       = 'block';
        if (weightNote)     weightNote.textContent       = 'Ubah berat jika diperlukan';
        if (totalPriceDiv)  totalPriceDiv.style.display  = 'none';

        // Set dropdown: coba by ID dulu, fallback by nama service
        setLayananDropdown(o.layanan_id, o.service);

        window.calculateTotal();

        setTimeout(() => {
            initAdminMap();
            if (o.latitude && o.longitude) {
                const lat = parseFloat(o.latitude), lng = parseFloat(o.longitude);
                adminMap.setView([lat, lng], 16);
                updateMapLoc(lat, lng, o.address);
            }
        }, 100);
    }

    /* ── OPEN MODAL ──────────────────────────────────────────────────── */
    window.openModal = function (mode, id = null) {
        editingId = id;
        const modal      = document.getElementById('modal');
        const modalTitle = document.getElementById('modalTitle');
        const statusDiv  = document.getElementById('statusDiv');
        const expressDiv = document.getElementById('expressDiv');
        const addressDiv = document.getElementById('addressDiv');
        const notesDiv   = document.getElementById('notesDiv');
        const weightNote = document.getElementById('weightNote');
        const totalPriceDiv = document.getElementById('totalPrice')?.closest('div');

        if (modalTitle) modalTitle.textContent = mode === 'create' ? 'Tambah Pesanan' : 'Edit Pesanan';

        // Tampilkan modal DULU agar DOM siap
        modal?.classList.remove('hidden');
        modal?.classList.add('flex');

        if (mode === 'create') {
            document.getElementById('orderForm')?.reset();
            document.getElementById('totalPrice').value = '';
            document.getElementById('isExpress').checked = false;
            document.getElementById('latitude').value  = '';
            document.getElementById('longitude').value = '';
            document.getElementById('address').value   = '';

            if (statusDiv)    statusDiv.style.display    = 'block';
            if (expressDiv)   expressDiv.style.display   = 'block';
            if (addressDiv)   addressDiv.style.display   = 'block';
            if (notesDiv)     notesDiv.style.display     = 'block';
            if (weightNote)   weightNote.textContent     = 'Masukkan berat cucian';
            if (totalPriceDiv) totalPriceDiv.style.display = 'block';

            setTimeout(() => initAdminMap(), 100);
        } else {
            const o = ordersData.find(x => x.id === id);
            if (o) {
                fillEditForm(o);
            } else {
                fetch(getDataUrl, { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': token } })
                    .then(r => r.json())
                    .then(data => {
                        ordersData = data;
                        const found = ordersData.find(x => x.id === id);
                        found ? fillEditForm(found) : (Swal.fire({ icon: 'error', title: 'Error', text: 'Data tidak ditemukan' }), window.closeModal());
                    })
                    .catch(() => { Swal.fire({ icon: 'error', title: 'Error', text: 'Gagal memuat data' }); window.closeModal(); });
            }
        }
    };

    /* ── CLOSE MODAL ─────────────────────────────────────────────────── */
    window.closeModal = function () {
        document.getElementById('modal')?.classList.add('hidden');
        document.getElementById('modal')?.classList.remove('flex');
        editingId = null;
        if (adminMap) { adminMap.remove(); adminMap = null; adminMarker = null; }
    };

    /* ── HITUNG TOTAL ────────────────────────────────────────────────── */
    window.calculateTotal = function () {
        const sel = document.getElementById('layananId');
        const totalEl = document.getElementById('totalPrice');
        if (!sel || !sel.value) { if (totalEl) totalEl.value = ''; return; }
        const price     = parseFloat(sel.options[sel.selectedIndex].getAttribute('data-price')) || 0;
        const w         = parseFloat(document.getElementById('weight')?.value) || 0;
        const express   = document.getElementById('isExpress')?.checked ? 10000 : 0;
        const total     = (price * w) + (express * w) + 5000;
        if (totalEl) totalEl.value = total > 0 ? 'Rp ' + total.toLocaleString('id-ID') : '';
    };

    /* ── SAVE ORDER ──────────────────────────────────────────────────── */
    window.saveOrder = async function (e) {
        e.preventDefault();
        const layananId = document.getElementById('layananId')?.value;
        if (!layananId) { Swal.fire({ icon: 'error', title: 'Gagal!', text: 'Silakan pilih layanan' }); return; }
        const data = {
            customer_name:  document.getElementById('customerName')?.value,
            customer_phone: document.getElementById('customerPhone')?.value,
            layanan_id:     layananId,
            weight:         parseFloat(document.getElementById('weight')?.value),
            address:        document.getElementById('address')?.value   || null,
            latitude:       document.getElementById('latitude')?.value  || null,
            longitude:      document.getElementById('longitude')?.value || null,
            notes:          document.getElementById('notes')?.value     || null,
        };
        if (!editingId) {
            data.is_express = document.getElementById('isExpress')?.checked ? 1 : 0;
            data.status     = document.getElementById('status')?.value;
        }
        try {
            const url = editingId ? `${storeUrl}/${editingId}` : storeUrl;
            const res = await fetch(url, {
                method: editingId ? 'PUT' : 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                body: JSON.stringify(data)
            });
            const result = await res.json();
            if (!res.ok) { Swal.fire({ icon: 'error', title: 'Gagal!', text: result.errors ? Object.values(result.errors).flat().join('\n') : result.message }); return; }
            Swal.fire({ icon: 'success', title: 'Berhasil!', text: result.message, timer: 3000, showConfirmButton: false, position: 'top-end', toast: true, timerProgressBar: true });
            window.closeModal();
            loadOrders();
        } catch (e) { Swal.fire({ icon: 'error', title: 'Error!', text: e.message }); }
    };

    window.editOrder = id => window.openModal('edit', id);

    /* ── STATUS MODAL ────────────────────────────────────────────────── */
    window.openStatusModal = function (id, currentStatus) {
        statusEditingId = id;
        document.getElementById('newStatus').value = currentStatus;
        document.getElementById('statusModal')?.classList.remove('hidden');
        document.getElementById('statusModal')?.classList.add('flex');
    };

    window.closeStatusModal = function () {
        document.getElementById('statusModal')?.classList.add('hidden');
        document.getElementById('statusModal')?.classList.remove('flex');
        statusEditingId = null;
    };

    window.updateStatus = async function (e) {
        e.preventDefault();
        try {
            const res = await fetch(`${storeUrl}/${statusEditingId}/status`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                body: JSON.stringify({ status: document.getElementById('newStatus')?.value })
            });
            const result = await res.json();
            if (!res.ok) { Swal.fire({ icon: 'error', title: 'Gagal!', text: result.message }); return; }
            Swal.fire({ icon: 'success', title: 'Berhasil!', text: result.message, timer: 3000, showConfirmButton: false, position: 'top-end', toast: true, timerProgressBar: true });
            window.closeStatusModal(); loadOrders();
        } catch (e) { Swal.fire({ icon: 'error', title: 'Error!', text: e.message }); }
    };

    /* ── DELETE ──────────────────────────────────────────────────────── */
    window.deleteOrder = async function (id) {
        const r = await Swal.fire({ title: 'Yakin ingin menghapus?', text: 'Data tidak dapat dikembalikan!', icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#3085d6', confirmButtonText: 'Ya, Hapus!', cancelButtonText: 'Batal' });
        if (!r.isConfirmed) return;
        try {
            const res = await fetch(`${storeUrl}/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' } });
            const d = await res.json();
            Swal.fire({ icon: 'success', title: 'Terhapus!', text: d.message, timer: 3000, showConfirmButton: false, position: 'top-end', toast: true, timerProgressBar: true });
            loadOrders();
        } catch (e) { Swal.fire({ icon: 'error', title: 'Error!', text: 'Gagal menghapus data' }); }
    };

    /* ── INIT ────────────────────────────────────────────────────────── */
    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('searchInput')?.addEventListener('input', loadOrders);
        document.getElementById('filterStatus')?.addEventListener('change', loadOrders);
        loadOrders();
    });

})();