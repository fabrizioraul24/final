<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Mostrar formulario de login.
     */
    public function showLogin(Request $request): View
    {
        $errors = $request->session()->get('errors');

        return view('react-page', [
            'page' => 'login',
            'title' => 'Acceder | Pil Andina',
            'stylesheets' => [asset('landing/auth.css')],
            'props' => [
                'hero' => [
                    'roles' => [
                        [
                            'name' => 'Administrador',
                            'tagline' => 'Supervision total',
                            'description' => 'Gestiona usuarios, reportes generales y configuraciones del sistema.',
                        ],
                        [
                            'name' => 'Vendedor',
                            'tagline' => 'Front comercial',
                            'description' => 'Atiende pedidos, arma promociones y monitorea metas por campana.',
                        ],
                        [
                            'name' => 'Comprador',
                            'tagline' => 'Clientes B2C',
                            'description' => 'Puede registrarse por cuenta propia para comprar directo.',
                        ],
                        [
                            'name' => 'Almacen',
                            'tagline' => 'Operacion',
                            'description' => 'Controla lotes, inventario y confirma entregas desde las plantas.',
                        ],
                    ],
                ],
                'form' => [
                    'action' => route('login.perform'),
                    'oldEmail' => $request->session()->getOldInput('email', ''),
                ],
                'flash' => [
                    'status' => session('status'),
                    'error' => $errors?->first(),
                ],
                'routes' => [
                    'register' => route('register'),
                ],
                'csrfToken' => csrf_token(),
            ],
        ]);
    }

    /**
     * Procesar login y redirigir por rol.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            AuditLog::create([
                'user_id' => null,
                'entity_type' => 'auth',
                'entity_id' => 0,
                'action' => 'login_failed',
                'description' => 'Login fallido para ' . $credentials['email'],
                'created_at' => now(),
            ]);

            return back()
                ->withErrors([
                    'email' => 'Las credenciales no coinciden con nuestros registros.',
                ])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        AuditLog::create([
            'user_id' => Auth::id(),
            'entity_type' => 'auth',
            'entity_id' => Auth::id() ?? 0,
            'action' => 'login',
            'description' => 'Login exitoso',
            'created_at' => now(),
        ]);

        return redirect()->route($this->routeForRole(Auth::user()));
    }

    /**
     * Cerrar sesión.
     */
    public function logout(Request $request): RedirectResponse
    {
        $userId = Auth::id();

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        AuditLog::create([
            'user_id' => $userId,
            'entity_type' => 'auth',
            'entity_id' => $userId ?? 0,
            'action' => 'logout',
            'description' => 'Logout',
            'created_at' => now(),
        ]);

        return redirect()->route('login')->with('status', 'Sesión finalizada correctamente.');
    }

    /**
     * Mostrar formulario de registro.
     */
    public function showRegister(): View
    {
        return view('auth.register');
    }

    /**
     * Registrar compradores y redirigir a su panel.
     */
    public function register(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        if ($validator->fails()) {
            AuditLog::create([
                'user_id' => null,
                'entity_type' => 'auth',
                'entity_id' => 0,
                'action' => 'register_failed',
                'description' => 'Fallo registro: ' . implode(' | ', $validator->errors()->all()),
                'created_at' => now(),
            ]);

            return back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();

        $role = Role::whereRaw('LOWER(name) = ?', ['comprador'])->first()
            ?? Role::create(['name' => 'Comprador', 'description' => 'Clientes finales con acceso al catalogo y compras']);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'username' => Str::slug($data['name']) . Str::random(3),
            'password' => Hash::make($data['password']),
            'role_id' => $role->id,
        ]);

        AuditLog::create([
            'user_id' => $user->id,
            'entity_type' => 'auth',
            'entity_id' => $user->id,
            'action' => 'register',
            'description' => 'Registro exitoso',
            'created_at' => now(),
        ]);

        Auth::login($user);

        return redirect()->route($this->routeForRole($user));
    }

    /**
     * Determina la ruta del dashboard segun el rol del usuario.
     */
    private function routeForRole(User $user): string
    {
        $roleSlug = Str::slug(optional($user->role)->name ?? '');

        return match ($roleSlug) {
            'administrador' => 'dashboard.admin',
            'vendedor' => 'dashboard.vendedor.home',
            'almacen' => 'dashboard.almacen',
            default => 'dashboard.comprador',
        };
    }
}
