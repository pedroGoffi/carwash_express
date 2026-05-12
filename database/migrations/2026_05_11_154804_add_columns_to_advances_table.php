<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('advances', function (Blueprint $table) {
            // Adicionar colunas se não existirem
            if (!Schema::hasColumn('advances', 'type')) {
                $table->string('type')->default('money')->after('reason');
            }
            if (!Schema::hasColumn('advances', 'product_id')) {
                $table->foreignId('product_id')->nullable()->after('type')->constrained()->nullOnDelete();
            }
            if (!Schema::hasColumn('advances', 'quantity')) {
                $table->integer('quantity')->default(1)->after('product_id');
            }
            if (!Schema::hasColumn('advances', 'unit_price')) {
                $table->decimal('unit_price', 10, 2)->default(0)->after('quantity');
            }
        });
    }

    public function down(): void
    {
        Schema::table('advances', function (Blueprint $table) {
            $table->dropColumn(['type', 'product_id', 'quantity', 'unit_price']);
        });
    }
};