import React from 'react';
import { preloadPage } from '../../pageRegistry';

function Sidebar({ logoUrl, items, csrfToken, logoutAction, isOpen, onClose }) {
    const activeItem = items.find((item) => item.active) || items[0] || null;

    return (
        <aside className={`sidebar neo-sidebar${isOpen ? ' open' : ''}`} id="sidebar">
            <div className="neo-sidebar-brand">
                <div className="neo-sidebar-logo">
                    {logoUrl ? <img src={logoUrl} alt="PIL Bolivia" /> : <span>P</span>}
                </div>
                <div>
                    <strong>PIL Bolivia</strong>
                    <p>Dashboard corporativo</p>
                </div>
            </div>

            <div className="neo-sidebar-chip">
                <span />
                <div>
                    <strong>{activeItem?.label || 'Dashboard'}</strong>
                    <p>{items.length} accesos activos</p>
                </div>
            </div>

            <nav className="neo-sidebar-nav" id="sidebarNav">
                {items.map((item) => (
                    <a
                        key={item.label}
                        href={item.href}
                        className={`neo-sidebar-item${item.active ? ' active' : ''}`}
                        onMouseEnter={() => preloadPage(item.page)}
                        onFocus={() => preloadPage(item.page)}
                        onTouchStart={() => preloadPage(item.page)}
                        onClick={onClose}
                    >
                        <span className="neo-sidebar-icon"><i className={item.icon} /></span>
                        <span className="neo-sidebar-label">{item.label}</span>
                    </a>
                ))}
            </nav>

            <form method="POST" action={logoutAction} className="neo-sidebar-logout-form">
                <input type="hidden" name="_token" value={csrfToken} />
                <button type="submit" className="neo-sidebar-logout" title="Cerrar sesion">
                    <span className="neo-sidebar-icon"><i className="ri-logout-box-r-line" /></span>
                    <span className="neo-sidebar-label">Cerrar sesion</span>
                    <i className="ri-arrow-right-line neo-sidebar-logout-arrow" />
                </button>
            </form>

        </aside>
    );
}

export default React.memo(Sidebar);
