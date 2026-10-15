<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Todos os campos opcionais. Nenhum dado legado é substituído.
        $staff = [
            'monthly_salary' => fn ($t) => $t->decimal('monthly_salary', 12, 2)->default(0),
            'daily_rate' => fn ($t) => $t->decimal('daily_rate', 12, 2)->default(0),
            'days_worked' => fn ($t) => $t->unsignedInteger('days_worked')->default(0),
            'active' => fn ($t) => $t->boolean('active')->default(true),
            'hired_at' => fn ($t) => $t->date('hired_at')->nullable(),
            'phone' => fn ($t) => $t->string('phone', 32)->nullable(),
            'email_contact' => fn ($t) => $t->string('email_contact')->nullable(),
            'document' => fn ($t) => $t->string('document', 32)->nullable(),
            'job_title' => fn ($t) => $t->string('job_title', 100)->nullable(),
            'employment_type' => fn ($t) => $t->string('employment_type', 24)->nullable(),
            'pix_key' => fn ($t) => $t->string('pix_key')->nullable(),
            'staff_notes' => fn ($t) => $t->text('staff_notes')->nullable(),
        ];
        foreach ($staff as $column => $add) {
            if (!Schema::hasColumn('users', $column)) {
                Schema::table('users', fn (Blueprint $t) => $add($t));
            }
        }
        // Corrige erro PostgreSQL 42703 ao concluir/entregar veículos em bancos antigos.
        foreach ([
            'queued_at' => fn ($t) => $t->timestamp('queued_at')->nullable(),
            'delivered_at' => fn ($t) => $t->timestamp('delivered_at')->nullable(),
            'finished_at' => fn ($t) => $t->timestamp('finished_at')->nullable(),
        ] as $column => $add) {
            if (!Schema::hasColumn('service_orders', $column)) {
                Schema::table('service_orders', fn (Blueprint $t) => $add($t));
            }
        }
        if (!Schema::hasTable('payroll_histories')) {
            Schema::create('payroll_histories', function (Blueprint $t) {
                $t->id();
                $t->foreignId('company_id')->constrained()->cascadeOnDelete();
                $t->foreignId('user_id')->constrained()->cascadeOnDelete();
                $t->date('period');
                $t->decimal('base_amount', 12, 2)->default(0);
                $t->decimal('advances_amount', 12, 2)->default(0);
                $t->decimal('consumptions_amount', 12, 2)->default(0);
                $t->decimal('net_amount', 12, 2)->default(0);
                $t->unsignedInteger('days_worked')->default(0);
                $t->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
                $t->timestamp('closed_at')->nullable();
                $t->timestamps();
                $t->unique(['company_id', 'user_id', 'period']);
                $t->index(['company_id', 'period']);
            });
        } else {
            $columns = [
                'base_amount' => fn ($t) => $t->decimal('base_amount', 12, 2)->default(0),
                'advances_amount' => fn ($t) => $t->decimal('advances_amount', 12, 2)->default(0),
                'consumptions_amount' => fn ($t) => $t->decimal('consumptions_amount', 12, 2)->default(0),
                'net_amount' => fn ($t) => $t->decimal('net_amount', 12, 2)->default(0),
                'days_worked' => fn ($t) => $t->unsignedInteger('days_worked')->default(0),
                'closed_by' => fn ($t) => $t->unsignedBigInteger('closed_by')->nullable(),
                'closed_at' => fn ($t) => $t->timestamp('closed_at')->nullable(),
                'created_at' => fn ($t) => $t->timestamp('created_at')->nullable(),
                'updated_at' => fn ($t) => $t->timestamp('updated_at')->nullable(),
            ];
            foreach ($columns as $column => $add) {
                if (!Schema::hasColumn('payroll_histories', $column)) {
                    Schema::table('payroll_histories', fn (Blueprint $t) => $add($t));
                }
            }
        }
        foreach ([
            'extra_costs_amount' => fn ($t) => $t->decimal('extra_costs_amount', 12, 2)->default(0),
            'employer_cost_amount' => fn ($t) => $t->decimal('employer_cost_amount', 12, 2)->default(0),
        ] as $column => $add) {
            if (!Schema::hasColumn('payroll_histories', $column)) {
                Schema::table('payroll_histories', fn (Blueprint $t) => $add($t));
            }
        }
        if (!Schema::hasTable('employee_expenses')) {
            Schema::create('employee_expenses', function (Blueprint $t) {
                $t->id();
                $t->foreignId('company_id')->constrained()->cascadeOnDelete();
                $t->foreignId('user_id')->constrained()->cascadeOnDelete();
                $t->string('category', 40);
                $t->decimal('amount', 12, 2);
                $t->date('date');
                $t->string('description', 500)->nullable();
                $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $t->timestamps();
                $t->index(['company_id','date','user_id'], 'employee_expenses_comp_date_user_idx');
            });
        }
    }

    public function down(): void
    {
        // Somente a nova tabela é reversível; os dados legados jamais são apagados.
        Schema::dropIfExists('employee_expenses');
    }
};
