<?php

namespace Database\Seeders;

use App\Models\Backup;
use App\Models\BackupSchedule;
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
use App\Models\Role;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Transfer;
use App\Models\TransferItem;
use App\Models\TransferRequest;
use App\Models\User;
use App\Models\VendorVisit;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class Historical2026Seeder extends Seeder
{
    private const START = '2026-01-01 00:00:00';
    private const END = '2026-09-07 23:59:59';
    private const SALES_TARGET = 16850;
    private const SALE_LINES_TARGET = 39300;
    private const NEW_USERS_TARGET = 38;
    private const INACTIVE_USERS_TARGET = 8;

    private array $lotQueues = [];
    private array $lotBalances = [];

    public function run(): void
    {
        mt_srand(20260101);

        $products = Product::with('category')->orderBy('id')->get();
        $warehouses = Warehouse::orderBy('id')->get()->keyBy('code');
        $cities = City::orderBy('id')->get()->keyBy('name');

        if ($products->isEmpty() || ! $warehouses->has('LPZ')) {
            $this->command?->warn('No hay productos o almacenes suficientes para crear la historia 2026.');
            return;
        }

        DB::transaction(function () use ($products, $warehouses, $cities) {
            $this->cleanPreviousLoad();

            $users = $this->seedUsers($cities);
            $customers = $this->seedCustomers($users['buyers'], $cities);
            $companies = Company::whereIn('company_type', ['empresa_institucional', 'tienda_barrio'])
                ->whereNull('deleted_at')
                ->orderBy('id')
                ->get()
                ->groupBy('company_type');

            $this->seedLots($products, $warehouses, $users['warehouse']);
            $this->seedTransfersAndAgentRequests($products, $warehouses, $users);
            $this->seedDamageReports($users['warehouse']);
            $this->seedSales($products, $warehouses, $cities, $companies, $customers, $users['sellers']);
            $this->seedAiEvaluatorSnapshots($products);
            $this->seedBuyerOrders($products, $customers);
            $this->seedQuotations($products, $companies, $customers, $users['sellers']);
            $this->seedVendorVisits($companies, $users['sellers']);
            $this->seedBackups($users['admin']);
            $this->syncInventory();
            $this->seedAuditLogs($users);
        });
    }

    private function cleanPreviousLoad(): void
    {
        $saleIds = Sale::withTrashed()->whereBetween('created_at', [self::START, self::END])->pluck('id');
        foreach ($saleIds->chunk(1000) as $chunk) {
            SaleItem::whereIn('sale_id', $chunk)->delete();
            Sale::withTrashed()->whereIn('id', $chunk)->forceDelete();
        }

        ProductLotMovement::whereBetween('created_at', [self::START, self::END])
            ->where(function ($query) {
                $query->whereIn('type', ['venta', 'ingreso', 'traspaso', 'merma', 'vencimiento', 'ajuste', 'venta_demo', 'traspaso_demo', 'dano_demo'])
                    ->orWhereRaw('LOWER(note) LIKE ?', ['%demo%'])
                    ->orWhereRaw('LOWER(note) LIKE ?', ['%seeder%']);
            })
            ->delete();

        DamageReport::whereBetween('created_at', [self::START, self::END])->delete();
        TransferRequest::whereBetween('created_at', [self::START, self::END])->delete();
        if (Schema::hasTable('ai_forecast_snapshots')) {
            DB::table('ai_forecast_snapshots')->whereYear('forecast_start', 2026)->delete();
        }
        if (Schema::hasTable('ai_learning_states')) {
            DB::table('ai_learning_states')->delete();
        }
        TransferItem::whereBetween('created_at', [self::START, self::END])->delete();
        Transfer::withTrashed()->whereBetween('created_at', [self::START, self::END])->forceDelete();
        ProductLot::where('lote_code', 'like', 'PIL-2026-%')->delete();

        BuyerOrderItem::whereBetween('created_at', [self::START, self::END])->delete();
        BuyerOrder::whereBetween('created_at', [self::START, self::END])->delete();
        $quotationIds = Quotation::withTrashed()->whereBetween('created_at', [self::START, self::END])->pluck('id');
        foreach ($quotationIds->chunk(1000) as $chunk) {
            QuotationItem::whereIn('quotation_id', $chunk)->delete();
            Quotation::withTrashed()->whereIn('id', $chunk)->forceDelete();
        }
        VendorVisit::whereBetween('created_at', [self::START, self::END])->delete();
        Backup::whereBetween('created_at', [self::START, self::END])->delete();
        DB::table('audit_logs')->whereBetween('created_at', [self::START, self::END])->delete();
    }

    private function seedUsers(Collection $cities): array
    {
        $roles = Role::pluck('id', 'name');
        $sellerRole = $roles['Vendedor'] ?? null;
        $buyerRole = $roles['Comprador'] ?? null;
        $warehouseRole = Role::where('name', 'like', 'Almac%')->value('id') ?? ($roles['Almacen'] ?? null);
        $adminId = User::where('email', 'admin@gmail.com')->value('id') ?? User::query()->value('id');

        $sellerNames = [
            'Alejandra Saavedra', 'Mario Bustillos', 'Patricia Calderon', 'Ruben Vargas',
            'Tamara Aruquipa', 'Nicolas Miranda', 'Carmen Cuentas', 'Diego Chambi',
            'Lorena Menacho', 'Esteban Flores', 'Pamela Aguilar', 'Kevin Soria',
            'Micaela Poma', 'Ramiro Terrazas', 'Daniela Aliaga', 'Nelson Quenta',
            'Fabiola Nina', 'Rodrigo Aparicio',
        ];
        $warehouseNames = [
            'Cristian Laura', 'Mirtha Choque', 'Edgar Condori', 'Belen Mamani',
            'Franz Quisbert', 'Carolina Tola', 'Oscar Ticona', 'Luz Callisaya',
        ];
        $buyerNames = [
            'Paola Rivero', 'Martin Salvatierra', 'Luciana Rocha', 'Jorge Cardenas',
            'Valeria Ibarra', 'Hugo Arteaga', 'Cecilia Ramos', 'Andres Mercado',
            'Marina Cuellar', 'Rocio Villarroel', 'Samuel Lopez', 'Gabriela Quiroz',
        ];

        $records = [];
        foreach ($sellerNames as $name) {
            $records[] = [$name, $sellerRole, 'vendedor'];
        }
        foreach ($warehouseNames as $name) {
            $records[] = [$name, $warehouseRole, 'almacen'];
        }
        foreach ($buyerNames as $name) {
            $records[] = [$name, $buyerRole, 'comprador'];
        }

        $created = [
            'admin' => $adminId,
            'sellers' => collect(),
            'warehouse' => collect(),
            'buyers' => collect(),
        ];

        foreach (array_slice($records, 0, self::NEW_USERS_TARGET) as $index => [$name, $roleId, $group]) {
            if (! $roleId) {
                continue;
            }

            $slug = Str::slug($name, '.');
            $createdAt = Carbon::create(2026, 1, 3)->addDays($index * 2);
            $user = User::withTrashed()->updateOrCreate(
                ['email' => $slug.'@pil2026.bo'],
                [
                    'name' => $name,
                    'username' => $slug.'.2026',
                    'password' => Hash::make($group.'1234'),
                    'role_id' => $roleId,
                    'deleted_at' => null,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]
            );

            if ($index >= self::NEW_USERS_TARGET - self::INACTIVE_USERS_TARGET) {
                $user->delete();
                continue;
            }

            if ($group === 'vendedor') {
                $created['sellers']->push($user);
            } elseif ($group === 'almacen') {
                $created['warehouse']->push($user);
            } else {
                $created['buyers']->push($user);
            }
        }

        $created['sellers'] = $created['sellers']->merge(User::where('email', 'ventas@gmail.com')->get())->values();
        $created['warehouse'] = $created['warehouse']->merge(User::where('email', 'almacen@gmail.com')->get())->values();
        $created['buyers'] = $created['buyers']->merge(User::where('email', 'comprador@gmail.com')->get())->values();

        return $created;
    }

    private function seedCustomers(Collection $buyers, Collection $cities): Collection
    {
        $addresses = [
            ['La Paz', 'Av. Arce #1845, San Jorge'],
            ['La Paz', 'Calle 21 #891, Calacoto'],
            ['La Paz', 'Av. Costanera #440, Obrajes'],
            ['La Paz', 'Calle 10 #223, Achumani'],
            ['El Alto', 'Av. Juan Pablo II #920, Rio Seco'],
            ['El Alto', 'Av. Civica #510, Ceja'],
            ['El Alto', 'Av. Bolivia #370, Ciudad Satelite'],
            ['El Alto', 'Av. Litoral #708, Villa Adela'],
        ];

        foreach ($buyers as $index => $user) {
            if ($user->trashed()) {
                continue;
            }

            [$city, $address] = $addresses[$index % count($addresses)];
            Customer::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'nit' => (string) (7600000 + $index),
                    'delivery_address' => $address,
                    'city' => $city,
                    'city_id' => $cities[$city]->id ?? null,
                ]
            );
        }

        return Customer::with('user')->whereHas('user', fn ($query) => $query->whereNull('deleted_at'))->get();
    }

    private function seedLots(Collection $products, Collection $warehouses, Collection $warehouseUsers): void
    {
        $movementRows = [];
        $warehouseUserId = $warehouseUsers->first()?->id;

        foreach ($products as $product) {
            foreach ($warehouses as $warehouse) {
                for ($month = 1; $month <= 9; $month++) {
                    $day = $month === 9 ? 1 : min(24, 2 + ($product->id % 18));
                    $date = Carbon::create(2026, $month, $day, 7 + ($month % 6), 0);
                    $base = $warehouse->code === 'LPZ' ? 210000 : 56000;
                    $qty = $base + (($product->id + $month) % 10) * 4200;
                    $lot = ProductLot::create([
                        'product_id' => $product->id,
                        'warehouse_id' => $warehouse->id,
                        'lote_code' => sprintf('PIL-2026-%s-%s-%02d', $warehouse->code, $product->sku, $month),
                        'quantity' => $qty,
                        'expires_at' => $date->copy()->addDays($this->shelfLife($product, $month))->toDateString(),
                        'safety_threshold' => $product->min_quantity ?? 0,
                        'created_at' => $date,
                        'updated_at' => $date,
                    ]);

                    $this->lotQueues[$product->id][$warehouse->id][] = ['id' => $lot->id, 'quantity' => $qty];
                    $this->lotBalances[$lot->id] = $qty;

                    $movementRows[] = [
                        'lot_id' => $lot->id,
                        'user_id' => $warehouseUserId,
                        'type' => 'ingreso',
                        'quantity' => $qty,
                        'note' => 'Ingreso por produccion y abastecimiento planificado 2026.',
                        'created_at' => $date,
                        'updated_at' => $date,
                    ];
                }
            }
        }

        $this->insertChunked('product_lot_movements', $movementRows);
    }

    private function seedTransfersAndAgentRequests(Collection $products, Collection $warehouses, array $users): void
    {
        $sourceWarehouses = $warehouses->filter(fn ($warehouse, $code) => $code !== 'LPZ')->values();
        $lpz = $warehouses['LPZ'];
        $warehouseUsers = $users['warehouse'];
        $sellers = $users['sellers'];

        for ($i = 0; $i < 118; $i++) {
            $date = $this->dateByIndex($i, 118, 8);
            $status = $i < 92 ? Transfer::STATUS_RECEIVED : ($i < 110 ? Transfer::STATUS_IN_TRANSIT : Transfer::STATUS_PENDING);
            $transfer = Transfer::create([
                'from_warehouse_id' => $sourceWarehouses[$i % max(1, $sourceWarehouses->count())]->id ?? null,
                'to_warehouse_id' => $lpz->id,
                'requested_by' => $sellers[$i % max(1, $sellers->count())]->id,
                'approved_by' => $warehouseUsers[$i % max(1, $warehouseUsers->count())]->id ?? null,
                'received_by' => $status === Transfer::STATUS_RECEIVED ? ($warehouseUsers[($i + 1) % max(1, $warehouseUsers->count())]->id ?? null) : null,
                'status' => $status,
                'expected_date' => $date->copy()->addDays(2)->toDateString(),
                'received_date' => $status === Transfer::STATUS_RECEIVED ? $date->copy()->addDays(2)->toDateString() : null,
                'notes' => 'Traspaso regional por cobertura de demanda y continuidad de frio.',
                'created_at' => $date,
                'updated_at' => $date->copy()->addDays($status === Transfer::STATUS_RECEIVED ? 2 : 0),
            ]);

            $linkedRequest = $i < 72 && $i % 2 === 0;
            foreach ($products->slice(($i * 5) % max(1, $products->count()), 3) as $product) {
                TransferItem::create([
                    'transfer_id' => $transfer->id,
                    'product_id' => $product->id,
                    'requested_qty' => 240 + (($i + $product->id) % 8) * 32,
                    'received_qty' => $status === Transfer::STATUS_RECEIVED ? 220 + (($i + $product->id) % 8) * 30 : null,
                    'damaged_qty' => $status === Transfer::STATUS_RECEIVED ? ($i + $product->id) % 5 : 0,
                    'notes' => 'Despacho controlado con revision de cantidades y vencimientos.',
                    'lot_code' => $status === Transfer::STATUS_RECEIVED ? sprintf('PIL-2026-LPZ-%s-T%03d', $product->sku, $i + 1) : null,
                    'receiving_expires_at' => $status === Transfer::STATUS_RECEIVED ? $date->copy()->addDays($this->shelfLife($product, (int) $date->month))->toDateString() : null,
                    'receiving_note' => $status === Transfer::STATUS_RECEIVED ? 'Recepcion registrada por almacen La Paz.' : null,
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);

                if ($linkedRequest) {
                    TransferRequest::create([
                        'product_id' => $product->id,
                        'requested_qty' => 240 + (($i + $product->id) % 8) * 32,
                        'status' => TransferRequest::STATUS_APPROVED,
                        'priority' => ['Alta', 'Media', 'Critica'][$i % 3],
                        'reason' => 'Proyeccion 2026 detecta demanda superior al stock de seguridad en La Paz.',
                        'created_by_agent' => true,
                        'approved_by' => $users['admin'],
                        'approved_at' => $date->copy()->addHours(2),
                        'decision_reason' => 'Aprobado por rotacion alta y cobertura institucional.',
                        'transfer_id' => $transfer->id,
                        'created_at' => $date->copy()->subHours(3),
                        'updated_at' => $date->copy()->addHours(2),
                    ]);
                }
            }
        }

        for ($i = 0; $i < 54; $i++) {
            $product = $products[($i * 7) % max(1, $products->count())];
            $date = Carbon::create(2026, 8, 18, 7, 20)->addHours($i * 6);
            $status = match ($i % 6) {
                0, 1, 2 => TransferRequest::STATUS_PENDING,
                3, 4 => TransferRequest::STATUS_APPROVED,
                default => TransferRequest::STATUS_REJECTED,
            };
            $qty = 320 + (($i + $product->id) % 9) * 45;
            $stock = 120 + (($i + $product->id) % 6) * 35;
            $forecast = $stock + $qty - (($i % 4) * 20);
            $result = $stock - $forecast;
            $threshold = max(80, (int) ($product->min_quantity ?? 0));
            $transferId = null;

            if ($status === TransferRequest::STATUS_APPROVED) {
                $transfer = Transfer::create([
                    'from_warehouse_id' => $sourceWarehouses[$i % max(1, $sourceWarehouses->count())]->id ?? null,
                    'to_warehouse_id' => $lpz->id,
                    'requested_by' => $users['admin'],
                    'approved_by' => $warehouseUsers[$i % max(1, $warehouseUsers->count())]->id ?? null,
                    'received_by' => null,
                    'status' => $i % 4 === 0 ? Transfer::STATUS_IN_TRANSIT : Transfer::STATUS_PENDING,
                    'expected_date' => $date->copy()->addDays(2)->toDateString(),
                    'received_date' => null,
                    'notes' => 'Traspaso generado desde solicitud del agente por riesgo de quiebre de stock.',
                    'created_at' => $date->copy()->addHours(2),
                    'updated_at' => $date->copy()->addHours(2),
                ]);
                $transferId = $transfer->id;

                TransferItem::create([
                    'transfer_id' => $transfer->id,
                    'product_id' => $product->id,
                    'requested_qty' => $qty,
                    'received_qty' => null,
                    'damaged_qty' => 0,
                    'notes' => 'Producto sugerido por el agente inteligente.',
                    'created_at' => $date->copy()->addHours(2),
                    'updated_at' => $date->copy()->addHours(2),
                ]);
            }

            TransferRequest::create([
                'product_id' => $product->id,
                'requested_qty' => $qty,
                'status' => $status,
                'priority' => ['Critica', 'Alta', 'Media'][$i % 3],
                'reason' => "Stock {$stock} + traspasos 7d 0 - demanda proyectada 7d {$forecast} = {$result}; cae bajo umbral {$threshold}.",
                'created_by_agent' => true,
                'approved_by' => $status === TransferRequest::STATUS_APPROVED ? $users['admin'] : null,
                'rejected_by' => $status === TransferRequest::STATUS_REJECTED ? $users['admin'] : null,
                'approved_at' => $status === TransferRequest::STATUS_APPROVED ? $date->copy()->addHours(1) : null,
                'rejected_at' => $status === TransferRequest::STATUS_REJECTED ? $date->copy()->addHours(1) : null,
                'decision_reason' => $status === TransferRequest::STATUS_PENDING
                    ? null
                    : ($status === TransferRequest::STATUS_APPROVED ? 'Aprobado para prevenir quiebre de stock en La Paz.' : 'Rechazado por reposicion reciente en ruta.'),
                'transfer_id' => $transferId,
                'created_at' => $date,
                'updated_at' => $status === TransferRequest::STATUS_PENDING ? $date : $date->copy()->addHours(1),
            ]);
        }
    }

    private function seedDamageReports(Collection $warehouseUsers): void
    {
        $warehouseUserId = $warehouseUsers->first()?->id;
        $lots = ProductLot::where('lote_code', 'like', 'PIL-2026-%')->orderBy('expires_at')->limit(105)->get();

        foreach ($lots as $index => $lot) {
            $date = $this->dateByIndex($index, 105, 10);
            $qty = min($lot->quantity, 6 + ($index % 16));
            if ($qty <= 0) {
                continue;
            }

            $this->lotBalances[$lot->id] = max(0, ($this->lotBalances[$lot->id] ?? $lot->quantity) - $qty);
            DamageReport::create([
                'product_lot_id' => $lot->id,
                'product_id' => $lot->product_id,
                'warehouse_id' => $lot->warehouse_id,
                'reported_by' => $warehouseUserId,
                'damaged_qty' => $qty,
                'comment' => 'Baja por inspeccion de calidad, manipulacion o observacion de cadena de frio.',
                'created_at' => $date,
                'updated_at' => $date,
            ]);
            ProductLotMovement::create([
                'lot_id' => $lot->id,
                'user_id' => $warehouseUserId,
                'type' => 'merma',
                'quantity' => -$qty,
                'note' => 'Baja operativa por control de calidad 2026.',
                'created_at' => $date,
                'updated_at' => $date,
            ]);
        }
    }

    private function seedSales(Collection $products, Collection $warehouses, Collection $cities, Collection $companies, Collection $customers, Collection $sellers): void
    {
        $saleItemsRows = [];
        $movementRows = [];
        $updates = [];
        $monthlyPlan = [1750, 1900, 2100, 1950, 2050, 2100, 2150, 2200, 650];
        $saleNumber = 0;
        $extraLines = self::SALE_LINES_TARGET - (self::SALES_TARGET * 2);

        foreach ($monthlyPlan as $monthIndex => $count) {
            $month = $monthIndex + 1;
            for ($i = 0; $i < $count; $i++) {
                $saleNumber++;
                $date = $this->saleDate($month, $i, $count);
                $channel = $this->channelForSale($saleNumber);
                $context = $this->saleContext($channel, $companies, $customers, $cities);
                $warehouse = $this->warehouseForCity($warehouses, $context['city']);
                $seller = $sellers[($saleNumber + $month) % max(1, $sellers->count())];
                $payment = $this->paymentMethod($channel);
                $itemCount = 2 + ($saleNumber <= $extraLines ? 1 : 0);
                $saleId = DB::table('sales')->insertGetId([
                    'company_id' => $context['company_id'],
                    'customer_id' => $context['customer_id'],
                    'seller_id' => $seller->id,
                    'warehouse_id' => $warehouse->id,
                    'sale_type' => $channel,
                    'delivery_address' => $context['address'],
                    'delivery_city' => $context['city'],
                    'delivery_city_id' => $context['city_id'],
                    'status' => $this->saleStatus($date),
                    'payment_method' => $payment,
                    'amount_received' => null,
                    'change_amount' => null,
                    'total_amount' => 0,
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);

                $total = 0;
                foreach ($this->pickProducts($products, $channel, $month, $saleNumber, $itemCount) as $product) {
                    $quantity = $this->quantity($product, $channel, $month);
                    $price = $channel === 'empresa_institucional' ? (float) $product->price_institutional : (float) $product->suggested_price_public;
                    $subtotal = round($quantity * $price, 2);
                    $lotId = $this->consumeLot($product->id, $warehouse->id, $quantity);

                    $saleItemsRows[] = [
                        'sale_id' => $saleId,
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'unit_price' => $price,
                        'subtotal' => $subtotal,
                        'created_at' => $date,
                        'updated_at' => $date,
                    ];
                    $movementRows[] = [
                        'lot_id' => $lotId,
                        'user_id' => $seller->id,
                        'type' => 'venta',
                        'quantity' => -$quantity,
                        'note' => sprintf('Venta 2026 #%05d - canal %s.', $saleNumber, str_replace('_', ' ', $channel)),
                        'created_at' => $date,
                        'updated_at' => $date,
                    ];
                    $total += $subtotal;
                }

                [$received, $change] = $this->cashAmounts($payment, $total);
                $updates[] = ['id' => $saleId, 'total_amount' => round($total, 2), 'amount_received' => $received, 'change_amount' => $change];

                if (count($saleItemsRows) >= 2000) {
                    $this->insertChunked('sale_items', $saleItemsRows);
                    $this->insertChunked('product_lot_movements', $movementRows);
                    $saleItemsRows = [];
                    $movementRows = [];
                }
            }
        }

        $this->insertChunked('sale_items', $saleItemsRows);
        $this->insertChunked('product_lot_movements', $movementRows);

        foreach (array_chunk($updates, 500) as $chunk) {
            foreach ($chunk as $row) {
                DB::table('sales')->where('id', $row['id'])->update([
                    'total_amount' => $row['total_amount'],
                    'amount_received' => $row['amount_received'],
                    'change_amount' => $row['change_amount'],
                ]);
            }
        }

        foreach ($this->lotBalances as $lotId => $quantity) {
            DB::table('product_lots')->where('id', $lotId)->update([
                'quantity' => max(0, $quantity),
                'updated_at' => Carbon::create(2026, 9, 7, 18, 0),
            ]);
        }
    }

    private function seedBuyerOrders(Collection $products, Collection $customers): void
    {
        if ($customers->isEmpty()) {
            return;
        }

        for ($i = 0; $i < 980; $i++) {
            $date = $this->dateByIndex($i, 980, 9);
            $customer = $customers[$i % $customers->count()];
            $order = BuyerOrder::create([
                'user_id' => $customer->user_id,
                'receipt_number' => sprintf('PED-2026-%05d', $i + 1),
                'payment_method' => ['efectivo', 'qr', 'tarjeta_debito'][$i % 3],
                'payment_status' => $i % 21 === 0 ? 'reembolsado' : ($i % 5 === 0 ? 'pendiente' : 'pagado'),
                'status' => $i % 29 === 0 ? 'cancelado' : ($i % 7 === 0 ? 'enviado' : 'entregado'),
                'subtotal' => 0,
                'shipping' => [0, 0, 5, 7.5, 10][$i % 5],
                'total' => 0,
                'issued_at' => $date,
                'created_at' => $date,
                'updated_at' => $date,
            ]);

            $subtotal = 0;
            foreach ($this->pickProducts($products, 'comprador_minorista', (int) $date->month, $i, 3) as $product) {
                $qty = 1 + (($i + $product->id) % 5);
                $price = (float) $product->suggested_price_public;
                BuyerOrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);
                $subtotal += $qty * $price;
            }

            $order->forceFill(['subtotal' => round($subtotal, 2), 'total' => round($subtotal + $order->shipping, 2)])->saveQuietly();
        }
    }

    private function seedAiEvaluatorSnapshots(Collection $products): void
    {
        if (! Schema::hasTable('ai_forecast_snapshots') || ! Schema::hasTable('ai_learning_states')) {
            return;
        }

        $weekStarts = [];
        $cursor = Carbon::create(2026, 1, 5)->startOfDay();
        $lastClosedWeekStart = Carbon::create(2026, 8, 31)->startOfDay();

        while ($cursor->lte($lastClosedWeekStart)) {
            $weekStarts[] = $cursor->copy();
            $cursor->addWeek();
        }

        $rows = [];
        $stateRows = [];

        foreach ($products->take(72) as $productIndex => $product) {
            $factor = 0.92 + (($productIndex % 7) * 0.03);
            $underStreak = 0;
            $overStreak = 0;
            $last = ['wape' => null, 'mae' => null, 'level' => 'SIN_EVALUAR'];

            foreach ($weekStarts as $weekIndex => $weekStart) {
                $weekEnd = $weekStart->copy()->addDays(6);
                $lookbackStart = $weekStart->copy()->subWeeks(4);
                $lookbackEnd = $weekStart->copy()->subDay();
                $lookbackDemand = $this->actualDemandForProduct($product->id, $lookbackStart, $lookbackEnd);
                $actual = $this->actualDemandForProduct($product->id, $weekStart, $weekEnd);

                if ($actual <= 0 && $lookbackDemand <= 0) {
                    continue;
                }

                $baseDemand = $lookbackDemand > 0 ? (int) round($lookbackDemand / 4) : $actual;
                $seasonal = in_array((int) $weekStart->month, [2, 3, 7, 8], true) ? 1.08 : 1.0;
                $noise = 0.86 + ((($product->id + $weekIndex) % 9) * 0.035);
                $predicted = max(1, (int) round($baseDemand * $factor * $seasonal * $noise));
                $previousFactor = $factor;
                $evaluation = $this->evaluateForecastNumbers($predicted, $actual, $previousFactor, $underStreak, $overStreak);

                $factor = $evaluation['factor_after'];
                $underStreak = $evaluation['under_streak'];
                $overStreak = $evaluation['over_streak'];
                $last = [
                    'wape' => $evaluation['wape'],
                    'mae' => $evaluation['mae'],
                    'level' => $evaluation['level'],
                ];

                $rows[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'generated_at' => $weekStart->copy()->subDay()->setTime(6, 30),
                    'forecast_start' => $weekStart->toDateString(),
                    'forecast_end' => $weekEnd->toDateString(),
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
                    'factor_after' => $evaluation['factor_after'],
                    'evaluated_at' => $weekEnd->copy()->addDay()->setTime(3, 15),
                    'created_at' => $weekEnd->copy()->addDay()->setTime(3, 15),
                    'updated_at' => $weekEnd->copy()->addDay()->setTime(3, 15),
                ];
            }

            $stateRows[] = [
                'product_id' => $product->id,
                'learning_factor' => $factor,
                'under_streak' => $underStreak,
                'over_streak' => $overStreak,
                'last_wape' => $last['wape'],
                'last_mae' => $last['mae'],
                'last_level' => $last['level'],
                'updated_at' => Carbon::create(2026, 9, 7, 7, 0),
            ];
        }

        $this->insertChunked('ai_forecast_snapshots', $rows);
        foreach (array_chunk($stateRows, 500) as $chunk) {
            DB::table('ai_learning_states')->upsert(
                $chunk,
                ['product_id'],
                ['learning_factor', 'under_streak', 'over_streak', 'last_wape', 'last_mae', 'last_level', 'updated_at']
            );
        }
    }

    private function seedQuotations(Collection $products, Collection $companies, Collection $customers, Collection $sellers): void
    {
        for ($i = 0; $i < 780; $i++) {
            $channel = $i % 6 === 0 ? 'comprador_minorista' : ($i % 2 === 0 ? 'empresa_institucional' : 'tienda_barrio');
            $date = $this->dateByIndex($i, 780, 11);
            $context = $this->saleContext($channel, $companies, $customers, City::get()->keyBy('name'));
            $seller = $sellers[$i % max(1, $sellers->count())];
            $quotation = Quotation::create([
                'company_id' => $context['company_id'],
                'customer_id' => $context['customer_id'],
                'seller_id' => $seller->id,
                'sale_type' => $channel,
                'valid_until' => $date->copy()->addDays(12)->toDateString(),
                'status' => ['aceptada', 'enviada', 'aceptada', 'rechazada', 'borrador'][$i % 5],
                'total_amount' => 0,
                'notes' => 'Cotizacion 2026 por reposicion, volumen y negociacion comercial.',
                'created_at' => $date,
                'updated_at' => $date,
            ]);

            $total = 0;
            foreach ($this->pickProducts($products, $channel, (int) $date->month, $i, 4) as $product) {
                $qty = max(2, (int) round($this->quantity($product, $channel, (int) $date->month) * 0.72));
                $price = $channel === 'empresa_institucional' ? (float) $product->price_institutional : (float) $product->suggested_price_public;
                $total += $qty * $price;
                QuotationItem::create([
                    'quotation_id' => $quotation->id,
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'subtotal' => round($qty * $price, 2),
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);
            }

            $quotation->forceFill(['total_amount' => round($total, 2)])->saveQuietly();
        }
    }

    private function seedVendorVisits(Collection $companies, Collection $sellers): void
    {
        $allCompanies = $companies->flatten(1)->values();
        if ($allCompanies->isEmpty()) {
            return;
        }

        for ($i = 0; $i < 2100; $i++) {
            $date = $this->dateByIndex($i, 2100, 8);
            VendorVisit::create([
                'user_id' => $sellers[$i % max(1, $sellers->count())]->id,
                'company_id' => $allCompanies[$i % $allCompanies->count()]->id,
                'visit_date' => $date->toDateString(),
                'status' => $i % 19 === 0 ? 'reprogramada' : ($i % 23 === 0 ? 'pendiente' : 'completada'),
                'note' => 'Seguimiento 2026 de exhibicion, cobertura, cobranza y reposicion.',
                'created_at' => $date,
                'updated_at' => $date->copy()->addHours(2),
            ]);
        }
    }

    private function seedBackups(?int $adminId): void
    {
        $schedule = BackupSchedule::updateOrCreate(
            ['name' => 'Respaldo automatico principal'],
            [
                'frequency_days' => 3,
                'run_time' => '02:00',
                'is_active' => true,
                'last_run_at' => Carbon::create(2026, 9, 7, 2, 0),
                'next_run_at' => Carbon::create(2026, 9, 10, 2, 0),
                'created_at' => Carbon::create(2026, 1, 1, 2, 0),
                'updated_at' => Carbon::create(2026, 9, 7, 2, 0),
            ]
        );

        $rows = [];
        for ($i = 0; $i < 73; $i++) {
            $date = Carbon::create(2026, 1, 1, 2, 0)->addDays($i * 3);
            if ($date->greaterThan(Carbon::create(2026, 9, 7, 23, 59))) {
                break;
            }

            $rows[] = [
                'file_name' => 'backup_'.$date->format('Ymd_His').'.sql',
                'disk' => 'local',
                'size' => 85000000 + ($i * 173000),
                'status' => $i % 17 === 0 ? 'failed' : 'completed',
                'message' => $i % 17 === 0 ? 'Fallo temporal de escritura, reintentado por operacion.' : 'Respaldo generado correctamente.',
                'created_by' => $adminId,
                'triggered_by' => $i % 9 === 0 ? 'manual' : 'scheduled',
                'backup_schedule_id' => $schedule->id,
                'created_at' => $date,
                'updated_at' => $date,
            ];
        }

        $this->insertChunked('backups', $rows);
    }

    private function seedAuditLogs(array $users): void
    {
        $adminId = $users['admin'] ?? User::query()->value('id');
        $rows = [];

        foreach (Sale::whereBetween('created_at', [self::START, self::END])->latest('id')->limit(850)->get() as $sale) {
            $rows[] = [
                'user_id' => $sale->seller_id ?: $adminId,
                'entity_type' => Sale::class,
                'entity_id' => $sale->id,
                'action' => 'create',
                'description' => 'Venta historica registrada en operacion comercial 2026.',
                'old_values' => json_encode([]),
                'new_values' => json_encode($sale->only(['sale_type', 'status', 'total_amount'])),
                'created_at' => $sale->created_at,
            ];
        }

        foreach (TransferRequest::whereBetween('created_at', [self::START, self::END])->latest('id')->limit(180)->get() as $request) {
            $rows[] = [
                'user_id' => $adminId,
                'entity_type' => TransferRequest::class,
                'entity_id' => $request->id,
                'action' => strtolower($request->status),
                'description' => 'Decision registrada sobre solicitud de reposicion 2026.',
                'old_values' => json_encode([]),
                'new_values' => json_encode($request->only(['product_id', 'requested_qty', 'status', 'priority'])),
                'created_at' => $request->updated_at ?? $request->created_at,
            ];
        }

        $this->insertChunked('audit_logs', $rows);
    }

    private function saleContext(string $channel, Collection $companies, Collection $customers, Collection $cities): array
    {
        if ($channel === 'comprador_minorista' && $customers->isNotEmpty()) {
            $customer = $customers[random_int(0, $customers->count() - 1)];
            return [
                'company_id' => null,
                'customer_id' => $customer->id,
                'city' => $customer->city,
                'city_id' => $customer->city_id ?? ($cities[$customer->city]->id ?? null),
                'address' => $customer->delivery_address,
            ];
        }

        $group = $companies[$channel] ?? collect();
        $company = $group->isNotEmpty() ? $group[random_int(0, $group->count() - 1)] : Company::first();

        return [
            'company_id' => $company?->id,
            'customer_id' => null,
            'city' => $company?->city ?? 'La Paz',
            'city_id' => $company?->city_id ?? ($cities[$company?->city ?? 'La Paz']->id ?? null),
            'address' => $company?->address ?? 'La Paz',
        ];
    }

    private function pickProducts(Collection $products, string $channel, int $month, int $salt, int $count): Collection
    {
        return $products
            ->sortByDesc(fn ($product) => $this->demandScore($product, $channel, $month, $salt))
            ->take($count)
            ->values();
    }

    private function demandScore(Product $product, string $channel, int $month, int $salt): int
    {
        $text = Str::lower($product->name.' '.$product->description);
        $score = (($product->id * 41 + $salt * 17) % 100);

        if (preg_match('/leche|yogurt|pilfrut|juguito|chiqui|chicolac/', $text)) {
            $score += 48;
        }
        if ($channel === 'empresa_institucional' && preg_match('/1 l|2 l|1 kg|5 l|800 ml|946 ml/', $text)) {
            $score += 34;
        }
        if ($channel === 'tienda_barrio' && (float) $product->suggested_price_public <= 12) {
            $score += 30;
        }
        if (in_array($month, [2, 3, 7, 8], true)) {
            $score += 15;
        }

        return $score;
    }

    private function quantity(Product $product, string $channel, int $month): int
    {
        $text = Str::lower($product->name.' '.$product->description);
        $bulk = preg_match('/1 l|2 l|1 kg|5 l|800 ml|946 ml|760 g/', $text);
        $small = preg_match('/100 g|110 g|120 g|140 ml|150 ml|170 g|190 ml|200 ml/', $text);

        $quantity = match ($channel) {
            'empresa_institucional' => $bulk ? random_int(120, 480) : random_int(220, 780),
            'tienda_barrio' => $bulk ? random_int(22, 86) : random_int(42, 160),
            default => $bulk ? random_int(2, 12) : random_int(4, 22),
        };

        if ($small) {
            $quantity = (int) round($quantity * 1.32);
        }
        if (preg_match('/pilfrut|juguito|chiqui|chicolac/', $text) && in_array($month, [2, 3], true)) {
            $quantity = (int) round($quantity * 1.35);
        }

        return max(1, $quantity);
    }

    private function consumeLot(int $productId, int $warehouseId, int $quantity): int
    {
        $queue = &$this->lotQueues[$productId][$warehouseId];

        foreach ($queue as &$lot) {
            if ($lot['quantity'] <= 0) {
                continue;
            }

            $lot['quantity'] -= $quantity;
            $this->lotBalances[$lot['id']] = max(0, ($this->lotBalances[$lot['id']] ?? 0) - $quantity);
            return $lot['id'];
        }

        throw new \RuntimeException("Stock insuficiente para producto {$productId} en almacen {$warehouseId}.");
    }

    private function saleDate(int $month, int $index, int $count): Carbon
    {
        $days = $month === 9 ? 7 : Carbon::create(2026, $month, 1)->daysInMonth;
        $day = 1 + (int) floor(($index / max(1, $count)) * $days);

        return Carbon::create(2026, $month, min($day, $days), 7 + ($index % 12), ($index * 7) % 60);
    }

    private function dateByIndex(int $index, int $total, int $hour): Carbon
    {
        $start = Carbon::create(2026, 1, 1, $hour, 0);
        $end = Carbon::create(2026, 9, 7, 18, 0);
        $seconds = max(1, $end->diffInSeconds($start));

        return $start->copy()->addSeconds((int) floor(($index / max(1, $total)) * $seconds))->setMinute(($index * 11) % 60);
    }

    private function channelForSale(int $saleNumber): string
    {
        return match ($saleNumber % 20) {
            0, 3, 6 => 'comprador_minorista',
            1, 2, 4, 5, 7, 8, 10, 12, 14, 16 => 'empresa_institucional',
            default => 'tienda_barrio',
        };
    }

    private function warehouseForCity(Collection $warehouses, ?string $city): Warehouse
    {
        return match ($city) {
            'El Alto', 'La Paz' => $warehouses['LPZ'],
            'Santa Cruz' => $warehouses['SCZ'] ?? $warehouses['LPZ'],
            'Cochabamba' => $warehouses['CBA'] ?? $warehouses['LPZ'],
            default => $warehouses['LPZ'],
        };
    }

    private function paymentMethod(string $channel): string
    {
        return match ($channel) {
            'empresa_institucional' => ['transferencia', 'credito', 'qr'][random_int(0, 2)],
            'tienda_barrio' => ['efectivo', 'qr', 'tarjeta_debito'][random_int(0, 2)],
            default => ['efectivo', 'qr', 'tarjeta_debito'][random_int(0, 2)],
        };
    }

    private function saleStatus(Carbon $date): string
    {
        return $date->greaterThan(Carbon::create(2026, 9, 3))
            ? (random_int(1, 100) <= 76 ? 'entregado' : 'sin_entregar')
            : (random_int(1, 100) <= 93 ? 'entregado' : 'sin_entregar');
    }

    private function cashAmounts(string $payment, float $total): array
    {
        if ($payment !== 'efectivo') {
            return [null, null];
        }

        $received = ceil(($total + random_int(10, 60)) / 10) * 10;
        return [$received, round($received - $total, 2)];
    }

    private function shelfLife(Product $product, int $month): int
    {
        $category = $product->category->name ?? '';

        return match ($category) {
            'Leches fluidas', 'Leches saborizadas', 'Yogurt', 'Bebidas lacteas' => 38 + (($month % 4) * 12),
            'Jugos y nectares', 'Agua', 'Te helado', 'Alimento de soya' => 80 + (($month % 5) * 18),
            'Reposteria', 'Dulce de leche', 'Mantequillas y margarinas' => 125 + (($month % 4) * 24),
            'Leches en polvo', 'Mermeladas', 'Postres' => 190 + (($month % 6) * 30),
            default => 96 + (($month % 5) * 20),
        };
    }

    private function syncInventory(): void
    {
        $rows = ProductLot::select('product_id', 'warehouse_id', DB::raw('SUM(quantity) as quantity'))
            ->groupBy('product_id', 'warehouse_id')
            ->get();

        foreach ($rows as $row) {
            DB::table('inventory')->updateOrInsert(
                ['product_id' => $row->product_id, 'warehouse_id' => $row->warehouse_id],
                ['quantity' => $row->quantity, 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    private function actualDemandForProduct(int $productId, Carbon $start, Carbon $end): int
    {
        return (int) DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sale_items.product_id', $productId)
            ->whereBetween('sales.created_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            ->sum('sale_items.quantity');
    }

    private function evaluateForecastNumbers(int $predicted, int $actual, float $previousFactor, int $underStreak, int $overStreak): array
    {
        $mae = abs($actual - $predicted);
        $wape = $actual > 0 ? $mae / $actual : ($predicted > 0 ? 1 : 0);
        $wapePercent = $wape * 100;
        $level = $wapePercent <= 15 ? 'BUENO' : ($wapePercent <= 30 ? 'REGULAR' : 'BAJO');
        $factor = $previousFactor;
        $decision = 'OK';
        $transferQty = 0;

        if ($wapePercent > 15 && $actual > $predicted) {
            $underStreak++;
            $overStreak = 0;
            $factor = min(1.5, $previousFactor + ($wapePercent > 30 || $underStreak >= 2 ? 0.05 : 0.02));
            if ($level === 'BAJO') {
                $decision = 'CREATE_TRANSFER_REQUEST';
                $transferQty = max(50, $mae);
            }
        } elseif ($wapePercent > 15 && $actual < $predicted) {
            $overStreak++;
            $underStreak = 0;
            $factor = max(0.65, $previousFactor - ($wapePercent > 30 || $overStreak >= 2 ? 0.05 : 0.02));
        } else {
            $underStreak = 0;
            $overStreak = 0;
        }

        return [
            'mae' => $mae,
            'wape' => round($wape, 4),
            'level' => $level,
            'factor_after' => round($factor, 4),
            'under_streak' => $underStreak,
            'over_streak' => $overStreak,
            'decision' => $decision,
            'transfer_qty' => $transferQty,
        ];
    }

    private function insertChunked(string $table, array $rows, int $size = 1000): void
    {
        foreach (array_chunk($rows, $size) as $chunk) {
            if ($chunk !== []) {
                DB::table($table)->insert($chunk);
            }
        }
    }
}
