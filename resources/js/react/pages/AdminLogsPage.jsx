import React, { useState } from 'react';
import DashboardShell from '../components/admin/DashboardShell';
import { FlashMessages, Modal, Pagination, TableEmpty } from '../components/admin/common';

const entityLabels = {
    User: 'Usuarios',
    Company: 'Clientes',
    Product: 'Productos',
    Category: 'Categorias',
    Transfer: 'Traspasos',
    Sale: 'Ventas',
    Quotation: 'Cotizaciones',
    auth: 'Autenticacion',
};

const actionLabels = {
    create: 'Creacion',
    update: 'Edicion',
    deactivate: 'Desactivacion',
    activate: 'Activacion',
    restore: 'Reactivacion',
    toggle: 'Cambio de estado',
    login: 'Inicio de sesion',
    login_failed: 'Inicio de sesion fallido',
    logout: 'Cierre de sesion',
    register: 'Registro',
    register_failed: 'Registro fallido',
};

const entityName = (value = '') => entityLabels[value.split('\\').pop()] || value.split('\\').pop();
const actionName = (value = '') => actionLabels[value.toLowerCase()] || value;
const formatValue = (value) => {
    if (value === null || value === undefined || value === '') return '-';
    if (typeof value === 'object') return JSON.stringify(value);
    return String(value);
};

function ActionBadge({ action }) {
    const normalized = String(action || '').toLowerCase();
    const tone = normalized.includes('fallido') || normalized.includes('desactivacion')
        ? 'danger'
        : normalized.includes('creacion') || normalized.includes('registro') || normalized.includes('activacion')
            ? 'success'
            : normalized.includes('sesion')
                ? 'warning'
                : 'default';

    return <span className={`fit-log-action ${tone}`}>{action}</span>;
}

function LogDiffColumn({ title, values, emptyText }) {
    const entries = Object.entries(values || {});

    return (
        <div className="fit-log-diff-panel">
            <h4>{title}</h4>
            <div className="fit-log-change-list">
                {entries.length ? entries.map(([key, value]) => (
                    <div className="fit-log-change-item" key={key}>
                        <span>{key}</span>
                        <strong>{formatValue(value)}</strong>
                    </div>
                )) : <p>{emptyText}</p>}
            </div>
        </div>
    );
}

