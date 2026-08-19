<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('damage_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_lot_id');
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('damaged_qty');
            $table->text('comment')->nullable();
            $table->timestamps();
        });

        if (Schema::hasTable('product_lots')) {
            Schema::table('damage_reports', function (Blueprint $table) {
                $table->foreign('product_lot_id')
                    ->references('id')
                    ->on('product_lots')
                    ->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('damage_reports');
    }
};
