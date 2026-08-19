<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class PredictController extends Controller
{
    public function index(): View
    {
        try {
            $response = Http::timeout(5)->get(rtrim(config('services.fastapi.url'), '/') . '/predict');
            $response->throw();
        } catch (RequestException $exception) {
            abort(502, 'No se pudo obtener las predicciones del servicio FastAPI.');
        }

        $payload = $response->json();

        return view('predicciones.index', [
            'generatedAt' => $payload['generated_at'] ?? null,
            'forecast' => $payload['forecast'] ?? [],
            'restock' => $payload['restock'] ?? [],
            'alerts' => $payload['alerts'] ?? ['low_stock' => [], 'overstock' => [], 'expiring' => []],
        ]);
    }
}
