<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_order_items', function (Blueprint $table) {
            $table->boolean('is_primary_wash')->default(false);
        });

        // Ordens legadas tinham exatamente uma lavagem. Preserve seu papel sem
        // alterar os produtos, preços e totais históricos.
        DB::table('service_order_items')
            ->whereIn('product_id', DB::table('products')->select('id')->where('type', 'wash'))
            ->update(['is_primary_wash' => true]);
    }

    public function down(): void
    {
        Schema::table('service_order_items', fn (Blueprint $table) => $table->dropColumn('is_primary_wash'));
    }
};
