<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Idempotente: algumas bases Laravel antigas já possuem parte destes campos.
        foreach ([
            'monthly_salary' => fn ($t) => $t->decimal('monthly_salary', 10, 2)->default(0),
            'daily_rate' => fn ($t) => $t->decimal('daily_rate', 10, 2)->default(0),
            'days_worked' => fn ($t) => $t->integer('days_worked')->default(0),
            'total_advances' => fn ($t) => $t->decimal('total_advances', 10, 2)->default(0),
            'total_consumptions' => fn ($t) => $t->decimal('total_consumptions', 10, 2)->default(0),
            'total_to_receive' => fn ($t) => $t->decimal('total_to_receive', 10, 2)->default(0),
            'active' => fn ($t) => $t->boolean('active')->default(true),
            'hired_at' => fn ($t) => $t->timestamp('hired_at')->nullable(),
        ] as $column => $create) {
            if (!Schema::hasColumn('users', $column)) {
                Schema::table('users', fn (Blueprint $table) => $create($table));
            }
        }
    }

    public function down(): void
    {
        // Não remover colunas salariais compartilhadas com instalações legadas.
    }
};
