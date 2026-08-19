<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 50)->unique();
            $table->string('address')->nullable();
            $table->string('city', 120)->index();
            $table->unsignedInteger('capacity_min')->default(0);
            $table->unsignedInteger('capacity_max')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouses');
    }
};

/* Nota: Código único y soft deletes para permitir desactivar bodegas temporalmente. */

