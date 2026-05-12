<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('monthly_salary', 10, 2)->default(0)->after('salary');
            $table->decimal('daily_rate', 10, 2)->default(0)->after('monthly_salary');
            $table->integer('days_worked')->default(0)->after('daily_rate');
            $table->decimal('total_advances', 10, 2)->default(0)->after('days_worked');
            $table->decimal('total_to_receive', 10, 2)->default(0)->after('total_advances');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['monthly_salary', 'daily_rate', 'days_worked', 'total_advances', 'total_to_receive']);
        });
    }
};