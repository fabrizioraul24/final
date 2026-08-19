<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use App\Models\Company;
use App\Models\Product;
use App\Models\Category;
use App\Models\Transfer;
use App\Support\AdminReact;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $actorId = $request->input('actor_id');
        $entityType = $request->input('entity_type');
        $action = $request->input('action');
        $scope = $request->input('scope', 'all'); // all, login, register, users

        $logsQuery = AuditLog::with('user')->latest('created_at');

        if ($scope === 'login') {
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
                    'entity_label' => class_basename($log->entity_type) . ' #' . $log->entity_id,
                    'action' => ucfirst($log->action),
                    'description' => $log->description,
                    'old_values' => $log->old_values,
                    'new_values' => $log->new_values,
                    'pdf_url' => $pdfUrl,
                ];
            });

        return view('react-page', AdminReact::page('logs', 'Logs del sistema | Pil Andina', 'Bitacora de acciones', 'logs', [
            'data' => [
                'logs' => AdminReact::paginator($logs),
                'actors' => User::orderBy('name')->get(),
                'entityTypes' => AuditLog::query()->select('entity_type')->distinct()->pluck('entity_type'),
                'actions' => AuditLog::query()->select('action')->distinct()->pluck('action'),
                'filters' => [
                    'actor_id' => $actorId,
                    'entity_type' => $entityType,
                    'action' => $action,
                    'scope' => $scope,
                ],
                'scopes' => collect([
                    ['key' => 'all', 'label' => 'Todos'],
                    ['key' => 'login', 'label' => 'Login/Logout'],
                    ['key' => 'register', 'label' => 'Registro'],
                    ['key' => 'users', 'label' => 'Usuarios'],
                    ['key' => 'customers', 'label' => 'Clientes'],
                    ['key' => 'products', 'label' => 'Productos'],
                    ['key' => 'categories', 'label' => 'Categorias'],
                    ['key' => 'transfers', 'label' => 'Traspasos'],
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

        $logsQuery = AuditLog::with('user')->latest('created_at');

        if ($scope === 'login') {
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
            'login' => 'Reporte de Sesiones',
            'register' => 'Reporte de Registros',
            'users' => 'Reporte de Auditoria: Usuarios',
            'customers' => 'Reporte de Auditoria: Clientes',
            'products' => 'Reporte de Auditoria: Productos',
            'categories' => 'Reporte de Auditoria: Categorias',
            'transfers' => 'Reporte de Auditoria: Traspasos',
            default => 'Bitacora de Acciones',
        };

        return \App\Services\ReportService::download('reports.logs', [
            'title' => $title,
            'generatedAt' => now(),
            'logs' => $logsQuery->limit(200)->get(), 
            'filters' => [
                'actor' => $actorId ? User::find($actorId)?->name : 'Todos',
                'entity_type' => $entityType ? class_basename($entityType) : 'Todas',
                'action' => $action ?: 'Todas',
                'scope' => $scope,
            ],
        ], 'reporte-logs.pdf');
    }
}
