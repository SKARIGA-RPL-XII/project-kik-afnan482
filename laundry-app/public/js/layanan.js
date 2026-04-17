// CSRF Token setup
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

// URL Store dari data-attribute (lebih aman daripada hardcoded)
const appData = document.getElementById('app-data');
const storeUrl = appData ? appData.getAttribute('data-store-url') : '/admin/layanan';

/**
 * Fitur Pencarian Real-time (Desktop & Mobile)
 */
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            
            // 1. Filter Tabel Desktop
            const tableRows = document.querySelectorAll('#tableBody tr');
            tableRows.forEach(row => {
                // Jangan filter baris "Data tidak ditemukan"
                if (row.id === 'no-data-msg') return;

                const namaLayanan = row.querySelector('td:nth-child(2)')?.textContent.toLowerCase() || '';
                const deskripsi = row.querySelector('td:nth-child(5)')?.textContent.toLowerCase() || '';
                
                if (namaLayanan.includes(searchTerm) || deskripsi.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });

            // 2. Filter Kartu Mobile
            const mobileCards = document.querySelectorAll('#mobileCards > div');
            mobileCards.forEach(card => {
                const text = card.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });

            updateEmptyState(searchTerm);
        });
    }
});

/**
 * Menampilkan pesan jika hasil pencarian kosong
 */
function updateEmptyState(term) {
    const tableBody = document.getElementById('tableBody');
    const oldMsg = document.getElementById('no-data-msg');
    if (oldMsg) oldMsg.remove();

    // Cek baris yang visible (kecuali baris @empty bawaan Laravel)
    const visibleRows = Array.from(tableBody.querySelectorAll('tr'))
                             .filter(tr => tr.style.display !== 'none');

    if (visibleRows.length === 0) {
        const noDataHTML = `
            <tr id="no-data-msg">
                <td colspan="6" class="px-6 py-10 text-center text-gray-500 italic">
                    Layanan "${term}" tidak ditemukan
                </td>
            </tr>`;
        tableBody.insertAdjacentHTML('beforeend', noDataHTML);
    }
}

// --- Fungsi CRUD & UI ---

function toggleSidebar() {
    // Pastikan ID sidebar sesuai dengan di Blade (admin.sidebar)
    const sidebar = document.getElementById('sidebar');
    if (sidebar) sidebar.classList.toggle('-translate-x-full');
    document.getElementById('overlay').classList.toggle('hidden');
}

function openModal() {
    document.getElementById('layananModal').classList.remove('hidden');
    document.getElementById('modalTitle').textContent = 'Tambah Layanan';
    document.getElementById('layananForm').reset();
    document.getElementById('layananId').value = '';
}

function closeModal() {
    document.getElementById('layananModal').classList.add('hidden');
}

function editLayanan(layanan) {
    document.getElementById('layananModal').classList.remove('hidden');
    document.getElementById('modalTitle').textContent = 'Edit Layanan';
    document.getElementById('layananId').value = layanan.id;
    document.getElementById('nama_layanan').value = layanan.nama_layanan;
    document.getElementById('tarif').value = layanan.tarif;
    document.getElementById('satuan').value = layanan.satuan;
    document.getElementById('deskripsi').value = layanan.deskripsi || '';
}

// Handler Submit Form (Create & Update)
document.getElementById('layananForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const id = document.getElementById('layananId').value;
    const data = {
        nama_layanan: document.getElementById('nama_layanan').value,
        tarif: document.getElementById('tarif').value,
        satuan: document.getElementById('satuan').value,
        deskripsi: document.getElementById('deskripsi').value
    };

    // Jika ada ID maka PUT (Update), jika tidak maka POST (Create)
    const url = id ? `${storeUrl}/${id}` : storeUrl;
    const method = id ? 'PUT' : 'POST';

    try {
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success) {
            showToast('success', result.message);
            setTimeout(() => location.reload(), 1500);
        } else {
            Swal.fire('Gagal!', result.message || 'Terjadi kesalahan validasi', 'error');
        }
    } catch (error) {
        Swal.fire('Error!', 'Gagal menyambung ke server', 'error');
    }
});

async function deleteLayanan(id) {
    const result = await Swal.fire({
        title: 'Hapus Layanan?',
        text: 'Data yang dihapus tidak dapat dikembalikan',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444', // Red-500
        cancelButtonColor: '#6b7280',  // Gray-500
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    });

    if (result.isConfirmed) {
        try {
            const response = await fetch(`${storeUrl}/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                showToast('success', data.message);
                setTimeout(() => location.reload(), 1500);
            } else {
                Swal.fire('Gagal!', data.message, 'error');
            }
        } catch (error) {
            Swal.fire('Error!', 'Terjadi kesalahan sistem', 'error');
        }
    }
}

/**
 * Reusable Toast (Konsisten dengan Pesanan)
 */
function showToast(icon, title) {
    Swal.fire({
        icon: icon,
        title: title,
        timer: 2000,
        showConfirmButton: false,
        position: 'top-end',
        toast: true,
        timerProgressBar: true
    });
}

// Close modal when clicking outside content
document.getElementById('layananModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});