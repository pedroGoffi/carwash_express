<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_orders', function (Blueprint $table) {
            $table->index(['company_id', 'id'], 'so_company_id_idx');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->index(['created_at', 'service_order_id'], 'pay_created_order_idx');
        });
    }

    public function down(): void
    {
        Schema::table('service_orders', fn (Blueprint $table) => $table->dropIndex('so_company_id_idx'));
        Schema::table('payments', fn (Blueprint $table) => $table->dropIndex('pay_created_order_idx'));
    }
};
