<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use App\Models\Company;
use App\Models\Product;
use App\Models\Category;
use App\Models\Transfer;
use App\Models\Sale;
use App\Models\Quotation;
use App\Models\ProductLot;
use App\Models\DamageReport;
use App\Models\BuyerOrder;
use App\Models\Backup;
use App\Support\AdminReact;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    private const ENTITY_LABELS = [
        'User' => 'Usuarios', 'Company' => 'Clientes', 'Product' => 'Productos',
        'Category' => 'Categorias', 'Transfer' => 'Traspasos', 'Sale' => 'Ventas',
        'TransferItem' => 'Items de traspaso', 'Quotation' => 'Cotizaciones',
        'ProductLot' => 'Lotes', 'ProductLotMovement' => 'Movimientos de lote',
        'DamageReport' => 'Danos', 'BuyerOrder' => 'Pedidos comprador',
        'VendorVisit' => 'Visitas vendedor', 'Backup' => 'Backups',
        'BackupSchedule' => 'Programacion de backups', 'TransferRequest' => 'Solicitudes IA',
        'auth' => 'Autenticacion',
    ];

    private const ACTION_LABELS = [
        'create' => 'Creacion', 'update' => 'Edicion', 'deactivate' => 'Desactivacion',
        'activate' => 'Activacion', 'restore' => 'Reactivacion', 'toggle' => 'Cambio de estado',
        'login' => 'Inicio de sesion', 'login_failed' => 'Inicio de sesion fallido',
        'logout' => 'Cierre de sesion', 'register' => 'Registro', 'register_failed' => 'Registro fallido',
        'delete' => 'Eliminacion', 'status_update' => 'Cambio de estado',
        'payment' => 'Pago', 'stock_in' => 'Ingreso de stock',
        'stock_adjustment' => 'Ajuste de stock', 'receive_item' => 'Recepcion de item',
        'damage' => 'Registro de dano', 'backup_create' => 'Backup generado',
        'schedule_update' => 'Programacion actualizada', 'approve' => 'Aprobacion',
        'reject' => 'Rechazo',
    ];

    public function index(Request $request): View
    {
        $actorId = $request->input('actor_id');
        $entityType = $request->input('entity_type');
        $action = $request->input('action');
        $scope = $request->input('scope', 'all');

        $logsQuery = AuditLog::with('user')->orderByDesc('created_at')->orderByDesc('id');

        if ($scope === 'today') {
            $logsQuery->whereDate('created_at', now()->toDateString());
        } elseif ($scope === 'login') {
            $logsQuery->where('entity_type', 'auth')
                ->whereIn('action', ['login', 'login_failed', 'logout']);
        } elseif ($scope === 'register') {
            $logsQuery->where('entity_type', 'auth')
                ->whereIn('action', ['register', 'register_failed']);
        } elseif ($scope === 'users') {
            $logsQuery->where('entity_type', User::class);
        } elseif ($scope === 'customers') {
            $logsQuery->where('entity_type', Company::class);
        } elseif ($scope === 'products') {
            $logsQuery->where('entity_type', Product::class);
        } elseif ($scope === 'categories') {
            $logsQuery->where('entity_type', Category::class);
        } elseif ($scope === 'transfers') {
            $logsQuery->where('entity_type', Transfer::class);
        } elseif ($scope === 'sales') {
            $logsQuery->where('entity_type', Sale::class);
        } elseif ($scope === 'quotations') {
            $logsQuery->where('entity_type', Quotation::class);
        } elseif ($scope === 'lots') {
            $logsQuery->whereIn('entity_type', [ProductLot::class, DamageReport::class]);
        } elseif ($scope === 'payments') {
            $logsQuery->where('entity_type', BuyerOrder::class);
        } elseif ($scope === 'backups') {
            $logsQuery->where('entity_type', Backup::class);
        } elseif ($scope === 'created') {
            $logsQuery->whereIn('action', ['create', 'register', 'stock_in', 'backup_create']);
        } elseif ($scope === 'updated') {
            $logsQuery->whereIn('action', ['update', 'status_update', 'toggle', 'stock_adjustment', 'receive_item', 'schedule_update', 'approve', 'reject']);
        } elseif ($scope === 'deleted') {
            $logsQuery->whereIn('action', ['delete', 'deactivate', 'damage']);
        }

        if ($actorId) {
            $logsQuery->where('user_id', $actorId);
        }

        if ($entityType) {
            $logsQuery->where('entity_type', $entityType);
        }

        if ($action) {
            $logsQuery->where('action', $action);
        }

        $logs = $logsQuery->paginate(15)->withQueryString()
            ->through(function (AuditLog $log) {
                $pdfUrl = class_basename($log->entity_type) === 'Transfer'
                    ? route('dashboard.transfers.report.single', $log->entity_id)
                    : null;

                return [
                    'id' => $log->id,
                    'created_at_formatted' => optional($log->created_at)->format('d/m/Y H:i'),
                    'user' => $log->user ? ['name' => $log->user->name] : null,
                    'entity_label' => $this->entityLabel($log),
                    'action' => $this->actionLabel($log->action),
                    'description' => $log->description,
                    'old_values' => $log->old_values,
                    'new_values' => $log->new_values,
                    'pdf_url' => $pdfUrl,
                ];
            });

        $stats = [
            'total' => AuditLog::count(),
            'today' => AuditLog::whereDate('created_at', now()->toDateString())->count(),
            'created' => AuditLog::whereIn('action', ['create', 'register', 'stock_in', 'backup_create'])->count(),
            'updated' => AuditLog::whereIn('action', ['update', 'status_update', 'toggle', 'stock_adjustment', 'receive_item', 'schedule_update', 'approve', 'reject'])->count(),
            'deleted' => AuditLog::whereIn('action', ['delete', 'deactivate', 'damage'])->count(),
        ];

        return view('react-page', AdminReact::page('logs', 'Logs del sistema | Pil Andina', 'Bitacora de acciones', 'logs', [
            'data' => [
                'logs' => AdminReact::paginator($logs),
                'stats' => $stats,
                'actors' => User::orderBy('name')->get(),
                'entityTypes' => AuditLog::query()->select('entity_type')->distinct()->orderBy('entity_type')->pluck('entity_type'),
                'actions' => AuditLog::query()->select('action')->distinct()->orderBy('action')->pluck('action'),
                'filters' => [
                    'actor_id' => $actorId,
                    'entity_type' => $entityType,
                    'action' => $action,
                    'scope' => $scope,
                ],
                'scopes' => collect([
                    ['key' => 'all', 'label' => 'Todos'],
                    ['key' => 'today', 'label' => 'Hoy'],
                    ['key' => 'login', 'label' => 'Login/Logout'],
                    ['key' => 'register', 'label' => 'Registro'],
                    ['key' => 'users', 'label' => 'Usuarios'],
                    ['key' => 'customers', 'label' => 'Clientes'],
                    ['key' => 'products', 'label' => 'Productos'],
                    ['key' => 'categories', 'label' => 'Categorias'],
                    ['key' => 'transfers', 'label' => 'Traspasos'],
                    ['key' => 'sales', 'label' => 'Ventas'],
                    ['key' => 'quotations', 'label' => 'Cotizaciones'],
                    ['key' => 'lots', 'label' => 'Lotes/Stock'],
                    ['key' => 'payments', 'label' => 'Pagos'],
                    ['key' => 'backups', 'label' => 'Backups'],
                ])->map(fn (array $item) => $item + [
                    'url' => route('dashboard.logs', array_merge(request()->except('page'), ['scope' => $item['key']])),
                    'active' => $scope === $item['key'],
                ])->values(),
                'routes' => [
                    'index' => route('dashboard.logs'),
                    'report' => route('dashboard.logs.report', request()->all()),
                ],
            ],
        ], 'adminLogs'));
    }

    public function report(Request $request)
    {
        $actorId = $request->input('actor_id');
        $entityType = $request->input('entity_type');
        $action = $request->input('action');
        $scope = $request->input('scope', 'all');

        $logsQuery = AuditLog::with('user')->orderByDesc('created_at')->orderByDesc('id');

        if ($scope === 'today') {
            $logsQuery->whereDate('created_at', now()->toDateString());
        } elseif ($scope === 'login') {
            $logsQuery->where('entity_type', 'auth')
                ->whereIn('action', ['login', 'login_failed', 'logout']);
        } elseif ($scope === 'register') {
            $logsQuery->where('entity_type', 'auth')
                ->whereIn('action', ['register', 'register_failed']);
        } elseif ($scope === 'users') {
            $logsQuery->where('entity_type', User::class);
        } elseif ($scope === 'customers') {
            $logsQuery->where('entity_type', Company::class);
        } elseif ($scope === 'products') {
            $logsQuery->where('entity_type', Product::class);
        } elseif ($scope === 'categories') {
            $logsQuery->where('entity_type', Category::class);
        } elseif ($scope === 'transfers') {
            $logsQuery->where('entity_type', Transfer::class);
        } elseif ($scope === 'sales') {
            $logsQuery->where('entity_type', Sale::class);
        } elseif ($scope === 'quotations') {
            $logsQuery->where('entity_type', Quotation::class);
        } elseif ($scope === 'lots') {
            $logsQuery->whereIn('entity_type', [ProductLot::class, DamageReport::class]);
        } elseif ($scope === 'payments') {
            $logsQuery->where('entity_type', BuyerOrder::class);
        } elseif ($scope === 'backups') {
            $logsQuery->where('entity_type', Backup::class);
        } elseif ($scope === 'created') {
            $logsQuery->whereIn('action', ['create', 'register', 'stock_in', 'backup_create']);
        } elseif ($scope === 'updated') {
            $logsQuery->whereIn('action', ['update', 'status_update', 'toggle', 'stock_adjustment', 'receive_item', 'schedule_update', 'approve', 'reject']);
        } elseif ($scope === 'deleted') {
            $logsQuery->whereIn('action', ['delete', 'deactivate', 'damage']);
        }

        if ($actorId) {
            $logsQuery->where('user_id', $actorId);
        }

        if ($entityType) {
            $logsQuery->where('entity_type', $entityType);
        }

        if ($action) {
            $logsQuery->where('action', $action);
        }

        $title = match ($scope) {
            'today' => 'Reporte de Actividad de Hoy',
            'login' => 'Reporte de Sesiones',
            'register' => 'Reporte de Registros',
            'users' => 'Reporte de Auditoria: Usuarios',
            'customers' => 'Reporte de Auditoria: Clientes',
            'products' => 'Reporte de Auditoria: Productos',
            'categories' => 'Reporte de Auditoria: Categorias',
            'transfers' => 'Reporte de Auditoria: Traspasos',
            'sales' => 'Reporte de Auditoria: Ventas',
            'quotations' => 'Reporte de Auditoria: Cotizaciones',
            'lots' => 'Reporte de Auditoria: Lotes y stock',
            'payments' => 'Reporte de Auditoria: Pagos de comprador',
            'backups' => 'Reporte de Auditoria: Backups',
            'created' => 'Reporte de Auditoria: Creados',
            'updated' => 'Reporte de Auditoria: Editados',
            'deleted' => 'Reporte de Auditoria: Borrados y desactivados',
            default => 'Bitacora de Acciones',
        };

        $logs = $logsQuery->limit(200)->get()->each(function (AuditLog $log) {
            $log->entity_label = $this->entityLabel($log);
            $log->action_label = $this->actionLabel($log->action);
        });

        return \App\Services\ReportService::download('reports.logs', [
            'title' => $title,
            'generatedAt' => now(),
            'logs' => $logs, 
            'filters' => [
                'actor' => $actorId ? User::find($actorId)?->name : 'Todos',
                'entity_type' => $entityType ? class_basename($entityType) : 'Todas',
                'action' => $action ?: 'Todas',
                'scope' => $scope,
            ],
        ], 'reporte-logs.pdf');
    }

    private function entityLabel(AuditLog $log): string
    {
        return (self::ENTITY_LABELS[class_basename($log->entity_type)] ?? class_basename($log->entity_type)) . ' #' . $log->entity_id;
    }

    private function actionLabel(string $action): string
    {
        return self::ACTION_LABELS[strtolower($action)] ?? ucfirst(str_replace('_', ' ', $action));
    }
}
