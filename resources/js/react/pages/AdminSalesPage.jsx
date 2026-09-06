import React, { useState } from 'react';
import DashboardShell from '../components/admin/DashboardShell';
import { FlashMessages, Modal, Pagination, TableEmpty } from '../components/admin/common';

const saleTypeLabels = {
    empresa_institucional: 'Empresa institucional',
    tienda_barrio: 'Tienda de barrio',
    comprador_minorista: 'Comprador minorista',
};

const statusLabels = { sin_entregar: 'Sin entregar', entregado: 'Entregado' };

function SaleStatus({ status }) {
    const tone = status === 'entregado' ? 'active' : 'pending';

    return (
        <span className={`fit-transfer-status ${tone}`}>
            <span /> {statusLabels[status] || status}
        </span>
    );
}

function SaleClient({ sale }) {
    const client = sale.company || sale.customer;

    if (!client) {
        return <span className="fit-muted-text">Sin cliente</span>;
    }

    return (
        <div className="fit-user-cell fit-sale-client">
            <span className="fit-sale-client-icon"><i className={sale.company ? 'ri-building-4-line' : 'ri-user-smile-line'} /></span>
            <div>
                <strong>{client.name}</strong>
                <small>{client.city || 'Ciudad no registrada'}</small>
            </div>
        </div>
    );
}

