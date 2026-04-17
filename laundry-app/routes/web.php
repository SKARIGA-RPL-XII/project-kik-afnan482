<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\User\DashboardController;
use App\Http\Controllers\User\PemesananController;
use App\Http\Controllers\User\ProfileController;

// HAPUS import RiwayatController karena riwayat ditangani PemesananController
// use App\Http\Controllers\User\RiwayatController;

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\PesananController;
use App\Http\Controllers\Admin\PelangganController;
use App\Http\Controllers\Admin\LayananController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Admin\GeocodingController;

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

        Route::post('/pemesanan/{id}/cancel', [PemesananController::class, 'cancel'])
            ->name('pemesanan.cancel');

        /* Detail pesanan — dipakai di riwayat blade */
        Route::get('/pemesanan/{id}/detail', [PemesananController::class, 'show'])
            ->name('pemesanan.detail');

        /* Pesanan */
        Route::get('/pesanan', [PemesananController::class, 'myOrders'])
            ->name('pesanan.my');

        Route::get('/pesanan/{id}', [PemesananController::class, 'show'])
            ->name('pesanan.show');

        /* Riwayat — tetap pakai PemesananController@riwayat */
        Route::get('/riwayat', [PemesananController::class, 'riwayat'])
            ->name('riwayat.index');

        /* Profile */
        Route::prefix('profile')->group(function () {
            Route::get('/', [ProfileController::class, 'index'])->name('profile');
            Route::put('/', [ProfileController::class, 'update'])->name('profile.update');
            Route::post('/image', [ProfileController::class, 'updateImage'])->name('profile.image.update');
            Route::delete('/image', [ProfileController::class, 'deleteImage'])->name('profile.image.delete');
            Route::put('/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
        });
    });

/*
|--------------------------------------------------------------------------
| API ROUTES FOR REAL-TIME MONITORING (USER)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:user'])
    ->prefix('api/user')
    ->name('api.user.')
    ->group(function () {

        Route::get('/orders-status', function () {
            $userId = Auth::id();

            $orders = \App\Models\Pesanan::where('user_id', $userId)
                ->whereNotIn('status', ['cancelled'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($order) {
                    return [
                        'id'             => $order->id,
                        'invoice'        => $order->invoice,
                        'status'         => $order->status,
                        'payment_status' => $order->payment_status ?? 'unpaid',
                        'payment_method' => $order->payment_method ?? 'cash',
                        'service_type'   => $order->service_type,
                        'weight'         => $order->weight,
                        'is_express'     => $order->is_express ?? false,
                        'total'          => $order->total,
                        'subtotal'       => $order->subtotal ?? $order->total,
                        'delivery_fee'   => $order->delivery_fee ?? 5000,
                        'created_at'     => $order->created_at->toISOString(),
                        'snap_token'     => $order->snap_token ?? null,
                    ];
                });

            return response()->json([
                'success'   => true,
                'data'      => $orders,
                'timestamp' => now()->toISOString()
            ]);
        })->name('orders.status');

        Route::get('/orders/{id}', function ($id) {
            $userId = Auth::id();

            $order = \App\Models\Pesanan::where('user_id', $userId)
                ->where('id', $id)
                ->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pesanan tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data'    => [
                    'id'             => $order->id,
                    'invoice'        => $order->invoice,
                    'status'         => $order->status,
                    'payment_status' => $order->payment_status ?? 'unpaid',
                    'payment_method' => $order->payment_method ?? 'cash',
                    'service_type'   => $order->service_type,
                    'weight'         => $order->weight,
                    'is_express'     => $order->is_express ?? false,
                    'address'        => $order->address ?? '-',
                    'notes'          => $order->notes ?? null,
                    'total'          => $order->total,
                    'subtotal'       => $order->subtotal ?? $order->total,
                    'delivery_fee'   => $order->delivery_fee ?? 5000,
                    'created_at'     => $order->created_at->toISOString(),
                    'updated_at'     => $order->updated_at->toISOString(),
                    'snap_token'     => $order->snap_token ?? null,
                ]
            ]);
        })->name('orders.detail');

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
        Route::get('/pesanan', [PesananController::class, 'index'])->name('pesanan.index');
        Route::get('/pesanan/get-data', [PesananController::class, 'getData'])->name('pesanan.getData');
        Route::post('/pesanan', [PesananController::class, 'store'])->name('pesanan.store');
        Route::put('/pesanan/{id}', [PesananController::class, 'update'])->name('pesanan.update');
        Route::delete('/pesanan/{id}', [PesananController::class, 'destroy'])->name('pesanan.destroy');
        Route::put('/pesanan/{id}/status', [PesananController::class, 'updateStatus'])->name('pesanan.updateStatus');
        Route::put('/pesanan/{id}/final-weight', [PesananController::class, 'updateFinalWeight'])->name('pesanan.updateFinalWeight');

        /* Pelanggan — route spesifik HARUS sebelum route dengan parameter {id} */
        Route::post('/pelanggan/geocode', [PelangganController::class, 'reverseGeocode'])
            ->name('pelanggan.geocode');
        Route::get('/pelanggan/{id}/riwayat', [PelangganController::class, 'getRiwayatPesanan'])
            ->name('pelanggan.riwayat');
        Route::get('/pelanggan', [PelangganController::class, 'index'])->name('pelanggan.index');
        Route::post('/pelanggan', [PelangganController::class, 'store'])->name('pelanggan.store');
        Route::put('/pelanggan/{id}', [PelangganController::class, 'update'])->name('pelanggan.update');
        Route::delete('/pelanggan/{id}', [PelangganController::class, 'destroy'])->name('pelanggan.destroy');

        /* Layanan */
        Route::get('/layanan', [LayananController::class, 'index'])->name('layanan.index');
        Route::post('/layanan', [LayananController::class, 'store'])->name('layanan.store');
        Route::put('/layanan/{id}', [LayananController::class, 'update'])->name('layanan.update');
        Route::delete('/layanan/{id}', [LayananController::class, 'destroy'])->name('layanan.destroy');

        /* Laporan */
        Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
        Route::get('/laporan/export-excel', [LaporanController::class, 'exportExcel'])->name('laporan.export.excel');
        Route::get('/laporan/export-pdf', [LaporanController::class, 'exportPdf'])->name('laporan.export.pdf');
        Route::get('/laporan/print', [LaporanController::class, 'print'])->name('laporan.print');
    });

/*
|--------------------------------------------------------------------------
| LOGOUT
|--------------------------------------------------------------------------
*/
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| API GEOCODING (PUBLIC)
|--------------------------------------------------------------------------
*/
Route::get('/api/geocode/reverse', [GeocodingController::class, 'reverseGeocode'])
    ->name('api.geocode.reverse');