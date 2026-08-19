import React from 'react';

export function FlashMessages({ flash }) {
    if (!flash?.status && !flash?.error) {
        return null;
    }

    return (
        <>
            {flash.status && (
                <div className="card">
                    <span className="chip chip-muted">{flash.status}</span>
                </div>
            )}
            {flash.error && (
                <div className="card" style={{ border: '1px solid rgba(248,113,113,0.4)' }}>
                    <span className="chip" style={{ background: 'rgba(248,113,113,0.2)', color: '#fee2e2' }}>{flash.error}</span>
                </div>
            )}
        </>
    );
}

export function FieldError({ errors, name }) {
    const message = errors?.[name]?.[0];

    if (!message) {
        return null;
    }

    return <small style={{ color: '#f87171' }}>{message}</small>;
}

export function Pagination({ pagination }) {
    if (!pagination || pagination.last_page <= 1) {
        return null;
    }

    return (
        <div className="pagination">
            <div className="pagination-meta">
                <span className="pagination-summary">{pagination.total} registros</span>
                <span className="pagination-summary pagination-summary--page">
                    {pagination.from ?? 0}-{pagination.to ?? 0}
                </span>
            </div>
            <ul className="pagination-list">
                {pagination.links.map((link, index) => (
                    <li key={`${link.label}-${index}`} className={`page-item${link.active ? ' active' : ''}${link.url ? '' : ' disabled'}`}>
                        {link.url ? (
                            <a
                                href={link.url}
                                className={`page-link${index === 0 || index === pagination.links.length - 1 ? ' page-link-nav' : ''}`}
                                dangerouslySetInnerHTML={{ __html: link.label }}
                            />
                        ) : (
                            <span
                                className={`page-link${link.label.includes('...') ? ' page-link-dots' : ''}`}
                                dangerouslySetInnerHTML={{ __html: link.label }}
                            />
                        )}
                    </li>
                ))}
            </ul>
        </div>
    );
}

export function Modal({ open, title, children, onClose, wide = false, contentClassName = '' }) {
    if (!open) {
        return null;
    }

    return (
        <div className="modal active" onClick={(event) => event.target === event.currentTarget && onClose()}>
            <div className={`modal-content${wide ? ' modal-content--wide' : ''}${contentClassName ? ` ${contentClassName}` : ''}`}>
                <div className="modal-header">
                    <h3>{title}</h3>
                    <button className="close-button" type="button" onClick={onClose}>&times;</button>
                </div>
                {children}
            </div>
        </div>
    );
}

export function StatsGrid({ items }) {
    return (
        <div className="stats-grid">
            {items.map((item) => (
                <div className="card" key={item.label}>
                    <h3>{item.label}</h3>
                    <div className="value">{item.value}</div>
                    <span className={`chip ${item.chipClass || ''}`}>{item.chip}</span>
                </div>
            ))}
        </div>
    );
}

export function TableEmpty({ colSpan, text }) {
    return (
        <tr>
            <td colSpan={colSpan} style={{ textAlign: 'center', padding: '1.5rem' }}>{text}</td>
        </tr>
    );
}
