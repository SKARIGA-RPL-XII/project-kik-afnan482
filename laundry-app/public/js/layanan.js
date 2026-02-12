// CSRF Token setup
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        // Toggle Sidebar untuk Mobile
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('-translate-x-full');
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

        document.getElementById('layananForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const id = document.getElementById('layananId').value;
            const data = {
                nama_layanan: document.getElementById('nama_layanan').value,
                tarif: document.getElementById('tarif').value,
                satuan: document.getElementById('satuan').value,
                deskripsi: document.getElementById('deskripsi').value
            };

            const url = id ? `/admin/layanan/${id}` : '/admin/layanan';
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
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: result.message,
                        timer: 3000,
                        showConfirmButton: false,
                        position: 'top-end',
                        toast: true,
                        timerProgressBar: true
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: result.message || 'Terjadi kesalahan'
                    });
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: 'Terjadi kesalahan: ' + error.message
                });
            }
        });

        async function deleteLayanan(id) {
            const result = await Swal.fire({
                title: 'Hapus Layanan?',
                text: 'Data yang dihapus tidak dapat dikembalikan',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            });

            if (result.isConfirmed) {
                try {
                    const response = await fetch(`/admin/layanan/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();

                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Terhapus!',
                            text: data.message,
                            timer: 3000,
                            showConfirmButton: false,
                            position: 'top-end',
                            toast: true,
                            timerProgressBar: true
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: data.message || 'Terjadi kesalahan'
                        });
                    }
                } catch (error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: 'Terjadi kesalahan: ' + error.message
                    });
                }
            }
        }

        // Close modal when clicking outside
        document.getElementById('layananModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });