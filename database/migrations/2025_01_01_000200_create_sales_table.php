<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('seller_id')->constrained('users');
            $table->foreignId('warehouse_id')->constrained('warehouses');
            $table->string('sale_type', 50)->index();
            $table->string('delivery_address')->nullable();
            $table->string('delivery_city', 120)->nullable()->index();
            $table->string('status', 50)->index();
            $table->string('payment_method', 100)->nullable();
            $table->decimal('total_amount', 12, 2);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};

/* Nota: Índices para filtros por tipo/estado/ciudad y soft deletes para conciliaciones. */