export default function AdminLogsPage({ layout, data, flash, csrfToken, logoutAction }) {
    const [detailLog, setDetailLog] = useState(null);
    const initialMetric = data.filters?.scope || 'all';
    const [activeMetric, setActiveMetric] = useState(initialMetric);
    const hasFilters = Boolean(
        (data.filters?.scope && data.filters.scope !== 'all') ||
        data.filters?.actor_id ||
        data.filters?.entity_type ||
        data.filters?.action
    );

    const buildIndexUrl = (params = {}) => {
        const url = new URL(data.routes.index, window.location.origin);
        const scope = params.scope ?? data.filters.scope;
        const actorId = params.actor_id ?? data.filters.actor_id;
        const entityType = params.entity_type ?? data.filters.entity_type;
        const action = params.action ?? data.filters.action;

        if (scope && scope !== 'all') url.searchParams.set('scope', scope);
        if (actorId) url.searchParams.set('actor_id', actorId);
        if (entityType) url.searchParams.set('entity_type', entityType);
        if (action) url.searchParams.set('action', action);

        return `${url.pathname}${url.search}`;
    };

    const handleMetricClick = (key) => {
        if (key === 'all') {
            if (data.filters?.scope && data.filters.scope !== 'all') {
                window.location.href = buildIndexUrl({ scope: 'all' });
                return;
            }

            setActiveMetric('all');
            return;
        }

        window.location.href = buildIndexUrl({ scope: key });
    };

    const metricCards = [
        { key: 'all', label: 'Bitacora total', value: data.stats.total, hint: 'Bitacora completa', icon: 'ri-file-list-3-line', tone: 'indigo' },
        { key: 'today', label: 'Actividad Hoy', value: data.stats.today, hint: 'Ultimas acciones', icon: 'ri-pulse-line', tone: 'amber' },
        { key: 'users', label: 'Usuarios', value: data.stats.users, hint: 'Auditoria', icon: 'ri-user-settings-line', tone: 'rose' },
        { key: 'transfers', label: 'Traspasos', value: data.stats.transfers, hint: 'Movimientos', icon: 'ri-arrow-left-right-line', tone: 'green' },
    ];

    return (
        <DashboardShell sidebar={layout.sidebar} topbar={layout.topbar} csrfToken={csrfToken} logoutAction={logoutAction}>
            <div className="fit-users-page fit-logs-page">
                <FlashMessages flash={flash} />

                <section className="fit-users-header">
                    <div className="fit-users-header-left">
                        <div className="fit-header-icon"><i className="ri-history-line" /></div>
                        <div>
                            <h1>Bitacora del sistema</h1>
                            <p>Audita sesiones, cambios administrativos, movimientos y acciones registradas.</p>
                        </div>
                    </div>

                    <div className="fit-users-header-actions">
                        <a className="fit-outline-button" target="_blank" rel="noopener noreferrer" href={data.routes.report}>
                            <i className="ri-file-chart-line" />
                            <span>Descargar Reporte PDF</span>
                        </a>
                    </div>
                </section>

                <section className="fit-metric-grid">
                    {metricCards.map((card) => (
                        <button
                            type="button"
                            key={card.key}
                            className={`fit-metric-card ${card.tone}${activeMetric === card.key ? ' active' : ''}`}
                            onClick={() => handleMetricClick(card.key)}
                        >
                            <span>
                                <small>{card.label}</small>
                                <strong>{card.value}</strong>
                                <em>{card.hint}</em>
                            </span>
                            <span className="fit-metric-icon"><i className={card.icon} /></span>
                        </button>
                    ))}
                </section>

                <section className="fit-filter-card fit-log-filter-card">
                    <div className="fit-log-scope-list">
                        {data.scopes.map((scope) => (
                            <a key={scope.key} href={scope.url} className={`fit-log-scope-link${scope.active ? ' is-active' : ''}`}>
                                {scope.label}
                            </a>
                        ))}
                    </div>

                    <form method="GET" action={data.routes.index} className="fit-filter-form fit-log-filter-form">
                        <input type="hidden" name="scope" value={data.filters.scope || 'all'} />

                        <label className="fit-select-control" htmlFor="actor_id">
                            <i className="ri-user-line" />
                            <select id="actor_id" name="actor_id" defaultValue={data.filters.actor_id || ''}>
                                <option value="">Todos los actores</option>
                                {data.actors.map((actor) => <option key={actor.id} value={actor.id}>{actor.name}</option>)}
                            </select>
                        </label>

                        <label className="fit-select-control" htmlFor="entity_type">
                            <i className="ri-database-2-line" />
                            <select id="entity_type" name="entity_type" defaultValue={data.filters.entity_type || ''}>
                                <option value="">Todas las entidades</option>
                                {data.entityTypes.map((type) => <option key={type} value={type}>{entityName(type)}</option>)}
                            </select>
                        </label>

                        <label className="fit-select-control" htmlFor="action">
                            <i className="ri-flashlight-line" />
                            <select id="action" name="action" defaultValue={data.filters.action || ''}>
                                <option value="">Todas las acciones</option>
                                {data.actions.map((action) => <option key={action} value={action}>{actionName(action)}</option>)}
                            </select>
                        </label>

                        <button type="submit" className="fit-primary-button compact">
                            <i className="ri-search-line" /> Buscar
                        </button>

                        {hasFilters && <a href={data.routes.index} className="fit-clear-button">Limpiar Filtros</a>}
                    </form>
                </section>

                <section className="fit-section">
                    <div className="fit-section-head">
                        <div>
                            <h2>Actividad Reciente</h2>
                            <p>Eventos ordenados del mas reciente al mas antiguo.</p>
                        </div>
                        <span className="fit-section-badge green">{data.logs.total} registros</span>
                    </div>

                    <div className="fit-table-card">
                        <div className="fit-table-scroll">
                            <table className="fit-users-table fit-logs-table">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Actor</th>
                                        <th>Entidad</th>
                                        <th>Accion</th>
                                        <th>Descripcion</th>
                                        <th className="text-right">Detalle</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {data.logs.data.length ? data.logs.data.map((log) => (
                                        <tr key={log.id}>
                                            <td>
                                                <span className="fit-log-date">
                                                    <i className="ri-time-line" /> {log.created_at_formatted}
                                                </span>
                                            </td>
                                            <td><strong>{log.user?.name || 'Sistema'}</strong></td>
                                            <td><span className="fit-role-badge default"><i className="ri-database-2-line" /> {log.entity_label}</span></td>
                                            <td><ActionBadge action={log.action} /></td>
                                            <td><span className="fit-muted-text fit-log-description">{log.description || '-'}</span></td>
                                            <td className="text-right">
                                                {(log.old_values || log.new_values || log.pdf_url) ? (
                                                    <div className="fit-row-actions">
                                                        <button type="button" className="fit-action-button success" onClick={() => setDetailLog(log)} title="Ver detalles">
                                                            <i className="ri-eye-line" />
                                                        </button>
                                                    </div>
                                                ) : <span className="fit-muted-text">-</span>}
                                            </td>
                                        </tr>
                                    )) : <TableEmpty colSpan={6} text="Sin registros para los filtros." />}
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <Pagination pagination={data.logs} />
                </section>

                <Modal open={!!detailLog} title="Detalle de Cambio" onClose={() => setDetailLog(null)} wide contentClassName="fit-modal-content">
                    {detailLog && (
                        <div className="fit-transfer-detail fit-log-detail">
                            <div className="fit-transfer-summary fit-log-summary">
                                <div><span>Entidad</span><strong>{detailLog.entity_label}</strong></div>
                                <div><span>Actor</span><strong>{detailLog.user?.name || 'Sistema'}</strong></div>
                                <div><span>Accion</span><ActionBadge action={detailLog.action} /></div>
                                <div><span>Fecha</span><strong>{detailLog.created_at_formatted}</strong></div>
                            </div>

                            {detailLog.pdf_url && (
                                <div className="fit-transfer-panel">
                                    <h4>Documento asociado</h4>
                                    <a href={detailLog.pdf_url} target="_blank" rel="noopener noreferrer" className="fit-primary-button fit-log-pdf">
                                        <i className="ri-file-download-line" /> Abrir PDF del Traspaso
                                    </a>
                                </div>
                            )}

                            <div className="fit-log-diff-grid">
                                <LogDiffColumn title="Valores anteriores" values={detailLog.old_values} emptyText="No hay valores anteriores registrados." />
                                <LogDiffColumn title="Valores nuevos" values={detailLog.new_values} emptyText="No hay valores nuevos registrados." />
                            </div>

                            <div className="fit-modal-footer">
                                <button type="button" className="fit-outline-button" onClick={() => setDetailLog(null)}>Cerrar</button>
                            </div>
                        </div>
                    )}
                </Modal>
            </div>
        </DashboardShell>
    );
}
