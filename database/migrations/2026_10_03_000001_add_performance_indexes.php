<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_orders', function (Blueprint $table) {
            $table->index(['company_id', 'created_at'], 'so_company_created_idx');
            $table->index(['company_id', 'status', 'created_at'], 'so_company_status_created_idx');
            $table->index(['company_id', 'finished_at'], 'so_company_finished_idx');
            $table->index(['company_id', 'customer_type'], 'so_company_customer_type_idx');
        });
        Schema::table('payments', function (Blueprint $table) {
            $table->index(['service_order_id', 'created_at'], 'pay_order_created_idx');
            $table->index(['method', 'service_order_id'], 'pay_method_order_idx');
        });
        Schema::table('advances', function (Blueprint $table) {
            $table->index(['company_id', 'type', 'date'], 'adv_company_type_date_idx');
            $table->index(['user_id', 'date'], 'adv_user_date_idx');
        });
        Schema::table('products', function (Blueprint $table) {
            $table->index(['company_id', 'active', 'type'], 'products_company_active_type_idx');
        });
        Schema::table('service_order_items', function (Blueprint $table) {
            $table->index(['service_order_id', 'product_id'], 'soi_order_product_idx');
            $table->index(['product_id', 'service_order_id'], 'soi_product_order_idx');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->index(['company_id', 'role', 'active'], 'users_company_role_active_idx');
        });
    }

    public function down(): void
    {
        Schema::table('service_orders', function (Blueprint $t) {
            $t->dropIndex('so_company_created_idx');
            $t->dropIndex('so_company_status_created_idx');
            $t->dropIndex('so_company_finished_idx');
            $t->dropIndex('so_company_customer_type_idx');
        });
        Schema::table('payments', function (Blueprint $t) { $t->dropIndex('pay_order_created_idx'); $t->dropIndex('pay_method_order_idx'); });
        Schema::table('advances', function (Blueprint $t) { $t->dropIndex('adv_company_type_date_idx'); $t->dropIndex('adv_user_date_idx'); });
        Schema::table('products', fn (Blueprint $t) => $t->dropIndex('products_company_active_type_idx'));
        Schema::table('service_order_items', function (Blueprint $t) { $t->dropIndex('soi_order_product_idx'); $t->dropIndex('soi_product_order_idx'); });
        Schema::table('users', fn (Blueprint $t) => $t->dropIndex('users_company_role_active_idx'));
    }
};
