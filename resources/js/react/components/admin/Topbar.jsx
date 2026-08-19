import React from 'react';

function Topbar({ pageTitle, user, csrfToken, logoutAction, onSidebarToggle }) {
    return (
        <header className="topbar">
            <div className="topbar-left">
                <button className="icon-button" id="sidebarToggle" type="button" onClick={onSidebarToggle}>
                    <i className="ri-menu-line" />
                </button>
                <div>
                    <p className="topbar-meta">Pil Andina HQ</p>
                    <h1>{pageTitle}</h1>
                </div>
            </div>
            <div className="topbar-right">
                <button className="icon-button" title="Notificaciones" type="button">
                    <i className="ri-notification-3-line" />
                </button>
                <div className="user-chip">
                    <i className="ri-user-3-line" />
                    <div>
                        <strong>{user.name}</strong>
                        <p className="user-role">{user.role}</p>
                    </div>
                </div>
                <form method="POST" action={logoutAction}>
                    <input type="hidden" name="_token" value={csrfToken} />
                    <button className="pill-button" type="submit">Cerrar sesion</button>
                </form>
            </div>
        </header>
    );
}

export default React.memo(Topbar);
