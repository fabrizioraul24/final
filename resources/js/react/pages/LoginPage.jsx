import React, { useState } from 'react';

const bannerStyles = {
    status: {
        background: 'rgba(78, 107, 175, 0.4)',
        border: '1px solid rgba(255, 255, 255, 0.2)',
        borderRadius: '1rem',
        padding: '0.75rem 1rem',
        fontSize: '0.875rem',
        color: 'rgba(255, 255, 255, 0.9)',
        marginBottom: '1rem',
    },
    error: {
        background: 'rgba(239, 68, 68, 0.3)',
        border: '1px solid rgba(252, 165, 165, 0.4)',
        borderRadius: '1rem',
        padding: '0.75rem 1rem',
        fontSize: '0.875rem',
        color: '#fff',
        marginBottom: '1rem',
    },
    title: {
        marginTop: '1.25rem',
        marginBottom: '1rem',
        fontSize: '2.25rem',
        fontWeight: 900,
        lineHeight: 1.1,
    },
    helperRow: {
        marginTop: '1.5rem',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'space-between',
        gap: '1rem',
        flexWrap: 'wrap',
        fontSize: '0.875rem',
    },
};

export default function LoginPage({
    hero,
    form,
    flash,
    routes,
    csrfToken,
}) {
    const [passwordVisible, setPasswordVisible] = useState(false);

    return (
        <div className="auth-container">
            <div className="glass-panel">
                <div className="auth-grid">
                    <section className="panel-info">
                        <span className="badge">
                            <i className="ri-shield-check-line" />
                            Acceso seguro
                        </span>
                        <h1 style={bannerStyles.title}>Bienvenido de nuevo</h1>
                        <p className="panel-copy">
                            Este panel concentra a los 4 roles estrategicos del ecosistema Pil Andina.
                            Usa tus credenciales corporativas para continuar.
                        </p>
                        <div className="roles-grid">
                            {hero.roles.map((role) => (
                                <div key={role.name} className="role-card">
                                    <h4>{role.name}</h4>
                                    <span>{role.tagline}</span>
                                    <p>{role.description}</p>
                                </div>
                            ))}
                        </div>
                    </section>
                    <section className="form-card">
                        <h2>Iniciar sesion</h2>
                        <p>Ingresa tu correo y contrasena. Te enviaremos automaticamente al panel de tu rol.</p>

                        {flash.status && <div style={bannerStyles.status}>{flash.status}</div>}
                        {flash.error && <div style={bannerStyles.error}>{flash.error}</div>}

                        <form method="POST" action={form.action}>
                            <input type="hidden" name="_token" value={csrfToken} />
                            <div className="form-group">
                                <label htmlFor="email">Correo electronico</label>
                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    className="form-control"
                                    placeholder="tucorreo@pil.bo"
                                    defaultValue={form.oldEmail}
                                    required
                                    autoFocus
                                />
                            </div>
                            <div className="form-group">
                                <label htmlFor="password">Contrasena</label>
                                <div className="password-field">
                                    <input
                                        type={passwordVisible ? 'text' : 'password'}
                                        id="password"
                                        name="password"
                                        className="form-control password-input"
                                        placeholder="********"
                                        required
                                    />
                                    <button
                                        type="button"
                                        className="password-toggle"
                                        aria-label={passwordVisible ? 'Ocultar contrasena' : 'Mostrar contrasena'}
                                        aria-pressed={passwordVisible}
                                        aria-controls="password"
                                        onClick={() => setPasswordVisible((value) => !value)}
                                    >
                                        <i className={passwordVisible ? 'ri-eye-off-line' : 'ri-eye-line'} aria-hidden="true" />
                                    </button>
                                </div>
                            </div>
                            <button type="submit" className="pill-button">
                                Acceder al panel
                            </button>
                        </form>
                        <div style={bannerStyles.helperRow}>
                            <a href="#" className="link-muted">Olvidaste tu contrasena?</a>
                            <a href={routes.register} className="link-muted">Comprador nuevo? Registrate</a>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    );
}
