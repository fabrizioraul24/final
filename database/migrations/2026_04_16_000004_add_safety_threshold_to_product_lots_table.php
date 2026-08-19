<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('product_lots') || Schema::hasColumn('product_lots', 'safety_threshold')) {
            return;
        }

        Schema::table('product_lots', function (Blueprint $table) {
            $table->unsignedInteger('safety_threshold')->default(0)->after('expires_at');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('product_lots') || ! Schema::hasColumn('product_lots', 'safety_threshold')) {
            return;
        }

        Schema::table('product_lots', function (Blueprint $table) {
            $table->dropColumn('safety_threshold');
        });
    }
};
