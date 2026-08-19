<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceder | Pil Andina</title>
    <link rel="stylesheet" href="{{ asset('landing/auth.css') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.1.0/fonts/remixicon.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#4e6baf',
                        'primary-dark': '#3a5186',
                        'primary-light': '#86acd4',
                        accent: '#42568b',
                    }
                }
            }
        }
    </script>
</head>
<body>
    <div class="auth-container">
        <div class="glass-panel">
            <div class="auth-grid">
                <section class="panel-info">
                    <span class="badge">
                        <i class="ri-shield-check-line"></i>
                        Acceso seguro
                    </span>
                    <h1 class="text-4xl font-black mt-5 mb-4 leading-tight">
                        Bienvenido de nuevo
                    </h1>
                    <p class="text-white/80 leading-relaxed mb-6">
                        Este panel concentra a los 4 roles estrategicos del ecosistema Pil Andina.
                        Usa tus credenciales corporativas para continuar.
                    </p>
                    <div class="roles-grid">
                        <div class="role-card">
                            <h4>Administrador</h4>
                            <span>Supervision total</span>
                            <p>Gestiona usuarios, reportes generales y configuraciones del sistema.</p>
                        </div>
                        <div class="role-card">
                            <h4>Vendedor</h4>
                            <span>Front comercial</span>
                            <p>Atiende pedidos, arma promociones y monitorea metas por campana.</p>
                        </div>
                        <div class="role-card">
                            <h4>Comprador</h4>
                            <span>Clientes B2C</span>
                            <p>Puede registrarse por cuenta propia para comprar directo.</p>
                        </div>
                        <div class="role-card">
                            <h4>Almacen</h4>
                            <span>Operacion</span>
                            <p>Controla lotes, inventario y confirma entregas desde las plantas.</p>
                        </div>
                    </div>
                </section>
                <section class="form-card">
                    <h2>Iniciar sesion</h2>
                    <p>Ingresa tu correo y contrasena. Te enviaremos automaticamente al panel de tu rol.</p>
                    @if(session('status'))
                        <div class="bg-primary/40 border border-white/20 rounded-xl px-4 py-3 text-sm text-white/90 mb-4">
                            {{ session('status') }}
                        </div>
                    @endif
                    @if($errors->any())
                        <div class="bg-red-500/30 border border-red-300/40 rounded-xl px-4 py-3 text-sm text-white mb-4">
                            {{ $errors->first() }}
                        </div>
                    @endif
                    <form method="POST" action="{{ route('login.perform') }}">
                        @csrf
                        <div class="form-group">
                            <label for="email">Correo electronico</label>
                            <input type="email" id="email" name="email" class="form-control" placeholder="tucorreo@pil.bo" value="{{ old('email') }}" required autofocus>
                        </div>
                        <div class="form-group">
                            <label for="password">Contrasena</label>
                            <div class="password-field">
                                <input type="password" id="password" name="password" class="form-control password-input" placeholder="********" required>
                                <button
                                    type="button"
                                    class="password-toggle"
                                    data-password-toggle
                                    aria-label="Mostrar contrasena"
                                    aria-pressed="false"
                                    aria-controls="password"
                                >
                                    <i class="ri-eye-line" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>
                        <button type="submit" class="pill-button">
                            Acceder al panel
                        </button>
                    </form>
                    <div class="mt-6 flex items-center justify-between text-sm">
                        <a href="#" class="link-muted">Olvidaste tu contrasena?</a>
                        <a href="{{ route('register') }}" class="link-muted">Comprador nuevo? Registrate</a>
                    </div>
                </section>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toggle = document.querySelector('[data-password-toggle]');
            const passwordInput = document.getElementById('password');

            if (!toggle || !passwordInput) {
                return;
            }

            const icon = toggle.querySelector('i');

            toggle.addEventListener('click', () => {
                const isVisible = passwordInput.type === 'text';

                passwordInput.type = isVisible ? 'password' : 'text';
                toggle.setAttribute('aria-pressed', String(!isVisible));
                toggle.setAttribute('aria-label', isVisible ? 'Mostrar contrasena' : 'Ocultar contrasena');

                if (icon) {
                    icon.className = isVisible ? 'ri-eye-line' : 'ri-eye-off-line';
                }
            });
        });
    </script>
</body>
</html>
