<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('service_orders', 'queued_at')) {
                $table->timestamp('queued_at')->nullable()->after('status');
            }
            if (!Schema::hasColumn('service_orders', 'delivered_at')) {
                $table->timestamp('delivered_at')->nullable()->after('finished_at');
            }
            // Índice criado por migração de desempenho dedicada quando necessário.
        });


        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'total_consumptions')) $table->decimal('total_consumptions', 10, 2)->default(0);
            if (!Schema::hasColumn('users', 'active')) $table->boolean('active')->default(true);
            if (!Schema::hasColumn('users', 'hired_at')) $table->timestamp('hired_at')->nullable();
        });

        if (!Schema::hasTable('payroll_histories')) Schema::create('payroll_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('period');
            $table->decimal('base_amount', 12, 2)->default(0);
            $table->decimal('advances_amount', 12, 2)->default(0);
            $table->decimal('consumptions_amount', 12, 2)->default(0);
            $table->decimal('net_amount', 12, 2)->default(0);
            $table->unsignedInteger('days_worked')->default(0);
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            $table->unique(['company_id', 'user_id', 'period']);
            $table->index(['company_id', 'period']);
        });
    }

    public function down(): void
    {
        // Preservar histórico de folha existente nos rollbacks deste upgrade.
        Schema::table('service_orders', function (Blueprint $table) {
            // Índice legado preservado.
            // Nunca remover timestamps operacionais de bases legadas.
        });
    }
};
