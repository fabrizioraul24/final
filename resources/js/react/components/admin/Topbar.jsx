import React, { useEffect, useMemo, useState } from 'react';

function Topbar({ pageTitle, user, csrfToken, logoutAction, onSidebarToggle }) {
    const [now, setNow] = useState(() => new Date());

    useEffect(() => {
        const timer = window.setInterval(() => setNow(new Date()), 60_000);
        return () => window.clearInterval(timer);
    }, []);

    const timeLabel = useMemo(() => now.toLocaleTimeString('es-BO', {
        hour: '2-digit',
        minute: '2-digit',
    }), [now]);

    return (
        <header className="topbar neo-topbar">
            <div className="neo-topbar-left">
                <button className="icon-button neo-sidebar-toggle" id="sidebarToggle" type="button" onClick={onSidebarToggle}>
                    <i className="ri-menu-line" />
                </button>
                <h1>{pageTitle}</h1>
            </div>

            <div className="neo-topbar-search" role="search">
                <i className="ri-search-line" />
                <input type="search" placeholder="Buscar aqui..." aria-label="Buscar en el sistema" />
            </div>

            <div className="neo-topbar-right">
                <button className="icon-button neo-bell-button" title="Notificaciones" type="button">
                    <i className="ri-notification-3-line" />
                </button>
                <div className="neo-user-chip">
                    <div className="neo-user-avatar">{user.name?.slice(0, 2).toUpperCase() || 'US'}</div>
                    <div className="neo-user-copy">
                        <strong>{user.name}</strong>
                        <span>{user.role}</span>
                    </div>
                    <span className="neo-user-time">{timeLabel}</span>
                </div>
                <form method="POST" action={logoutAction}>
                    <input type="hidden" name="_token" value={csrfToken} />
                    <button className="neo-signout" type="submit" title="Cerrar sesion">
                        <i className="ri-logout-box-r-line" />
                    </button>
                </form>
            </div>
        </header>
    );
}

export default React.memo(Topbar);
