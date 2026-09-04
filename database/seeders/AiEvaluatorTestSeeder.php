<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class AiEvaluatorTestSeeder extends Seeder
{
    public function run(): void
    {
        $this->ensureEvaluatorTables();

        $category = $this->category();
        $seller = $this->seller();
        $warehouse = $this->warehouse();
        $periodStart = Carbon::today()->subDays(14);
        $periodEnd = Carbon::today()->subDays(8);

        // Son casos cerrados: el evaluador debe poder compararlos contra ventas ya registradas.
        $cases = [
            ['name' => 'AE Leche Entera 1L', 'sku' => 'AE-LECHE-ENTERA-1L', 'predicted' => 700, 'actual' => 735, 'state' => [1.00, 0, 0], 'decision' => 'OK'],
            ['name' => 'AE Yogurt Natural 1L', 'sku' => 'AE-YOGURT-NATURAL-1L', 'predicted' => 560, 'actual' => 590, 'state' => [1.00, 0, 0], 'decision' => 'OK'],
            ['name' => 'AE Queso Fresco 500g', 'sku' => 'AE-QUESO-FRESCO-500G', 'predicted' => 420, 'actual' => 400, 'state' => [1.00, 0, 0], 'decision' => 'OK'],
            ['name' => 'AE Leche Deslactosada 1L', 'sku' => 'AE-LECHE-DESL-1L', 'predicted' => 500, 'actual' => 680, 'state' => [1.00, 1, 0], 'decision' => 'CREATE_TRANSFER_REQUEST'],
            ['name' => 'AE Yogurt Frutilla 1L', 'sku' => 'AE-YOGURT-FRUTILLA-1L', 'predicted' => 640, 'actual' => 850, 'state' => [1.05, 1, 0], 'decision' => 'CREATE_TRANSFER_REQUEST'],
            ['name' => 'AE Mantequilla 200g', 'sku' => 'AE-MANTEQUILLA-200G', 'predicted' => 320, 'actual' => 460, 'state' => [1.00, 1, 0], 'decision' => 'CREATE_TRANSFER_REQUEST'],
            ['name' => 'AE Crema de Leche 250ml', 'sku' => 'AE-CREMA-250ML', 'predicted' => 900, 'actual' => 690, 'state' => [1.00, 0, 1], 'decision' => 'OK'],
            ['name' => 'AE Leche Chocolatada 200ml', 'sku' => 'AE-CHOCOLATADA-200ML', 'predicted' => 760, 'actual' => 560, 'state' => [0.98, 0, 1], 'decision' => 'OK'],
            ['name' => 'AE Pack Familiar Lacteo', 'sku' => 'AE-PACK-FAMILIAR', 'predicted' => 300, 'actual' => 780, 'state' => [1.00, 1, 0], 'decision' => 'CREATE_TRANSFER_REQUEST'],
            ['name' => 'AE Bebida Probiótica 180ml', 'sku' => 'AE-PROBIOTICA-180ML', 'predicted' => 240, 'actual' => 12, 'state' => [1.00, 0, 1], 'decision' => 'OK'],
        ];

        $products = collect($cases)->map(fn (array $case) => $this->product($case, $category))->values();

        $this->clearPreviousTestData($products->pluck('id')->all(), $seller->id, $periodStart, $periodEnd);

        foreach ($cases as $index => $case) {
            $product = $products[$index];
            $this->createSalesForProduct($product, $seller, $warehouse, $periodStart, $case['actual']);
            $this->createLearningState($product, $case['state']);
            $this->createForecastSnapshot($product, $case, $periodStart, $periodEnd);
        }
    }

    private function ensureEvaluatorTables(): void
    {
        if (! Schema::hasTable('ai_forecast_snapshots')) {
            Schema::create('ai_forecast_snapshots', function ($table) {
                $table->id();
                $table->unsignedBigInteger('product_id');
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
            Schema::create('ai_learning_states', function ($table) {
                $table->unsignedBigInteger('product_id')->primary();
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

    private function category(): Category
    {
        return Category::firstOrCreate(
            ['name' => 'Agente Evaluador'],
            ['description' => 'Productos usados para probar evaluaciones reales.']
        );
    }

    private function seller(): User
    {
        $role = Role::firstOrCreate(['name' => 'Administrador'], ['description' => 'Acceso administrativo']);

        return User::firstOrCreate(
            ['email' => 'agente.evaluador@pil.test'],
            [
                'role_id' => $role->id,
                'name' => 'Seeder Agente Evaluador',
                'username' => 'agente_evaluador',
                'password' => Hash::make('password'),
            ]
        );
    }

    private function warehouse(): Warehouse
    {
        return Warehouse::firstOrCreate(
            ['code' => 'AE-LPZ'],
            [
                'name' => 'Almacen Evaluador La Paz',
                'address' => 'Datos de prueba',
                'city' => 'La Paz',
                'capacity_min' => 0,
                'capacity_max' => 10000,
            ]
        );
    }

    private function product(array $case, Category $category): Product
    {
        return Product::updateOrCreate(
            ['sku' => $case['sku']],
            [
                'category_id' => $category->id,
                'name' => $case['name'],
                'description' => 'Producto de prueba para el agente evaluador adaptativo.',
                'suggested_price_public' => 8.50,
                'price_institutional' => 7.20,
                'is_active' => true,
                'min_quantity' => 50,
                'max_quantity' => 2000,
            ]
        );
    }

    private function clearPreviousTestData(array $productIds, int $sellerId, Carbon $periodStart, Carbon $periodEnd): void
    {
        DB::table('sale_items')->whereIn('product_id', $productIds)->delete();

        DB::table('sales')
            ->where('seller_id', $sellerId)
            ->where('delivery_address', 'Seeder agente evaluador')
            ->whereBetween(DB::raw('DATE(created_at)'), [$periodStart->toDateString(), $periodEnd->toDateString()])
            ->delete();

        DB::table('ai_forecast_snapshots')->whereIn('product_id', $productIds)->delete();
        DB::table('ai_learning_states')->whereIn('product_id', $productIds)->delete();
    }

    private function createSalesForProduct(Product $product, User $seller, Warehouse $warehouse, Carbon $periodStart, int $actualTotal): void
    {
        if ($actualTotal <= 0) {
            return;
        }

        $dailyBase = intdiv($actualTotal, 7);
        $remainder = $actualTotal % 7;

        for ($day = 0; $day < 7; $day++) {
            $quantity = $dailyBase + ($day < $remainder ? 1 : 0);
            if ($quantity <= 0) {
                continue;
            }

            $createdAt = $periodStart->copy()->addDays($day)->setTime(10, 0);
            $unitPrice = (float) $product->suggested_price_public;
            $subtotal = round($quantity * $unitPrice, 2);

            $saleId = DB::table('sales')->insertGetId([
                'company_id' => null,
                'customer_id' => null,
                'seller_id' => $seller->id,
                'warehouse_id' => $warehouse->id,
                'sale_type' => 'tienda_barrio',
                'delivery_address' => 'Seeder agente evaluador',
                'delivery_city' => 'La Paz',
                'status' => 'entregado',
                'payment_method' => 'Efectivo',
                'amount_received' => $subtotal,
                'change_amount' => 0,
                'total_amount' => $subtotal,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            DB::table('sale_items')->insert([
                'sale_id' => $saleId,
                'product_id' => $product->id,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'subtotal' => $subtotal,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }
    }

    private function createLearningState(Product $product, array $state): void
    {
        DB::table('ai_learning_states')->insert([
            'product_id' => $product->id,
            'learning_factor' => $state[0],
            'under_streak' => $state[1],
            'over_streak' => $state[2],
            'last_wape' => null,
            'last_mae' => null,
            'last_level' => 'SIN_EVALUAR',
            'updated_at' => now(),
        ]);
    }

    private function createForecastSnapshot(Product $product, array $case, Carbon $periodStart, Carbon $periodEnd): void
    {
        $factor = (float) $case['state'][0];
        $predicted = (int) $case['predicted'];
        $baseDemand = $factor > 0 ? (int) round($predicted / $factor) : $predicted;

        DB::table('ai_forecast_snapshots')->insert([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'generated_at' => $periodStart->copy()->subDay()->setTime(8, 0),
            'forecast_start' => $periodStart->toDateString(),
            'forecast_end' => $periodEnd->toDateString(),
            'base_demand_7d' => $baseDemand,
            'adjusted_demand_7d' => $predicted,
            'learning_factor_used' => $factor,
            'predicted_demand' => $predicted,
            'decision' => $case['decision'],
            'transfer_qty' => $case['decision'] === 'CREATE_TRANSFER_REQUEST' ? max(50, (int) abs($case['actual'] - $predicted)) : 0,
            'actual_demand' => null,
            'mae' => null,
            'wape' => null,
            'level' => null,
            'factor_after' => null,
            'evaluated_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
