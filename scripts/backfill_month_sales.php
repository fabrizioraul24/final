<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\City;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductLot;
use App\Models\ProductLotMovement;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

$products = Product::with('category')->get();
$warehouses = Warehouse::get()->keyBy('code');
$cities = City::get()->keyBy('name');
$companiesByType = Company::get()->groupBy('company_type');
$customers = Customer::with('user')->get();
$sellerId = User::where('email', 'ventas@gmail.com')->value('id')
    ?? User::where('email', 'admin@gmail.com')->value('id')
    ?? User::query()->value('id');

if ($products->isEmpty() || $warehouses->isEmpty() || $customers->isEmpty() || ! $sellerId) {
    throw new RuntimeException('Faltan datos base para crear ventas mensuales.');
}

function warehouseForCity($warehouses, ?string $city)
{
    return match ($city) {
        'La Paz', 'El Alto' => $warehouses->get('LPZ'),
        'Santa Cruz' => $warehouses->get('SCZ'),
        'Cochabamba' => $warehouses->get('CBA'),
        default => $warehouses->get('LPZ') ?? $warehouses->first(),
    };
}

function pickChannel(): string
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

function saleContext(string $channel, $companiesByType, $customers, $cities): ?array
{
    if ($channel === 'comprador_minorista') {
        $customer = $customers->random();

        return [
            'company_id' => null,
            'customer_id' => $customer->id,
            'city' => $customer->city,
            'city_id' => $customer->city_id ?: $cities->get($customer->city)?->id,
            'address' => $customer->delivery_address,
        ];
    }

    $group = $companiesByType->get($channel);
    if (! $group || $group->isEmpty()) {
        return null;
    }

    $company = $group->random();

    return [
        'company_id' => $company->id,
        'customer_id' => null,
        'city' => $company->city,
        'city_id' => $company->city_id ?: $cities->get($company->city)?->id,
        'address' => $company->address,
    ];
}

function demandWeight($product, string $channel, Carbon $date): int
{
    $name = Str::lower($product->name);
    $category = Str::lower($product->category->name ?? '');
    $description = Str::lower($product->description ?? '');
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

    if (preg_match('/(biogurt|yogurt bebible|greco yogurt griego|leche fresca natural|leche natural larga vida)/', $name)) {
        $weight += 8;
    }

    return max(1, $weight);
}

function pickProducts($products, string $channel, Carbon $date, int $count)
{
    $available = $products->values();
    $selected = collect();

    while ($selected->count() < $count && $available->isNotEmpty()) {
        $weights = $available->map(fn ($product) => max(1, demandWeight($product, $channel, $date)))->values();
        $total = $weights->sum();
        $pivot = rand(1, max(1, $total));
        $carry = 0;
        $chosen = 0;

        foreach ($weights as $index => $weight) {
            $carry += $weight;
            if ($pivot <= $carry) {
                $chosen = $index;
                break;
            }
        }

        $selected->push($available->get($chosen));
        $available->forget($chosen);
        $available = $available->values();
    }

    return $selected;
}

function quantityForSale($product, string $channel): int
{
    $description = Str::lower($product->description ?? '');
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

    return max(1, $quantity);
}

function paymentMethodForChannel(string $channel): string
{
    return match ($channel) {
        'empresa_institucional' => collect(['transferencia', 'credito', 'qr'])->random(),
        'tienda_barrio' => collect(['efectivo', 'qr', 'tarjeta_debito'])->random(),
        default => collect(['efectivo', 'qr', 'tarjeta_debito'])->random(),
    };
}

function createSaleForDate(Carbon $date, $products, $warehouses, $cities, $companiesByType, $customers, int $sellerId): bool
{
    $channel = pickChannel();
    $context = saleContext($channel, $companiesByType, $customers, $cities);
    if (! $context) {
        return false;
    }

    $warehouse = warehouseForCity($warehouses, $context['city']);
    if (! $warehouse) {
        return false;
    }

    $items = pickProducts($products, $channel, $date, match ($channel) {
        'empresa_institucional' => rand(3, 6),
        'tienda_barrio' => rand(3, 5),
        default => rand(2, 4),
    });

    if ($items->isEmpty()) {
        return false;
    }

    $paymentMethod = paymentMethodForChannel($channel);

    $sale = Sale::create([
        'company_id' => $context['company_id'],
        'customer_id' => $context['customer_id'],
        'seller_id' => $sellerId,
        'warehouse_id' => $warehouse->id,
        'sale_type' => $channel,
        'delivery_address' => $context['address'],
        'delivery_city' => $context['city'],
        'delivery_city_id' => $context['city_id'],
        'status' => rand(0, 100) < 85 ? 'entregado' : 'sin_entregar',
        'payment_method' => $paymentMethod,
        'amount_received' => $paymentMethod === 'efectivo' ? 0 : null,
        'change_amount' => 0,
        'total_amount' => 0,
    ]);

    $total = 0;

    foreach ($items as $product) {
        $quantity = quantityForSale($product, $channel);
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

        $lot = ProductLot::query()
            ->where('product_id', $product->id)
            ->where('warehouse_id', $warehouse->id)
            ->orderBy('expires_at')
            ->first();

        if ($lot) {
            $movement = ProductLotMovement::create([
                'lot_id' => $lot->id,
                'user_id' => $sellerId,
                'type' => 'venta_backfill',
                'quantity' => -abs($quantity),
                'note' => 'Venta historica backfill #' . $sale->id,
            ]);

            $movement->forceFill([
                'created_at' => $date,
                'updated_at' => $date,
            ])->saveQuietly();
        }

        $total += $subtotal;
    }

    $amountReceived = null;
    $changeAmount = null;

    if ($paymentMethod === 'efectivo') {
        $amountReceived = ceil(($total + rand(5, 35)) / 10) * 10;
        $changeAmount = round(max(0, $amountReceived - $total), 2);
    }

    $sale->forceFill([
        'total_amount' => round($total, 2),
        'amount_received' => $amountReceived,
        'change_amount' => $changeAmount,
        'created_at' => $date,
        'updated_at' => $date,
    ])->saveQuietly();

    return true;
}

$start = now()->startOfMonth();
$end = now()->copy()->startOfDay();
$created = 0;
$perDay = [];

DB::transaction(function () use ($start, $end, $products, $warehouses, $cities, $companiesByType, $customers, $sellerId, &$created, &$perDay): void {
    for ($day = $start->copy(); $day->lte($end); $day->addDay()) {
        $existing = Sale::whereDate('created_at', $day->toDateString())->count();
        $minimum = $day->gte(now()->copy()->subDays(6)->startOfDay()) ? 8 : 4;
        $missing = max(0, $minimum - $existing);

        for ($i = 0; $i < $missing; $i++) {
            $date = $day->copy()->setTime(rand(8, 19), rand(0, 59), rand(0, 59));
            if (createSaleForDate($date, $products, $warehouses, $cities, $companiesByType, $customers, $sellerId)) {
                $created++;
            }
        }

        $perDay[$day->toDateString()] = Sale::whereDate('created_at', $day->toDateString())->count();
    }
});

echo json_encode([
    'created' => $created,
    'range' => [$start->toDateString(), $end->toDateString()],
    'per_day' => $perDay,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
