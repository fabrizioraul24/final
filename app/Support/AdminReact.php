<?php

namespace App\Support;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class AdminReact
{
    public static function page(string $resource, string $title, string $pageTitle, string $activeKey, array $props = [], string $page = 'adminResource'): array
    {
        return [
            'page' => $page,
            'title' => $title,
            'stylesheets' => [asset('landing/dashboard.css')],
            'props' => array_merge([
                'resource' => $resource,
                'layout' => self::layout($pageTitle, $activeKey),
                'csrfToken' => csrf_token(),
                'logoutAction' => route('logout'),
                'flash' => [
                    'status' => session('status'),
                    'error' => session('error'),
                ],
                'errors' => session('errors')?->getBag('default')->toArray() ?? [],
                'old' => session()->getOldInput(),
            ], $props),
        ];
    }

    public static function layout(string $pageTitle, string $activeKey): array
    {
        $items = [
            'dashboard' => ['label' => 'Panel de control', 'href' => route('dashboard.admin'), 'icon' => 'ri-dashboard-line', 'page' => 'adminDashboard'],
            'users' => ['label' => 'Usuarios', 'href' => route('dashboard.users'), 'icon' => 'ri-group-line', 'page' => 'adminUsers'],
            'companies' => ['label' => 'Clientes', 'href' => route('dashboard.companies'), 'icon' => 'ri-user-smile-line', 'page' => 'adminCompanies'],
            'products' => ['label' => 'Productos', 'href' => route('dashboard.products'), 'icon' => 'ri-shopping-bag-line', 'page' => 'adminProducts'],
            'lots' => ['label' => 'Lotes', 'href' => route('dashboard.lots'), 'icon' => 'ri-archive-2-line', 'page' => 'adminLots'],
            'categories' => ['label' => 'Categorias', 'href' => route('dashboard.categories'), 'icon' => 'ri-price-tag-3-line', 'page' => 'adminCategories'],
            'transfers' => ['label' => 'Traspasos', 'href' => route('dashboard.transfers'), 'icon' => 'ri-shuffle-line', 'page' => 'adminTransfers'],
            'sales' => ['label' => 'Ventas', 'href' => route('dashboard.sales'), 'icon' => 'ri-currency-line', 'page' => 'adminSales'],
            'quotations' => ['label' => 'Cotizaciones', 'href' => route('dashboard.quotations'), 'icon' => 'ri-file-list-3-line', 'page' => 'adminQuotations'],
            'logs' => ['label' => 'Logs', 'href' => route('dashboard.logs'), 'icon' => 'ri-history-line', 'page' => 'adminLogs'],
            'backups' => ['label' => 'Backups', 'href' => route('dashboard.backups'), 'icon' => 'ri-shield-keyhole-line', 'page' => 'adminBackups'],
            'agent' => ['label' => 'Agente Inteligente', 'href' => route('admin.agent.replenishment'), 'icon' => 'ri-robot-2-line', 'page' => 'adminAgentReplenishment'],
        ];

        return [
            'sidebar' => [
                'logoUrl' => asset('storage/images/logo.png'),
                'items' => collect($items)->map(function (array $item, string $key) use ($activeKey) {
                    $item['active'] = $key === $activeKey;

                    return $item;
                })->values()->all(),
            ],
            'topbar' => [
                'pageTitle' => $pageTitle,
                'user' => [
                    'name' => Auth::user()->name ?? 'Usuario Pil',
                    'role' => optional(Auth::user()?->role)->name ?? 'Rol no asignado',
                ],
            ],
        ];
    }

    public static function paginator(LengthAwarePaginator $paginator): array
    {
        $data = $paginator->toArray();

        return [
            'data' => $data['data'],
            'current_page' => $data['current_page'],
            'last_page' => $data['last_page'],
            'per_page' => $data['per_page'],
            'total' => $data['total'],
            'from' => $data['from'],
            'to' => $data['to'],
            'links' => $data['links'],
            'path' => $data['path'],
        ];
    }
}
