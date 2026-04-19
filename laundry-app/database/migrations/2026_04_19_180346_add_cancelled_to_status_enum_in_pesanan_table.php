<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Ubah ENUM status agar include 'cancelled'
        DB::statement("ALTER TABLE pesanan MODIFY COLUMN status ENUM('pending','proses','selesai','diambil','cancelled') NOT NULL DEFAULT 'pending'");

        // Cleanup: tandai pesanan Midtrans yang status='pending' dan payment_status='pending'
        // sebagai cancelled (ini adalah order zombie yang user buka popup tapi tidak bayar)
        DB::statement("
            UPDATE pesanan
            SET status = 'cancelled', payment_status = 'failed'
            WHERE payment_method = 'midtrans'
              AND status = 'pending'
              AND payment_status = 'pending'
        ");
        
        $affected = DB::select("SELECT ROW_COUNT() as cnt")[0]->cnt ?? 0;
        \Illuminate\Support\Facades\Log::info("Migration: {$affected} zombie Midtrans orders marked as cancelled");
    }

    public function down(): void
    {
        // Revert: hapus 'cancelled' dari ENUM
        // (harus tidak ada baris dengan status='cancelled' sebelum rollback)
        DB::statement("UPDATE pesanan SET status = 'pending' WHERE status = 'cancelled'");
        DB::statement("ALTER TABLE pesanan MODIFY COLUMN status ENUM('pending','proses','selesai','diambil') NOT NULL DEFAULT 'pending'");
    }
};