export default function AdminSalesPage({ layout, data, flash, errors, old, csrfToken, logoutAction }) {
    const [statusSale, setStatusSale] = useState(null);
    const initialMetric = data.filters?.status || 'all';
    const [activeMetric, setActiveMetric] = useState(initialMetric);
    const hasFilters = Boolean(data.filters?.search || data.filters?.sale_type || data.filters?.status);

    const buildIndexUrl = (params = {}) => {
        const url = new URL(data.routes.index, window.location.origin);
        const search = params.search ?? data.filters.search;
        const type = params.sale_type ?? data.filters.sale_type;
        const status = params.status ?? data.filters.status;

        if (search) url.searchParams.set('search', search);
        if (type) url.searchParams.set('sale_type', type);
        if (status) url.searchParams.set('status', status);

        return `${url.pathname}${url.search}`;
    };

    const handleMetricClick = (key) => {
        if (key === 'all') {
            if (data.filters?.status) {
                window.location.href = buildIndexUrl({ status: '' });
                return;
            }

            setActiveMetric('all');
            return;
        }

        window.location.href = buildIndexUrl({ status: key });
    };

    const metricCards = [
        { key: 'all', label: 'Ventas Total', value: data.stats.count, hint: 'Ver todas', icon: 'ri-shopping-cart-2-line', tone: 'indigo' },
        { key: 'sin_entregar', label: 'Sin Entregar', value: data.stats.pending, hint: 'Pendientes', icon: 'ri-time-line', tone: 'amber' },
        { key: 'entregado', label: 'Entregadas', value: data.stats.delivered, hint: 'Completadas', icon: 'ri-checkbox-circle-line', tone: 'green' },
        { key: 'amount', label: 'Monto Total', value: `Bs ${Number(data.stats.total_amount).toFixed(2)}`, hint: 'Historico', icon: 'ri-money-dollar-circle-line', tone: 'rose', disabled: true },
    ];

    return (
        <DashboardShell sidebar={layout.sidebar} topbar={layout.topbar} csrfToken={csrfToken} logoutAction={logoutAction}>
            <div className="fit-users-page fit-sales-page">
                <FlashMessages flash={flash} />

                <section className="fit-users-header">
                    <div className="fit-users-header-left">
                        <div className="fit-header-icon"><i className="ri-shopping-cart-2-line" /></div>
                        <div>
                            <h1>Ventas y Registro Comercial</h1>
                            <p>Consulta ventas, controla entregas y registra productos vendidos desde el almacen de La Paz.</p>
                        </div>
                    </div>

                    <div className="fit-users-header-actions">
                        <a href={data.routes.create} className="fit-primary-button">
                            <i className="ri-add-box-line" />
                            <span>Crear Venta</span>
                        </a>
                    </div>
                </section>

                <section className="fit-metric-grid">
                    {metricCards.map((card) => (
                        <button
                            type="button"
                            key={card.key}
                            className={`fit-metric-card ${card.tone}${activeMetric === card.key ? ' active' : ''}`}
                            onClick={() => !card.disabled && handleMetricClick(card.key)}
                            disabled={card.disabled}
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

                <section className="fit-filter-card">
                    <form method="GET" action={data.routes.index} className="fit-filter-form fit-sale-filter-form">
                        <label className="fit-search-control" htmlFor="search">
                            <i className="ri-search-line" />
                            <input
                                type="search"
                                id="search"
                                name="search"
                                placeholder="Buscar ID o cliente..."
                                defaultValue={data.filters.search || ''}
                            />
                        </label>

                        <label className="fit-select-control" htmlFor="sale_type">
                            <i className="ri-price-tag-3-line" />
                            <select id="sale_type" name="sale_type" defaultValue={data.filters.sale_type || ''}>
                                <option value="">Todos los tipos</option>
                                {Object.entries(saleTypeLabels).map(([key, label]) => <option key={key} value={key}>{label}</option>)}
                            </select>
                        </label>

                        <label className="fit-select-control" htmlFor="status">
                            <i className="ri-checkbox-circle-line" />
                            <select id="status" name="status" defaultValue={data.filters.status || ''}>
                                <option value="">Todos los estados</option>
                                {Object.entries(statusLabels).map(([key, label]) => <option key={key} value={key}>{label}</option>)}
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
                            <h2>Ventas Recientes</h2>
                            <p>Registros ordenados del mas reciente al mas antiguo.</p>
                        </div>
                        <span className="fit-section-badge green">{data.sales.total} registros</span>
                    </div>

                    <div className="fit-table-card">
                        <div className="fit-table-scroll">
                            <table className="fit-users-table fit-sales-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Cliente</th>
                                        <th>Tipo</th>
                                        <th>Estado</th>
                                        <th>Pago</th>
                                        <th>Monto</th>
                                        <th>Almacen</th>
                                        <th>Fecha</th>
                                        <th className="text-right">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {data.sales.data.length ? data.sales.data.map((sale) => (
                                        <tr key={sale.id}>
                                            <td><code className="fit-code fit-sale-id">#{sale.id}</code></td>
                                            <td><SaleClient sale={sale} /></td>
                                            <td><span className="fit-role-badge default"><i className="ri-price-tag-3-line" /> {saleTypeLabels[sale.sale_type] || sale.sale_type}</span></td>
                                            <td><SaleStatus status={sale.status} /></td>
                                            <td><span className="fit-sale-payment">{sale.payment_label}</span></td>
                                            <td><strong className="fit-sale-amount">Bs {Number(sale.total_amount).toFixed(2)}</strong></td>
                                            <td><span className="fit-muted-text">{sale.warehouse?.name || '-'}</span></td>
                                            <td><span className="fit-muted-text">{sale.created_at_formatted}</span></td>
                                            <td className="text-right">
                                                <div className="fit-row-actions">
                                                    <a href={sale.show_url} className="fit-action-button success" title="Ver detalles">
                                                        <i className="ri-eye-line" />
                                                    </a>
                                                    <button type="button" className="fit-action-button warning" onClick={() => setStatusSale(sale)} title="Actualizar">
                                                        <i className="ri-pencil-line" />
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    )) : <TableEmpty colSpan={9} text="No hay ventas registradas." />}
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <Pagination pagination={data.sales} />
                </section>

                <Modal open={!!statusSale} title="Actualizar Venta" onClose={() => setStatusSale(null)} contentClassName="fit-modal-content fit-sale-status-modal">
                    {statusSale && (
                        <form method="POST" action={statusSale.update_url} className="fit-register-form">
                            <input type="hidden" name="_token" value={csrfToken} />
                            <input type="hidden" name="_method" value="PUT" />

                            <div className="fit-form-grid">
                                <div className="fit-form-field span-2">
                                    <label htmlFor="status_update">Estado *</label>
                                    <select id="status_update" name="status" defaultValue={statusSale.status} required>
                                        {Object.entries(statusLabels).map(([key, label]) => <option key={key} value={key}>{label}</option>)}
                                    </select>
                                </div>
                            </div>

                            <div className="fit-modal-footer">
                                <button type="button" className="fit-outline-button" onClick={() => setStatusSale(null)}>Cancelar</button>
                                <button type="submit" className="fit-primary-button">
                                    <i className="ri-save-3-line" /> Guardar Cambios
                                </button>
                            </div>
                        </form>
                    )}
                </Modal>
            </div>
        </DashboardShell>
    );
}
