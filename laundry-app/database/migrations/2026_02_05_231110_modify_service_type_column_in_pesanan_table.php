<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pesanan', function (Blueprint $table) {
            // Ubah service_type dari VARCHAR yang terlalu pendek menjadi lebih panjang
            $table->string('service_type', 100)->change();
        });
    }

    public function down()
    {
        Schema::table('pesanan', function (Blueprint $table) {
            $table->string('service_type', 50)->change(); // sesuaikan dengan ukuran awal
        });
    }
};