<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Laundry Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
         @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateX(-20px); }
            to { opacity: 1; transform: translateX(0); }
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        @keyframes zoomIn {
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
        }
        .animate-fade-in { animation: fadeIn 0.6s ease-out; }
        .animate-slide-in { animation: slideIn 0.6s ease-out; }
        .animate-float { animation: float 3s ease-in-out infinite; }
        .animate-zoom-in { animation: zoomIn 0.5s ease-out; }
        .input-focus:focus { transform: translateY(-2px); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
        .btn-hover:hover { transform: translateY(-2px); }
        .feature-item { transition: all 0.3s ease; }
        .feature-item:hover { transform: translateX(5px); }
        
        .page-transition-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.4s ease;
        }
        
        .page-transition-overlay.active {
            opacity: 1;
            pointer-events: all;
        }
        
        .page-entering {
            animation: zoomIn 0.6s ease-out;
        }
        
        .input-error {
            border-color: #ef4444 !important;
        }
        
        .error-message {
            color: #ef4444;
            font-size: 0.75rem;
            margin-top: 0.25rem;
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
                <div class="hidden md:flex bg-gradient-to-br from-blue-600 to-blue-700 p-12 items-center justify-center relative overflow-hidden">
                    <div class="absolute inset-0 opacity-10">
                        <div class="absolute top-0 left-0 w-40 h-40 bg-white rounded-full -translate-x-20 -translate-y-20"></div>
                        <div class="absolute bottom-0 right-0 w-60 h-60 bg-white rounded-full translate-x-20 translate-y-20"></div>
                    </div>
                    <div class="text-white relative z-10">
                      
                        <h2 class="text-2xl font-bold mb-4 text-center">Selamat Datang Kembali!</h2>
                        <p class="text-center text-blue-100 mb-6">Login untuk mengakses dashboard dan fitur-fitur unggulan sistem kami</p>
                        <ul class="space-y-3">
                            <li class="flex items-start feature-item">
                                <svg class="w-6 h-6 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span>Kelola pesanan laundry dengan mudah</span>
                            </li>
                            <li class="flex items-start feature-item">
                                <svg class="w-6 h-6 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span>Pantau status laundry secara real-time</span>
                            </li>
                            <li class="flex items-start feature-item">
                                <svg class="w-6 h-6 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span>Akses riwayat transaksi lengkap</span>
                            </li>
                            <li class="flex items-start feature-item">
                                <svg class="w-6 h-6 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span>Dapatkan notifikasi otomatis</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="p-8 md:p-12 flex items-center">
                    <div class="w-full">
                        <div class="mb-8">
                            <h2 class="text-2xl font-bold text-gray-800 mb-2">Login ke Akun Anda</h2>
                            <p class="text-gray-600 text-sm">Masukkan kredensial Anda untuk melanjutkan</p>
                        </div>

                        <form action="<?php echo e(route('login')); ?>" method="POST" class="space-y-5" id="loginForm">
                            <?php echo csrf_field(); ?>
                            
                            <div class="animate-fade-in">   
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                    <input type="email" name="email" id="email" class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg input-focus focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> input-error <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="email@example.com" value="<?php echo e(old('email')); ?>" required>
                                </div>
                                <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <p class="error-message"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="animate-fade-in">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                        </svg>
                                    </div>
                                    <input type="password" name="password" id="password" class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg input-focus focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> input-error <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="Masukkan password" required>
                                </div>
                                <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <p class="error-message"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="flex items-center justify-between text-sm animate-fade-in">
                                <label class="flex items-center cursor-pointer hover:text-blue-600 transition">
                                    <input type="checkbox" name="remember" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 cursor-pointer">
                                    <span class="ml-2 text-gray-600">Ingat saya</span>
                                </label>
                            </div>

                            <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold py-3 rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all duration-300 shadow-lg hover:shadow-xl btn-hover animate-fade-in">
                                Login
                            </button>
                        </form>

                        <div class="mt-6 text-center animate-fade-in">
                            <p class="text-sm text-gray-600">
                                Belum punya akun? 
                                <a href="<?php echo e(route('register')); ?>" class="text-blue-600 font-semibold hover:text-blue-700 hover:underline transition page-link">
                                    Daftar sekarang
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mt-8 text-sm text-gray-500 animate-fade-in">
            <p>&copy; 2026 Sistem Informasi Laundry. All rights reserved.</p>
        </div>
    </div>

    <script>
        const Toast = Swal.mixin({
            toast: true,
            position: "top-end",
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.onmouseenter = Swal.stopTimer;
                toast.onmouseleave = Swal.resumeTimer;
            }
        });

        window.addEventListener('load', function() {
            document.getElementById('pageTransition').classList.remove('active');

            // Alert dari session Laravel
            <?php if(session('success')): ?>
                Toast.fire({
                    icon: "success",
                    title: "<?php echo e(session('success')); ?>"
                });
            <?php endif; ?>

            <?php if(session('error')): ?>
                Toast.fire({
                    icon: "error",
                    title: "<?php echo e(session('error')); ?>"
                });
            <?php endif; ?>

            <?php if(session('status')): ?>
                Toast.fire({
                    icon: "info",
                    title: "<?php echo e(session('status')); ?>"
                });
            <?php endif; ?>

            <?php if($errors->any()): ?>
                Toast.fire({
                    icon: "error",
                    title: "<?php echo e($errors->first()); ?>"
                });
            <?php endif; ?>
        });

        // Page transition untuk link register
        document.querySelectorAll('.page-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const href = this.getAttribute('href');
                document.getElementById('pageTransition').classList.add('active');
                setTimeout(() => window.location.href = href, 400);
            });
        });

        // PENTING: Validasi sederhana di frontend, tapi submit form secara normal
        // Biar Laravel yang handle validasi database
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            
            // Validasi basic: pastikan tidak kosong
            if (!email || !password) {
                e.preventDefault();
                Toast.fire({
                    icon: 'error',
                    title: 'Email dan password harus diisi!'
                });
                return false;
            }
            
            // Validasi format email
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                Toast.fire({
                    icon: 'error',
                    title: 'Format email tidak valid!'
                });
                return false;
            }
            
            // Jika validasi lolos, submit form NORMAL (bukan pakai fetch)
            // Laravel akan handle pengecekan email & password di database
            return true;
        });
    </script>
</body>
</html><?php /**PATH C:\laragon\www\project-kik-afnan482\laundry-app\resources\views/auth/login.blade.php ENDPATH**/ ?>