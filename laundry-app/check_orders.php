<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Status Midtrans orders setelah migration ===\n";
$orders = \App\Models\Pesanan::where('payment_method','midtrans')
    ->orderBy('id','desc')->limit(10)
    ->get(['id','invoice','status','payment_status']);
foreach ($orders as $o) {
    echo "ID:{$o->id} | {$o->invoice} | status:{$o->status} | payment_status:{$o->payment_status}\n";
}

echo "\n=== Test: buat order Midtrans baru dengan payment_status='unpaid' ===\n";
// Simulasi logika baru
$paymentMethod = 'midtrans';
$paymentStatus = ($paymentMethod === 'cash') ? 'unpaid' : 'unpaid';
echo "payment_method: {$paymentMethod} → payment_status awal: {$paymentStatus}\n";
echo "(Harus 'unpaid', bukan 'pending')\n";
