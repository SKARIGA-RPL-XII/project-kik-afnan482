<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pesanan', function (Blueprint $table) {
            if (!Schema::hasColumn('pesanan', 'order_id')) {
                $table->string('order_id')->nullable()->after('invoice');
            }
            if (!Schema::hasColumn('pesanan', 'snap_token')) {
                $table->text('snap_token')->nullable()->after('order_id');
            }
            if (!Schema::hasColumn('pesanan', 'payment_status')) {
                $table->string('payment_status')->default('pending')->after('status');
            }
            if (!Schema::hasColumn('pesanan', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('payment_status');
            }
        });
    }

    public function down()
    {
        Schema::table('pesanan', function (Blueprint $table) {
            $table->dropColumn(['order_id', 'snap_token', 'payment_status', 'paid_at']);
        });
    }
};