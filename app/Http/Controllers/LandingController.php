<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductLot;
use App\Models\SaleItem;
use Illuminate\Support\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LandingController extends Controller
{
    public function __invoke(): View
    {
        $products = collect();
        $connectionAvailable = true;
        $stockAvailable = true;
        $lotSummary = collect();
        $salesByProduct = collect();

        try {
            $products = Product::with('category')
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        } catch (QueryException $exception) {
            report($exception);
            $connectionAvailable = false;
        }

        if ($connectionAvailable) {
            try {
                $lotSummary = ProductLot::query()
                    ->select(
                        'product_id',
                        DB::raw('SUM(quantity) as stock'),
                        DB::raw('MIN(expires_at) as next_exp')
                    )
                    ->groupBy('product_id')
                    ->get()
                    ->keyBy('product_id');
            } catch (QueryException $exception) {
                report($exception);
                $stockAvailable = false;
            }

            try {
                $salesByProduct = SaleItem::query()
                    ->select('product_id', DB::raw('SUM(quantity) as total_sold'))
                    ->groupBy('product_id')
                    ->pluck('total_sold', 'product_id');
            } catch (QueryException $exception) {
                report($exception);
            }

            $products = $products
                ->map(function (Product $product) use ($lotSummary, $stockAvailable, $salesByProduct) {
                    $lot = $lotSummary->get($product->id);

                    $product->available_qty = $stockAvailable ? max(0, (int) ($lot->stock ?? 0)) : null;
                    $product->nearest_expire = $stockAvailable && $lot?->next_exp
                        ? Carbon::parse($lot->next_exp)->format('d/m/Y')
                        : null;
                    $product->price_for_landing = (float) ($product->suggested_price_public ?? $product->price_institutional ?? 0);
                    $product->category_name = $product->category->name ?? 'Sin categoria';
                    $product->total_sold = (int) ($salesByProduct[$product->id] ?? 0);

                    return $product;
                })
                ->values();
        }

        $featuredProducts = $products
            ->sortByDesc(function (Product $product) {
                return (($product->total_sold ?? 0) * 100000) + (int) ($product->available_qty ?? 0);
            })
            ->take(8)
            ->values();

        $startingPrice = $products->where('price_for_landing', '>', 0)->min('price_for_landing');
        $heroProduct = $featuredProducts->first();

        return view('react-page', [
            'page' => 'landing',
            'title' => 'PIL Andina | Uniendo a las familias bolivianas',
            'description' => 'PIL Andina acompana a las familias bolivianas con productos de calidad, sabor y confianza. Descubre nuestros mas elegidos.',
            'stylesheets' => [asset('landing/landing.css')],
            'props' => [
                'nav' => [
                    ['label' => 'Favoritos', 'href' => '#destacados'],
                    ['label' => 'Nuestra promesa', 'href' => '#promesa'],
                    ['label' => 'Momentos PIL', 'href' => '#momentos'],
                    ['label' => 'Ingresar', 'href' => url('/login'), 'className' => 'nav-cta'],
                ],
                'hero' => [
                    'title' => 'PIL Andina, el sabor que acompana cada mesa boliviana.',
                    'description' => 'Desde el desayuno hasta los momentos que reunen a toda la familia, llevamos calidad, frescura y carino en cada producto. Descubre los preferidos de nuestros clientes y deja que PIL Andina este presente en tu hogar.',
                    'imageUrl' => asset('landing/landing_assets/products.png'),
                    'startingPriceLabel' => $startingPrice ? 'Desde Bs ' . number_format((float) $startingPrice, 2) : 'Calidad PIL',
                    'heroProductName' => $heroProduct?->name,
                    'promises' => [
                        ['icon' => 'ri-cup-line', 'text' => 'Calidad que une generaciones'],
                        ['icon' => 'ri-truck-line', 'text' => 'Presencia que acompana a toda Bolivia'],
                        ['icon' => 'ri-star-smile-line', 'text' => 'Productos elegidos por miles de familias'],
                    ],
                    'insights' => [
                        ['icon' => 'ri-home-heart-line', 'title' => 'Hecho para el hogar', 'text' => 'Sabores que acompanan desayunos, meriendas y momentos especiales en familia.'],
                        ['icon' => 'ri-shield-check-line', 'title' => 'Confianza que permanece', 'text' => 'Una marca que ha crecido junto a Bolivia ofreciendo calidad consistente y cercana.'],
                        ['icon' => 'ri-service-line', 'title' => 'Compra simple y segura', 'text' => 'Descubre tus favoritos y da el siguiente paso con una cuenta para comprar con tranquilidad.'],
                    ],
                    'moments' => [
                        ['number' => '01', 'title' => 'Desayunos con energia', 'text' => 'Empieza cada manana con productos que llenan de sabor y bienestar a toda la familia.'],
                        ['number' => '02', 'title' => 'Meriendas para compartir', 'text' => 'Convierte cada pausa en un momento especial con los sabores mas queridos de PIL Andina.'],
                        ['number' => '03', 'title' => 'Confianza todos los dias', 'text' => 'Cuando eliges PIL, eliges una marca que acompana a Bolivia con calidad y cercania.'],
                    ],
                ],
                'status' => [
                    'connectionAvailable' => $connectionAvailable,
                    'stockAvailable' => $stockAvailable,
                ],
                'featuredProducts' => $featuredProducts->map(function (Product $product) {
                    $stock = $product->available_qty;

                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'description' => $product->description,
                        'imageUrl' => $product->getImageUrl(),
                        'categoryName' => $product->category_name,
                        'totalSoldLabel' => $product->total_sold > 0 ? number_format($product->total_sold) . ' vendidos' : 'Muy pedido',
                        'price' => (float) $product->price_for_landing,
                        'stockLabel' => is_null($stock) ? 'Stock al ingresar' : ($stock > 0 ? $stock . ' disponibles' : 'Alta demanda'),
                        'nearestExpire' => $product->nearest_expire,
                    ];
                })->values(),
                'authModal' => [
                    'defaultCopy' => 'Accede a tu cuenta para comprar tus productos favoritos, revisar disponibilidad y continuar tu pedido con total confianza.',
                    'registerUrl' => url('/register'),
                    'loginUrl' => url('/login'),
                ],
            ],
        ]);
    }
}
