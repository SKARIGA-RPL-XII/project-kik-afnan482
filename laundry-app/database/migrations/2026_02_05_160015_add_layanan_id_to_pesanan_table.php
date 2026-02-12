<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pesanan', function (Blueprint $table) { // Ubah dari 'pesanans' ke 'pesanan'
            $table->foreignId('layanan_id')->nullable()->after('user_id')->constrained('layanans')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('pesanan', function (Blueprint $table) { // Ubah dari 'pesanans' ke 'pesanan'
            $table->dropForeign(['layanan_id']);
            $table->dropColumn('layanan_id');
        });
    }
};