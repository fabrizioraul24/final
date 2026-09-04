<?php

namespace App\Services;

use App\Models\TransferRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiReplenishmentAgentService
{
    public function health(): array
    {
        $url = rtrim(config('services.ai_agent.url'), '/') . '/health';

        try {
            $response = Http::timeout(5)->get($url);

            return [
                'online' => $response->successful(),
                'status' => $response->status(),
                'data' => $response->json() ?? [],
            ];
        } catch (\Throwable $exception) {
            Log::warning('El agente de reposicion no respondio al health check', [
                'url' => $url,
                'message' => $exception->getMessage(),
            ]);

            return [
                'online' => false,
                'status' => null,
                'data' => [],
                'error' => $exception->getMessage(),
            ];
        }
    }

    public function predict(): array
    {
        $url = rtrim(config('services.ai_agent.url'), '/') . '/api/predict';
        $timeout = (int) config('services.ai_agent.predict_timeout', 180);

        try {
            if (function_exists('set_time_limit')) {
                set_time_limit($timeout + 10);
            }

            $response = Http::retry(2, 1000)
                ->timeout($timeout)
                ->get($url);

            if ($response->failed()) {
                Log::warning('El agente de reposicion respondio con error', [
                    'url' => $url,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return $this->offlinePayload('Respuesta HTTP ' . $response->status());
            }

            return $this->normalizePayload($response->json() ?? []);
        } catch (\Throwable $exception) {
            Log::error('No se pudo contactar al agente de reposicion', [
                'url' => $url,
                'message' => $exception->getMessage(),
            ]);

            return $this->offlinePayload($exception->getMessage());
        }
    }

    public function createPendingRequests(array $transferRequests, ?int $windowHours = null): array
    {
        $windowHours = $windowHours ?? (int) config('services.ai_agent.duplicate_window_hours', 24);
        $created = [];
        $skipped = [];

        foreach ($transferRequests as $request) {
            $productId = (int) ($request['product_id'] ?? 0);
            $qty = (int) ($request['requested_qty'] ?? $request['quantity'] ?? $request['suggested_qty'] ?? 0);

            if ($productId <= 0 || $qty <= 0) {
                $skipped[] = ['request' => $request, 'reason' => 'Solicitud sin product_id o cantidad valida'];
                continue;
            }

            $hasRecentDuplicate = TransferRequest::query()
                ->where('product_id', $productId)
                ->where('created_by_agent', true)
                ->where('status', TransferRequest::STATUS_PENDING)
                ->where('created_at', '>=', Carbon::now()->subHours($windowHours))
                ->exists();

            if ($hasRecentDuplicate) {
                $skipped[] = ['request' => $request, 'reason' => 'Duplicado reciente'];
                continue;
            }

            $created[] = TransferRequest::create([
                'product_id' => $productId,
                'requested_qty' => $qty,
                'status' => TransferRequest::STATUS_PENDING,
                'priority' => $request['priority'] ?? null,
                'reason' => $request['reason'] ?? $request['decision'] ?? json_encode($request, JSON_UNESCAPED_UNICODE),
                'created_by_agent' => true,
            ]);
        }

        return ['created' => $created, 'skipped' => $skipped];
    }

    private function normalizePayload(array $payload): array
    {
        return [
            'online' => true,
            'last_run_at' => now(),
            'forecasts' => $payload['forecasts'] ?? $payload['forecast'] ?? [],
            'transfer_requests' => $payload['transfer_requests'] ?? $payload['restock'] ?? [],
            'alerts' => [
                'low_stock' => data_get($payload, 'alerts.low_stock', []),
                'expiring' => data_get($payload, 'alerts.expiring', []),
                'post_peak_drop' => data_get($payload, 'alerts.post_peak_drop', data_get($payload, 'post_peak_drop', [])),
            ],
            'raw' => $payload,
            'error' => null,
        ];
    }

    private function offlinePayload(string $error): array
    {
        return [
            'online' => false,
            'last_run_at' => now(),
            'forecasts' => [],
            'transfer_requests' => [],
            'alerts' => [
                'low_stock' => [],
                'expiring' => [],
                'post_peak_drop' => [],
            ],
            'raw' => [],
            'error' => $error,
        ];
    }
}
