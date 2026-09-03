import React, { useLayoutEffect, useState } from 'react';

function Topbar({ pageTitle, user, csrfToken, logoutAction, onSidebarToggle }) {
    const [showProfileMenu, setShowProfileMenu] = useState(false);
    const [theme, setTheme] = useState(() => localStorage.getItem('fitonist_theme') || 'dark');
    const isDark = theme === 'dark';

    useLayoutEffect(() => {
        document.documentElement.classList.toggle('dark', isDark);
        document.documentElement.classList.toggle('light', !isDark);
        document.documentElement.style.colorScheme = isDark ? 'dark' : 'light';
        localStorage.setItem('fitonist_theme', theme);
    }, [theme, isDark]);

    const initials = user.name
        ?.split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0])
        .join('')
        .toUpperCase() || 'US';

    return (
        <header className="fit-navbar">
            <div className="fit-navbar-left">
                <button className="fit-icon-button fit-mobile-menu" type="button" onClick={onSidebarToggle} aria-label="Abrir menu">
                    <i className="ri-menu-2-line" />
                </button>

                <div className="fit-navbar-title">
                    <i className="ri-dashboard-3-line" />
                    <span>{pageTitle || 'Dashboard Pil La Paz'}</span>
                </div>
            </div>

            <div className="fit-navbar-right">
                <div className="fit-navbar-search" role="search">
                    <i className="ri-search-line" />
                    <input type="search" placeholder="Buscar..." aria-label="Buscar en el sistema" />
                </div>

                <button
                    className="fit-icon-button"
                    type="button"
                    title={isDark ? 'Modo Claro' : 'Modo Oscuro'}
                    onClick={() => setTheme((value) => (value === 'dark' ? 'light' : 'dark'))}
                >
                    <i className={isDark ? 'ri-sun-line' : 'ri-moon-line'} />
                </button>

                <button className="fit-icon-button fit-bell" type="button" title="Notificaciones">
                    <i className="ri-notification-3-line" />
                    <span />
                </button>

                <div className="fit-profile">
                    <button
                        type="button"
                        className="fit-profile-button admin-profile-button"
                        onClick={() => setShowProfileMenu((value) => !value)}
                    >
                        <span className="fit-profile-avatar admin-profile-avatar">{initials}</span>
                        <span className="fit-profile-meta">
                            <span className="fit-profile-name">{user.name}</span>
                            <small>{user.role}</small>
                        </span>
                        <i className="ri-arrow-down-s-line" />
                    </button>

                    {showProfileMenu && (
                        <div className="fit-profile-menu">
                            <div className="fit-profile-menu-head">
                                <strong>{user.name}</strong>
                                <span>{user.role}</span>
                            </div>
                            <form method="POST" action={logoutAction}>
                                <input type="hidden" name="_token" value={csrfToken} />
                                <button type="submit">
                                    <i className="ri-logout-circle-r-line" /> Cerrar sesion
                                </button>
                            </form>
                            <button type="button" onClick={() => setTheme((value) => (value === 'dark' ? 'light' : 'dark'))}>
                                <i className={isDark ? 'ri-sun-line' : 'ri-moon-line'} /> {isDark ? 'Modo Claro' : 'Modo Oscuro'}
                            </button>
                        </div>
                    )}
                </div>
            </div>
        </header>
    );
}

export default React.memo(Topbar);
