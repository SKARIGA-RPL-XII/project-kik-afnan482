<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('pesanan', function (Blueprint $table) {
            $table->id();

            $table->string('invoice')->unique();

            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->string('customer_name');
            $table->string('customer_phone');

            $table->enum('service_type', [
                'cuci_kering',
                'cuci_setrika',
                'setrika_saja'

            ]);

            $table->integer('price_per_kg');
            $table->decimal('weight', 8, 2);

            $table->integer('total_price');

            $table->enum('status', [
                'pending',
                'proses',
                'selesai',
                'diambil'
            ])->default('pending');

            $table->text('note')->nullable();
            $table->timestamp('finished_at')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pesanan');
    }
};
