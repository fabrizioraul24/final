import React, { useState } from 'react';

export default function LoginPage({
    hero,
    form,
    flash,
    routes,
    csrfToken,
}) {
    const [passwordVisible, setPasswordVisible] = useState(false);
    const [rememberMe, setRememberMe] = useState(false);

    return (
        <div className="login-shell">
            {/* Left Column (Blue Hero) */}
            <section className="login-hero">
                <div className="hero-shapes">
                    <div className="hero-circle-orange" />
                    <div className="hero-outline-circle circle-1" />
                    <div className="hero-outline-circle circle-2" />
                    <div className="hero-outline-circle circle-3" />
                </div>

                <header className="login-brand">
                    <a href="/" className="login-logo" aria-label="PIL Bolivia">
                        <span className="login-logo-mark">PIL</span>
                        <span className="login-logo-country">BOLIVIA</span>
                    </a>
                </header>

                <div className="login-hero-copy">
                    <div className="login-hero-kicker">PORTAL CORPORATIVO</div>
                    <h1>
                        Gestionamos
                        <span>lo que <em className="highlight">nutre.</em></span>
                    </h1>
                    <p>
                        Información clara para decisiones que alimentan el futuro de Bolivia.
                    </p>
                </div>

                <div className="login-hero-footer">
                    <span className="plus-icon">+</span>
                    <span>Calidad, confianza y compromiso en cada proceso.</span>
                </div>
            </section>

            {/* Right Column (Form Panel) */}
            <section className="login-panel">
                <a className="login-back" href="/">
                    <i className="ri-arrow-left-line" /> Volver al sitio
                </a>

                <div className="login-card-wrapper">
                    <div className="login-card">
                        <span className="login-badge">ACCESO SEGURO</span>
                        <h2>Bienvenido de nuevo</h2>
                        <p className="login-subtitle">
                            Ingresa tus datos para acceder al panel administrativo.
                        </p>

                        {flash.status && <div className="login-alert status">{flash.status}</div>}
                        {flash.error && <div className="login-alert error">{flash.error}</div>}

                        <form method="POST" action={form.action} className="login-form">
                            <input type="hidden" name="_token" value={csrfToken} />
                            
                            <div className="field">
                                <label htmlFor="email">Correo corporativo</label>
                                <div className="field-input">
                                    <span className="field-icon" aria-hidden="true">
                                        <i className="ri-mail-line" />
                                    </span>
                                    <input
                                        type="email"
                                        id="email"
                                        name="email"
                                        placeholder="nombre@pilbolivia.com.bo"
                                        defaultValue={form.oldEmail}
                                        required
                                        autoFocus
                                    />
                                </div>
                            </div>

                            <div className="field">
                                <label htmlFor="password">Contraseña</label>
                                <div className="field-input password-field">
                                    <span className="field-icon" aria-hidden="true">
                                        <i className="ri-lock-line" />
                                    </span>
                                    <input
                                        type={passwordVisible ? 'text' : 'password'}
                                        id="password"
                                        name="password"
                                        placeholder="••••••••"
                                        required
                                    />
                                    <button
                                        type="button"
                                        className="password-toggle"
                                        aria-label={passwordVisible ? 'Ocultar contraseña' : 'Mostrar contraseña'}
                                        aria-pressed={passwordVisible}
                                        aria-controls="password"
                                        onClick={() => setPasswordVisible((value) => !value)}
                                    >
                                        {passwordVisible ? 'Ocultar' : 'Ver'}
                                    </button>
                                </div>
                            </div>

                            <div className="login-helpers">
                                <label className="remember-row">
                                    <input
                                        type="checkbox"
                                        checked={rememberMe}
                                        onChange={(event) => setRememberMe(event.target.checked)}
                                        name="remember"
                                    />
                                    <span>Recordarme</span>
                                </label>

                                <a href="#" className="forgot-link">
                                    ¿Olvidaste tu contraseña?
                                </a>
                            </div>

                            <button type="submit" className="login-submit">
                                Ingresar al dashboard <i className="ri-arrow-right-line" />
                            </button>
                        </form>

                        <p className="register-line">
                            ¿Aún no tienes una cuenta? <a href={routes.register}>Crear cuenta</a>
                        </p>

                        <div className="demo-box">
                            <div className="demo-icon">
                                <i className="ri-information-line" />
                            </div>
                            <div>
                                <strong>Vista demostrativa</strong>
                                <p>Puedes usar cualquier correo y contraseña de 6 caracteres.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    );
}
