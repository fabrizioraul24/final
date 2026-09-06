<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ai_forecast_snapshots')) {
            Schema::create('ai_forecast_snapshots', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->string('product_name');
                $table->dateTime('generated_at');
                $table->date('forecast_start');
                $table->date('forecast_end');
                $table->integer('base_demand_7d')->default(0);
                $table->integer('adjusted_demand_7d')->default(0);
                $table->decimal('learning_factor_used', 8, 4)->default(1);
                $table->integer('predicted_demand')->default(0);
                $table->string('decision', 80)->default('OK');
                $table->integer('transfer_qty')->default(0);
                $table->integer('actual_demand')->nullable();
                $table->decimal('mae', 12, 4)->nullable();
                $table->decimal('wape', 12, 4)->nullable();
                $table->string('level', 30)->nullable();
                $table->decimal('factor_after', 8, 4)->nullable();
                $table->dateTime('evaluated_at')->nullable();
                $table->timestamps();

                $table->index(['forecast_end', 'evaluated_at'], 'idx_ai_forecast_pending');
                $table->index(['product_id', 'forecast_start', 'forecast_end'], 'idx_ai_forecast_product');
            });
        }

        if (! Schema::hasTable('ai_learning_states')) {
            Schema::create('ai_learning_states', function (Blueprint $table) {
                $table->foreignId('product_id')->primary()->constrained('products')->cascadeOnDelete();
                $table->decimal('learning_factor', 8, 4)->default(1);
                $table->integer('under_streak')->default(0);
                $table->integer('over_streak')->default(0);
                $table->decimal('last_wape', 12, 4)->nullable();
                $table->decimal('last_mae', 12, 4)->nullable();
                $table->string('last_level', 30)->default('SIN_EVALUAR');
                $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_learning_states');
        Schema::dropIfExists('ai_forecast_snapshots');
    }
};
