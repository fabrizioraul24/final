<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiAgentService
{
    public function generate(int $timeout = 25, int $retries = 2, int $retryDelay = 2000): array
    {
        $url = rtrim(config('services.ai_agent.url'), '/') . '/predict';

        return Cache::remember('ai-agent-response', 60 * 5, function () use ($url, $timeout, $retries, $retryDelay) {
            try {
                $response = Http::retry($retries, $retryDelay)
                    ->timeout($timeout)
                    ->get($url);

                if ($response->failed()) {
                    Log::warning('Agente inteligente respondió con error', [
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);

                    throw new \RuntimeException('No pudimos consultar el agente inteligente. Intenta nuevamente en unos minutos.');
                }

                return $response->json();
            } catch (\Throwable $exception) {
                Log::error('No se pudo contactar al agente inteligente', [
                    'message' => $exception->getMessage(),
                ]);

                throw new \RuntimeException('El agente inteligente no está disponible en este momento.');
            }
        });
    }
}
