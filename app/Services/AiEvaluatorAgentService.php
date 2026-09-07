<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class AiEvaluatorAgentService
{
    public function health(): array
    {
        $url = rtrim(config('services.ai_evaluator_agent.url'), '/') . '/health';

        try {
            Log::info('Consultando health del agente evaluador', ['url' => $url]);

            $response = Http::timeout(5)->get($url);

            return [
                'online' => $response->successful(),
                'status' => $response->status(),
                'data' => $response->json() ?? [],
                'final_url' => $url,
            ];
        } catch (\Throwable $exception) {
            Log::warning('El agente evaluador no respondio al health check', [
                'url' => $url,
                'message' => $exception->getMessage(),
            ]);

            return [
                'online' => false,
                'status' => null,
                'data' => [],
                'error' => $exception->getMessage(),
                'final_url' => $url,
            ];
        }
    }

    public function evaluateReal(): array
    {
        $local = $this->evaluateFromDatabase();
        if (($local['evaluated_predictions'] ?? 0) > 0) {
            return $local;
        }

        $baseUrl = rtrim(config('services.ai_evaluator_agent.url'), '/');
        $urls = [
            $baseUrl . '/real',
            $baseUrl . '/evaluate/database',
        ];
        $lastError = null;
        $lastUrl = $urls[0];

        foreach ($urls as $url) {
            $lastUrl = $url;

            try {
                Log::info('Consultando evaluacion real del agente evaluador', ['url' => $url]);

                $response = Http::retry(1, 400)
                    ->timeout(20)
                    ->get($url);

                if ($response->successful()) {
                    Log::info('Evaluacion real del agente evaluador recibida correctamente', [
                        'url' => $url,
                        'status' => $response->status(),
                    ]);

                    return $this->normalizePayload($response->json() ?? [], $url);
                }

                $lastError = 'Respuesta HTTP ' . $response->status();
                Log::warning('El agente evaluador respondio con error', [
                    'url' => $url,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                if ($response->status() !== 404) {
                    break;
                }
            } catch (\Throwable $exception) {
                $lastError = $exception->getMessage();
                Log::warning('No se pudo contactar al agente evaluador', [
                    'url' => $url,
                    'message' => $exception->getMessage(),
                ]);

                break;
            }
        }

        return $this->emptyPayload(($lastError ?: 'No se pudo cargar la evaluacion real') . ' en ' . $lastUrl, $lastUrl);
    }

    private function evaluateFromDatabase(): array
    {
        $this->ensureTables();
        $this->ensureHistoricalForecasts();

        $productId = (int) request()->query('product_id', 0);
        $page = max(1, (int) request()->query('page', 1));
        $perPage = min(80, max(10, (int) request()->query('per_page', $productId > 0 ? 80 : 40)));
        $baseQuery = DB::table('ai_forecast_snapshots')
            ->whereDate('forecast_end', '<', now()->toDateString())
            ->whereYear('forecast_start', 2026)
            ->whereYear('forecast_end', 2026)
            ->where(function ($query) {
                $query->where('predicted_demand', '>', 0)
                    ->orWhere('actual_demand', '>', 0);
            });

        if ($productId > 0) {
            $baseQuery->where('product_id', $productId);
        }

        $total = (clone $baseQuery)->count();
        $summary = $this->databaseSummary(clone $baseQuery);
        $rows = (clone $baseQuery)
            ->select([
                'product_id',
                'product_name',
                'forecast_start',
                'forecast_end',
                'learning_factor_used',
                'predicted_demand',
                'adjusted_demand_7d',
                'actual_demand',
                'mae',
                'wape',
                'level',
                'factor_after',
            ])
            ->orderByDesc('forecast_start')
            ->orderBy('product_name')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        if ($total === 0) {
            return $this->emptyPayload('No hay predicciones cerradas en la base de datos.', 'database://ai_forecast_snapshots');
        }

        $states = DB::table('ai_learning_states')->get()->keyBy('product_id');
        $items = $rows->map(function ($row) use ($states, $productId) {
            $item = $this->rowItem($row);
            $state = $states->get($row->product_id);
            $item['learning_state'] = [
                'learning_factor' => (float) ($state->learning_factor ?? $item['new_factor']),
                'under_streak' => (int) ($state->under_streak ?? 0),
                'over_streak' => (int) ($state->over_streak ?? 0),
                'last_wape' => $state->last_wape ?? $item['wape'],
                'last_mae' => $state->last_mae ?? $item['mae'],
                'last_level' => $state->last_level ?? $item['level'],
            ];
            if ($productId > 0) {
                $item['weekly_history'] = [];
            }

            return $item;
        })->values()->all();

        if ($productId > 0) {
            $items = array_map(function (array $item) use ($items) {
                $item['weekly_history'] = $items;
                return $item;
            }, $items);
        }

        return [
            'online' => true,
            'error' => null,
            'final_url' => 'database://ai_forecast_snapshots',
            'generated_at' => now()->toIso8601String(),
            'predictions_loaded' => $total,
            'evaluated_predictions' => $total,
            'summary' => $summary,
            'items' => $items,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'has_more' => ($page * $perPage) < $total,
            ],
            'learning_states' => $states->toArray(),
            'data_source' => [
                'type' => 'database',
                'predictions_loaded' => $total,
                'period' => '2026 semanal',
            ],
            'raw' => [],
        ];
    }

    private function ensureHistoricalForecasts(): void
    {
        $year = 2026;
        $weekStarts = $this->weekStartsForYear($year);
        $productIds = DB::table('sale_items')->distinct()->pluck('product_id');
        $products = Product::query()
            ->whereIn('id', $productIds)
            ->orderBy('name')
            ->get(['id', 'name']);

        if ($products->isEmpty()) {
            return;
        }

        $expected = $products->count() * count($weekStarts);
        $existing = DB::table('ai_forecast_snapshots')
            ->whereYear('forecast_start', $year)
            ->count();

        if ($existing >= $expected) {
            return;
        }

        foreach ($products as $product) {
            $factor = 1.0;
            $underStreak = 0;
            $overStreak = 0;
            $last = ['wape' => null, 'mae' => null, 'level' => 'SIN_EVALUAR'];

            foreach ($weekStarts as $weekStart) {
                $weekEnd = $weekStart->copy()->addDays(6);
                $baseDemand = $this->historicalBaseDemand($product->id, $weekStart);
                $predicted = max(0, (int) round($baseDemand * $factor));
                $actual = $this->actualDemand($product->id, $weekStart, $weekEnd);
                $previousFactor = $factor;
                $evaluation = $this->evaluateNumbers($predicted, $actual, $previousFactor, $underStreak, $overStreak);

                $factor = $evaluation['new_factor'];
                $underStreak = $evaluation['under_streak'];
                $overStreak = $evaluation['over_streak'];
                $last = [
                    'wape' => $evaluation['wape'],
                    'mae' => $evaluation['mae'],
                    'level' => $evaluation['level'],
                ];

                DB::table('ai_forecast_snapshots')->updateOrInsert(
                    [
                        'product_id' => $product->id,
                        'forecast_start' => $weekStart->toDateString(),
                        'forecast_end' => $weekEnd->toDateString(),
                    ],
                    [
                        'product_name' => $product->name,
                        'generated_at' => $weekStart->copy()->subDay()->setTime(8, 0),
                        'base_demand_7d' => $baseDemand,
                        'adjusted_demand_7d' => $predicted,
                        'learning_factor_used' => $previousFactor,
                        'predicted_demand' => $predicted,
                        'decision' => $evaluation['decision'],
                        'transfer_qty' => $evaluation['transfer_qty'],
                        'actual_demand' => $actual,
                        'mae' => $evaluation['mae'],
                        'wape' => $evaluation['wape'],
                        'level' => $evaluation['level'],
                        'factor_after' => $evaluation['new_factor'],
                        'evaluated_at' => $weekEnd->copy()->addDay()->setTime(3, 0),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }

            DB::table('ai_learning_states')->updateOrInsert(
                ['product_id' => $product->id],
                [
                    'learning_factor' => $factor,
                    'under_streak' => $underStreak,
                    'over_streak' => $overStreak,
                    'last_wape' => $last['wape'],
                    'last_mae' => $last['mae'],
                    'last_level' => $last['level'],
                    'updated_at' => now(),
                ]
            );
        }
    }

    private function databaseSummary($query): array
    {
        $stats = $query
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('AVG(COALESCE(wape, 0)) as avg_wape')
            ->selectRaw('AVG(COALESCE(mae, 0)) as avg_mae')
            ->selectRaw("SUM(CASE WHEN level = 'BUENO' THEN 1 ELSE 0 END) as good")
            ->selectRaw("SUM(CASE WHEN level = 'REGULAR' THEN 1 ELSE 0 END) as regular")
            ->selectRaw("SUM(CASE WHEN level = 'BAJO' THEN 1 ELSE 0 END) as low")
            ->selectRaw('SUM(CASE WHEN ABS(COALESCE(factor_after, learning_factor_used) - learning_factor_used) >= 0.0001 THEN 1 ELSE 0 END) as changed_factors')
            ->first();

        $avgWape = (float) ($stats->avg_wape ?? 0);

        return [
            'avg_wape' => $avgWape,
            'avg_wape_percent' => $avgWape * 100,
            'avg_mae' => (float) ($stats->avg_mae ?? 0),
            'good' => (int) ($stats->good ?? 0),
            'regular' => (int) ($stats->regular ?? 0),
            'low' => (int) ($stats->low ?? 0),
            'changed_factors' => (int) ($stats->changed_factors ?? 0),
        ];
    }

    private function historicalBaseDemand(int $productId, Carbon $weekStart): int
    {
        $lookbackStart = $weekStart->copy()->subWeeks(4);
        $lookbackEnd = $weekStart->copy()->subDay();
        $lookback = $this->actualDemand($productId, $lookbackStart, $lookbackEnd);

        if ($lookback > 0) {
            return (int) round($lookback / 4);
        }

        return $this->actualDemand($productId, $weekStart, $weekStart->copy()->addDays(6));
    }

    private function actualDemand(int $productId, Carbon $start, Carbon $end): int
    {
        return (int) DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sale_items.product_id', $productId)
            ->whereBetween('sales.created_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            ->sum('sale_items.quantity');
    }

    private function evaluateNumbers(int $predicted, int $actual, float $previousFactor, int $underStreak, int $overStreak): array
    {
        $mae = abs($actual - $predicted);
        $wape = $actual > 0 ? $mae / $actual : ($predicted > 0 ? 1 : 0);
        $wapePercent = $wape * 100;
        $level = $wapePercent <= 15 ? 'BUENO' : ($wapePercent <= 30 ? 'REGULAR' : 'BAJO');
        $direction = 'NEUTRO';
        $newFactor = $previousFactor;

        if ($wapePercent > 15 && $actual > $predicted) {
            $direction = 'SUBESTIMACION';
            $underStreak++;
            $overStreak = 0;
            $newFactor = min(1.5, $previousFactor + ($wapePercent > 30 || $underStreak >= 2 ? 0.05 : 0.02));
        } elseif ($wapePercent > 15 && $actual < $predicted) {
            $direction = 'SOBREESTIMACION';
            $overStreak++;
            $underStreak = 0;
            $newFactor = max(0.65, $previousFactor - ($wapePercent > 30 || $overStreak >= 2 ? 0.05 : 0.02));
        } else {
            $underStreak = 0;
            $overStreak = 0;
        }

        return [
            'mae' => $mae,
            'wape' => round($wape, 4),
            'wape_percent' => round($wapePercent, 2),
            'level' => $level,
            'error_direction' => $direction,
            'new_factor' => round($newFactor, 4),
            'factor_changed' => abs($newFactor - $previousFactor) >= 0.0001,
            'under_streak' => $underStreak,
            'over_streak' => $overStreak,
            'decision' => $direction === 'SUBESTIMACION' && $level === 'BAJO' ? 'CREATE_TRANSFER_REQUEST' : 'OK',
            'transfer_qty' => $direction === 'SUBESTIMACION' && $level === 'BAJO' ? max(50, $mae) : 0,
        ];
    }

    private function rowItem(object $row): array
    {
        $previous = (float) ($row->learning_factor_used ?? 1);
        $new = (float) ($row->factor_after ?? $previous);
        $predicted = (float) ($row->predicted_demand ?? $row->adjusted_demand_7d ?? 0);
        $actual = (float) ($row->actual_demand ?? 0);
        $mae = (float) ($row->mae ?? abs($actual - $predicted));
        $wape = (float) ($row->wape ?? ($actual > 0 ? $mae / $actual : ($predicted > 0 ? 1 : 0)));

        return [
            'product_id' => (int) $row->product_id,
            'product_name' => $row->product_name,
            'period' => [
                'start' => $row->forecast_start,
                'end' => $row->forecast_end,
            ],
            'predicted_demand' => $predicted,
            'actual_demand' => $actual,
            'mae' => $mae,
            'wape' => $wape,
            'wape_percent' => round($wape * 100, 2),
            'level' => $row->level ?? 'SIN_EVALUAR',
            'error_direction' => $this->directionFor($predicted, $actual, $wape * 100),
            'previous_factor' => $previous,
            'new_factor' => $new,
            'factor_changed' => abs($new - $previous) >= 0.0001,
            'reason' => $this->reasonFor($predicted, $actual, $wape * 100),
        ];
    }

    private function directionFor(float $predicted, float $actual, float $wapePercent): string
    {
        if ($wapePercent <= 15) {
            return 'NEUTRO';
        }

        return $actual > $predicted ? 'SUBESTIMACION' : ($actual < $predicted ? 'SOBREESTIMACION' : 'NEUTRO');
    }

    private function reasonFor(float $predicted, float $actual, float $wapePercent): string
    {
        if ($wapePercent <= 15) {
            return 'Prediccion estable: el error semanal se mantiene dentro del rango bueno.';
        }

        if ($actual > $predicted) {
            return 'La venta real supero la prediccion; el factor de aprendizaje aumenta para la siguiente semana.';
        }

        return 'La prediccion quedo por encima de la venta real; el factor de aprendizaje baja para evitar sobrestock.';
    }

    private function weekStartsForYear(int $year): array
    {
        $cursor = Carbon::create($year, 1, 1)->startOfWeek();
        if ((int) $cursor->year < $year) {
            $cursor->addWeek();
        }
        $end = Carbon::create($year, 12, 31)->startOfWeek();
        if ($end->copy()->addDays(6)->year > $year) {
            $end->subWeek();
        }
        $weeks = [];

        while ($cursor->lte($end)) {
            $weeks[] = $cursor->copy();
            $cursor->addWeek();
        }

        return $weeks;
    }

    private function ensureTables(): void
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

    private function normalizePayload(array $payload, string $url): array
    {
        $items = $payload['items'] ?? $payload['evaluations'] ?? $payload['products'] ?? [];
        $dataSource = $payload['data_source'] ?? [];
        $predictionsLoaded = (int) ($payload['predictions_loaded'] ?? data_get($dataSource, 'predictions_loaded', count($items)));

        return [
            'online' => true,
            'error' => null,
            'final_url' => $url,
            'generated_at' => $payload['generated_at'] ?? now()->toIso8601String(),
            'predictions_loaded' => $predictionsLoaded,
            'evaluated_predictions' => (int) ($payload['evaluated_predictions'] ?? count($items)),
            'summary' => $this->summary($payload['summary'] ?? [], $items),
            'items' => collect($items)->map(fn (array $item) => $this->item($item, $payload['learning_states'] ?? []))->values()->all(),
            'learning_states' => $payload['learning_states'] ?? [],
            'data_source' => $dataSource,
            'raw' => $payload,
        ];
    }

    private function summary(array $summary, array $items): array
    {
        return [
            'avg_wape' => (float) ($summary['avg_wape'] ?? 0),
            'avg_wape_percent' => (float) ($summary['avg_wape_percent'] ?? (($summary['avg_wape'] ?? 0) * 100)),
            'avg_mae' => (float) ($summary['avg_mae'] ?? 0),
            'good' => (int) ($summary['good'] ?? collect($items)->where('level', 'BUENO')->count()),
            'regular' => (int) ($summary['regular'] ?? collect($items)->where('level', 'REGULAR')->count()),
            'low' => (int) ($summary['low'] ?? collect($items)->where('level', 'BAJO')->count()),
            'changed_factors' => (int) ($summary['changed_factors'] ?? collect($items)->where('factor_changed', true)->count()),
        ];
    }

    private function item(array $item, array $states): array
    {
        $productId = (int) ($item['product_id'] ?? 0);
        $state = $states[$productId] ?? $states[(string) $productId] ?? [];

        return [
            'product_id' => $productId,
            'product_name' => $item['product_name'] ?? $item['name'] ?? 'Producto ' . $productId,
            'period' => [
                'start' => data_get($item, 'period.start'),
                'end' => data_get($item, 'period.end'),
            ],
            'predicted_demand' => (float) ($item['predicted_demand'] ?? 0),
            'actual_demand' => (float) ($item['actual_demand'] ?? 0),
            'mae' => (float) ($item['mae'] ?? 0),
            'wape' => (float) ($item['wape'] ?? 0),
            'wape_percent' => (float) ($item['wape_percent'] ?? (($item['wape'] ?? 0) * 100)),
            'level' => $item['level'] ?? 'SIN_EVALUAR',
            'error_direction' => $item['error_direction'] ?? 'NEUTRO',
            'previous_factor' => (float) ($item['previous_factor'] ?? data_get($state, 'learning_factor', 1)),
            'new_factor' => (float) ($item['new_factor'] ?? data_get($state, 'learning_factor', 1)),
            'factor_changed' => (bool) ($item['factor_changed'] ?? false),
            'reason' => $item['reason'] ?? 'Evaluacion registrada por el agente adaptativo.',
            'learning_state' => $state,
        ];
    }

    private function emptyPayload(string $error, string $url): array
    {
        return [
            'online' => false,
            'error' => $error,
            'final_url' => $url,
            'generated_at' => now()->toIso8601String(),
            'predictions_loaded' => 0,
            'evaluated_predictions' => 0,
            'summary' => $this->summary([], []),
            'items' => [],
            'learning_states' => [],
            'data_source' => ['type' => 'database', 'predictions_loaded' => 0],
            'raw' => [],
        ];
    }
}
