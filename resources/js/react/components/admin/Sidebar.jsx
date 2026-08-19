import React from 'react';
import { preloadPage } from '../../pageRegistry';

function Sidebar({ logoUrl, items, isOpen, onClose }) {
    return (
        <aside className={`sidebar${isOpen ? ' open' : ''}`} id="sidebar">
            <div className="sidebar-logo">
                <div style={{ width: '48px', height: '48px', borderRadius: '1.2rem', overflow: 'hidden', display: 'flex', alignItems: 'center', justifyContent: 'center', background: 'rgba(255,255,255,0.08)' }}>
                    <img src={logoUrl} alt="Pil Andina" style={{ maxWidth: '100%', maxHeight: '100%', objectFit: 'contain' }} />
                </div>
                <span>Pil Andina</span>
            </div>
            <nav className="nav-section" id="sidebarNav">
                {items.map((item) => (
                    <a
                        key={item.label}
                        href={item.href}
                        className={`nav-item${item.active ? ' active' : ''}`}
                        onMouseEnter={() => preloadPage(item.page)}
                        onFocus={() => preloadPage(item.page)}
                        onTouchStart={() => preloadPage(item.page)}
                        onClick={onClose}
                    >
                        <i className={item.icon} />
                        {item.label}
                    </a>
                ))}
            </nav>
        </aside>
    );
}

export default React.memo(Sidebar);
