<!-- Mobile Menu Button -->
<button id="mobileMenuBtn" class="fixed top-4 left-4 z-50 lg:hidden bg-blue-600 text-white p-3 rounded-lg shadow-lg">
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
    </svg>
</button>

<!-- Overlay -->
<div id="overlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden lg:hidden"></div>

<!-- Sidebar -->
<aside id="sidebar" class="fixed lg:sticky lg:top-0 inset-y-0 left-0 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out z-40 w-64 bg-gradient-to-b from-blue-600 to-blue-700 text-white lg:h-screen flex-shrink-0">
    <div class="h-full flex flex-col">
        <!-- Header Section (Fixed - No Scroll) -->
        <div class="p-6 flex-shrink-0">
            <!-- Close Button (Mobile Only) -->
            <button id="closeSidebar" class="lg:hidden absolute top-4 right-4 text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>

            <!-- Logo - Updated to match dashboard.blade.php -->
            <div class="flex items-center space-x-3 mb-8">
                <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center shadow-lg">
                    <i class="fas fa-tshirt text-blue-600"></i>
                </div>
                <div>
                    <h1 class="font-bold text-lg">LaundryKu</h1>
                    <p class="text-xs text-blue-200">Admin Panel</p>
                </div>
            </div>
        </div>

        <!-- Navigation Menu (Scrollable Area) -->
        <nav class="flex-1 px-6 space-y-2 overflow-y-auto">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-3 px-4 py-3 {{ Request::routeIs('admin.dashboard') ? 'bg-white bg-opacity-20' : 'hover:bg-white hover:bg-opacity-10' }} rounded-lg transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                <span class="font-medium">Dashboard</span>
            </a>
            <a href="{{ route('admin.layanan.index') }}" class="flex items-center space-x-3 px-4 py-3 {{ Request::routeIs('admin.layanan.*') ? 'bg-white bg-opacity-20' : 'hover:bg-white hover:bg-opacity-10' }} rounded-lg transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                <span class="font-medium">Layanan</span>
            </a>
            <a href="{{ route('admin.pesanan.index') }}" class="flex items-center space-x-3 px-4 py-3 {{ Request::routeIs('admin.pesanan.*') ? 'bg-white bg-opacity-20' : 'hover:bg-white hover:bg-opacity-10' }} rounded-lg transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
                <span class="font-medium">Pesanan</span>
            </a>
            <a href="{{ route('admin.pelanggan.index') }}" class="flex items-center space-x-3 px-4 py-3 {{ Request::routeIs('admin.pelanggan.*') ? 'bg-white bg-opacity-20' : 'hover:bg-white hover:bg-opacity-10' }} rounded-lg transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <span class="font-medium">Pelanggan</span>
            </a>
            <a href="#" class="flex items-center space-x-3 px-4 py-3 {{ Request::routeIs('admin.pembayaran.*') ? 'bg-white bg-opacity-20' : 'hover:bg-white hover:bg-opacity-10' }} rounded-lg transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <span class="font-medium">Pembayaran</span>
            </a>
        </nav>

        <!-- Logout Button at Bottom (Fixed - No Scroll) -->
        <div class="p-6 flex-shrink-0 border-t border-white border-opacity-20">
            <button id="logoutBtn" type="button" class="w-full flex items-center space-x-3 px-4 py-3 bg-red-500 hover:bg-red-600 rounded-lg transition text-white font-medium">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                </svg>
                <span>Logout</span>
            </button>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
        </div>
    </div>
</aside>

<!-- Add Font Awesome CDN if not already included in your layout -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Mobile Menu Toggle
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const closeSidebar = document.getElementById('closeSidebar');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');

        function openSidebar() {
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.remove('hidden');
        }

        function closeSidebarFunc() {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
        }

        if (mobileMenuBtn) {
            mobileMenuBtn.addEventListener('click', openSidebar);
        }

        if (closeSidebar) {
            closeSidebar.addEventListener('click', closeSidebarFunc);
        }

        if (overlay) {
            overlay.addEventListener('click', closeSidebarFunc);
        }

        // Logout Confirmation
        const logoutBtn = document.getElementById('logoutBtn');
        const logoutForm = document.getElementById('logout-form');

        if (logoutBtn && logoutForm) {
            logoutBtn.addEventListener('click', function(e) {
                e.preventDefault();
                
                Swal.fire({
                    title: "Yakin ingin keluar?",
                    text: "Anda akan keluar dari sistem!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Ya, Logout!",
                    cancelButtonText: "Batal"
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: "Berhasil!",
                            text: "Anda telah logout dari sistem",
                            icon: "success",
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            logoutForm.submit();
                        });
                    }
                });
            });
        }
    });
</script>