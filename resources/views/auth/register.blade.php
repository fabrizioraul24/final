<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cuenta | Pil Andina</title>
    <link rel="stylesheet" href="{{ asset('landing/auth.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.1.0/fonts/remixicon.css">
</head>
<body>
    <div class="login-shell">
        <!-- Left Column (Blue Hero) -->
        <section class="login-hero">
            <div class="hero-shapes">
                <div class="hero-circle-orange"></div>
                <div class="hero-outline-circle circle-1"></div>
                <div class="hero-outline-circle circle-2"></div>
                <div class="hero-outline-circle circle-3"></div>
            </div>

            <header class="login-brand">
                <a href="/" class="login-logo" aria-label="PIL Bolivia">
                    <img src="{{ asset('pil.svg') }}" alt="PIL Bolivia">
                    <span>BOLIVIA</span>
                </a>
            </header>

            <div class="login-hero-copy">
                <div class="login-hero-kicker">NUEVO USUARIO</div>
                <h1>
                    Crece con
                    <span>nuestro <em class="highlight">equipo.</em></span>
                </h1>
                <p>
                    Únete al espacio donde conectamos personas, plantas y resultados.
                </p>
            </div>

            <div class="login-hero-footer">
                <span class="plus-icon">+</span>
                <span>Más de 65 años creciendo junto a Bolivia.</span>
            </div>
        </section>

        <!-- Right Column (Form Panel) -->
        <section class="login-panel">
            <a class="login-back" href="{{ route('login') }}">
                <i class="ri-arrow-left-line"></i> Volver al inicio de sesión
            </a>

            <div class="login-card-wrapper">
                <div class="login-card">
                    <span class="login-badge">NUEVA CUENTA</span>
                    <h2>Crea tu perfil</h2>
                    <p class="login-subtitle">
                        Completa tus datos para solicitar acceso administrativo.
                    </p>

                    @if($errors->any())
                        <div class="login-alert error">{{ $errors->first() }}</div>
                    @endif

                    <form method="POST" action="{{ route('register.perform') }}" class="login-form">
                        @csrf
                        
                        <div class="field-row">
                            <div class="field">
                                <label for="name">Nombre completo</label>
                                <div class="field-input">
                                    <span class="field-icon" aria-hidden="true">
                                        <i class="ri-user-line"></i>
                                    </span>
                                    <input
                                        type="text"
                                        id="name"
                                        name="name"
                                        placeholder="Tu nombre"
                                        value="{{ old('name') }}"
                                        required
                                        autofocus
                                    />
                                </div>
                            </div>

                            <div class="field">
                                <label for="cargo">Cargo</label>
                                <div class="field-input">
                                    <span class="field-icon" aria-hidden="true">
                                        <i class="ri-briefcase-line"></i>
                                    </span>
                                    <input
                                        type="text"
                                        id="cargo"
                                        name="cargo"
                                        placeholder="Ej. Supervisor"
                                        value="{{ old('cargo') }}"
                                    />
                                </div>
                            </div>
                        </div>

                        <div class="field">
                            <label for="email">Correo corporativo</label>
                            <div class="field-input">
                                <span class="field-icon" aria-hidden="true">
                                    <i class="ri-mail-line"></i>
                                </span>
                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    placeholder="nombre@pilbolivia.com.bo"
                                    value="{{ old('email') }}"
                                    required
                                />
                            </div>
                        </div>

                        <div class="field">
                            <label for="password">Contraseña</label>
                            <div class="field-input password-field">
                                <span class="field-icon" aria-hidden="true">
                                    <i class="ri-lock-line"></i>
                                </span>
                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    placeholder="Mínimo 6 caracteres"
                                    required
                                />
                                <button
                                    type="button"
                                    id="togglePassword"
                                    class="password-toggle"
                                    aria-label="Mostrar contraseña"
                                >
                                    Ver
                                </button>
                            </div>
                        </div>

                        <div class="login-helpers">
                            <label class="remember-row">
                                <input
                                    type="checkbox"
                                    name="terms"
                                    id="terms"
                                    required
                                />
                                <span>Acepto los términos de uso y la política de privacidad.</span>
                            </label>
                        </div>

                        <button type="submit" class="login-submit">
                            Crear cuenta <i class="ri-arrow-right-line"></i>
                        </button>
                    </form>

                    <p class="register-line">
                        ¿Ya tienes una cuenta? <a href="{{ route('login') }}">Inicia sesión</a>
                    </p>
                </div>
            </div>
        </section>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toggle = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');

            if (toggle && passwordInput) {
                toggle.addEventListener('click', () => {
                    const isVisible = passwordInput.type === 'text';
                    passwordInput.type = isVisible ? 'password' : 'text';
                    toggle.textContent = isVisible ? 'Ver' : 'Ocultar';
                    toggle.setAttribute('aria-label', isVisible ? 'Mostrar contraseña' : 'Ocultar contraseña');
                });
            }
        });
    </script>
</body>
</html>
