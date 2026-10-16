<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('operating_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('category', 40);
            $table->string('description', 500)->nullable();
            $table->decimal('amount', 12, 2);
            $table->date('date');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['company_id', 'date'], 'operating_expenses_company_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operating_expenses');
    }
};
