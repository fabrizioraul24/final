<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transfer_items', function (Blueprint $table) {
            $table->integer('damaged_qty')->default(0)->after('received_qty');
            $table->string('lot_code', 120)->nullable()->after('damaged_qty');
            $table->date('receiving_expires_at')->nullable()->after('lot_code');
            $table->text('receiving_note')->nullable()->after('receiving_expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('transfer_items', function (Blueprint $table) {
            $table->dropColumn(['damaged_qty', 'lot_code', 'receiving_expires_at', 'receiving_note']);
        });
    }
};
