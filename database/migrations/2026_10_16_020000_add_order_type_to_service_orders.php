<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_orders', function (Blueprint $table) {
            $table->string('order_type', 16)->default('service');
            $table->index(['company_id', 'order_type', 'created_at'], 'orders_company_type_created_idx');
        });
    }

    public function down(): void
    {
        Schema::table('service_orders', function (Blueprint $table) {
            $table->dropIndex('orders_company_type_created_idx');
            $table->dropColumn('order_type');
        });
    }
};
