<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_lots', function (Blueprint $table) {
            $table->index(['warehouse_id', 'expires_at', 'product_id'], 'product_lots_warehouse_expiry_product_idx');
            $table->index(['product_id', 'expires_at', 'id'], 'product_lots_product_expiry_id_idx');
        });

        Schema::table('product_lot_movements', function (Blueprint $table) {
            $table->index(['lot_id', 'created_at'], 'product_lot_movements_lot_created_idx');
        });
    }

    public function down(): void
    {
        Schema::table('product_lot_movements', function (Blueprint $table) {
            $table->dropIndex('product_lot_movements_lot_created_idx');
        });

        Schema::table('product_lots', function (Blueprint $table) {
            $table->dropIndex('product_lots_product_expiry_id_idx');
            $table->dropIndex('product_lots_warehouse_expiry_product_idx');
        });
    }
};
