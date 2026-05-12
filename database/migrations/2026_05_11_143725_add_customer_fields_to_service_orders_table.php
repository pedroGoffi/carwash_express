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
        Schema::table('service_orders', function (Blueprint $table) {
            $table->string('customer_type')->default('pf')->after('customer_name');
            $table->string('customer_document')->nullable()->after('customer_type');
            $table->string('contact_name')->nullable()->after('customer_document');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_orders', function (Blueprint $table) {
            //
        });
    }
};
