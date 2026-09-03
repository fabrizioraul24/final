<?php

namespace Database\Seeders;

use App\Models\DamageReport;
use App\Models\Product;
use App\Models\ProductLot;
use App\Models\ProductLotMovement;
use App\Models\Transfer;
use App\Models\TransferItem;
use App\Models\TransferRequest;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class AgentTransferDemoSeeder extends Seeder
{
    public function run(): void
    {
        $warehouses = Warehouse::query()
            ->whereIn('code', ['LPZ', 'SCZ', 'CBA'])
            ->get()
            ->keyBy('code');

        if ($warehouses->count() < 3) {
            $this->command?->warn('No existen los almacenes LPZ, SCZ y CBA para AgentTransferDemoSeeder.');
            return;
        }

        $products = Product::query()
            ->whereIn('sku', collect($this->scenarios())->pluck('sku'))
            ->get()
            ->keyBy('sku');

        if ($products->count() < 10) {
            $this->command?->warn('Faltan productos base para AgentTransferDemoSeeder. Ejecuta ProductSeeder primero.');
            return;
        }

        $adminId = User::where('email', 'admin@gmail.com')->value('id')
            ?? User::where('email', 'almacen@gmail.com')->value('id')
            ?? User::query()->value('id');

        $warehouseUserId = User::where('email', 'almacen@gmail.com')->value('id') ?? $adminId;
        $sellerId = User::where('email', 'ventas@gmail.com')->value('id') ?? $adminId;

        if (! $adminId) {
            $this->command?->warn('No hay usuarios base suficientes para AgentTransferDemoSeeder.');
            return;
        }

        foreach ($this->scenarios() as $index => $scenario) {
            $product = $products->get($scenario['sku']);
            if (! $product) {
                continue;
            }

            $createdAt = now()->copy()->subDays(18 - $index)->setTime(9 + ($index % 6), 15);
            $approvedAt = $createdAt->copy()->addHours(4);
            $rejectedAt = $createdAt->copy()->addHours(6);

            $sourceWarehouse = $warehouses->get($scenario['source']);
            $lpzWarehouse = $warehouses->get('LPZ');

            if (! $sourceWarehouse || ! $lpzWarehouse) {
                continue;
            }

            $this->upsertLot(
                $product->id,
                $sourceWarehouse->id,
                'AGENT-SOURCE-'.$product->sku.'-'.$sourceWarehouse->code.'-A',
                $scenario['source_stock_a'],
                now()->copy()->addDays($scenario['source_expiry_days_a']),
                $product->min_quantity ?? 0,
                'seed_demo_source',
                'Stock demo para origen del agente'
            );

            $this->upsertLot(
                $product->id,
                $sourceWarehouse->id,
                'AGENT-SOURCE-'.$product->sku.'-'.$sourceWarehouse->code.'-B',
                $scenario['source_stock_b'],
                now()->copy()->addDays($scenario['source_expiry_days_b']),
                $product->min_quantity ?? 0,
                'seed_demo_source',
                'Stock demo adicional para origen del agente'
            );

            $healthyLot = $this->upsertLot(
                $product->id,
                $lpzWarehouse->id,
                'AGENT-LPZ-'.$product->sku.'-SAFE',
                $scenario['lpz_safe_stock'],
                now()->copy()->addDays($scenario['lpz_safe_expiry_days']),
                $product->min_quantity ?? 0,
                'seed_demo_target',
                'Stock demo estable en LPZ'
            );

            $riskLot = $this->upsertLot(
                $product->id,
                $lpzWarehouse->id,
                'AGENT-LPZ-'.$product->sku.'-RISK',
                $scenario['lpz_risk_stock'],
                now()->copy()->addDays($scenario['lpz_risk_expiry_days']),
                $product->min_quantity ?? 0,
                'seed_demo_target',
                'Stock demo critico en LPZ'
            );

            if (($scenario['damage_qty'] ?? 0) > 0) {
                $this->upsertDamage($riskLot, $warehouseUserId, $scenario['damage_qty'], $scenario['damage_comment'], $createdAt->copy()->addDay());
            }

            $transferId = null;
            if ($scenario['request_status'] === TransferRequest::STATUS_APPROVED) {
                $transfer = $this->upsertApprovedTransfer(
                    $product->id,
                    $scenario['request_qty'],
                    $scenario['source'],
                    $sellerId,
                    $warehouseUserId,
                    $createdAt,
                    $approvedAt,
                    $scenario['reason']
                );

                $transferId = $transfer->id;
            }

            $request = TransferRequest::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'reason' => $scenario['reason'],
                ],
                [
                    'requested_qty' => $scenario['request_qty'],
                    'status' => $scenario['request_status'],
                    'priority' => $scenario['priority'],
                    'created_by_agent' => true,
                    'approved_by' => $scenario['request_status'] === TransferRequest::STATUS_APPROVED ? $warehouseUserId : null,
                    'rejected_by' => $scenario['request_status'] === TransferRequest::STATUS_REJECTED ? $warehouseUserId : null,
                    'approved_at' => $scenario['request_status'] === TransferRequest::STATUS_APPROVED ? $approvedAt : null,
                    'rejected_at' => $scenario['request_status'] === TransferRequest::STATUS_REJECTED ? $rejectedAt : null,
                    'decision_reason' => $scenario['decision_reason'],
                    'transfer_id' => $transferId,
                ]
            );

            $request->forceFill([
                'created_at' => $createdAt,
                'updated_at' => $scenario['request_status'] === TransferRequest::STATUS_PENDING
                    ? $createdAt
                    : ($scenario['request_status'] === TransferRequest::STATUS_APPROVED ? $approvedAt : $rejectedAt),
            ])->saveQuietly();
        }
    }

    private function upsertApprovedTransfer(
        int $productId,
        int $requestedQty,
        string $sourceCode,
        int $sellerId,
        int $warehouseUserId,
        Carbon $createdAt,
        Carbon $approvedAt,
        string $reason
    ): Transfer {
        $transfer = Transfer::updateOrCreate(
            [
                'notes' => 'AGENT-DEMO|'.$reason,
            ],
            [
                'from_warehouse_id' => Warehouse::where('code', $sourceCode)->value('id'),
                'to_warehouse_id' => Warehouse::where('code', 'LPZ')->value('id'),
                'requested_by' => $sellerId,
                'approved_by' => $warehouseUserId,
                'status' => Transfer::STATUS_PENDING,
                'expected_date' => $approvedAt->copy()->addDay()->toDateString(),
                'received_date' => null,
            ]
        );

        TransferItem::updateOrCreate(
            [
                'transfer_id' => $transfer->id,
                'product_id' => $productId,
            ],
            [
                'requested_qty' => $requestedQty,
                'received_qty' => null,
                'damaged_qty' => 0,
                'notes' => 'Producto sugerido por el agente inteligente para demo.',
                'lot_code' => null,
                'receiving_expires_at' => null,
                'receiving_note' => null,
            ]
        );

        $transfer->forceFill([
            'created_at' => $createdAt,
            'updated_at' => $approvedAt,
        ])->saveQuietly();

        return $transfer;
    }

    private function upsertLot(
        int $productId,
        int $warehouseId,
        string $lotCode,
        int $quantity,
        Carbon $expiresAt,
        int $safetyThreshold,
        string $movementType,
        string $movementNote
    ): ProductLot {
        $lot = ProductLot::updateOrCreate(
            [
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'lote_code' => $lotCode,
            ],
            [
                'quantity' => $quantity,
                'expires_at' => $expiresAt->toDateString(),
                'safety_threshold' => $safetyThreshold,
            ]
        );

        ProductLotMovement::updateOrCreate(
            [
                'lot_id' => $lot->id,
                'type' => $movementType,
                'note' => $movementNote,
            ],
            [
                'user_id' => null,
                'quantity' => $quantity,
            ]
        );

        return $lot;
    }

    private function upsertDamage(ProductLot $lot, int $reportedBy, int $damagedQty, string $comment, Carbon $reportedAt): void
    {
        DamageReport::updateOrCreate(
            [
                'product_lot_id' => $lot->id,
                'comment' => $comment,
            ],
            [
                'product_id' => $lot->product_id,
                'warehouse_id' => $lot->warehouse_id,
                'reported_by' => $reportedBy,
                'damaged_qty' => $damagedQty,
            ]
        );

        ProductLotMovement::updateOrCreate(
            [
                'lot_id' => $lot->id,
                'type' => 'damage_demo',
                'note' => $comment,
            ],
            [
                'user_id' => $reportedBy,
                'quantity' => -abs($damagedQty),
            ]
        );

        $damageReport = DamageReport::where('product_lot_id', $lot->id)
            ->where('comment', $comment)
            ->first();

        if ($damageReport) {
            $damageReport->forceFill([
                'created_at' => $reportedAt,
                'updated_at' => $reportedAt,
            ])->saveQuietly();
        }
    }

    private function scenarios(): array
    {
        return [
            [
                'sku' => 'PIL-0001',
                'source' => 'SCZ',
                'source_stock_a' => 180,
                'source_stock_b' => 96,
                'source_expiry_days_a' => 40,
                'source_expiry_days_b' => 18,
                'lpz_safe_stock' => 9,
                'lpz_safe_expiry_days' => 11,
                'lpz_risk_stock' => 4,
                'lpz_risk_expiry_days' => 2,
                'damage_qty' => 2,
                'damage_comment' => 'AGENT-DEMO Dano visible en cajas de leche UHT durante recepcion.',
                'request_qty' => 90,
                'request_status' => TransferRequest::STATUS_PENDING,
                'priority' => 'Urgente',
                'reason' => 'AGENT-DEMO Stock critico en LPZ y lote por vencer para Leche fresca natural PIL UHT.',
                'decision_reason' => null,
            ],
            [
                'sku' => 'PIL-0002',
                'source' => 'CBA',
                'source_stock_a' => 170,
                'source_stock_b' => 84,
                'source_expiry_days_a' => 38,
                'source_expiry_days_b' => 16,
                'lpz_safe_stock' => 7,
                'lpz_safe_expiry_days' => 9,
                'lpz_risk_stock' => 3,
                'lpz_risk_expiry_days' => 1,
                'damage_qty' => 1,
                'damage_comment' => 'AGENT-DEMO Envases abollados detectados en lote critico de leche larga vida.',
                'request_qty' => 80,
                'request_status' => TransferRequest::STATUS_PENDING,
                'priority' => 'Urgente',
                'reason' => 'AGENT-DEMO Faltante inmediato en LPZ para Leche natural larga vida PIL.',
                'decision_reason' => null,
            ],
            [
                'sku' => 'PIL-0031',
                'source' => 'SCZ',
                'source_stock_a' => 145,
                'source_stock_b' => 70,
                'source_expiry_days_a' => 28,
                'source_expiry_days_b' => 12,
                'lpz_safe_stock' => 10,
                'lpz_safe_expiry_days' => 14,
                'lpz_risk_stock' => 5,
                'lpz_risk_expiry_days' => 0,
                'damage_qty' => 0,
                'damage_comment' => '',
                'request_qty' => 72,
                'request_status' => TransferRequest::STATUS_PENDING,
                'priority' => 'Alta',
                'reason' => 'AGENT-DEMO Biogurt frutilla con cobertura limitada y vence hoy en LPZ.',
                'decision_reason' => null,
            ],
            [
                'sku' => 'PIL-0035',
                'source' => 'CBA',
                'source_stock_a' => 140,
                'source_stock_b' => 68,
                'source_expiry_days_a' => 24,
                'source_expiry_days_b' => 10,
                'lpz_safe_stock' => 11,
                'lpz_safe_expiry_days' => 16,
                'lpz_risk_stock' => 6,
                'lpz_risk_expiry_days' => 3,
                'damage_qty' => 2,
                'damage_comment' => 'AGENT-DEMO Merma por cadena de frio comprometida en yogurt bebible.',
                'request_qty' => 65,
                'request_status' => TransferRequest::STATUS_APPROVED,
                'priority' => 'Alta',
                'reason' => 'AGENT-DEMO Reposicion preventiva para Yogurt bebible PIL frutilla en LPZ.',
                'decision_reason' => 'Demo aprobada para mostrar creacion automatica del traspaso.',
            ],
            [
                'sku' => 'PIL-0051',
                'source' => 'SCZ',
                'source_stock_a' => 220,
                'source_stock_b' => 110,
                'source_expiry_days_a' => 35,
                'source_expiry_days_b' => 14,
                'lpz_safe_stock' => 14,
                'lpz_safe_expiry_days' => 20,
                'lpz_risk_stock' => 4,
                'lpz_risk_expiry_days' => -2,
                'damage_qty' => 3,
                'damage_comment' => 'AGENT-DEMO Pilfrut con unidades derramadas y vencidas en camara LPZ.',
                'request_qty' => 120,
                'request_status' => TransferRequest::STATUS_APPROVED,
                'priority' => 'Urgente',
                'reason' => 'AGENT-DEMO Pilfrut manzana 190 ml con lote vencido y alta demanda escolar.',
                'decision_reason' => 'Se prioriza reposicion por alta rotacion y vencimiento del lote actual.',
            ],
            [
                'sku' => 'PIL-0055',
                'source' => 'CBA',
                'source_stock_a' => 160,
                'source_stock_b' => 74,
                'source_expiry_days_a' => 42,
                'source_expiry_days_b' => 17,
                'lpz_safe_stock' => 9,
                'lpz_safe_expiry_days' => 25,
                'lpz_risk_stock' => 4,
                'lpz_risk_expiry_days' => 4,
                'damage_qty' => 0,
                'damage_comment' => '',
                'request_qty' => 70,
                'request_status' => TransferRequest::STATUS_APPROVED,
                'priority' => 'Alta',
                'reason' => 'AGENT-DEMO Pilfrut manzana 800 ml con stock bajo para supermercados LPZ.',
                'decision_reason' => 'Aprobado para mantener cobertura antes del fin de semana.',
            ],
            [
                'sku' => 'PIL-0063',
                'source' => 'SCZ',
                'source_stock_a' => 132,
                'source_stock_b' => 66,
                'source_expiry_days_a' => 85,
                'source_expiry_days_b' => 41,
                'lpz_safe_stock' => 18,
                'lpz_safe_expiry_days' => 35,
                'lpz_risk_stock' => 6,
                'lpz_risk_expiry_days' => 6,
                'damage_qty' => 0,
                'damage_comment' => '',
                'request_qty' => 48,
                'request_status' => TransferRequest::STATUS_REJECTED,
                'priority' => 'Media',
                'reason' => 'AGENT-DEMO Nectar Pura Vida Frutts durazno con tendencia mixta en LPZ.',
                'decision_reason' => 'Rechazado en demo para mostrar validacion humana ante demanda incierta.',
            ],
            [
                'sku' => 'PIL-0067',
                'source' => 'CBA',
                'source_stock_a' => 128,
                'source_stock_b' => 60,
                'source_expiry_days_a' => 80,
                'source_expiry_days_b' => 38,
                'lpz_safe_stock' => 20,
                'lpz_safe_expiry_days' => 45,
                'lpz_risk_stock' => 7,
                'lpz_risk_expiry_days' => 5,
                'damage_qty' => 0,
                'damage_comment' => '',
                'request_qty' => 42,
                'request_status' => TransferRequest::STATUS_REJECTED,
                'priority' => 'Media',
                'reason' => 'AGENT-DEMO Nectar PIL durazno caja con stock recuperable sin mover inventario.',
                'decision_reason' => 'Se rechaza porque el stock aun cubre el minimo en la semana.',
            ],
            [
                'sku' => 'PIL-0081',
                'source' => 'SCZ',
                'source_stock_a' => 115,
                'source_stock_b' => 54,
                'source_expiry_days_a' => 95,
                'source_expiry_days_b' => 48,
                'lpz_safe_stock' => 8,
                'lpz_safe_expiry_days' => 21,
                'lpz_risk_stock' => 5,
                'lpz_risk_expiry_days' => -1,
                'damage_qty' => 1,
                'damage_comment' => 'AGENT-DEMO Crema repostera con rotura de empaque y lote vencido.',
                'request_qty' => 55,
                'request_status' => TransferRequest::STATUS_PENDING,
                'priority' => 'Urgente',
                'reason' => 'AGENT-DEMO Crema de leche repostera con quiebre y lote vencido en LPZ.',
                'decision_reason' => null,
            ],
            [
                'sku' => 'PIL-0089',
                'source' => 'CBA',
                'source_stock_a' => 102,
                'source_stock_b' => 50,
                'source_expiry_days_a' => 120,
                'source_expiry_days_b' => 56,
                'lpz_safe_stock' => 12,
                'lpz_safe_expiry_days' => 32,
                'lpz_risk_stock' => 4,
                'lpz_risk_expiry_days' => 8,
                'damage_qty' => 0,
                'damage_comment' => '',
                'request_qty' => 44,
                'request_status' => TransferRequest::STATUS_PENDING,
                'priority' => 'Alta',
                'reason' => 'AGENT-DEMO Mantequilla sin sal con stock bajo frente a pedidos institucionales.',
                'decision_reason' => null,
            ],
        ];
    }
}
