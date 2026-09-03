<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->normalizeCities();
        $this->addDeferredForeignKeys();
    }

    public function down(): void
    {
        if (Schema::hasTable('damage_reports')) {
            Schema::table('damage_reports', function (Blueprint $table) {
                $table->dropForeign('fk_damage_reports_product_lot');
            });
        }

        foreach (['warehouses', 'companies', 'customers'] as $table) {
            if (Schema::hasColumn($table, 'city_id')) {
                Schema::table($table, fn (Blueprint $blueprint) => $blueprint->dropConstrainedForeignId('city_id'));
            }
        }
    }

    private function normalizeCities(): void
    {
        $this->seedCitiesFrom('warehouses', 'city');
        $this->seedCitiesFrom('companies', 'city');
        $this->seedCitiesFrom('customers', 'city');
        $this->seedCitiesFrom('sales', 'delivery_city');

        foreach (['warehouses', 'companies', 'customers'] as $table) {
            if (! Schema::hasColumn($table, 'city_id')) {
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->foreignId('city_id')->nullable()->after('city')->constrained('cities')->nullOnDelete();
                });
            }

            DB::table($table)
                ->join('cities', DB::raw("LOWER(cities.name)"), '=', DB::raw("LOWER({$table}.city)"))
                ->whereNull("{$table}.city_id")
                ->update(["{$table}.city_id" => DB::raw('cities.id')]);
        }

        if (Schema::hasColumn('sales', 'delivery_city_id')) {
            DB::table('sales')
                ->join('cities', DB::raw('LOWER(cities.name)'), '=', DB::raw('LOWER(sales.delivery_city)'))
                ->whereNull('sales.delivery_city_id')
                ->update(['sales.delivery_city_id' => DB::raw('cities.id')]);
        }
    }

    private function seedCitiesFrom(string $table, string $column): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return;
        }

        DB::table($table)
            ->whereNotNull($column)
            ->select($column)
            ->distinct()
            ->orderBy($column)
            ->pluck($column)
            ->each(function (?string $name) {
                $name = trim((string) $name);
                if ($name === '') {
                    return;
                }

                DB::table('cities')->updateOrInsert(
                    ['code' => strtoupper(substr(preg_replace('/[^A-Za-z0-9]+/', '', $name), 0, 10))],
                    [
                        'name' => $name,
                        'department' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            });
    }

    private function addDeferredForeignKeys(): void
    {
        if (! Schema::hasTable('damage_reports') || ! Schema::hasTable('product_lots')) {
            return;
        }

        Schema::table('damage_reports', function (Blueprint $table) {
            $table->foreign('product_lot_id', 'fk_damage_reports_product_lot')
                ->references('id')
                ->on('product_lots')
                ->cascadeOnDelete();
        });
    }
};
