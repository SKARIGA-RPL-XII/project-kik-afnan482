/**
 * Pelanggan Management JavaScript
 * Handles map initialization, address geocoding, and modal interactions
 */

const $ = id => document.getElementById(id);

let addMap, addMarker, editMap, editMarker;

const defaultLat = -7.9666;
const defaultLng = 112.6326;

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => { clearTimeout(timeout); func(...args); };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

async function reverseGeocode(lat, lng) {
    try {
        const response = await fetch('/admin/pelanggan/geocode', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ lat, lng }),
        });

        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);

        const data = await response.json();
        return (data.success && data.address) ? data.address : null;
    } catch (error) {
        console.warn('Geocoding failed:', error);
        return null;
    }
}

const debouncedReverseGeocode = debounce(async (lat, lng, callback) => {
    const address = await reverseGeocode(lat, lng);
    if (callback) callback(lat, lng, address);
}, 1000);

/**
 * Modal Management
 */
function openModal(id) {
    $(id).classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    if (id === 'addModal')  setTimeout(() => initAddMap(),  100);
    if (id === 'editModal') setTimeout(() => initEditMap(), 100);
}

function closeModal(id) {
    $(id).classList.add('hidden');
    document.body.style.overflow = 'auto';

    if (id === 'addModal') {
        $('addForm').reset();
        if (addMap) { addMap.remove(); addMap = null; addMarker = null; }
    }
    if (id === 'editModal') {
        $('editForm').reset();
        if (editMap) { editMap.remove(); editMap = null; editMarker = null; }
    }
}

/**
 * Add Map
 */
function initAddMap() {
    if (addMap) addMap.remove();

    addMap = L.map('addMap').setView([defaultLat, defaultLng], 15);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors', maxZoom: 19,
    }).addTo(addMap);

    addMarker = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(addMap);

    const geocoder = L.Control.Geocoder.nominatim({ geocodingQueryParams: { countrycodes: 'id', limit: 5 } });
    L.Control.geocoder({ geocoder, defaultMarkGeocode: false, placeholder: 'Cari alamat...', errorMessage: 'Alamat tidak ditemukan' })
        .on('markgeocode', function (e) {
            const latlng = e.geocode.center;
            addMap.setView(latlng, 16);
            addMarker.setLatLng(latlng);
            updateAddAddress(latlng.lat, latlng.lng, e.geocode.name);
        })
        .addTo(addMap);

    addMarker.on('dragend', function () {
        const pos = addMarker.getLatLng();
        debouncedReverseGeocode(pos.lat, pos.lng, updateAddAddress);
    });

    addMap.on('click', function (e) {
        addMarker.setLatLng(e.latlng);
        debouncedReverseGeocode(e.latlng.lat, e.latlng.lng, updateAddAddress);
    });

    $('addUseMyLocation').onclick = function () {
        if (!navigator.geolocation) return;
        const btn  = this;
        const icon = btn.querySelector('i');
        icon.classList.replace('fa-crosshairs', 'fa-spinner');
        icon.classList.add('fa-spin');
        btn.disabled = true;

        navigator.geolocation.getCurrentPosition(
            async function (pos) {
                const lat = pos.coords.latitude, lng = pos.coords.longitude;
                addMap.setView([lat, lng], 17);
                addMarker.setLatLng([lat, lng]);
                const address = await reverseGeocode(lat, lng);
                updateAddAddress(lat, lng, address);
                icon.classList.remove('fa-spinner', 'fa-spin');
                icon.classList.add('fa-crosshairs');
                btn.disabled = false;
            },
            function () {
                Swal.fire({ icon: 'error', title: 'Gagal Mengakses Lokasi', text: 'Izin lokasi ditolak atau tidak tersedia.' });
                icon.classList.remove('fa-spinner', 'fa-spin');
                icon.classList.add('fa-crosshairs');
                btn.disabled = false;
            }
        );
    };
}

function updateAddAddress(lat, lng, addressText) {
    $('add_latitude').value  = lat;
    $('add_longitude').value = lng;
    $('add_address').value   = addressText || `Lokasi: ${lat.toFixed(6)}, ${lng.toFixed(6)}`;
}

/**
 * Edit Map
 */
