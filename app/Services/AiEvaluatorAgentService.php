<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
