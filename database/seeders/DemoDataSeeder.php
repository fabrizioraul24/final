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
use App\Models\Role;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Transfer;
use App\Models\TransferItem;
use App\Models\User;
use App\Models\VendorVisit;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    private const SALES_TARGET_2025 = 20000;
    private const SALE_MOVEMENTS_TARGET_2025 = 46607;
    private const NEW_USERS_TARGET = 50;
    private const INACTIVE_USERS_TARGET = 10;

    private array $lotQueues = [];
    private array $lotBalances = [];

    public function run(): void
    {
        mt_srand(20250101);

        $products = Product::with('category')->orderBy('id')->get();
        $warehouses = Warehouse::orderBy('id')->get()->keyBy('code');
        $cities = City::orderBy('id')->get()->keyBy('name');

        if ($products->isEmpty() || $warehouses->isEmpty() || ! $warehouses->has('LPZ')) {
            $this->command?->warn('No hay productos o almacenes suficientes para crear la historia 2025.');
            return;
        }

        DB::transaction(function () use ($products, $warehouses, $cities) {
            $this->cleanPrevious2025Load();

            $users = $this->seedOperationalUsers($cities);
            $customers = $this->seedRetailCustomers($users['buyers'], $cities);
            $companies = Company::whereIn('company_type', ['empresa_institucional', 'tienda_barrio'])
                ->orderBy('id')
                ->get()
                ->groupBy('company_type');

            $this->seedLots($products, $warehouses, $users['warehouse']);
            $this->seedTransfers($products, $warehouses, $users);
            $this->seedDamageReports($products, $warehouses, $users['warehouse']);
            $this->seedSales($products, $warehouses, $cities, $companies, $customers, $users['sellers']);
            $this->seedBuyerOrders($products, $customers);
            $this->seedQuotations($products, $companies, $customers, $users['sellers']);
            $this->seedVendorVisits($companies, $users['sellers']);
            $this->syncInventory();
            $this->seedAuditLogs($users);
        });
    }

    private function cleanPrevious2025Load(): void
    {
        $saleIds = Sale::withTrashed()
            ->whereBetween('created_at', ['2025-01-01 00:00:00', '2025-12-31 23:59:59'])
            ->pluck('id');

        foreach ($saleIds->chunk(1000) as $chunk) {
            SaleItem::whereIn('sale_id', $chunk)->delete();
            Sale::withTrashed()->whereIn('id', $chunk)->forceDelete();
        }

        ProductLotMovement::whereBetween('created_at', ['2025-01-01 00:00:00', '2025-12-31 23:59:59'])
            ->where(function ($query) {
                $query->whereIn('type', ['venta', 'ingreso', 'traspaso', 'merma', 'vencimiento', 'ajuste', 'venta_demo', 'traspaso_demo', 'dano_demo'])
                    ->orWhereRaw('LOWER(note) LIKE ?', ['%demo%'])
                    ->orWhereRaw('LOWER(note) LIKE ?', ['%seeder%']);
            })
            ->delete();

        ProductLot::where('lote_code', 'like', 'PIL-2025-%')->delete();

        BuyerOrderItem::whereBetween('created_at', ['2025-01-01 00:00:00', '2025-12-31 23:59:59'])->delete();
        BuyerOrder::whereBetween('created_at', ['2025-01-01 00:00:00', '2025-12-31 23:59:59'])->delete();
        QuotationItem::whereBetween('created_at', ['2025-01-01 00:00:00', '2025-12-31 23:59:59'])->delete();
        Quotation::whereBetween('created_at', ['2025-01-01 00:00:00', '2025-12-31 23:59:59'])->delete();
        VendorVisit::whereBetween('created_at', ['2025-01-01 00:00:00', '2025-12-31 23:59:59'])->delete();
        TransferItem::whereBetween('created_at', ['2025-01-01 00:00:00', '2025-12-31 23:59:59'])->delete();
        Transfer::whereBetween('created_at', ['2025-01-01 00:00:00', '2025-12-31 23:59:59'])->delete();
        DamageReport::whereBetween('created_at', ['2025-01-01 00:00:00', '2025-12-31 23:59:59'])->delete();
        AuditLog::whereBetween('created_at', ['2025-01-01 00:00:00', '2025-12-31 23:59:59'])->delete();
    }

    private function seedOperationalUsers(Collection $cities): array
    {
        $roles = Role::pluck('id', 'name');
        $sellerRole = $roles['Vendedor'] ?? null;
        $buyerRole = $roles['Comprador'] ?? null;
        $warehouseRole = Role::where('name', 'like', 'Almac%')->value('id') ?? ($roles['Almacen'] ?? null);

        $sellerNames = ['Rodrigo Lima', 'Natalia Paredes', 'Edwin Flores', 'Carla Choque', 'Marco Rios', 'Lucia Soria', 'Rene Calderon', 'Paola Vargas', 'Javier Mamani', 'Daniela Cordero', 'Ivan Quispe', 'Andrea Molina', 'Luis Arteaga', 'Gabriela Salinas', 'Victor Condori', 'Mariana Arias', 'Sergio Teran', 'Claudia Villarroel', 'Miguel Apaza', 'Sandra Gutierrez', 'Hugo Rocabado', 'Noelia Bustillos', 'Ernesto Rojas', 'Pamela Caceres'];
        $warehouseNames = ['Armando Gutierrez', 'Celia Huanca', 'Bruno Poma', 'Walter Choque', 'Ruth Aguilar', 'Oscar Beltran', 'Lidia Cori', 'Franz Mendoza'];
        $buyerNames = ['Roxana Velarde', 'Oscar Montano', 'Elena Tapia', 'Hernan Paredes', 'Janeth Callisaya', 'Mario Alarcon', 'Veronica Pinto', 'Alvaro Crespo', 'Rosa Nina', 'Diego Saavedra', 'Patricia Cuentas', 'Raul Aliaga', 'Jimena Blanco', 'Cesar Rivas', 'Silvia Lora', 'Adrian Maldonado', 'Martha Copa', 'Fernando Villca'];

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

        $created = ['sellers' => collect(), 'warehouse' => collect(), 'buyers' => collect()];

        foreach (array_slice($records, 0, self::NEW_USERS_TARGET) as $index => [$name, $roleId, $group]) {
            if (! $roleId) {
                continue;
            }

            $slug = Str::slug($name, '.');
            $user = User::withTrashed()->updateOrCreate(
                ['email' => $slug.'@pil2025.bo'],
                [
                    'name' => $name,
                    'username' => $slug.'.2025',
                    'password' => Hash::make($group.'1234'),
                    'role_id' => $roleId,
                    'deleted_at' => null,
                    'created_at' => Carbon::create(2025, 1, 2)->addDays($index),
                    'updated_at' => Carbon::create(2025, 1, 2)->addDays($index),
                ]
            );

            if ($index >= self::NEW_USERS_TARGET - self::INACTIVE_USERS_TARGET) {
                $user->delete();
            } elseif ($group === 'vendedor') {
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

    private function seedRetailCustomers(Collection $buyers, Collection $cities): Collection
    {
        $addresses = [
            ['La Paz', 'Calle Rosendo Gutierrez #456, Sopocachi'],
            ['La Paz', 'Av. Ballivian #1234, Calacoto'],
            ['La Paz', 'Calle 29 #220, Achumani'],
            ['La Paz', 'Av. Saavedra #901, Miraflores'],
            ['El Alto', 'Av. Juan Pablo II #778, Rio Seco'],
            ['El Alto', 'Av. 6 de Marzo #1420, Senkata'],
            ['El Alto', 'Av. Civica #330, Ceja'],
            ['El Alto', 'Av. Litoral #251, Villa Adela'],
        ];

        foreach ($buyers as $index => $user) {
            if ($user->trashed()) {
                continue;
            }

            [$city, $address] = $addresses[$index % count($addresses)];
            Customer::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'nit' => (string) (6500000 + $index),
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
        $createdAt = Carbon::create(2025, 1, 2, 7, 30);
        $warehouseUserId = $warehouseUsers->first()?->id;

        foreach ($products as $product) {
            foreach ($warehouses as $warehouse) {
                for ($month = 1; $month <= 12; $month++) {
                    $base = $warehouse->code === 'LPZ' ? 250000 : 65000;
                    $qty = $base + (($product->id + $month) % 9) * 3500;
                    $date = Carbon::create(2025, $month, min(24, 2 + ($product->id % 18)), 7 + ($month % 5), 0);
                    $lotCode = sprintf('PIL-2025-%s-%s-%02d', $warehouse->code, $product->sku, $month);

                    $lot = ProductLot::create([
                        'product_id' => $product->id,
                        'warehouse_id' => $warehouse->id,
                        'lote_code' => $lotCode,
                        'quantity' => $qty,
                        'expires_at' => $date->copy()->addDays($this->shelfLife($product, $month))->toDateString(),
                        'safety_threshold' => $product->min_quantity ?? 0,
                        'created_at' => $date,
                        'updated_at' => $date,
                    ]);

                    $this->lotQueues[$product->id][$warehouse->id][] = [
                        'id' => $lot->id,
                        'quantity' => $qty,
                        'expires_at' => $lot->expires_at,
                    ];
                    $this->lotBalances[$lot->id] = $qty;

                    $movementRows[] = [
                        'lot_id' => $lot->id,
                        'user_id' => $warehouseUserId,
                        'type' => 'ingreso',
                        'quantity' => $qty,
                        'note' => 'Ingreso por produccion y distribucion regional 2025.',
                        'created_at' => $date,
                        'updated_at' => $date,
                    ];
                }
            }
        }

        $this->insertChunked('product_lot_movements', $movementRows);
    }

    private function seedSales(Collection $products, Collection $warehouses, Collection $cities, Collection $companies, Collection $customers, Collection $sellers): void
    {
        $salesRows = [];
        $saleItemsRows = [];
        $movementRows = [];
        $monthlyPlan = [1450, 1600, 1750, 1620, 1580, 1650, 1700, 1780, 1850, 1750, 1850, 1420];
        $saleNumber = 0;
        $extraItems = self::SALE_MOVEMENTS_TARGET_2025 - (self::SALES_TARGET_2025 * 2);

        foreach ($monthlyPlan as $monthIndex => $salesInMonth) {
            $month = $monthIndex + 1;
            for ($i = 0; $i < $salesInMonth; $i++) {
                $saleNumber++;
                $date = $this->saleDate($month, $i, $salesInMonth);
                $channel = $this->channelForSale($saleNumber);
                $context = $this->saleContext($channel, $companies, $customers, $cities);
                $warehouse = $this->warehouseForCity($warehouses, $context['city']);
                $seller = $sellers[($saleNumber + $month) % max(1, $sellers->count())];
                $payment = $this->paymentMethod($channel);
                $itemCount = 2 + ($saleNumber <= $extraItems ? 1 : 0);
                $selectedProducts = $this->pickProducts($products, $channel, $month, $saleNumber, $itemCount);
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
                foreach ($selectedProducts as $product) {
                    $quantity = $this->quantity($product, $channel, $month);
                    $unitPrice = $channel === 'empresa_institucional'
                        ? (float) $product->price_institutional
                        : (float) $product->suggested_price_public;
                    $subtotal = round($quantity * $unitPrice, 2);
                    $lotId = $this->consumeLot($product->id, $warehouse->id, $quantity);

                    $saleItemsRows[] = [
                        'sale_id' => $saleId,
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'subtotal' => $subtotal,
                        'created_at' => $date,
                        'updated_at' => $date,
                    ];
                    $movementRows[] = [
                        'lot_id' => $lotId,
                        'user_id' => $seller->id,
                        'type' => 'venta',
                        'quantity' => -$quantity,
                        'note' => sprintf('Venta 2025 #%05d - canal %s.', $saleNumber, str_replace('_', ' ', $channel)),
                        'created_at' => $date,
                        'updated_at' => $date,
                    ];
                    $total += $subtotal;
                }

                [$received, $change] = $this->cashAmounts($payment, $total);
                $salesRows[] = [
                    'id' => $saleId,
                    'total_amount' => round($total, 2),
                    'amount_received' => $received,
                    'change_amount' => $change,
                ];

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

        foreach (array_chunk($salesRows, 500) as $chunk) {
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
                'updated_at' => Carbon::create(2025, 12, 31, 18, 0),
            ]);
        }
    }

    private function seedTransfers(Collection $products, Collection $warehouses, array $users): void
    {
        $sourceWarehouses = $warehouses->filter(fn ($warehouse, $code) => $code !== 'LPZ')->values();
        $lpz = $warehouses['LPZ'];
        $warehouseUsers = $users['warehouse'];
        $sellers = $users['sellers'];

        for ($i = 0; $i < 96; $i++) {
            $date = Carbon::create(2025, (($i % 12) + 1), (($i % 24) + 2), 8 + ($i % 8), 0);
            $status = $i < 78 ? Transfer::STATUS_RECEIVED : ($i < 90 ? Transfer::STATUS_IN_TRANSIT : Transfer::STATUS_PENDING);
            $transfer = Transfer::create([
                'from_warehouse_id' => $sourceWarehouses[$i % max(1, $sourceWarehouses->count())]->id ?? null,
                'to_warehouse_id' => $lpz->id,
                'requested_by' => $sellers[$i % max(1, $sellers->count())]->id,
                'approved_by' => $warehouseUsers[$i % max(1, $warehouseUsers->count())]->id ?? null,
                'received_by' => $status === Transfer::STATUS_RECEIVED ? ($warehouseUsers[($i + 1) % max(1, $warehouseUsers->count())]->id ?? null) : null,
                'status' => $status,
                'expected_date' => $date->copy()->addDays(2)->toDateString(),
                'received_date' => $status === Transfer::STATUS_RECEIVED ? $date->copy()->addDays(2)->toDateString() : null,
                'notes' => 'Reabastecimiento operativo por rotacion regional y cobertura de cuentas clave.',
                'created_at' => $date,
                'updated_at' => $date->copy()->addDays($status === Transfer::STATUS_RECEIVED ? 2 : 0),
            ]);

            foreach ($products->slice(($i * 3) % max(1, $products->count()), 3) as $product) {
                TransferItem::create([
                    'transfer_id' => $transfer->id,
                    'product_id' => $product->id,
                    'requested_qty' => 180 + (($i + $product->id) % 7) * 24,
                    'received_qty' => $status === Transfer::STATUS_RECEIVED ? 170 + (($i + $product->id) % 7) * 23 : null,
                    'damaged_qty' => $status === Transfer::STATUS_RECEIVED ? ($i + $product->id) % 4 : 0,
                    'notes' => 'Controlado por despacho, camara fria y hoja de ruta.',
                    'lot_code' => $status === Transfer::STATUS_RECEIVED ? sprintf('PIL-2025-LPZ-%s-T%02d', $product->sku, $i + 1) : null,
                    'receiving_expires_at' => $status === Transfer::STATUS_RECEIVED ? $date->copy()->addDays($this->shelfLife($product, $i % 12))->toDateString() : null,
                    'receiving_note' => $status === Transfer::STATUS_RECEIVED ? 'Recepcion validada por almacen La Paz.' : null,
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);
            }
        }
    }

    private function seedDamageReports(Collection $products, Collection $warehouses, Collection $warehouseUsers): void
    {
        $warehouseUserId = $warehouseUsers->first()?->id;
        $lots = ProductLot::where('lote_code', 'like', 'PIL-2025-%')->orderBy('expires_at')->limit(140)->get();

        foreach ($lots as $index => $lot) {
            $date = Carbon::create(2025, (($index % 12) + 1), (($index % 24) + 3), 10, 15);
            $qty = min($lot->quantity, 5 + ($index % 14));
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
                'comment' => 'Merma registrada por control de calidad, cadena de frio o manipulacion de despacho.',
                'created_at' => $date,
                'updated_at' => $date,
            ]);
            ProductLotMovement::create([
                'lot_id' => $lot->id,
                'user_id' => $warehouseUserId,
                'type' => 'merma',
                'quantity' => -$qty,
                'note' => 'Baja por control de calidad 2025.',
                'created_at' => $date,
                'updated_at' => $date,
            ]);
        }
    }

    private function seedBuyerOrders(Collection $products, Collection $customers): void
    {
        if ($customers->isEmpty()) {
            return;
        }

        for ($i = 0; $i < 1200; $i++) {
            $date = Carbon::create(2025, (($i % 12) + 1), (($i % 26) + 1), 9 + ($i % 9), ($i * 7) % 60);
            $customer = $customers[$i % $customers->count()];
            $order = BuyerOrder::create([
                'user_id' => $customer->user_id,
                'receipt_number' => sprintf('PED-2025-%05d', $i + 1),
                'payment_method' => ['efectivo', 'qr', 'tarjeta_debito'][$i % 3],
                'payment_status' => $i % 19 === 0 ? 'reembolsado' : 'pagado',
                'status' => $i % 23 === 0 ? 'cancelado' : 'entregado',
                'subtotal' => 0,
                'shipping' => [0, 5, 7.5, 10][$i % 4],
                'total' => 0,
                'issued_at' => $date,
                'created_at' => $date,
                'updated_at' => $date,
            ]);

            $subtotal = 0;
            foreach ($this->pickProducts($products, 'comprador_minorista', (int) $date->month, $i, 3) as $product) {
                $qty = 1 + (($i + $product->id) % 4);
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

            $order->forceFill([
                'subtotal' => round($subtotal, 2),
                'total' => round($subtotal + $order->shipping, 2),
            ])->saveQuietly();
        }
    }

    private function seedQuotations(Collection $products, Collection $companies, Collection $customers, Collection $sellers): void
    {
        for ($i = 0; $i < 960; $i++) {
            $channel = $i % 5 === 0 ? 'comprador_minorista' : ($i % 2 === 0 ? 'empresa_institucional' : 'tienda_barrio');
            $date = Carbon::create(2025, (($i % 12) + 1), (($i % 25) + 1), 11, ($i * 11) % 60);
            $context = $this->saleContext($channel, $companies, $customers, City::get()->keyBy('name'));
            $seller = $sellers[$i % max(1, $sellers->count())];
            $quotation = Quotation::create([
                'company_id' => $context['company_id'],
                'customer_id' => $context['customer_id'],
                'seller_id' => $seller->id,
                'sale_type' => $channel,
                'valid_until' => $date->copy()->addDays(10)->toDateString(),
                'status' => ['aceptada', 'aceptada', 'enviada', 'rechazada'][$i % 4],
                'total_amount' => 0,
                'notes' => 'Cotizacion comercial para reposicion, volumen y negociacion de precios 2025.',
                'created_at' => $date,
                'updated_at' => $date,
            ]);

            $total = 0;
            foreach ($this->pickProducts($products, $channel, (int) $date->month, $i, 4) as $product) {
                $qty = max(2, (int) round($this->quantity($product, $channel, (int) $date->month) * 0.8));
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

        for ($i = 0; $i < 2600; $i++) {
            $date = Carbon::create(2025, (($i % 12) + 1), (($i % 26) + 1), 8, 0);
            VendorVisit::create([
                'user_id' => $sellers[$i % max(1, $sellers->count())]->id,
                'company_id' => $allCompanies[$i % $allCompanies->count()]->id,
                'visit_date' => $date->toDateString(),
                'status' => $i % 17 === 0 ? 'reprogramada' : 'completada',
                'note' => 'Seguimiento de exhibicion, frio, cobranza y reposicion semanal.',
                'created_at' => $date,
                'updated_at' => $date->copy()->addHours(2),
            ]);
        }
    }

    private function seedAuditLogs(array $users): void
    {
        $adminId = User::where('email', 'admin@gmail.com')->value('id') ?? $users['sellers']->first()?->id;
        $sales = Sale::whereYear('created_at', 2025)->latest('id')->limit(900)->get(['id', 'seller_id', 'sale_type', 'status', 'total_amount', 'created_at']);
        $rows = [];

        foreach ($sales as $sale) {
            $rows[] = [
                'user_id' => $sale->seller_id ?: $adminId,
                'entity_type' => Sale::class,
                'entity_id' => $sale->id,
                'action' => 'create',
                'description' => 'Venta historica registrada en operacion comercial 2025.',
                'old_values' => json_encode([]),
                'new_values' => json_encode($sale->only(['sale_type', 'status', 'total_amount'])),
                'created_at' => $sale->created_at,
            ];
        }

        $this->insertChunked('audit_logs', $rows);
    }

    private function saleContext(string $channel, Collection $companies, Collection $customers, Collection $cities): array
    {
        if ($channel === 'comprador_minorista' && $customers->isNotEmpty()) {
            $customer = $customers[array_rand($customers->all())];
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
        $name = Str::lower($product->name.' '.$product->description);
        $score = (($product->id * 37 + $salt * 13) % 100);

        if (preg_match('/leche|yogurt|pilfrut|juguito|chiqui|chicolac/', $name)) {
            $score += 45;
        }
        if ($channel === 'empresa_institucional' && preg_match('/1 l|2 l|1 kg|5 l|800 ml|946 ml/', $name)) {
            $score += 30;
        }
        if ($channel === 'tienda_barrio' && (float) $product->suggested_price_public <= 12) {
            $score += 26;
        }
        if (in_array($month, [2, 3, 11, 12], true)) {
            $score += 12;
        }

        return $score;
    }

    private function quantity(Product $product, string $channel, int $month): int
    {
        $text = Str::lower($product->name.' '.$product->description);
        $bulk = preg_match('/1 l|2 l|1 kg|5 l|800 ml|946 ml|760 g/', $text);
        $small = preg_match('/100 g|110 g|120 g|140 ml|150 ml|170 g|190 ml|200 ml/', $text);

        $quantity = match ($channel) {
            'empresa_institucional' => $bulk ? random_int(96, 420) : random_int(180, 720),
            'tienda_barrio' => $bulk ? random_int(18, 72) : random_int(36, 144),
            default => $bulk ? random_int(2, 10) : random_int(3, 18),
        };

        if ($small) {
            $quantity = (int) round($quantity * 1.35);
        }
        if (preg_match('/pilfrut|juguito|chiqui|chicolac/', $text) && in_array($month, [2, 3], true)) {
            $quantity = (int) round($quantity * 1.28);
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

    private function saleDate(int $month, int $index, int $salesInMonth): Carbon
    {
        $days = Carbon::create(2025, $month, 1)->daysInMonth;
        $day = 1 + (int) floor(($index / max(1, $salesInMonth)) * $days);
        $hour = 7 + ($index % 12);
        $minute = ($index * 7) % 60;

        return Carbon::create(2025, $month, min($day, $days), $hour, $minute);
    }

    private function channelForSale(int $saleNumber): string
    {
        return match ($saleNumber % 20) {
            0, 3, 6 => 'comprador_minorista',
            1, 2, 4, 5, 7, 8, 10, 12, 14 => 'empresa_institucional',
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
        return $date->month === 12 && $date->day > 24 && random_int(1, 100) <= 22
            ? 'sin_entregar'
            : (random_int(1, 100) <= 94 ? 'entregado' : 'sin_entregar');
    }

    private function cashAmounts(string $payment, float $total): array
    {
        if ($payment !== 'efectivo') {
            return [null, null];
        }

        $received = ceil(($total + random_int(5, 45)) / 10) * 10;
        return [$received, round($received - $total, 2)];
    }

    private function shelfLife(Product $product, int $month): int
    {
        $category = $product->category->name ?? '';

        return match ($category) {
            'Leches fluidas', 'Leches saborizadas', 'Yogurt', 'Bebidas lacteas' => 35 + (($month % 4) * 12),
            'Jugos y nectares', 'Agua', 'Te helado', 'Alimento de soya' => 75 + (($month % 5) * 18),
            'Reposteria', 'Dulce de leche', 'Mantequillas y margarinas' => 120 + (($month % 4) * 24),
            'Leches en polvo', 'Mermeladas', 'Postres' => 180 + (($month % 6) * 30),
            default => 90 + (($month % 5) * 20),
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

    private function insertChunked(string $table, array $rows, int $size = 1000): void
    {
        foreach (array_chunk($rows, $size) as $chunk) {
            if ($chunk !== []) {
                DB::table($table)->insert($chunk);
            }
        }
    }
}
