import React, { useEffect, useState } from 'react';

export function FlashMessages({ flash }) {
    const message = flash?.error || flash?.status;
    const isError = Boolean(flash?.error);
    const [visible, setVisible] = useState(Boolean(message));
    const [leaving, setLeaving] = useState(false);

    useEffect(() => {
        if (!message) {
            setVisible(false);
            return undefined;
        }

        setVisible(true);
        setLeaving(false);

        const fadeTimer = window.setTimeout(() => setLeaving(true), 3600);
        const dismissTimer = window.setTimeout(() => setVisible(false), 4000);

        return () => {
            window.clearTimeout(fadeTimer);
            window.clearTimeout(dismissTimer);
        };
    }, [message]);

    if (!message || !visible) {
        return null;
    }

    return (
        <div className={`flash-toast${isError ? ' flash-toast--error' : ''}${leaving ? ' is-leaving' : ''}`} role={isError ? 'alert' : 'status'}>
            <span className="flash-toast__icon"><i className={isError ? 'ri-error-warning-line' : 'ri-checkbox-circle-line'} /></span>
            <p>{message}</p>
            <button className="flash-toast__close" type="button" title="Cerrar notificacion" onClick={() => setVisible(false)}>
                <i className="ri-close-line" />
            </button>
        </div>
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
        <div className="fit-metric-grid admin-fit-stats-grid">
            {items.map((item, index) => {
                const tones = ['indigo', 'green', 'rose', 'amber'];
                const tone = item.tone || tones[index % tones.length];

                return (
                    <div className={`fit-metric-card ${tone} ${item.cardClass || ''}`} key={item.label}>
                        <span>
                            <small>{item.label}</small>
                            <strong>{item.value}</strong>
                            <em className={item.chipClass || ''}>{item.chip}</em>
                        </span>
                        {item.icon && <span className="fit-metric-icon"><i className={item.icon} /></span>}
                    </div>
                );
            })}
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
