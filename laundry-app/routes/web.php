<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\User\DashboardController;
use App\Http\Controllers\User\PemesananController;
use App\Http\Controllers\User\ProfileController;

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\PesananController;
use App\Http\Controllers\Admin\PelangganController;
use App\Http\Controllers\Admin\LayananController;

/*
|--------------------------------------------------------------------------
| ROOT
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    if (Auth::check()) {
        return Auth::user()->role === 'admin'
            ? redirect()->route('admin.dashboard')
            : redirect()->route('user.dashboard');
    }
    return redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| MIDTRANS CALLBACK (PUBLIC)
|--------------------------------------------------------------------------
*/
Route::post('/midtrans/callback', [PemesananController::class, 'callback'])
    ->name('midtrans.callback');

/*
|--------------------------------------------------------------------------
| AUTH ROUTES (GUEST)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.process');

    Route::get('/register', [AuthController::class, 'registerForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.process');
});

/*
|--------------------------------------------------------------------------
| USER ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:user'])
    ->prefix('user')
    ->name('user.')
    ->group(function () {

        /* Dashboard */
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');

        /* Pemesanan */
        Route::get('/pemesanan', [PemesananController::class, 'index'])
            ->name('pemesanan.index');

        Route::post('/pemesanan', [PemesananController::class, 'store'])
            ->name('pemesanan.store');

        Route::get('/pemesanan/{id}/bayar', [PemesananController::class, 'bayar'])
            ->name('pemesanan.bayar');

        /* Pesanan */
        Route::get('/pesanan', [PemesananController::class, 'myOrders'])
            ->name('pesanan.my');

        Route::get('/pesanan/{id}', [PemesananController::class, 'show'])
            ->name('pesanan.show');

        /* ✅ RIWAYAT (SUDAH DIPINDAH KE DALAM GROUP USER) */
        Route::get('/riwayat', [PemesananController::class, 'riwayat'])
            ->name('riwayat.index');

        /* Profile */
        Route::prefix('profile')->group(function () {

            Route::get('/', [ProfileController::class, 'index'])
                ->name('profile');

            Route::put('/', [ProfileController::class, 'update'])
                ->name('profile.update');

            Route::post('/image', [ProfileController::class, 'updateImage'])
                ->name('profile.image.update');

            Route::delete('/image', [ProfileController::class, 'deleteImage'])
                ->name('profile.image.delete');

            Route::put('/password', [ProfileController::class, 'updatePassword'])
                ->name('profile.password.update');
        });
    });

/*
|--------------------------------------------------------------------------
| ADMIN ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        /* Dashboard */
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])
            ->name('dashboard');

        /* Pesanan */
        Route::get('/pesanan', [PesananController::class, 'index'])
            ->name('pesanan.index');

        Route::get('/pesanan/get-data', [PesananController::class, 'getData'])
            ->name('pesanan.getData');

        Route::post('/pesanan', [PesananController::class, 'store'])
            ->name('pesanan.store');

        Route::put('/pesanan/{id}', [PesananController::class, 'update'])
            ->name('pesanan.update');

        Route::delete('/pesanan/{id}', [PesananController::class, 'destroy'])
            ->name('pesanan.destroy');

        Route::put('/pesanan/{id}/status', [PesananController::class, 'updateStatus'])
            ->name('pesanan.updateStatus');

        Route::put('/pesanan/{id}/final-weight', [PesananController::class, 'updateFinalWeight'])
            ->name('pesanan.updateFinalWeight');

        /* Pelanggan */
        Route::get('/pelanggan', [PelangganController::class, 'index'])
            ->name('pelanggan.index');

        Route::post('/pelanggan', [PelangganController::class, 'store'])
            ->name('pelanggan.store');

        Route::put('/pelanggan/{id}', [PelangganController::class, 'update'])
            ->name('pelanggan.update');

        Route::delete('/pelanggan/{id}', [PelangganController::class, 'destroy'])
            ->name('pelanggan.destroy');

        Route::get('/pelanggan/{id}/riwayat', [PelangganController::class, 'getRiwayatPesanan'])
            ->name('pelanggan.riwayat');

        /* Layanan */
        Route::get('/layanan', [LayananController::class, 'index'])
            ->name('layanan.index');

        Route::post('/layanan', [LayananController::class, 'store'])
            ->name('layanan.store');

        Route::put('/layanan/{id}', [LayananController::class, 'update'])
            ->name('layanan.update');

        Route::delete('/layanan/{id}', [LayananController::class, 'destroy'])
            ->name('layanan.destroy');
    });

/*
|--------------------------------------------------------------------------
| LOGOUT
|--------------------------------------------------------------------------
*/
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');
