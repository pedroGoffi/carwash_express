<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. COMPANIES
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('legal_name');
            $table->string('document');
            $table->timestamps();
        });

        // 2. USERS
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('password');
            $table->string('role'); // admin, manager, operator
            $table->timestamps();
        });

        // 3. PRODUCTS (lavagens, bebidas, comidas)
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->decimal('price', 10, 2);
            $table->string('type'); // wash, drink, snack
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // 4. SERVICE_ORDERS
        Schema::create('service_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('customer_name')->nullable();
            $table->string('license_plate')->nullable();
            $table->string('code')->unique();
            $table->string('status')->default('queued'); // queued, finished
            $table->decimal('total', 10, 2)->default(0);
            $table->datetime('finished_at')->nullable();
            $table->timestamps();
        });

        // 5. SERVICE_ORDER_ITEMS (pivot)
        Schema::create('service_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained();
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 10, 2);
            $table->timestamps();
        });

        // 6. PAYMENTS (split payments)
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_order_id')->constrained()->cascadeOnDelete();
            $table->string('method'); // cash, credit, debit, pix
            $table->decimal('amount', 10, 2);
            $table->timestamps();
        });

        // Adicionar dentro do método up() da migration principal:

        // NOTES
        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained();
            $table->text('content');
            $table->string('type')->default('note'); // note, cancel_request
            $table->boolean('is_pinned')->default(false);
            $table->unsignedBigInteger('related_id')->nullable(); // para relacionar com service_order_id
            $table->timestamps();
            
            $table->index(['company_id', 'type']);
            $table->index(['user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('service_order_items');
        Schema::dropIfExists('service_orders');
        Schema::dropIfExists('products');
        Schema::dropIfExists('users');
        Schema::dropIfExists('companies');
        Schema::dropIfExists('notes');
    }
};