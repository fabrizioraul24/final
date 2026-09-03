<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceder | Pil Andina</title>
    <link rel="stylesheet" href="{{ asset('landing/auth.css') }}">
</head>
<body>
    <div class="login-shell">
        <section class="login-hero">
            <header class="login-brand">
                <a href="/" class="login-logo" aria-label="PIL Bolivia">
                    <span class="login-logo-mark">PIL</span>
                    <span class="login-logo-country">BOLIVIA</span>
                </a>
            </header>

            <div class="login-hero-copy">
                <div class="login-hero-kicker">PORTAL CORPORATIVO</div>
                <h1>Gestionamos <span>lo que <em>nutre.</em></span></h1>
                <p>Informacion clara para decisiones que alimentan el futuro de Bolivia.</p>
            </div>

            <div class="login-hero-footer">
                <span class="login-hero-dot"></span>
                <span>Calidad, confianza y compromiso en cada proceso.</span>
            </div>
        </section>

        <section class="login-panel">
            <a class="login-back" href="/">← Volver al sitio</a>

            <div class="login-card">
                <span class="login-badge">ACCESO SEGURO</span>
                <h2>Bienvenido de nuevo</h2>
                <p class="login-subtitle">Ingresa tus datos para acceder al panel administrativo.</p>

                @if(session('status'))
                    <div class="login-alert status">{{ session('status') }}</div>
                @endif
                @if($errors->any())
                    <div class="login-alert error">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ route('login.perform') }}" class="login-form">
                    @csrf
                    <div class="field">
                        <label for="email">Correo corporativo</label>
                        <div class="field-input">
                            <span class="field-icon" aria-hidden="true">✉</span>
                            <input type="email" id="email" name="email" placeholder="nombre@pilbolivia.com.bo" value="{{ old('email') }}" required autofocus>
                        </div>
                    </div>

                    <div class="field">
                        <label for="password">Contraseña</label>
                        <div class="field-input password-field">
                            <span class="field-icon" aria-hidden="true">•</span>
                            <input type="password" id="password" name="password" placeholder="••••••••" required>
                            <button type="button" class="password-toggle" data-password-toggle aria-label="Mostrar contraseña" aria-pressed="false" aria-controls="password">Ver</button>
                        </div>
                    </div>

                    <div class="login-helpers">
                        <label class="remember-row">
                            <input type="checkbox" name="remember">
                            <span>Recordarme</span>
                        </label>
                        <a href="#" class="forgot-link">¿Olvidaste tu contraseña?</a>
                    </div>

                    <button type="submit" class="login-submit">
                        Ingresar al dashboard
                        <span aria-hidden="true">→</span>
                    </button>
                </form>

                <div class="login-divider">
                    <span>o</span>
                </div>

                <p class="register-line">
                    ¿Aún no tienes una cuenta? <a href="{{ route('register') }}">Crear cuenta</a>
                </p>

                <div class="demo-box">
                    <div class="demo-icon">i</div>
                    <div>
                        <strong>Vista demostrativa</strong>
                        <p>Puedes usar cualquier correo y contraseña de 6 caracteres.</p>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toggle = document.querySelector('[data-password-toggle]');
            const passwordInput = document.getElementById('password');

            if (!toggle || !passwordInput) {
                return;
            }

            toggle.addEventListener('click', () => {
                const isVisible = passwordInput.type === 'text';
                passwordInput.type = isVisible ? 'password' : 'text';
                toggle.setAttribute('aria-pressed', String(!isVisible));
                toggle.setAttribute('aria-label', isVisible ? 'Mostrar contraseña' : 'Ocultar contraseña');
                toggle.textContent = isVisible ? 'Ver' : 'Ocultar';
            });
        });
    </script>
</body>
</html>
