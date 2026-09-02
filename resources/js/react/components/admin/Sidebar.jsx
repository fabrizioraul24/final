import React from 'react';
import { preloadPage } from '../../pageRegistry';

function Sidebar({ logoUrl, items, isOpen, onClose }) {
    const visibleItems = items || [];

    return (
        <>
            {isOpen && (
                <button type="button" className="fit-sidebar-scrim" onClick={onClose} aria-label="Cerrar menu movil" />
            )}

            <aside className={`fit-sidebar${isOpen ? ' open' : ''}`} id="sidebar">
                <div className="fit-sidebar-main">
                    <div className="fit-sidebar-brand">
                        <div className="fit-sidebar-logo">
                            {logoUrl ? <img src={logoUrl} alt="PIL Bolivia" /> : <span>P</span>}
                        </div>
                        <div className="fit-sidebar-brand-copy">
                            <span className="fit-sidebar-title">PIL Bolivia</span>
                            <span className="fit-sidebar-subtitle">Indigo Console</span>
                        </div>
                        <button type="button" className="fit-sidebar-close" onClick={onClose} aria-label="Cerrar menu">
                            <i className="ri-close-line" />
                        </button>
                    </div>

                    <div className="fit-sidebar-nav-block">
                        <span className="fit-sidebar-section">Menu de Navegacion</span>
                        <nav className="fit-sidebar-nav">
                            {visibleItems.map((item) => (
                                <a
                                    key={item.label}
                                    href={item.href}
                                    className={`fit-sidebar-item${item.active ? ' active' : ''}`}
                                    onMouseEnter={() => preloadPage(item.page)}
                                    onFocus={() => preloadPage(item.page)}
                                    onTouchStart={() => preloadPage(item.page)}
                                    onClick={onClose}
                                >
                                    <span className="fit-sidebar-item-main">
                                        <span className="fit-sidebar-icon"><i className={item.icon} /></span>
                                        <span className="fit-sidebar-copy">
                                            <span>{item.label}</span>
                                            <small>{item.active ? 'Vista actual' : 'Modulo del sistema'}</small>
                                        </span>
                                    </span>
                                    {item.active && <span className="fit-sidebar-badge">ON</span>}
                                </a>
                            ))}
                        </nav>
                    </div>
                </div>

            </aside>
        </>
    );
}

export default React.memo(Sidebar);
