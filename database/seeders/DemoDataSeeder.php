<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\BuyerOrder;
use App\Models\BuyerOrderItem;
use App\Models\City;
use App\Models\Company;
use App\Models\Customer;
use App\Models\DamageReport;
use App\Models\Product;
use App\Models\ProductLot;
use App\Models\ProductLotMovement;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Transfer;
use App\Models\TransferItem;
use App\Models\User;
use App\Models\VendorVisit;
use App\Models\Warehouse;
use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    private const SALES_HISTORY_TARGET = 630;

    private const TODAY_SALES_TARGET = 12;

    private const QUOTATIONS_TARGET = 218;

    private const BUYER_ORDERS_TARGET = 180;

    private const VISITS_TARGET = 174;

    private const TRANSFERS_TARGET = 24;

    private const DAMAGES_TARGET = 24;

    private const AUDIT_TARGET = 420;

    public function run(): void
    {
        $faker = Faker::create('es_ES');

        $products = Product::with('category')->get();
        $warehouses = Warehouse::get()->keyBy('code');
        $cities = City::get()->keyBy('name');
        $companies = Company::get()->groupBy('company_type');
        $customers = Customer::with('user')->get();
        $users = User::with('role')->get()->keyBy('email');

        $sellerId = $users->get('ventas@gmail.com')?->id
            ?? $users->get('admin@gmail.com')?->id
            ?? User::query()->value('id');
        $warehouseUserId = $users->get('almacen@gmail.com')?->id ?? $sellerId;
        $adminId = $users->get('admin@gmail.com')?->id ?? $sellerId;

        if ($products->isEmpty() || $warehouses->isEmpty() || $cities->isEmpty() || ! $sellerId || ! $adminId) {
            $this->command?->warn('No hay datos base suficientes para DemoDataSeeder.');
            return;
        }

        $this->seedQuotations($faker, $products, $companies, $customers, $sellerId);
        $this->seedSales($faker, $products, $warehouses, $cities, $companies, $customers, $sellerId);
        $this->seedBuyerOrders($faker, $products, $customers, $sellerId);
        $this->seedVendorVisits($faker, $companies, $sellerId);
        $this->seedTransfers($faker, $products, $warehouses, $sellerId, $warehouseUserId);
        $this->seedDamageReports($faker, $warehouseUserId);
        $this->seedAuditLogs($adminId, $sellerId, $warehouseUserId);
    }

    private function seedQuotations(
        $faker,
        Collection $products,
        Collection $companiesByType,
        Collection $customers,
        int $sellerId
    ): void {
        if (Quotation::count() >= self::QUOTATIONS_TARGET) {
            return;
        }

        $monthlyPlan = [14, 15, 16, 17, 18, 18, 19, 20, 21, 22, 20, 18];

        foreach ($this->monthWindows() as $index => [$monthStart, $monthEnd]) {
            $count = $monthlyPlan[$index] ?? 16;

            for ($i = 0; $i < $count; $i++) {
                $channel = collect([
                    'empresa_institucional',
                    'empresa_institucional',
                    'tienda_barrio',
                    'tienda_barrio',
                    'comprador_minorista',
                ])->random();

                $context = $this->saleContext($channel, $companiesByType, $customers);
                if (! $context) {
                    continue;
                }

                $date = $this->randomDateInWindow($faker, $monthStart, $monthEnd);
                $status = $this->quotationStatusForDate($date);
                $items = $this->pickProductsForSale($products, $channel, $date, rand(2, 5));

                if ($items->isEmpty()) {
                    continue;
                }

                $quotation = Quotation::create([
                    'company_id' => $context['company_id'],
                    'customer_id' => $context['customer_id'],
                    'seller_id' => $sellerId,
                    'sale_type' => $channel,
                    'valid_until' => $date->copy()->addDays(rand(5, 18))->toDateString(),
                    'status' => $status,
                    'total_amount' => 0,
                    'notes' => $this->quotationNote($channel, $status),
                ]);

                $total = 0;

                foreach ($items as $product) {
                    $quantity = max(1, (int) round($this->quantityForSale($product, $channel, $date) * ($channel === 'empresa_institucional' ? 1.2 : 1)));
                    $unitPrice = $channel === 'empresa_institucional'
                        ? (float) $product->price_institutional
                        : (float) $product->suggested_price_public;
                    $subtotal = round($quantity * $unitPrice, 2);

                    $item = QuotationItem::create([
                        'quotation_id' => $quotation->id,
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'subtotal' => $subtotal,
                    ]);

                    $item->forceFill([
                        'created_at' => $date,
                        'updated_at' => $date,
                    ])->saveQuietly();

                    $total += $subtotal;
                }

                $quotation->forceFill([
                    'total_amount' => round($total, 2),
                    'created_at' => $date,
                    'updated_at' => $date,
                ])->saveQuietly();
            }
        }
    }

    private function seedSales(
        $faker,
        Collection $products,
        Collection $warehouses,
        Collection $cities,
        Collection $companiesByType,
        Collection $customers,
        int $sellerId
    ): void {
        if (Sale::count() < self::SALES_HISTORY_TARGET) {
            $monthlyPlan = [34, 36, 38, 42, 46, 50, 54, 58, 62, 66, 70, 74];

            foreach ($this->monthWindows() as $index => [$monthStart, $monthEnd]) {
                $count = $monthlyPlan[$index] ?? 42;

                for ($i = 0; $i < $count; $i++) {
                    $channel = $this->pickChannel();
                    $date = $this->randomDateInWindow($faker, $monthStart, $monthEnd);
                    $context = $this->saleContext($channel, $companiesByType, $customers, $cities);

                    if (! $context) {
                        continue;
                    }

                    $warehouse = $this->warehouseForCity($warehouses, $context['city']);
                    if (! $warehouse) {
                        continue;
                    }

                    $items = $this->pickProductsForSale(
                        $products,
                        $channel,
                        $date,
                        match ($channel) {
                            'empresa_institucional' => rand(3, 6),
                            'tienda_barrio' => rand(3, 5),
                            default => rand(2, 4),
                        }
                    );

                    if ($items->isEmpty()) {
                        continue;
                    }

                    $paymentMethod = $this->paymentMethodForChannel($channel);
                    $status = $this->statusForDate($date);

                    $sale = Sale::create([
                        'company_id' => $context['company_id'],
                        'customer_id' => $context['customer_id'],
                        'seller_id' => $sellerId,
                        'warehouse_id' => $warehouse->id,
                        'sale_type' => $channel,
                        'delivery_address' => $context['address'],
                        'delivery_city' => $context['city'],
                        'delivery_city_id' => $context['city_id'],
                        'status' => $status,
                        'payment_method' => $paymentMethod,
                        'amount_received' => $paymentMethod === 'efectivo' ? 0 : null,
                        'change_amount' => 0,
                        'total_amount' => 0,
                    ]);

                    $total = 0;

                    foreach ($items as $product) {
                        $quantity = $this->quantityForSale($product, $channel, $date);
                        $unitPrice = $channel === 'empresa_institucional'
                            ? (float) $product->price_institutional
                            : (float) $product->suggested_price_public;
                        $subtotal = round($quantity * $unitPrice, 2);

                        $item = SaleItem::create([
                            'sale_id' => $sale->id,
                            'product_id' => $product->id,
                            'quantity' => $quantity,
                            'unit_price' => $unitPrice,
                            'subtotal' => $subtotal,
                        ]);

                        $item->forceFill([
                            'created_at' => $date,
                            'updated_at' => $date,
                        ])->saveQuietly();

                        $this->logSaleMovement($product->id, $warehouse->id, $quantity, $sellerId, $date, $sale->id);
                        $total += $subtotal;
                    }

                    [$amountReceived, $changeAmount] = $this->cashFlowForSale($paymentMethod, $total);

                    $sale->forceFill([
                        'total_amount' => round($total, 2),
                        'amount_received' => $amountReceived,
                        'change_amount' => $changeAmount,
                        'created_at' => $date,
                        'updated_at' => $date,
                    ])->saveQuietly();
                }
            }
        }

        $this->seedTodaySales($faker, $products, $warehouses, $cities, $companiesByType, $customers, $sellerId);
    }

    private function seedTodaySales(
        $faker,
        Collection $products,
        Collection $warehouses,
        Collection $cities,
        Collection $companiesByType,
        Collection $customers,
        int $sellerId
    ): void {
        $existingTodaySales = Sale::whereDate('created_at', now()->toDateString())->count();
        $toCreate = max(0, self::TODAY_SALES_TARGET - $existingTodaySales);

        for ($i = 0; $i < $toCreate; $i++) {
            $channel = $this->pickChannel();
            $context = $this->saleContext($channel, $companiesByType, $customers, $cities);
            if (! $context) {
                continue;
            }

            $warehouse = $this->warehouseForCity($warehouses, $context['city']);
            if (! $warehouse) {
                continue;
            }

            $date = now()->copy()->subHours(rand(0, 13))->subMinutes(rand(0, 59));
            $items = $this->pickProductsForSale($products, $channel, $date, $channel === 'comprador_minorista' ? rand(2, 3) : rand(3, 5));

            if ($items->isEmpty()) {
                continue;
            }

            $paymentMethod = $this->paymentMethodForChannel($channel);

            $sale = Sale::create([
                'company_id' => $context['company_id'],
                'customer_id' => $context['customer_id'],
                'seller_id' => $sellerId,
                'warehouse_id' => $warehouse->id,
                'sale_type' => $channel,
                'delivery_address' => $context['address'],
                'delivery_city' => $context['city'],
                'delivery_city_id' => $context['city_id'],
                'status' => rand(0, 100) < 78 ? 'entregado' : 'sin_entregar',
                'payment_method' => $paymentMethod,
                'amount_received' => $paymentMethod === 'efectivo' ? 0 : null,
                'change_amount' => 0,
                'total_amount' => 0,
            ]);

            $total = 0;

            foreach ($items as $product) {
                $quantity = $this->quantityForSale($product, $channel, $date);
                $unitPrice = $channel === 'empresa_institucional'
                    ? (float) $product->price_institutional
                    : (float) $product->suggested_price_public;
                $subtotal = round($quantity * $unitPrice, 2);

                $item = SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ]);

                $item->forceFill([
                    'created_at' => $date,
                    'updated_at' => $date,
                ])->saveQuietly();

                $this->logSaleMovement($product->id, $warehouse->id, $quantity, $sellerId, $date, $sale->id);
                $total += $subtotal;
            }

            [$amountReceived, $changeAmount] = $this->cashFlowForSale($paymentMethod, $total);

            $sale->forceFill([
                'total_amount' => round($total, 2),
                'amount_received' => $amountReceived,
                'change_amount' => $changeAmount,
                'created_at' => $date,
                'updated_at' => $date,
            ])->saveQuietly();
        }
    }

    private function seedBuyerOrders($faker, Collection $products, Collection $customers, int $sellerId): void
    {
        if (BuyerOrder::count() >= self::BUYER_ORDERS_TARGET || $customers->isEmpty()) {
            return;
        }

        foreach ($this->monthWindows() as $index => [$monthStart, $monthEnd]) {
            $count = 10 + $index;

            for ($i = 0; $i < $count; $i++) {
                $customer = $customers->random();
                $userId = $customer->user_id;

                if (! $userId) {
                    continue;
                }

                $date = $this->randomDateInWindow($faker, $monthStart, $monthEnd);
                $items = $this->pickProductsForSale($products, 'comprador_minorista', $date, rand(2, 4));

                if ($items->isEmpty()) {
                    continue;
                }

                $order = BuyerOrder::create([
                    'user_id' => $userId,
                    'receipt_number' => sprintf('BO-%s-%04d', $date->format('Ym'), BuyerOrder::count() + 1),
                    'payment_method' => collect(['efectivo', 'qr', 'tarjeta_debito'])->random(),
                    'payment_status' => $date->isToday() ? collect(['pagado', 'pagado', 'pendiente'])->random() : collect(['pagado', 'pagado', 'pagado', 'reembolsado'])->random(),
                    'status' => $date->isToday() ? collect(['preparando', 'enviado', 'entregado'])->random() : collect(['entregado', 'entregado', 'entregado', 'cancelado'])->random(),
                    'subtotal' => 0,
                    'shipping' => (float) collect([0, 0, 5, 7.5, 10])->random(),
                    'total' => 0,
                    'issued_at' => $date,
                ]);

                $subtotal = 0;

                foreach ($items as $product) {
                    $quantity = max(1, min(6, $this->quantityForSale($product, 'comprador_minorista', $date)));
                    $unitPrice = (float) $product->suggested_price_public;

                    BuyerOrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'created_at' => $date,
                        'updated_at' => $date,
                    ]);

                    $subtotal += round($quantity * $unitPrice, 2);
                }

                $order->forceFill([
                    'subtotal' => round($subtotal, 2),
                    'total' => round($subtotal + $order->shipping, 2),
                    'created_at' => $date,
                    'updated_at' => $date,
                ])->saveQuietly();
            }
        }
    }

    private function seedVendorVisits($faker, Collection $companiesByType, int $sellerId): void
    {
        if (VendorVisit::count() >= self::VISITS_TARGET) {
            return;
        }

        $companies = $companiesByType->flatten(1)->values();
        if ($companies->isEmpty()) {
            return;
        }

        foreach ($this->monthWindows() as $index => [$monthStart, $monthEnd]) {
            $count = 12 + (int) floor($index / 2);

            for ($i = 0; $i < $count; $i++) {
                $company = $companies->random();
                $date = $this->randomDateInWindow($faker, $monthStart, $monthEnd)->startOfDay();
                $status = $date->isFuture()
                    ? 'pendiente'
                    : collect(['completada', 'completada', 'completada', 'reprogramada'])->random();

                $visit = VendorVisit::create([
                    'user_id' => $sellerId,
                    'company_id' => $company->id,
                    'visit_date' => $date->toDateString(),
                    'status' => $status,
                    'note' => $this->visitNote($company->company_type, $status),
                ]);

                $visit->forceFill([
                    'created_at' => $date->copy()->setTime(rand(7, 18), rand(0, 59)),
                    'updated_at' => $date->copy()->setTime(rand(7, 18), rand(0, 59)),
                ])->saveQuietly();
            }
        }
    }

    private function seedTransfers($faker, Collection $products, Collection $warehouses, int $sellerId, int $warehouseUserId): void
    {
        if (Transfer::count() >= self::TRANSFERS_TARGET || ! $warehouses->has('LPZ')) {
            return;
        }

        $sourceWarehouses = $warehouses->filter(fn (Warehouse $warehouse) => in_array($warehouse->code, ['SCZ', 'CBA'], true))->values();
        $targetWarehouse = $warehouses->first(fn (Warehouse $warehouse) => $warehouse->code === 'LPZ');

        if ($sourceWarehouses->isEmpty() || ! $targetWarehouse) {
            return;
        }

        $focusProducts = $products
            ->filter(fn (Product $product) => in_array($product->sku, [
                'PIL-0001', 'PIL-0002', 'PIL-0031', 'PIL-0035', 'PIL-0051', 'PIL-0055',
                'PIL-0063', 'PIL-0067', 'PIL-0081', 'PIL-0086', 'PIL-0089',
            ], true))
            ->values();

        for ($i = 0; $i < 24; $i++) {
            $status = match (true) {
                $i < 14 => Transfer::STATUS_RECEIVED,
                $i < 20 => Transfer::STATUS_IN_TRANSIT,
                default => Transfer::STATUS_PENDING,
            };

            $createdAt = now()->copy()->subDays(330 - ($i * 11))->setTime(rand(7, 18), rand(0, 59));
            $source = $sourceWarehouses->random();

            $transfer = Transfer::create([
                'from_warehouse_id' => $source->id,
                'to_warehouse_id' => $targetWarehouse->id,
                'requested_by' => $sellerId,
                'approved_by' => $warehouseUserId,
                'received_by' => $status === Transfer::STATUS_RECEIVED ? $warehouseUserId : null,
                'status' => $status,
                'expected_date' => $createdAt->copy()->addDays(rand(1, 6))->toDateString(),
                'received_date' => $status === Transfer::STATUS_RECEIVED ? $createdAt->copy()->addDays(rand(1, 5))->toDateString() : null,
                'notes' => 'Reabastecimiento planificado por rotacion, cobertura regional y proyeccion comercial.',
            ]);

            $items = $focusProducts->shuffle()->take(rand(2, 4));

            foreach ($items as $product) {
                $requestedQty = rand(24, 120);
                $receivedQty = $status === Transfer::STATUS_RECEIVED ? max(8, $requestedQty - rand(0, 10)) : null;
                $damagedQty = $status === Transfer::STATUS_RECEIVED ? rand(0, 4) : 0;
                $expiresAt = $createdAt->copy()->addDays(rand(30, 210))->toDateString();
                $lotCode = sprintf('TRF-%s-%02d', $product->sku, $i + 1);

                TransferItem::create([
                    'transfer_id' => $transfer->id,
                    'product_id' => $product->id,
                    'requested_qty' => $requestedQty,
                    'received_qty' => $receivedQty,
                    'damaged_qty' => $damagedQty,
                    'notes' => 'Traslado interplanta para sostener demanda y lotes sanos en LPZ.',
                    'lot_code' => $status === Transfer::STATUS_RECEIVED ? $lotCode : null,
                    'receiving_expires_at' => $status === Transfer::STATUS_RECEIVED ? $expiresAt : null,
                    'receiving_note' => $status === Transfer::STATUS_RECEIVED ? 'Recepcion validada por almacen.' : null,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                if ($status === Transfer::STATUS_RECEIVED && $receivedQty) {
                    $lot = ProductLot::updateOrCreate(
                        [
                            'warehouse_id' => $targetWarehouse->id,
                            'product_id' => $product->id,
                            'lote_code' => $lotCode,
                        ],
                        [
                            'quantity' => $receivedQty,
                            'expires_at' => $expiresAt,
                            'safety_threshold' => $product->min_quantity ?? 0,
                        ]
                    );

                    $this->upsertMovement(
                        $lot->id,
                        'traspaso_demo',
                        'Ingreso historico por traspaso recibido.',
                        $warehouseUserId,
                        $receivedQty,
                        $createdAt->copy()->addDays(1)
                    );
                }
            }

            $transfer->forceFill([
                'created_at' => $createdAt,
                'updated_at' => $status === Transfer::STATUS_RECEIVED ? $createdAt->copy()->addDays(2) : $createdAt,
            ])->saveQuietly();
        }
    }

    private function seedDamageReports($faker, int $warehouseUserId): void
    {
        if (DamageReport::count() >= self::DAMAGES_TARGET) {
            return;
        }

        $lots = ProductLot::with('product')
            ->where('quantity', '>', 5)
            ->orderBy('expires_at')
            ->limit(80)
            ->get();

        foreach ($lots->take(24) as $index => $lot) {
            $damagedQty = min(max(0, $lot->quantity - 1), rand(1, 4));
            if ($damagedQty <= 0) {
                continue;
            }

            $lot->quantity = max(0, $lot->quantity - $damagedQty);
            $lot->save();

            $reportedAt = now()->copy()->subDays(280 - ($index * 9))->setTime(rand(8, 16), rand(0, 59));

            $report = DamageReport::create([
                'product_lot_id' => $lot->id,
                'product_id' => $lot->product_id,
                'warehouse_id' => $lot->warehouse_id,
                'reported_by' => $warehouseUserId,
                'damaged_qty' => $damagedQty,
                'comment' => $faker->randomElement([
                    'Golpe en recepcion y merma registrada por control interno.',
                    'Envase comprometido durante manipulacion en camara fria.',
                    'Producto separado por dano visible en embalaje secundario.',
                    'Merma registrada despues de inspeccion de calidad del lote.',
                ]),
            ]);

            $report->forceFill([
                'created_at' => $reportedAt,
                'updated_at' => $reportedAt,
            ])->saveQuietly();

            $this->upsertMovement(
                $lot->id,
                'dano_demo',
                'Descuento historico por merma registrada.',
                $warehouseUserId,
                -$damagedQty,
                $reportedAt
            );
        }
    }

    private function seedAuditLogs(int $adminId, int $sellerId, int $warehouseUserId): void
    {
        if (AuditLog::count() >= self::AUDIT_TARGET) {
            return;
        }

        $events = collect();

        foreach (Sale::latest('created_at')->limit(150)->get() as $sale) {
            $events->push([
                'user_id' => $sellerId,
                'entity_type' => Sale::class,
                'entity_id' => $sale->id,
                'action' => 'create',
                'description' => 'Venta historica registrada para panel comercial.',
                'old_values' => [],
                'new_values' => $sale->only(['sale_type', 'status', 'total_amount', 'warehouse_id']),
                'created_at' => $sale->created_at,
            ]);
        }

        foreach (Quotation::latest('created_at')->limit(110)->get() as $quotation) {
            $events->push([
                'user_id' => $sellerId,
                'entity_type' => Quotation::class,
                'entity_id' => $quotation->id,
                'action' => 'create',
                'description' => 'Cotizacion historica generada para seguimiento comercial.',
                'old_values' => [],
                'new_values' => $quotation->only(['sale_type', 'status', 'total_amount']),
                'created_at' => $quotation->created_at,
            ]);
        }

        foreach (Transfer::latest('created_at')->limit(80)->get() as $transfer) {
            $events->push([
                'user_id' => $warehouseUserId,
                'entity_type' => Transfer::class,
                'entity_id' => $transfer->id,
                'action' => 'update',
                'description' => 'Traspaso operativo actualizado en historico de almacen.',
                'old_values' => ['status' => Transfer::STATUS_PENDING],
                'new_values' => $transfer->only(['status', 'from_warehouse_id', 'to_warehouse_id']),
                'created_at' => $transfer->updated_at ?? $transfer->created_at,
            ]);
        }

        foreach (VendorVisit::latest('created_at')->limit(80)->get() as $visit) {
            $events->push([
                'user_id' => $sellerId,
                'entity_type' => VendorVisit::class,
                'entity_id' => $visit->id,
                'action' => 'create',
                'description' => 'Visita comercial registrada en agenda historica.',
                'old_values' => [],
                'new_values' => $visit->only(['company_id', 'status', 'visit_date']),
                'created_at' => $visit->created_at,
            ]);
        }

        foreach (DamageReport::latest('created_at')->limit(40)->get() as $report) {
            $events->push([
                'user_id' => $warehouseUserId,
                'entity_type' => DamageReport::class,
                'entity_id' => $report->id,
                'action' => 'create',
                'description' => 'Merma historica registrada por control de calidad.',
                'old_values' => [],
                'new_values' => $report->only(['product_id', 'warehouse_id', 'damaged_qty']),
                'created_at' => $report->created_at,
            ]);
        }

        foreach ($events->take(self::AUDIT_TARGET) as $event) {
            AuditLog::create([
                'user_id' => $event['user_id'] ?: $adminId,
                'entity_type' => $event['entity_type'],
                'entity_id' => $event['entity_id'],
                'action' => $event['action'],
                'description' => $event['description'],
                'old_values' => $event['old_values'],
                'new_values' => $event['new_values'],
                'created_at' => $event['created_at'] ?? now(),
            ]);
        }
    }

    private function monthWindows(): array
    {
        $windows = [];
        $start = now()->copy()->startOfMonth()->subMonths(11);

        for ($i = 0; $i < 12; $i++) {
            $monthStart = $start->copy()->addMonths($i);
            $monthEnd = $monthStart->isSameMonth(now())
                ? now()->copy()
                : $monthStart->copy()->endOfMonth();
            $windows[] = [$monthStart, $monthEnd];
        }

        return $windows;
    }

    private function pickChannel(): string
    {
        return collect([
            'empresa_institucional',
            'empresa_institucional',
            'empresa_institucional',
            'tienda_barrio',
            'tienda_barrio',
            'tienda_barrio',
            'comprador_minorista',
            'comprador_minorista',
        ])->random();
    }

    private function saleContext(
        string $channel,
        Collection $companiesByType,
        Collection $customers,
        ?Collection $cities = null
    ): ?array {
        if ($channel === 'comprador_minorista') {
            $customer = $customers->random();

            return [
                'company_id' => null,
                'customer_id' => $customer->id,
                'city' => $customer->city,
                'city_id' => $customer->city_id ?: $cities?->get($customer->city)?->id,
                'address' => $customer->delivery_address,
            ];
        }

        $companyGroup = $companiesByType->get($channel);
        if (! $companyGroup instanceof Collection || $companyGroup->isEmpty()) {
            return null;
        }

        $company = $companyGroup->random();

        return [
            'company_id' => $company->id,
            'customer_id' => null,
            'city' => $company->city,
            'city_id' => $company->city_id ?: $cities?->get($company->city)?->id,
            'address' => $company->address,
        ];
    }

    private function warehouseForCity(Collection $warehouses, ?string $city): ?Warehouse
    {
        return match ($city) {
            'La Paz', 'El Alto' => $warehouses->get('LPZ'),
            'Santa Cruz' => $warehouses->get('SCZ'),
            'Cochabamba' => $warehouses->get('CBA'),
            default => $warehouses->get('LPZ') ?? $warehouses->first(),
        };
    }

    private function paymentMethodForChannel(string $channel): string
    {
        return match ($channel) {
            'empresa_institucional' => collect(['transferencia', 'credito', 'qr'])->random(),
            'tienda_barrio' => collect(['efectivo', 'qr', 'tarjeta_debito'])->random(),
            default => collect(['efectivo', 'qr', 'tarjeta_debito'])->random(),
        };
    }

    private function statusForDate(Carbon $date): string
    {
        if ($date->greaterThan(now()->copy()->subDays(5))) {
            return rand(0, 100) < 72 ? 'entregado' : 'sin_entregar';
        }

        return rand(0, 100) < 90 ? 'entregado' : 'sin_entregar';
    }

    private function quotationStatusForDate(Carbon $date): string
    {
        if ($date->greaterThan(now()->copy()->subDays(10))) {
            return collect(['enviada', 'enviada', 'borrador', 'aceptada'])->random();
        }

        return collect(['aceptada', 'aceptada', 'enviada', 'rechazada'])->random();
    }

    private function quotationNote(string $channel, string $status): string
    {
        $base = match ($channel) {
            'empresa_institucional' => 'Cotizacion preparada para negociacion con cuenta institucional.',
            'tienda_barrio' => 'Oferta enviada para reposicion semanal de tienda de barrio.',
            default => 'Propuesta comercial para pedido de cliente minorista.',
        };

        return $status === 'rechazada'
            ? $base.' No se concreto por precio, timing o presupuesto.'
            : $base;
    }

    private function visitNote(string $channel, string $status): string
    {
        $base = $channel === 'empresa_institucional'
            ? 'Revision de volumen, exhibicion fria y propuesta de reposicion.'
            : 'Seguimiento a surtido, rotacion y quiebres en punto de venta.';

        return $status === 'reprogramada'
            ? $base.' Cliente solicito reprogramacion para la siguiente semana.'
            : $base;
    }

    private function randomDateInWindow($faker, Carbon $start, Carbon $end): Carbon
    {
        $seconds = max(1, $end->diffInSeconds($start));

        return $start->copy()->addSeconds($faker->numberBetween(0, $seconds));
    }

    private function pickProductsForSale(Collection $products, string $channel, Carbon $date, int $count): Collection
    {
        $available = $products->values();
        $selected = collect();

        while ($selected->count() < $count && $available->isNotEmpty()) {
            $index = $this->weightedIndex($available, function (Product $product) use ($channel, $date) {
                return $this->demandWeight($product, $channel, $date);
            });

            $selected->push($available->get($index));
            $available->forget($index);
            $available = $available->values();
        }

        return $selected;
    }

    private function weightedIndex(Collection $items, callable $resolver): int
    {
        $weights = $items->map(fn ($item) => max(1, (int) $resolver($item)))->values();
        $total = $weights->sum();
        $pivot = rand(1, max(1, $total));
        $carry = 0;

        foreach ($weights as $index => $weight) {
            $carry += $weight;
            if ($pivot <= $carry) {
                return $index;
            }
        }

        return 0;
    }

    private function demandWeight(Product $product, string $channel, Carbon $date): int
    {
        $name = Str::lower($product->name);
        $category = Str::lower($product->category->name ?? '');
        $description = Str::lower($product->description ?? '');
        $month = (int) $date->format('n');

        $weight = 10;

        if (in_array($category, ['leches fluidas', 'yogurt', 'bebidas lacteas', 'jugos y nectares'], true)) {
            $weight += 14;
        }

        if (in_array($product->sku, ['PIL-0001', 'PIL-0002', 'PIL-0031', 'PIL-0035', 'PIL-0051', 'PIL-0055', 'PIL-0063', 'PIL-0081', 'PIL-0089'], true)) {
            $weight += 12;
        }

        if ($channel === 'empresa_institucional' && preg_match('/(1 l|2 l|1 kg|760 g|1.000 g|5 l|1.800 g|2.200 g)/', $description)) {
            $weight += 10;
        }

        if ($channel === 'tienda_barrio' && (float) $product->suggested_price_public <= 12) {
            $weight += 8;
        }

        if ($channel === 'comprador_minorista' && (float) $product->suggested_price_public <= 20) {
            $weight += 6;
        }

        $isSchoolProduct = preg_match('/(pilfrut|juguito|yogurello|chiqui|chicolac)/', $name);
        if ($isSchoolProduct && in_array($month, [2, 3], true)) {
            $weight += 18;
        } elseif ($isSchoolProduct && in_array($month, [4, 5], true)) {
            $weight -= 6;
        }

        if (preg_match('/(biogurt|yogurt bebible|greco yogurt griego|leche fresca natural|leche natural larga vida)/', $name)) {
            $weight += 8;
        }

        if (in_array($month, [11, 12, 1], true) && preg_match('/(leche|yogurt|mantequilla|crema)/', $name)) {
            $weight += 5;
        }

        return max(1, $weight);
    }

    private function quantityForSale(Product $product, string $channel, Carbon $date): int
    {
        $description = Str::lower($product->description ?? '');
        $name = Str::lower($product->name);

        $bulk = preg_match('/(2 l|1 kg|760 g|1.000 g|1.800 g|2.200 g|5 l)/', $description) === 1;
        $small = preg_match('/(100 g|110 g|120 g|140 ml|150 ml|170 g|170 ml|190 ml|200 ml)/', $description) === 1;

        $quantity = match ($channel) {
            'empresa_institucional' => $bulk ? rand(6, 18) : rand(12, 36),
            'tienda_barrio' => $bulk ? rand(4, 10) : rand(8, 24),
            default => $bulk ? rand(1, 3) : rand(2, 6),
        };

        if ($small) {
            $quantity += match ($channel) {
                'empresa_institucional' => rand(4, 8),
                'tienda_barrio' => rand(2, 5),
                default => rand(0, 2),
            };
        }

        if (preg_match('/(pilfrut|juguito|chiqui|yogurello)/', $name) && in_array((int) $date->format('n'), [2, 3], true)) {
            $quantity += match ($channel) {
                'empresa_institucional' => rand(6, 10),
                'tienda_barrio' => rand(4, 8),
                default => rand(1, 3),
            };
        }

        return max(1, $quantity);
    }

    private function cashFlowForSale(string $paymentMethod, float $total): array
    {
        if ($paymentMethod !== 'efectivo') {
            return [null, null];
        }

        $amountReceived = ceil(($total + rand(5, 35)) / 10) * 10;
        $changeAmount = round(max(0, $amountReceived - $total), 2);

        return [$amountReceived, $changeAmount];
    }

    private function logSaleMovement(int $productId, int $warehouseId, int $quantity, int $userId, Carbon $date, int $saleId): void
    {
        $lot = ProductLot::query()
            ->where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->orderByRaw('CASE WHEN expires_at IS NULL THEN 1 ELSE 0 END')
            ->orderBy('expires_at')
            ->first();

        if (! $lot) {
            return;
        }

        $this->upsertMovement(
            $lot->id,
            'venta_demo',
            'Salida historica asociada a venta demo #'.$saleId,
            $userId,
            -abs($quantity),
            $date
        );
    }

    private function upsertMovement(
        int $lotId,
        string $type,
        string $note,
        ?int $userId,
        int $quantity,
        Carbon $timestamp
    ): void {
        $movement = ProductLotMovement::firstOrCreate(
            [
                'lot_id' => $lotId,
                'type' => $type,
                'note' => $note,
            ],
            [
                'user_id' => $userId,
                'quantity' => $quantity,
            ]
        );

        $movement->forceFill([
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ])->saveQuietly();
    }
}
