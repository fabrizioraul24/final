<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductLot;
use App\Models\ProductLotMovement;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $warehouses = Warehouse::whereIn('code', ['LPZ', 'SCZ', 'CBA'])->get()->keyBy('code');

        if ($warehouses->count() < 3) {
            $this->command?->warn('No existen los almacenes LPZ, SCZ y CBA para ProductSeeder.');
            return;
        }

        $categoryCache = Category::query()->pluck('id', 'name');

        foreach ($this->catalogRows() as $position => $row) {
            $categoryId = $categoryCache[$row['category']]
                ?? Category::create([
                    'name' => $row['category'],
                    'description' => 'Categoria importada desde catalogo comercial PIL Andina.',
                ])->id;

            $categoryCache[$row['category']] = $categoryId;

            $minQuantity = $this->minimumQuantity($row);
            $totalStock = $this->totalStockTarget($row, $position + 1);

            $product = Product::updateOrCreate(
                ['sku' => $row['sku']],
                [
                    'category_id' => $categoryId,
                    'name' => $row['name'],
                    'description' => $this->buildDescription($row),
                    'suggested_price_public' => $row['suggested_price_public'],
                    'price_institutional' => $row['price_institutional'],
                    'is_active' => true,
                    'image_path' => null,
                    'min_quantity' => $minQuantity,
                    'max_quantity' => $totalStock + max(180, $minQuantity * 3),
                ]
            );

            $this->seedLotsForProduct($product, $row, $warehouses, $position + 1, $minQuantity, $totalStock);
        }
    }

    private function buildDescription(array $row): string
    {
        return sprintf(
            '%s de la categoria %s. Presentacion comercial %s. Precio referencial observado en %s.',
            $row['name'],
            $row['category'],
            $row['presentation'],
            $row['source']
        );
    }

    private function minimumQuantity(array $row): int
    {
        return match ($row['category']) {
            'Leches fluidas', 'Yogurt', 'Bebidas lacteas', 'Jugos y nectares' => 70,
            'Leches saborizadas', 'Agua', 'Te helado' => 55,
            'Leches en polvo', 'Reposteria', 'Dulce de leche', 'Mantequillas y margarinas' => 35,
            'Mermeladas', 'Postres', 'Alimento de soya' => 28,
            default => 25,
        };
    }

    private function totalStockTarget(array $row, int $position): int
    {
        $base = match ($row['category']) {
            'Leches fluidas' => 920,
            'Yogurt' => 860,
            'Bebidas lacteas' => 1200,
            'Jugos y nectares' => 880,
            'Leches saborizadas' => 720,
            'Agua' => 900,
            'Leches en polvo' => 420,
            'Reposteria' => 360,
            'Dulce de leche' => 320,
            'Mantequillas y margarinas' => 340,
            'Alimento de soya' => 300,
            'Mermeladas' => 240,
            'Postres' => 260,
            'Te helado' => 280,
            default => 250,
        };

        return $base + (($position % 5) * 35);
    }

    private function seedLotsForProduct(
        Product $product,
        array $row,
        Collection $warehouses,
        int $position,
        int $minQuantity,
        int $totalStock
    ): void {
        $priorityLpProducts = [
            'PIL-0001', 'PIL-0002', 'PIL-0008', 'PIL-0031', 'PIL-0035', 'PIL-0051',
            'PIL-0055', 'PIL-0063', 'PIL-0067', 'PIL-0081', 'PIL-0086', 'PIL-0089',
        ];

        $expiringFocusProducts = [
            'PIL-0039', 'PIL-0040', 'PIL-0048', 'PIL-0052', 'PIL-0059', 'PIL-0082',
            'PIL-0094', 'PIL-0098',
        ];

        $allocation = [
            'SCZ' => 0.45,
            'CBA' => 0.33,
            'LPZ' => in_array($row['sku'], $priorityLpProducts, true) ? 0.08 : 0.22,
        ];

        foreach ($allocation as $code => $ratio) {
            $warehouse = $warehouses->get($code);
            if (! $warehouse) {
                continue;
            }

            $warehouseQty = max(8, (int) round($totalStock * $ratio));

            if ($code === 'LPZ' && in_array($row['sku'], $priorityLpProducts, true)) {
                $warehouseQty = max(8, min($minQuantity - 8, 18 + ($position % 7) * 3));
            }

            $lotAQty = max(1, (int) round($warehouseQty * 0.68));
            $lotBQty = max(1, $warehouseQty - $lotAQty);

            $lotAExpiry = now()->addDays($this->shelfLifeDays($row['category'], $position, false))->toDateString();
            $lotBExpiry = now()->addDays($this->shelfLifeDays(
                $row['category'],
                $position,
                $code === 'LPZ' && (in_array($row['sku'], $priorityLpProducts, true) || in_array($row['sku'], $expiringFocusProducts, true))
            ))->toDateString();

            $lotA = ProductLot::updateOrCreate(
                [
                    'warehouse_id' => $warehouse->id,
                    'product_id' => $product->id,
                    'lote_code' => sprintf('SEED-%s-%s-A', $row['sku'], $code),
                ],
                [
                    'quantity' => $lotAQty,
                    'expires_at' => $lotAExpiry,
                    'safety_threshold' => $minQuantity,
                ]
            );

            $lotB = ProductLot::updateOrCreate(
                [
                    'warehouse_id' => $warehouse->id,
                    'product_id' => $product->id,
                    'lote_code' => sprintf('SEED-%s-%s-B', $row['sku'], $code),
                ],
                [
                    'quantity' => $lotBQty,
                    'expires_at' => $lotBExpiry,
                    'safety_threshold' => $minQuantity,
                ]
            );

            $this->upsertSeedMovement($lotA, $lotAQty, 'Stock semilla maestro lote A');
            $this->upsertSeedMovement($lotB, $lotBQty, 'Stock semilla maestro lote B');
        }
    }

    private function shelfLifeDays(string $category, int $position, bool $expiringSoon): int
    {
        if ($expiringSoon) {
            return 7 + ($position % 15);
        }

        return match ($category) {
            'Leches fluidas', 'Leches saborizadas', 'Yogurt', 'Bebidas lacteas' => 30 + (($position % 6) * 12),
            'Jugos y nectares', 'Agua', 'Te helado', 'Alimento de soya' => 55 + (($position % 8) * 18),
            'Reposteria', 'Dulce de leche', 'Mantequillas y margarinas' => 80 + (($position % 7) * 24),
            'Leches en polvo', 'Mermeladas', 'Postres' => 140 + (($position % 9) * 28),
            default => 90 + (($position % 8) * 20),
        };
    }

    private function upsertSeedMovement(ProductLot $lot, int $quantity, string $note): void
    {
        ProductLotMovement::updateOrCreate(
            [
                'lot_id' => $lot->id,
                'type' => 'seed',
                'note' => $note,
            ],
            [
                'user_id' => null,
                'quantity' => $quantity,
            ]
        );
    }

    private function catalogRows(): array
    {
        $lines = preg_split('/\r\n|\r|\n/', trim(<<<'TSV'
PIL-0001	Leches fluidas	Leche fresca natural PIL UHT	900 ml	9.70	8.54	Fidalga
PIL-0002	Leches fluidas	Leche natural larga vida PIL	946 ml	7.80	6.86	Referencial tienda
PIL-0003	Leches fluidas	Leche entera UHT PIL	946 ml	7.90	6.95	Referencial tienda
PIL-0004	Leches fluidas	Leche light PIL	946 ml	7.22	6.35	Pedidos PIL
PIL-0005	Leches fluidas	Leche light PIL	800 ml	9.60	8.45	Fidalga
PIL-0006	Leches fluidas	Leche descremada Calcifem PIL	800 ml	11.50	10.12	Fidalga
PIL-0007	Leches fluidas	Leche descremada Proteplus PIL	800 ml	11.60	10.21	Fidalga
PIL-0008	Leches fluidas	Leche deslactosada larga vida PIL	800 ml	9.00	7.92	Fidalga
PIL-0009	Leches fluidas	Leche light deslactosada PIL	800 ml	9.50	8.36	Fidalga
PIL-0010	Leches fluidas	Leche deslactosada chocolate PIL	800 ml	10.50	9.24	Fidalga
PIL-0011	Leches fluidas	Leche deslactosada frutilla PIL	800 ml	10.50	9.24	Fidalga
PIL-0012	Leches saborizadas	Chicolac leche chocolatada PIL	120 ml	1.60	1.41	Amarket
PIL-0013	Leches saborizadas	Chicolac leche chocolatada PIL	800 ml	11.00	9.68	Amarket
PIL-0014	Leches saborizadas	Chiqui Choc leche saborizada	140 ml	2.40	2.11	Amarket
PIL-0015	Leches saborizadas	Chiqui Frutilla leche saborizada	170 ml	2.40	2.11	Amarket
PIL-0016	Leches saborizadas	Leche sabor frutilla PIL	946 ml	9.80	8.62	Fidalga
PIL-0017	Leches saborizadas	Leche con avena PIL	800 ml	9.90	8.71	Fidalga
PIL-0018	Leches saborizadas	Leche con avena sabor canela PIL	946 ml	9.90	8.71	Referencial tienda
PIL-0019	Leches saborizadas	Leche con cafe PIL	946 ml	9.90	8.71	Referencial tienda
PIL-0020	Leches saborizadas	Leche chocolatada PIL bolsa	800 ml	9.00	7.92	Fidalga
PIL-0021	Leches en polvo	Leche entera en polvo instantanea PIL	120 g	10.35	9.11	Pedidos PIL
PIL-0022	Leches en polvo	Leche entera en polvo instantanea PIL	370 g	26.77	23.56	Pedidos PIL
PIL-0023	Leches en polvo	Leche entera en polvo instantanea PIL	760 g	48.88	43.01	Pedidos PIL
PIL-0024	Leches en polvo	Leche instantanea PIL	760 g	78.50	69.08	Fidalga
PIL-0025	Leches en polvo	Leche light en polvo instantanea PIL	760 g	52.22	45.95	Pedidos PIL
PIL-0026	Leches en polvo	Leche instantanea light PIL	760 g	84.00	73.92	Fidalga
PIL-0027	Leches en polvo	Leche entera en polvo instantanea PIL doypack	2.200 g	133.32	117.32	Pedidos PIL
PIL-0028	Leches en polvo	Leche entera en polvo instantanea PIL lata	1.800 g	137.36	120.88	Pedidos PIL
PIL-0029	Leches en polvo	Alimento lacteo en polvo chocolatado PIL	120 g	8.50	7.48	Referencial tienda
PIL-0030	Leches en polvo	Leche entera deslactosada en polvo PIL	760 g	86.00	75.68	Referencial tienda
PIL-0031	Yogurt	Biogurt frutilla PIL	1 L	19.60	17.25	Fidalga
PIL-0032	Yogurt	Biogurt durazno PIL	1 L	19.60	17.25	Fidalga
PIL-0033	Yogurt	Biogurt LGG durazno PIL	1 L	18.90	16.63	Fidalga
PIL-0034	Yogurt	Biogurt LGG frutilla PIL	1 L	18.90	16.63	Fidalga
PIL-0035	Yogurt	Yogurt bebible PIL frutilla	1 L	18.50	16.28	Referencial tienda
PIL-0036	Yogurt	Yogurt bebible PIL durazno	1 L	18.50	16.28	Referencial tienda
PIL-0037	Yogurt	Yogurt bebible PIL mora	1 L	18.50	16.28	Referencial tienda
PIL-0038	Yogurt	Yogurt bebible PIL vainilla	1 L	18.50	16.28	Referencial tienda
PIL-0039	Yogurt	Yogurt batido PIL frutilla bolsa	1 kg	19.50	17.16	Referencial tienda
PIL-0040	Yogurt	Yogurt batido PIL durazno bolsa	1 kg	19.50	17.16	Referencial tienda
PIL-0041	Yogurt	Greco yogurt griego natural PIL	1 kg	27.60	24.29	Amarket
PIL-0042	Yogurt	Yogurt griego PIL frutilla	150 g	6.50	5.72	Referencial tienda
PIL-0043	Yogurt	Yogurt griego PIL natural	150 g	6.50	5.72	Referencial tienda
PIL-0044	Yogurt	Yogurt light PIL frutilla	1 L	19.90	17.51	Referencial tienda
PIL-0045	Yogurt	Yogurt light PIL durazno	1 L	19.90	17.51	Referencial tienda
PIL-0046	Yogurt	Yogurt deslactosado PIL frutilla	1 L	20.50	18.04	Referencial tienda
PIL-0047	Yogurt	Yogurt deslactosado PIL durazno	1 L	20.50	18.04	Referencial tienda
PIL-0048	Yogurt	Yogurello escolar PIL frutilla	100 g	2.20	1.94	Referencial tienda
PIL-0049	Yogurt	Yogurello escolar PIL durazno	100 g	2.20	1.94	Referencial tienda
PIL-0050	Yogurt	Yogurt con sobrecopa cereal PIL	170 g	6.90	6.07	Referencial tienda
PIL-0051	Bebidas lacteas	Pilfrut manzana	190 ml	0.82	0.72	Pedidos PIL
PIL-0052	Bebidas lacteas	Pilfrut durazno	190 ml	0.82	0.72	Pedidos PIL
PIL-0053	Bebidas lacteas	Pilfrut frutilla	190 ml	0.82	0.72	Pedidos PIL
PIL-0054	Bebidas lacteas	Pilfrut maracuya	190 ml	0.82	0.72	Pedidos PIL
PIL-0055	Bebidas lacteas	Pilfrut manzana	800 ml	3.20	2.82	Pedidos PIL
PIL-0056	Bebidas lacteas	Pilfrut durazno	800 ml	3.20	2.82	Pedidos PIL
PIL-0057	Bebidas lacteas	Pilfrut frutilla	800 ml	3.20	2.82	Referencial tienda
PIL-0058	Bebidas lacteas	Pilfrut maracuya	800 ml	3.20	2.82	Referencial tienda
PIL-0059	Jugos y nectares	Juguito PIL durazno	150 ml	3.20	2.82	Amarket
PIL-0060	Jugos y nectares	Juguito PIL mango	150 ml	3.20	2.82	Amarket
PIL-0061	Jugos y nectares	Juguito PIL manzana	150 ml	3.20	2.82	Amarket
PIL-0062	Jugos y nectares	Juguito PIL pina	150 ml	3.20	2.82	Amarket
PIL-0063	Jugos y nectares	Nectar Pura Vida Frutts durazno	2 L	11.58	10.19	Pedidos PIL pack x6
PIL-0064	Jugos y nectares	Nectar Pura Vida Frutts manzana	2 L	11.58	10.19	Pedidos PIL pack x6
PIL-0065	Jugos y nectares	Nectar Pura Vida Frutts naranja	2 L	12.00	10.56	Referencial tienda
PIL-0066	Jugos y nectares	Nectar Pura Vida Frutts mango	2 L	12.00	10.56	Referencial tienda
PIL-0067	Jugos y nectares	Nectar PIL durazno caja	1 L	8.50	7.48	Referencial tienda
PIL-0068	Jugos y nectares	Nectar PIL manzana caja	1 L	8.50	7.48	Referencial tienda
PIL-0069	Jugos y nectares	Nectar PIL naranja botella	500 ml	5.50	4.84	Referencial tienda
PIL-0070	Jugos y nectares	Nectar PIL durazno botella	500 ml	5.50	4.84	Referencial tienda
PIL-0071	Alimento de soya	Leche de vainilla Soy PIL	946 ml	7.50	6.60	Fidalga
PIL-0072	Alimento de soya	Leche de soya banana PIL	946 ml	9.54	8.40	Pedidos PIL
PIL-0073	Alimento de soya	Leche de soya chocolate PIL	946 ml	9.54	8.40	Referencial tienda
PIL-0074	Alimento de soya	Leche de soya natural PIL	946 ml	9.54	8.40	Referencial tienda
PIL-0075	Alimento de soya	Leche de soya frutilla PIL	946 ml	9.54	8.40	Referencial tienda
PIL-0076	Agua	Agua PIL sin gas	600 ml	3.50	3.08	Referencial tienda
PIL-0077	Agua	Agua PIL sin gas	2 L	7.00	6.16	Referencial tienda
PIL-0078	Agua	Agua PIL con gas	600 ml	3.50	3.08	Referencial tienda
PIL-0079	Agua	Agua PIL bidon	5 L	16.00	14.08	Referencial tienda
PIL-0080	Te helado	Te helado PIL durazno	500 ml	5.50	4.84	Referencial tienda
PIL-0081	Reposteria	Crema de leche repostera PIL bolsa	1 L	30.70	27.02	Pedidos PIL
PIL-0082	Reposteria	Crema de leche PIL	200 ml	8.50	7.48	Referencial tienda
PIL-0083	Reposteria	Leche evaporada PIL cremosa tetra	1.000 g	20.70	18.22	Pedidos PIL
PIL-0084	Reposteria	Leche evaporada Bonle lata	400 g	10.50	9.24	Pedidos PIL
PIL-0085	Reposteria	Leche condensada PIL descremada bolsa	1.000 g	55.00	48.40	Amarket
PIL-0086	Dulce de leche	Dulce de leche PIL	250 g	16.00	14.08	Fidalga
PIL-0087	Dulce de leche	Dulce de leche PIL trilaminado	500 g	25.00	22.00	Fidalga
PIL-0088	Dulce de leche	Dulce de leche PIL sachet trilaminado	1 kg	26.51	23.33	Pedidos PIL
PIL-0089	Mantequillas y margarinas	Mantequilla PIL sin sal	200 g	16.00	14.08	Pedidos PIL
PIL-0090	Mantequillas y margarinas	Mantequilla PIL con sal	200 g	16.00	14.08	Referencial tienda
PIL-0091	Mantequillas y margarinas	Margarina PIL paquete	100 g	3.85	3.39	Pedidos PIL
PIL-0092	Mermeladas	Mermelada PIL frutilla	250 g	13.00	11.44	Referencial tienda
PIL-0093	Mermeladas	Mermelada PIL durazno	250 g	13.00	11.44	Referencial tienda
PIL-0094	Postres	Gelatina Yeli blueberry PIL	110 g	2.30	2.02	Fidalga
PIL-0095	Postres	Gelatina Yeli cereza PIL	120 g	2.40	2.11	Amarket
PIL-0096	Postres	Gelatina Yeli frutilla PIL	120 g	2.40	2.11	Referencial tienda
PIL-0097	Postres	Gelatina Yeli limon PIL	120 g	2.40	2.11	Referencial tienda
PIL-0098	Postres	Flan PIL vainilla	100 g	3.50	3.08	Referencial tienda
PIL-0099	Postres	Flan PIL chocolate	100 g	3.50	3.08	Referencial tienda
PIL-0100	Postres	Postre gelificado PIL frutilla	120 g	2.80	2.46	Referencial tienda
TSV));

        return collect($lines)
            ->filter()
            ->map(function (string $line) {
                [$sku, $category, $name, $presentation, $publicPrice, $institutionalPrice, $source] = array_pad(explode("\t", trim($line)), 7, null);

                return [
                    'sku' => $sku,
                    'category' => $category,
                    'name' => $name,
                    'presentation' => $presentation,
                    'suggested_price_public' => (float) $publicPrice,
                    'price_institutional' => (float) $institutionalPrice,
                    'source' => $source,
                ];
            })
            ->values()
            ->all();
    }
}
