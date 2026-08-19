<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cuenta | Pil Andina</title>
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
                        <i class="ri-information-line"></i>
                        Lineamientos de rol
                    </span>
                    <h1 class="text-4xl font-black mt-5 mb-4 leading-tight">
                        Registro exclusivo para compradores
                    </h1>
                    <p class="text-white/80 leading-relaxed">
                        Los roles de Administrador, Vendedor y Almacen son asignados internamente por TI.
                        Si necesitas acceso corporativo, comunicate con soporte.
                    </p>
                    <div class="roles-grid">
                        <div class="role-card">
                            <h4>Administrador</h4>
                            <span>Asignado por TI</span>
                            <p>Rol estrategico con permisos completos. Solicitalo a traves de la mesa de ayuda.</p>
                        </div>
                        <div class="role-card">
                            <h4>Vendedor</h4>
                            <span>Asignado por RRHH</span>
                            <p>Se habilita tras validacion comercial. Contacta a tu lider de canal.</p>
                        </div>
                        <div class="role-card">
                            <h4>Almacen</h4>
                            <span>Asignado por Operaciones</span>
                            <p>Solo personal certificado puede operar inventarios y despachos.</p>
                        </div>
                    </div>
                </section>
                <section class="form-card">
                    <h2>Crear cuenta</h2>
                    <p>Disfruta de beneficios exclusivos registrandote como comprador.</p>
                    @if($errors->any())
                        <div class="bg-red-500/30 border border-red-300/40 rounded-xl px-4 py-3 text-sm text-white mb-4">
                            {{ $errors->first() }}
                        </div>
                    @endif
                    <form method="POST" action="{{ route('register.perform') }}">
                        @csrf
                        <div class="form-group">
                            <label for="name">Nombre completo</label>
                            <input type="text" id="name" name="name" class="form-control" placeholder="Maria Fernandez" value="{{ old('name') }}" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Correo electronico</label>
                            <input type="email" id="email" name="email" class="form-control" placeholder="tuemail@dominio.com" value="{{ old('email') }}" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Contrasena</label>
                            <input type="password" id="password" name="password" class="form-control" placeholder="********" required>
                        </div>
                        <div class="form-group">
                            <label for="password_confirmation">Confirmar contrasena</label>
                            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" placeholder="Repite tu contrasena" required>
                        </div>
                        <button type="submit" class="pill-button">
                            Finalizar registro
                        </button>
                    </form>
                    <div class="mt-6 flex items-center justify-between text-sm">
                        <span class="text-white/70">Ya cuentas con una cuenta?</span>
                        <a href="{{ route('login') }}" class="link-muted">Inicia sesion aqui</a>
                    </div>
                </section>
            </div>
        </div>
    </div>
</body>
</html>
