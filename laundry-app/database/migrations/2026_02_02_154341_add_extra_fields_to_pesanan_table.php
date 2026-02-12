<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
  public function up()
{
    Schema::table('pesanan', function (Blueprint $table) {

        // ❌ HAPUS is_express (karena sudah ada)
        // $table->boolean('is_express')->default(false)->after('service_type');

        $table->integer('express_fee')->default(0)->after('price_per_kg');
        $table->integer('delivery_fee')->default(0)->after('express_fee');

        $table->integer('subtotal')->after('delivery_fee');
        $table->integer('total')->after('subtotal');

        $table->text('address')->nullable()->after('total');
        $table->text('notes')->nullable()->after('address');

        $table->enum('payment_method', ['cash', 'transfer'])
              ->default('cash')
              ->after('notes');

        $table->string('estimated_duration')->nullable()->after('payment_method');
    });
}

public function down()
{
    Schema::table('pesanan', function (Blueprint $table) {
        $table->dropColumn([
            'is_express',
            'express_fee',
            'delivery_fee',
            'subtotal',
            'total',
            'address',
            'notes',
            'payment_method',
            'estimated_duration'
        ]);
    });
}

};
