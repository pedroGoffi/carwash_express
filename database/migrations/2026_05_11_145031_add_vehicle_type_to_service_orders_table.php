<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('service_orders', 'vehicle_type')) {
            Schema::table('service_orders', function (Blueprint $table) {
                $table->string('vehicle_type')->default('normal');
            });
        }
    }

    public function down(): void
    {
        Schema::table('service_orders', function (Blueprint $table) {
            $table->dropColumn('vehicle_type');
        });
    }
};