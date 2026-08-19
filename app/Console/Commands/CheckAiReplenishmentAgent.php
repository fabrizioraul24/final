<?php

namespace App\Console\Commands;

use App\Services\AiReplenishmentAgentService;
use Illuminate\Console\Command;

class CheckAiReplenishmentAgent extends Command
{
    protected $signature = 'agent:replenishment-check {--window= : Horas para evitar duplicados por producto}';

    protected $description = 'Consulta el agente inteligente de reposicion y crea solicitudes pendientes sin duplicados recientes.';

    public function handle(AiReplenishmentAgentService $service): int
    {
        $payload = $service->predict();

        if (! $payload['online']) {
            $this->error('El agente no esta disponible: ' . ($payload['error'] ?? 'sin detalle'));
            return self::FAILURE;
        }

        $result = $service->createPendingRequests(
            $payload['transfer_requests'] ?? [],
            $this->option('window') ? (int) $this->option('window') : null
        );

        $this->info('Solicitudes creadas: ' . count($result['created']));
        $this->line('Solicitudes omitidas: ' . count($result['skipped']));

        foreach ($result['created'] as $request) {
            $priority = $request->priority ? " ({$request->priority})" : '';
            $this->line(" - Producto {$request->product_id}: {$request->requested_qty} uds{$priority}");
        }

        return self::SUCCESS;
    }
}
