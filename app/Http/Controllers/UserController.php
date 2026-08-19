<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Models\AuditLog;
use App\Http\Controllers\Concerns\LogsAudit;
use App\Services\ReportService;
use App\Support\AdminReact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    use LogsAudit;
    /**
     * Lista de usuarios con filtros.
     */
    public function index(Request $request): View
    {
        $roles = Role::orderBy('name')->get();
        $search = $request->input('search');
        $roleFilter = $request->input('role_id');

        $activeUsersQuery = User::with('role')->latest();
        $inactiveUsersQuery = User::onlyTrashed()->with('role')->latest();

        if ($search) {
            $activeUsersQuery->whereAnyLikeInsensitive(['name', 'email', 'username'], $search);
            $inactiveUsersQuery->whereAnyLikeInsensitive(['name', 'email', 'username'], $search);
        }

        if ($roleFilter) {
            $activeUsersQuery->where('role_id', $roleFilter);
            $inactiveUsersQuery->where('role_id', $roleFilter);
        }

        $activeUsers = $activeUsersQuery->paginate(10, ['*'], 'actives')->withQueryString()
            ->through(function (User $user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'username' => $user->username,
                    'role_id' => $user->role_id,
                    'role' => $user->role ? ['name' => $user->role->name] : null,
                    'created_at_formatted' => $user->created_at?->format('d/m/Y'),
                    'updated_at_formatted' => $user->updated_at?->format('d/m/Y'),
                    'update_url' => route('dashboard.users.update', $user),
                    'toggle_url' => route('dashboard.users.toggle', $user),
                    'destroy_url' => route('dashboard.users.destroy', $user),
                ];
            });
        $inactiveUsers = $inactiveUsersQuery->paginate(10, ['*'], 'inactives')->withQueryString()
            ->through(function (User $user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'username' => $user->username,
                    'role_id' => $user->role_id,
                    'role' => $user->role ? ['name' => $user->role->name] : null,
                    'created_at_formatted' => $user->created_at?->format('d/m/Y'),
                    'updated_at_formatted' => $user->updated_at?->format('d/m/Y'),
                    'update_url' => route('dashboard.users.update', $user),
                    'toggle_url' => route('dashboard.users.toggle', $user),
                    'destroy_url' => route('dashboard.users.destroy', $user),
                ];
            });

        return view('react-page', AdminReact::page('users', 'Gestion de Usuarios | Pil Andina', 'Gestion de Usuarios', 'users', [
            'data' => [
                'roles' => $roles,
                'activeUsers' => AdminReact::paginator($activeUsers),
                'inactiveUsers' => AdminReact::paginator($inactiveUsers),
                'filters' => [
                    'search' => $search,
                    'role_id' => $roleFilter,
                ],
                'routes' => [
                    'index' => route('dashboard.users'),
                    'store' => route('dashboard.users.store'),
                    'report' => route('dashboard.users.report', ['search' => $search, 'role_id' => $roleFilter]),
                ],
            ],
        ], 'adminUsers'));
    }

    /**
     * Crear nuevo usuario.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'username' => ['required', 'regex:/^[A-Za-z0-9_.-]+$/', 'max:255', 'unique:users,username'],
            'password' => ['required', 'string', 'min:8'],
            'role_id' => ['required', 'exists:roles,id'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'username' => $data['username'],
            'password' => Hash::make($data['password']),
            'role_id' => $data['role_id'],
        ]);

        $this->logAudit($user, 'create', [], $user->only(['name','email','username','role_id']), 'Creación de usuario');

        return redirect()->route('dashboard.users')
            ->with('status', 'Usuario creado correctamente.');
    }

    /**
     * Actualizar usuario desde modal.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'username' => ['required', 'regex:/^[A-Za-z0-9_.-]+$/', 'max:255', Rule::unique('users', 'username')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'role_id' => ['required', 'exists:roles,id'],
        ]);

        $old = $user->only(['name','email','username','role_id']);
        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'username' => $data['username'],
            'role_id' => $data['role_id'],
            'password' => $data['password']
                ? Hash::make($data['password'])
                : $user->password,
        ]);

        $this->logAudit($user, 'update', $old, $user->only(['name','email','username','role_id']), 'Actualización de usuario');

        return redirect()->route('dashboard.users')
            ->with('status', 'Usuario actualizado.');
    }

    /**
     * Desactivar o reactivar usuarios (borrado lógico).
     */
    public function toggle(int $userId): RedirectResponse
    {
        $user = User::withTrashed()->findOrFail($userId);

        if ($user->trashed()) {
            $user->restore();
            $message = 'Usuario reactivado.';
            $this->logAudit($user, 'restore', [], $user->only(['name','email','username','role_id']), 'Reactivación de usuario');
        } else {
            $user->delete();
            $message = 'Usuario desactivado.';
            $this->logAudit($user, 'deactivate', $user->only(['name','email','username','role_id']), [], 'Desactivación de usuario');
        }

        return redirect()->route('dashboard.users')->with('status', $message);
    }

    /**
     * Eliminación permanente en caso necesario.
     */
    public function destroy(int $userId): RedirectResponse
    {
        $user = User::withTrashed()->findOrFail($userId);
        $old = $user->only(['name','email','username','role_id']);
        $user->forceDelete();

        $this->logAudit(User::class, 'delete', $old, [], 'Eliminación definitiva');

        return redirect()->route('dashboard.users')->with('status', 'Usuario eliminado definitivamente.');
    }

    /**
     * Generar reporte PDF.
     */
    public function report(Request $request)
    {
        $roles = Role::orderBy('name')->get();
        $search = $request->input('search');
        $roleFilter = $request->input('role_id');

        $usersQuery = User::with('role')->latest();

        if ($search) {
            $usersQuery->whereAnyLikeInsensitive(['name', 'email', 'username'], $search);
        }

        if ($roleFilter) {
            $usersQuery->where('role_id', $roleFilter);
        }

        return ReportService::download('reports.users', [
            'title' => 'Reporte de usuarios',
            'generatedAt' => now(),
            'users' => $usersQuery->get(),
            'filters' => [
                'search' => $search,
                'role' => optional($roles->firstWhere('id', $roleFilter))->name,
            ],
        ], 'reporte-usuarios.pdf');
    }
}
