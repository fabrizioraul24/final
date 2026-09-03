<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductLot;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\BuyerOrder;

class BuyerController extends Controller
{
    public function index(): View
    {
        $lotSummary = ProductLot::select(
                'product_id',
                DB::raw('SUM(quantity) as stock'),
                DB::raw('MIN(expires_at) as next_exp')
            )
            ->groupBy('product_id')
            ->get()
            ->keyBy('product_id');

        $products = Product::with('category')
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function ($product) use ($lotSummary) {
                $lot = $lotSummary->get($product->id);
                $product->available_qty = $lot->stock ?? 0;
                $product->nearest_expire = $lot && $lot->next_exp ? optional($lot->next_exp)->format('d/m/Y') : null;
                $product->price_for_buyer = $product->suggested_price_public;
                return $product;
            });

        $categories = $products
            ->groupBy(fn($p) => $p->category->id ?? 0)
            ->map(function ($group) {
                return [
                    'id' => $group->first()->category->id ?? 0,
                    'name' => $group->first()->category->name ?? 'Sin categoria',
                    'count' => $group->count(),
                ];
            })
            ->values();

        $history = collect();
        if (Auth::check()) {
            $history = BuyerOrder::with(['items.product'])
                ->where('user_id', Auth::id())
                ->latest()
                ->get();
        }

        return view('dashboard.comprador', [
            'products' => $products,
            'categories' => $categories,
            'history' => $history,
        ]);
    }
}
