<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\LogsAudit;
use App\Models\Category;
use App\Services\ReportService;
use App\Support\AdminReact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    use LogsAudit;

    public function index(Request $request): View
    {
        $search = $request->input('search');
        $scope = $request->input('scope');

        $baseQuery = Category::withTrashed()->withCount('products')->orderByDesc('updated_at')->orderByDesc('created_at')->orderByDesc('id');

        $applyFilters = function ($query) use ($search, $scope) {
            if ($search) {
                $query->whereAnyLikeInsensitive(['name'], $search);
            }

            if ($scope === 'with_products') {
                $query->has('products');
            }
        };

        $activeCategories = (clone $baseQuery)
                ->whereNull('deleted_at')
                ->tap($applyFilters)
                ->paginate(10, ['*'], 'activos_page')
                ->withQueryString()
                ->through(fn (Category $category) => $this->categoryPayload($category));
        $inactiveCategories = (clone $baseQuery)
                ->onlyTrashed()
                ->tap($applyFilters)
                ->paginate(10, ['*'], 'inactivos_page')
                ->withQueryString()
                ->through(fn (Category $category) => $this->categoryPayload($category));

        return view('react-page', AdminReact::page('categories', 'Categorias de Productos | Pil Andina', 'Gestion de Categorias', 'categories', [
            'data' => [
                'activeCategories' => AdminReact::paginator($activeCategories),
                'inactiveCategories' => AdminReact::paginator($inactiveCategories),
                'filters' => [
                    'search' => $search,
                    'scope' => $scope,
                ],
                'summary' => [
                    'total' => Category::withTrashed()->count(),
                    'active' => Category::count(),
                    'with_products' => Category::has('products')->count(),
                    'inactive' => Category::onlyTrashed()->count(),
                ],
                'routes' => [
                    'index' => route('dashboard.categories'),
                    'store' => route('dashboard.categories.store'),
                    'report' => route('dashboard.categories.report', ['search' => $search, 'scope' => $scope]),
                ],
            ],
        ], 'adminCategories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name'],
            'description' => ['nullable', 'string'],
        ]);

        $category = Category::create($data);

        $this->logAudit($category, 'create', [], $category->only(['name','description']), 'Creacion de categoria');

        return redirect()->route('dashboard.categories')->with('status', 'Categoria creada correctamente.');
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('categories', 'name')->ignore($category->id)],
            'description' => ['nullable', 'string'],
        ]);

        $old = $category->only(['name','description']);
        $category->update($data);

        $this->logAudit($category, 'update', $old, $category->only(['name','description']), 'Actualizacion de categoria');

        return redirect()->route('dashboard.categories')->with('status', 'Categoria actualizada.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $old = $category->only(['name','description']);
        $category->delete();

        $this->logAudit($category, 'deactivate', $old, [], 'Categoria desactivada');

        return redirect()->route('dashboard.categories')->with('status', 'Categoria desactivada.');
    }

    public function restore(int $categoryId): RedirectResponse
    {
        $category = Category::withTrashed()->findOrFail($categoryId);
        $category->restore();

        $this->logAudit($category, 'restore', [], $category->only(['name','description']), 'Categoria reactivada');

        return redirect()->route('dashboard.categories')->with('status', 'Categoria reactivada.');
    }

    public function report(Request $request)
    {
        $search = $request->input('search');
        $categoriesQuery = Category::withCount('products')->orderBy('name');

        if ($search) {
            $categoriesQuery->whereAnyLikeInsensitive(['name'], $search);
        }

        return ReportService::download('reports.categories', [
            'title' => 'Reporte de categorias',
            'generatedAt' => now(),
            'categories' => $categoriesQuery->get(),
        ], 'reporte-categorias.pdf');
    }

    private function categoryPayload(Category $category): array
    {
        return [
            'id' => $category->id,
            'name' => $category->name,
            'description' => $category->description,
            'description_excerpt' => \Illuminate\Support\Str::limit($category->description ?: 'Sin descripcion registrada.', 100),
            'products_count' => $category->products_count,
            'created_at_formatted' => optional($category->created_at)->format('d/m/Y'),
            'deleted_at_formatted' => optional($category->deleted_at)->format('d/m/Y') ?? 'Sin fecha',
            'update_url' => route('dashboard.categories.update', $category),
            'destroy_url' => route('dashboard.categories.destroy', $category),
            'restore_url' => route('dashboard.categories.restore', $category->id),
        ];
    }
}
