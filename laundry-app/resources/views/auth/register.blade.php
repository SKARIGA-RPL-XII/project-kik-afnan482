<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Laundry Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateX(-20px); }
            to { opacity: 1; transform: translateX(0); }
        }
        @keyframes zoomIn {
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
        }
        .animate-fade-in { animation: fadeIn 0.6s ease-out; }
        .animate-slide-in { animation: slideIn 0.6s ease-out; }
        .animate-zoom-in { animation: zoomIn 0.5s ease-out; }
        .input-focus:focus { transform: translateY(-2px); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        .btn-hover:hover { transform: translateY(-2px); }
        .feature-item { transition: all 0.3s ease; }
        .feature-item:hover { transform: translateX(5px); }
        .page-transition-overlay {
            position: fixed; top: 0; left: 0;
            width: 100%; height: 100%;
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            z-index: 9999;
            display: flex; align-items: center; justify-content: center;
            opacity: 0; pointer-events: none;
            transition: opacity 0.4s ease;
        }
        .page-transition-overlay.active { opacity: 1; pointer-events: all; }
        .page-entering { animation: zoomIn 0.6s ease-out; }
        .input-error { border-color: #ef4444 !important; }
        .error-message { color: #ef4444; font-size: 0.75rem; margin-top: 0.25rem; }

        /* Map Styles */
        #map {
            height: 280px;
            width: 100%;
            border-radius: 0.5rem;
            border: 2px solid #e5e7eb;
            transition: border-color 0.2s;
            z-index: 1;
        }
        #map:hover { border-color: #3b82f6; }
        #map.map-active { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,0.15); }
        .map-pin-pulse {
            width: 16px; height: 16px;
            background: #2563eb;
            border-radius: 50%;
            border: 3px solid white;
            box-shadow: 0 0 0 4px rgba(37,99,235,0.3);
            animation: mapPulse 1.5s ease-in-out infinite;
        }
        @keyframes mapPulse {
            0%, 100% { box-shadow: 0 0 0 4px rgba(37,99,235,0.3); }
            50% { box-shadow: 0 0 0 8px rgba(37,99,235,0.1); }
        }
        .location-badge {
            display: flex; align-items: center; gap: 0.375rem;
            background: #eff6ff; border: 1px solid #bfdbfe;
            border-radius: 0.375rem; padding: 0.5rem 0.75rem;
            font-size: 0.75rem; color: #1d4ed8;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 via-white to-blue-50 min-h-screen flex items-center justify-center p-4">

    <div class="page-transition-overlay" id="pageTransition">
        <div class="text-center">
            <p class="text-white text-lg font-semibold">Memuat halaman...</p>
        </div>
    </div>

    <div class="w-full max-w-5xl page-entering">

        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden animate-slide-in">
            <div class="grid md:grid-cols-2 gap-0">

                {{-- LEFT PANEL --}}
                <div class="hidden md:flex bg-gradient-to-br from-blue-600 to-blue-700 p-12 items-center justify-center relative overflow-hidden">
                    <div class="text-white relative z-10">
                        <h2 class="text-2xl font-bold mb-6 text-center">Bergabunglah dengan Kami</h2>
                        <ul class="space-y-3">
                            @foreach([
                                'Pemesanan laundry online yang mudah',
                                'Monitoring status laundry secara real-time',
                                'Notifikasi otomatis via WhatsApp',
                                'Lacak lokasi penjemputan & pengantaran',
                                'Riwayat transaksi yang lengkap',
                            ] as $feature)
                            <li class="flex items-start feature-item">
                                <svg class="w-6 h-6 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span>{{ $feature }}</span>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="absolute inset-0 bg-blue-500 opacity-10">
                        <div class="absolute top-0 left-0 w-40 h-40 bg-white rounded-full -translate-x-1/2 -translate-y-1/2"></div>
                        <div class="absolute bottom-0 right-0 w-60 h-60 bg-white rounded-full translate-x-1/2 translate-y-1/2"></div>
                    </div>
                </div>

                {{-- RIGHT PANEL / FORM --}}
                <div class="p-8 md:p-10 overflow-y-auto max-h-screen">
                    <div class="mb-6">
                        <h2 class="text-2xl font-bold text-gray-800 mb-1">Buat Akun Baru</h2>
                        <p class="text-gray-500 text-sm">Silakan isi formulir di bawah untuk mendaftar</p>
                    </div>

                    <form action="{{ route('register') }}" method="POST" id="registerForm" class="space-y-4">
                        @csrf

                        {{-- Nama --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">
                                Nama Lengkap <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                                <input type="text" name="name" id="name"
                                    class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all input-focus @error('name') input-error @enderror"
                                    placeholder="Masukkan nama lengkap"
                                    value="{{ old('name') }}" required>
                            </div>
                            @error('name') <p class="error-message">{{ $message }}</p> @enderror
                        </div>

                        {{-- Email --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">
                                Email <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <input type="email" name="email" id="email"
                                    class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all input-focus @error('email') input-error @enderror"
                                    placeholder="email@example.com"
                                    value="{{ old('email') }}" required>
                            </div>
                            @error('email') <p class="error-message">{{ $message }}</p> @enderror
                        </div>

                        {{-- Nomor HP --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">
                                Nomor Telepon / WhatsApp <span class="text-gray-400 text-xs">(Opsional)</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                </div>
                                <input type="text" name="phone" id="phone"
                                    oninput="this.value=this.value.replace(/[^0-9]/g,'')"
                                    class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all input-focus @error('phone') input-error @enderror"
                                    placeholder="08xxxxxxxxxx"
                                    value="{{ old('phone') }}">
                            </div>
                            <p class="mt-1 text-xs text-gray-400">Untuk notifikasi status laundry via WhatsApp</p>
                            @error('phone') <p class="error-message">{{ $message }}</p> @enderror
                        </div>

                        {{-- ========== LOKASI SECTION ========== --}}
                        <div class="border border-gray-200 rounded-xl p-4 bg-gray-50 space-y-3">
                            <div class="flex items-center justify-between">
                                <label class="block text-sm font-semibold text-gray-700">
                                    📍 Lokasi Anda <span class="text-gray-400 text-xs">(Opsional)</span>
                                </label>
                                <button type="button" id="btnDetectLocation"
                                    class="flex items-center gap-1.5 text-xs font-medium text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 border border-blue-200 px-3 py-1.5 rounded-lg transition-all">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    Deteksi Otomatis
                                </button>
                            </div>

                            <p class="text-xs text-gray-500">Klik pada peta untuk menentukan lokasi Anda, atau gunakan deteksi otomatis. Lokasi digunakan untuk estimasi jarak penjemputan laundry.</p>

                            {{-- Map --}}
                            <div id="map"></div>

                            {{-- Koordinat Badge --}}
                            <div id="locationBadge" class="location-badge hidden">
                                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                </svg>
                                <span id="locationText">Lokasi belum dipilih</span>
                                <button type="button" id="btnClearLocation" class="ml-auto text-gray-400 hover:text-red-500 transition-colors" title="Hapus lokasi">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>

                            {{-- Alamat --}}
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Alamat Lengkap</label>
                                <textarea name="address" id="address" rows="2"
                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all resize-none @error('address') input-error @enderror"
                                    placeholder="Alamat akan terisi otomatis saat Anda memilih lokasi di peta, atau isi manual...">{{ old('address') }}</textarea>
                                @error('address') <p class="error-message">{{ $message }}</p> @enderror
                            </div>

                            {{-- Hidden lat/lng inputs --}}
                            <input type="hidden" name="latitude"  id="latitude"  value="{{ old('latitude') }}">
                            <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude') }}">
                        </div>
                        {{-- ========== END LOKASI SECTION ========== --}}

                        {{-- Password --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">
                                Password <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                </div>
                                <input type="password" name="password" id="password"
                                    class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all input-focus @error('password') input-error @enderror"
                                    placeholder="Minimal 8 karakter" required>
                            </div>
                            @error('password') <p class="error-message">{{ $message }}</p> @enderror
                        </div>

                        {{-- Konfirmasi Password --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">
                                Konfirmasi Password <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <input type="password" name="password_confirmation" id="password_confirmation"
                                    class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all input-focus"
                                    placeholder="Ulangi password" required>
                            </div>
                        </div>

                        <button type="submit"
                            class="w-full bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold py-3 rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all duration-300 shadow-lg hover:shadow-xl btn-hover mt-2">
                            Daftar Sekarang
                        </button>
                    </form>

                    <div class="mt-5 text-center">
                        <p class="text-sm text-gray-600">
                            Sudah punya akun?
                            <a href="{{ route('login') }}" class="text-blue-600 font-semibold hover:text-blue-700 hover:underline transition-colors page-link">
                                Login di sini
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mt-6 text-sm text-gray-400 animate-fade-in">
            <p>&copy; 2026 Sistem Informasi Laundry. All rights reserved.</p>
        </div>
    </div>

    <script>
        // ============================================================
        // PAGE TRANSITION
        // ============================================================
        window.addEventListener('load', function () {
            document.getElementById('pageTransition').classList.remove('active');

            @if($errors->any())
                let errorMessages = '';
                @foreach($errors->all() as $error)
                    errorMessages += '{{ $error }}<br>';
                @endforeach
                Swal.fire({ icon: 'error', title: 'Pendaftaran Gagal', html: errorMessages, confirmButtonColor: '#2563eb' });
            @endif

            @if(session('success'))
                Swal.fire({ icon: 'success', title: 'Berhasil!', text: '{{ session("success") }}', confirmButtonColor: '#2563eb' });
            @endif

            @if(session('error'))
                Swal.fire({ icon: 'error', title: 'Gagal!', text: '{{ session("error") }}', confirmButtonColor: '#2563eb' });
            @endif
        });

        document.querySelectorAll('.page-link').forEach(link => {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                const href = this.getAttribute('href');
                document.getElementById('pageTransition').classList.add('active');
                setTimeout(() => window.location.href = href, 400);
            });
        });

        // ============================================================
        // LEAFLET MAP
        // ============================================================
        // Default center: Malang, East Java
        const DEFAULT_LAT = -7.9666;
        const DEFAULT_LNG = 112.6326;
        const DEFAULT_ZOOM = 13;

        const map = L.map('map').setView([DEFAULT_LAT, DEFAULT_LNG], DEFAULT_ZOOM);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
            maxZoom: 19,
        }).addTo(map);

        // Custom marker icon
        const markerIcon = L.divIcon({
            className: '',
            html: `<div class="map-pin-pulse"></div>`,
            iconSize: [16, 16],
            iconAnchor: [8, 8],
        });

        let marker = null;

        // Restore old value if Laravel validation fails
        @if(old('latitude') && old('longitude'))
            placeMarker({{ old('latitude') }}, {{ old('longitude') }}, false);
        @endif

        function placeMarker(lat, lng, reverseGeocode = true) {
            if (marker) {
                marker.setLatLng([lat, lng]);
            } else {
                marker = L.marker([lat, lng], { icon: markerIcon, draggable: true }).addTo(map);
                marker.on('dragend', function (e) {
                    const pos = e.target.getLatLng();
                    updateHiddenFields(pos.lat, pos.lng);
                    if (reverseGeocode) doReverseGeocode(pos.lat, pos.lng);
                });
            }

            map.panTo([lat, lng]);
            updateHiddenFields(lat, lng);

            if (reverseGeocode) doReverseGeocode(lat, lng);
        }

        function updateHiddenFields(lat, lng) {
            document.getElementById('latitude').value  = lat.toFixed(7);
            document.getElementById('longitude').value = lng.toFixed(7);

            const badge    = document.getElementById('locationBadge');
            const locText  = document.getElementById('locationText');
            badge.classList.remove('hidden');
            locText.textContent = `${lat.toFixed(5)}, ${lng.toFixed(5)}`;
            document.getElementById('map').classList.add('map-active');
        }

        function clearLocation() {
            if (marker) { map.removeLayer(marker); marker = null; }
            document.getElementById('latitude').value  = '';
            document.getElementById('longitude').value = '';
            document.getElementById('address').value   = '';
            document.getElementById('locationBadge').classList.add('hidden');
            document.getElementById('map').classList.remove('map-active');
        }

        async function doReverseGeocode(lat, lng) {
            try {
                const res  = await fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}&accept-language=id`);
                const data = await res.json();
                if (data && data.display_name) {
                    document.getElementById('address').value = data.display_name;
                }
            } catch (err) {
                // Geocode gagal, biarkan user isi manual
            }
        }

        // Click on map → place marker
        map.on('click', function (e) {
            placeMarker(e.latlng.lat, e.latlng.lng, true);
        });

        // Detect location button
        document.getElementById('btnDetectLocation').addEventListener('click', function () {
            if (!navigator.geolocation) {
                Swal.fire({ icon: 'warning', title: 'Tidak Didukung', text: 'Browser Anda tidak mendukung geolokasi.', confirmButtonColor: '#2563eb' });
                return;
            }

            const btn = this;
            btn.disabled = true;
            btn.innerHTML = `<svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/></svg> Mendeteksi...`;

            navigator.geolocation.getCurrentPosition(
                function (pos) {
                    const lat = pos.coords.latitude;
                    const lng = pos.coords.longitude;
                    placeMarker(lat, lng, true);
                    map.setView([lat, lng], 16);
                    btn.disabled = false;
                    btn.innerHTML = `<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg> Deteksi Otomatis`;
                },
                function (err) {
                    btn.disabled = false;
                    btn.innerHTML = `<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg> Deteksi Otomatis`;
                    Swal.fire({
                        icon: 'warning',
                        title: 'Gagal Mendeteksi Lokasi',
                        text: 'Izinkan akses lokasi di browser Anda, atau pilih lokasi manual di peta.',
                        confirmButtonColor: '#2563eb'
                    });
                },
                { enableHighAccuracy: true, timeout: 10000 }
            );
        });

        // Clear location
        document.getElementById('btnClearLocation').addEventListener('click', clearLocation);

        // ============================================================
        // FORM VALIDATION
        // ============================================================
        document.getElementById('registerForm').addEventListener('submit', function (e) {
            const password = document.getElementById('password').value;
            const confirm  = document.getElementById('password_confirmation').value;

            if (password !== confirm) {
                e.preventDefault();
                Swal.fire({ icon: 'error', title: 'Password Tidak Cocok', text: 'Password dan konfirmasi password harus sama!', confirmButtonColor: '#2563eb' });
                return false;
            }
            if (password.length < 8) {
                e.preventDefault();
                Swal.fire({ icon: 'error', title: 'Password Terlalu Pendek', text: 'Password minimal 8 karakter!', confirmButtonColor: '#2563eb' });
                return false;
            }
            return true;
        });
    </script>
</body>
</html>