function initEditMap() {
    if (editMap) editMap.remove();

    editMap = L.map('editMap').setView([defaultLat, defaultLng], 15);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors', maxZoom: 19,
    }).addTo(editMap);

    editMarker = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(editMap);

    const geocoder = L.Control.Geocoder.nominatim({ geocodingQueryParams: { countrycodes: 'id', limit: 5 } });
    L.Control.geocoder({ geocoder, defaultMarkGeocode: false, placeholder: 'Cari alamat...', errorMessage: 'Alamat tidak ditemukan' })
        .on('markgeocode', function (e) {
            const latlng = e.geocode.center;
            editMap.setView(latlng, 16);
            editMarker.setLatLng(latlng);
            updateEditAddress(latlng.lat, latlng.lng, e.geocode.name);
        })
        .addTo(editMap);

    editMarker.on('dragend', function () {
        const pos = editMarker.getLatLng();
        debouncedReverseGeocode(pos.lat, pos.lng, updateEditAddress);
    });

    editMap.on('click', function (e) {
        editMarker.setLatLng(e.latlng);
        debouncedReverseGeocode(e.latlng.lat, e.latlng.lng, updateEditAddress);
    });

    $('editUseMyLocation').onclick = function () {
        if (!navigator.geolocation) return;
        const btn  = this;
        const icon = btn.querySelector('i');
        icon.classList.replace('fa-crosshairs', 'fa-spinner');
        icon.classList.add('fa-spin');
        btn.disabled = true;

        navigator.geolocation.getCurrentPosition(
            async function (pos) {
                const lat = pos.coords.latitude, lng = pos.coords.longitude;
                editMap.setView([lat, lng], 17);
                editMarker.setLatLng([lat, lng]);
                const address = await reverseGeocode(lat, lng);
                updateEditAddress(lat, lng, address);
                icon.classList.remove('fa-spinner', 'fa-spin');
                icon.classList.add('fa-crosshairs');
                btn.disabled = false;
            },
            function () {
                Swal.fire({ icon: 'error', title: 'Gagal Mengakses Lokasi', text: 'Izin lokasi ditolak atau tidak tersedia.' });
                icon.classList.remove('fa-spinner', 'fa-spin');
                icon.classList.add('fa-crosshairs');
                btn.disabled = false;
            }
        );
    };
}

function updateEditAddress(lat, lng, addressText) {
    $('edit_latitude').value  = lat;
    $('edit_longitude').value = lng;
    $('edit_address').value   = addressText || `Lokasi: ${lat.toFixed(6)}, ${lng.toFixed(6)}`;
}

/**
 * View Detail Modal
 */
function viewDetail(data) {
    const totalPesanan = data.pesanans_count ?? 0;
    $('viewContent').innerHTML = `
        <div class="space-y-4">
            <div class="flex items-center pb-4 border-b">
                <div class="w-16 h-16 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold text-2xl mr-4">
                    ${data.name.charAt(0).toUpperCase()}
                </div>
                <div>
                    <h4 class="font-bold text-lg">${data.name}</h4>
                    <p class="text-sm text-gray-500">${totalPesanan} Total Pesanan</p>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-xs text-gray-500">Email</p>
                    <p class="font-semibold text-sm break-all">${data.email}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Telepon</p>
                    <p class="font-semibold text-sm">${data.phone || '-'}</p>
                </div>
                <div class="col-span-2">
                    <p class="text-xs text-gray-500">Alamat</p>
                    <p class="font-semibold text-sm">${data.address || '-'}</p>
                </div>
            </div>
        </div>
    `;
    openModal('viewModal');
}

/**
 * Edit Pelanggan Modal
 */
function editPelanggan(data) {
    $('editForm').action = `/admin/pelanggan/${data.id}`;
    $('edit_name').value    = data.name;
    $('edit_email').value   = data.email;
    $('edit_phone').value   = data.phone   || '';
    $('edit_address').value = data.address || '';
    $('edit_password').value              = '';
    $('edit_password_confirmation').value = '';

    if (data.latitude && data.longitude) {
        $('edit_latitude').value  = data.latitude;
        $('edit_longitude').value = data.longitude;
    }

    openModal('editModal');

    if (data.latitude && data.longitude) {
        setTimeout(() => {
            if (editMap && editMarker) {
                const lat = parseFloat(data.latitude);
                const lng = parseFloat(data.longitude);
                editMap.setView([lat, lng], 16);
                editMarker.setLatLng([lat, lng]);
            }
        }, 200);
    }
}

/**
 * Delete Pelanggan Modal
 */
