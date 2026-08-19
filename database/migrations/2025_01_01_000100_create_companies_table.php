<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('company_type', 50)->index();
            $table->string('name');
            $table->string('nit')->unique();
            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('address')->nullable();
            $table->string('city', 120)->nullable()->index();
            $table->string('owner_first_name');
            $table->string('owner_last_name_paterno');
            $table->string('owner_last_name_materno')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};

/* Nota: Se agregaron índices en company_type y city, y soft deletes para permitir archivado sin pérdida. */