function deletePelanggan(id, name) {
    $('deleteForm').action    = `/admin/pelanggan/${id}`;
    $('deleteName').textContent = name;
    openModal('deleteModal');
}

/**
 * View Riwayat Pesanan
 */
async function viewRiwayat(userId) {
    openModal('riwayatModal');
    $('riwayatContent').innerHTML = `
        <div class="text-center py-8">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-purple-600"></div>
            <p class="text-gray-500 mt-2">Memuat data...</p>
        </div>
    `;

    try {
        const response = await fetch(`/admin/pelanggan/${userId}/riwayat`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        });

        if (!response.ok) throw new Error('Gagal memuat data');
        const data = await response.json();
        if (!data.success) throw new Error(data.message || 'Gagal memuat data');

        const statusColors = {
            'pending':    'bg-yellow-100 text-yellow-800',
            'processing': 'bg-blue-100 text-blue-800',
            'completed':  'bg-green-100 text-green-800',
            'cancelled':  'bg-red-100 text-red-800',
        };

        $('riwayatPelangganInfo').textContent = `${data.pelanggan.name} - ${data.pelanggan.email}`;

        if (data.pesanan.length === 0) {
            $('riwayatContent').innerHTML = `
                <div class="text-center py-12">
                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                    </svg>
                    <p class="text-gray-500">Belum ada riwayat pesanan</p>
                </div>`;
            return;
        }

        let html = '<div class="space-y-3">';
        data.pesanan.forEach(p => {
            const expressBadge = p.is_express
                ? '<span class="ml-2 px-2 py-0.5 bg-yellow-100 text-yellow-800 rounded-full text-xs font-semibold">Express</span>'
                : '';
            html += `
                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <div class="font-bold text-blue-600">${p.invoice}</div>
                            <div class="text-xs text-gray-500 mt-1">${p.created_at}</div>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold ${statusColors[p.status] || 'bg-gray-100 text-gray-800'}">
                            ${p.status.charAt(0).toUpperCase() + p.status.slice(1)}
                        </span>
                    </div>
                    <div class="grid grid-cols-2 gap-3 text-sm mb-3">
                        <div>
                            <span class="text-gray-500">Layanan:</span>
                            <div class="font-medium">${p.service_type}${expressBadge}</div>
                        </div>
                        <div>
                            <span class="text-gray-500">Berat:</span>
                            <div class="font-medium">
                                ${parseFloat(p.weight).toFixed(1)} kg
                                ${p.final_weight ? `<div class="text-green-600 text-xs">${parseFloat(p.final_weight).toFixed(1)} kg (akhir)</div>` : ''}
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-between items-center pt-3 border-t border-gray-100">
                        <span class="text-gray-500 text-sm">Total:</span>
                        <span class="font-bold text-blue-600">Rp ${parseFloat(p.total).toLocaleString('id-ID')}</span>
                    </div>
                </div>`;
        });
        html += '</div>';
        $('riwayatContent').innerHTML = html;

    } catch (error) {
        console.error('Error:', error);
        $('riwayatContent').innerHTML = `
            <div class="text-center py-12">
                <svg class="w-16 h-16 mx-auto text-red-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-red-500">${error.message}</p>
            </div>`;
    }
}

/**
 * Form Validation
 */
function initFormValidation() {
    $('addForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const p = $('add_password').value;
        const c = $('add_password_confirmation').value;
        if (p.length < 8) { Swal.fire({ icon: 'error', title: 'Error', text: 'Password minimal 8 karakter' }); return; }
        if (p !== c)       { Swal.fire({ icon: 'error', title: 'Error', text: 'Password tidak cocok' }); return; }
        this.submit();
    });

    $('editForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const p = $('edit_password').value;
        const c = $('edit_password_confirmation').value;
        if (p && p.length < 8) { Swal.fire({ icon: 'error', title: 'Error', text: 'Password minimal 8 karakter' }); return; }
        if (p && p !== c)      { Swal.fire({ icon: 'error', title: 'Error', text: 'Password tidak cocok' }); return; }
        this.submit();
    });
}

document.addEventListener('DOMContentLoaded', function () {
    initFormValidation();
});

window.openModal       = openModal;
window.closeModal      = closeModal;
window.viewDetail      = viewDetail;
window.editPelanggan   = editPelanggan;
window.deletePelanggan = deletePelanggan;
window.viewRiwayat     = viewRiwayat;