import React, { useState } from 'react';
import DashboardShell from '../components/admin/DashboardShell';
import { FieldError, FlashMessages, Modal, Pagination, TableEmpty } from '../components/admin/common';

const saleTypeLabels = {
    empresa_institucional: 'Empresa institucional',
    tienda_barrio: 'Tienda de barrio',
    comprador_minorista: 'Comprador minorista',
};

const statusLabels = {
    borrador: 'Borrador',
    enviada: 'Enviada',
    aceptada: 'Aceptada',
    rechazada: 'Rechazada',
};

function QuotationStatus({ status }) {
    const tone = {
        borrador: 'draft',
        enviada: 'sent',
        aceptada: 'active',
        rechazada: 'rejected',
    }[status] || 'draft';

    return (
        <span className={`fit-quotation-status ${tone}`}>
            <span /> {statusLabels[status] || status}
        </span>
    );
}

function QuotationClient({ quotation }) {
    const client = quotation.company || quotation.customer;

    if (!client) {
        return <span className="fit-muted-text">Sin cliente</span>;
    }

    return (
        <div className="fit-user-cell fit-sale-client">
            <span className="fit-sale-client-icon"><i className={quotation.company ? 'ri-building-4-line' : 'ri-user-smile-line'} /></span>
            <div>
                <strong>{client.name}</strong>
                <small>{client.city || 'Ciudad no registrada'}</small>
            </div>
        </div>
    );
}

export default function AdminQuotationsPage({ layout, data, flash, errors, old, csrfToken, logoutAction }) {
    const [saleType, setSaleType] = useState(old?.sale_type || 'empresa_institucional');
    const [items, setItems] = useState([{ id: Date.now(), sku: '', product_id: '', product_name: '', quantity: 1, unit_price: 0 }]);
    const [createQuotationOpen, setCreateQuotationOpen] = useState(Boolean(errors && Object.keys(errors).length));
    const [detailQuotation, setDetailQuotation] = useState(null);
    const initialMetric = data.filters?.status || 'all';
    const [activeMetric, setActiveMetric] = useState(initialMetric);
    const hasFilters = Boolean(data.filters?.search || data.filters?.sale_type || data.filters?.status);

    const totalAmount = items.reduce((sum, item) => sum + Number(item.quantity || 0) * Number(item.unit_price || 0), 0);
    const visibleCompanies = data.companies.filter((company) => saleType === 'tienda_barrio' ? company.company_type === 'tienda_barrio' : company.company_type === 'empresa_institucional');

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

    const updateItem = (id, patch) => setItems((current) => current.map((item) => item.id === id ? { ...item, ...patch } : item));
    const addItem = () => setItems((current) => [...current, { id: Date.now() + Math.random(), sku: '', product_id: '', product_name: '', quantity: 1, unit_price: 0 }]);
    const removeItem = (id) => setItems((current) => current.length > 1 ? current.filter((item) => item.id !== id) : current);

    const lookupItem = async (id, sku) => {
        if (!sku) return;

        const response = await fetch(`${data.routes.lookup}?${new URLSearchParams({ sku, sale_type: saleType }).toString()}`);
        const payload = await response.json();

        if (!response.ok) {
            updateItem(id, { product_id: '', product_name: payload.message || 'Producto no encontrado.' });
            return;
        }

        updateItem(id, { product_id: payload.product_id, product_name: `${payload.name} (${payload.sku})`, unit_price: payload.price ?? 0, quantity: 1 });
    };

    const metricCards = [
        { key: 'all', label: 'Cotizaciones Total', value: data.stats.total, hint: 'Ver todas', icon: 'ri-file-list-3-line', tone: 'indigo' },
        { key: 'borrador', label: 'Borradores', value: data.stats.draft, hint: 'En preparacion', icon: 'ri-draft-line', tone: 'amber' },
        { key: 'enviada', label: 'Enviadas', value: data.stats.sent, hint: 'En ruta', icon: 'ri-send-plane-line', tone: 'amber' },
        { key: 'aceptada', label: 'Aceptadas', value: data.stats.accepted, hint: 'Ganadas', icon: 'ri-checkbox-circle-line', tone: 'green' },
        { key: 'rechazada', label: 'Rechazadas', value: data.stats.rejected, hint: 'Descartadas', icon: 'ri-close-circle-line', tone: 'rose' },
    ];

    return (
        <DashboardShell sidebar={layout.sidebar} topbar={layout.topbar} csrfToken={csrfToken} logoutAction={logoutAction}>
            <div className="fit-users-page fit-quotations-page">
                <FlashMessages flash={flash} />

                <section className="fit-users-header">
                    <div className="fit-users-header-left">
                        <div className="fit-header-icon"><i className="ri-file-list-3-line" /></div>
                        <div>
                            <h1>Cotizaciones y Proformas</h1>
                            <p>Genera proformas, revisa estados comerciales y descarga PDFs por cliente.</p>
                        </div>
                    </div>

                    <div className="fit-users-header-actions">
                        <button type="button" className="fit-primary-button" onClick={() => setCreateQuotationOpen(true)}>
                            <i className="ri-file-add-line" />
                            <span>Crear Cotizacion</span>
                        </button>
                    </div>
                </section>

                <section className="fit-metric-grid fit-quotation-metric-grid">
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

                <section className="fit-filter-card">
                    <form method="GET" action={data.routes.index} className="fit-filter-form fit-quotation-filter-form">
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
                            <h2>Cotizaciones Recientes</h2>
                            <p>Proformas ordenadas por fecha de creacion y vigencia comercial.</p>
                        </div>
                        <span className="fit-section-badge green">{data.quotations.total} registros</span>
                    </div>

                    <div className="fit-table-card">
                        <div className="fit-table-scroll">
                            <table className="fit-users-table fit-quotations-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Cliente</th>
                                        <th>Tipo</th>
                                        <th>Estado</th>
                                        <th>Total</th>
                                        <th>Valido hasta</th>
                                        <th className="text-right">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {data.quotations.data.length ? data.quotations.data.map((quotation) => (
                                        <tr key={quotation.id}>
                                            <td><code className="fit-code fit-sale-id">#{quotation.id}</code></td>
                                            <td><QuotationClient quotation={quotation} /></td>
                                            <td><span className="fit-role-badge default"><i className="ri-price-tag-3-line" /> {saleTypeLabels[quotation.sale_type] || quotation.sale_type}</span></td>
                                            <td><QuotationStatus status={quotation.status} /></td>
                                            <td><strong className="fit-sale-amount">Bs {Number(quotation.total_amount).toFixed(2)}</strong></td>
                                            <td><span className="fit-muted-text">{quotation.valid_until_formatted}</span></td>
                                            <td className="text-right">
                                                <div className="fit-row-actions">
                                                    <button type="button" className="fit-action-button success" onClick={() => setDetailQuotation(quotation)} title="Ver detalles">
                                                        <i className="ri-eye-line" />
                                                    </button>
                                                    <a className="fit-action-button warning" target="_blank" rel="noopener noreferrer" href={quotation.pdf_url} title="Descargar PDF">
                                                        <i className="ri-file-download-line" />
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    )) : <TableEmpty colSpan={7} text="No hay cotizaciones registradas." />}
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <Pagination pagination={data.quotations} />
                </section>

                <Modal open={createQuotationOpen} title="Registro Oficial de Cotizaciones" onClose={() => setCreateQuotationOpen(false)} wide contentClassName="fit-modal-content">
                    <form method="POST" action={data.routes.store} className="fit-register-form">
                        <input type="hidden" name="_token" value={csrfToken} />

                        <div className="fit-form-grid">
                            <div className="fit-form-field">
                                <label htmlFor="sale_type_create">Tipo *</label>
                                <select id="sale_type_create" name="sale_type" value={saleType} onChange={(event) => setSaleType(event.target.value)} required>
                                    {Object.entries(saleTypeLabels).map(([key, label]) => <option key={key} value={key}>{label}</option>)}
                                </select>
                                <FieldError errors={errors} name="sale_type" />
                            </div>

                            <div className="fit-form-field">
                                <label htmlFor="valid_until">Valido hasta *</label>
                                <input id="valid_until" type="date" name="valid_until" defaultValue={old?.valid_until || ''} required />
                                <FieldError errors={errors} name="valid_until" />
                            </div>

                            <div className="fit-form-field">
                                <label htmlFor="status_create">Estado *</label>
                                <select id="status_create" name="status" defaultValue={old?.status || 'borrador'} required>
                                    {Object.entries(statusLabels).map(([key, label]) => <option key={key} value={key}>{label}</option>)}
                                </select>
                                <FieldError errors={errors} name="status" />
                            </div>

                            <div className="fit-form-field span-2">
                                {saleType === 'comprador_minorista' ? (
                                    <>
                                        <label htmlFor="customer_id">Comprador minorista *</label>
                                        <select id="customer_id" name="customer_id" defaultValue={old?.customer_id || ''}>
                                            <option value="">Seleccionar</option>
                                            {data.customers.map((customer) => <option key={customer.id} value={customer.id}>{customer.name} - {customer.city}</option>)}
                                        </select>
                                        <FieldError errors={errors} name="customer_id" />
                                    </>
                                ) : (
                                    <>
                                        <label htmlFor="company_id">Empresa / Tienda *</label>
                                        <select id="company_id" name="company_id" defaultValue={old?.company_id || ''}>
                                            <option value="">Seleccionar</option>
                                            {visibleCompanies.map((company) => <option key={company.id} value={company.id}>{company.name} - {company.city} (NIT: {company.nit})</option>)}
                                        </select>
                                        <FieldError errors={errors} name="company_id" />
                                    </>
                                )}
                            </div>

                            <div className="fit-form-field span-2">
                                <label htmlFor="notes">Notas</label>
                                <textarea id="notes" name="notes" rows="3" defaultValue={old?.notes || ''} />
                                <FieldError errors={errors} name="notes" />
                            </div>
                        </div>

                        <div className="fit-transfer-items fit-quotation-items">
                            <div className="fit-transfer-items-head">
                                <div>
                                    <span>Detalle</span>
                                    <h4>Productos de la cotizacion</h4>
                                </div>
                                <button type="button" className="fit-outline-button" onClick={addItem}>
                                    <i className="ri-add-line" /> Agregar Producto
                                </button>
                            </div>

                            <div className="fit-transfer-items-list">
                                {items.map((item, index) => (
                                    <div className="fit-transfer-item-row fit-quotation-item-row" key={item.id}>
                                        <div className="fit-form-grid">
                                            <div className="fit-form-field">
                                                <label htmlFor={`sku_${item.id}`}>Codigo SKU *</label>
                                                <input
                                                    id={`sku_${item.id}`}
                                                    type="text"
                                                    value={item.sku}
                                                    onChange={(event) => updateItem(item.id, { sku: event.target.value })}
                                                    onBlur={(event) => lookupItem(item.id, event.target.value.trim())}
                                                />
                                                <input type="hidden" name={`items[${index}][product_id]`} value={item.product_id} required />
                                            </div>

                                            <div className="fit-form-field">
                                                <label htmlFor={`product_${item.id}`}>Producto</label>
                                                <input id={`product_${item.id}`} type="text" value={item.product_name} readOnly />
                                            </div>

                                            <div className="fit-form-field">
                                                <label htmlFor={`qty_${item.id}`}>Cantidad *</label>
                                                <input
                                                    id={`qty_${item.id}`}
                                                    type="number"
                                                    min="1"
                                                    name={`items[${index}][quantity]`}
                                                    value={item.quantity}
                                                    onChange={(event) => updateItem(item.id, { quantity: event.target.value })}
                                                    required
                                                />
                                            </div>

                                            <div className="fit-form-field">
                                                <label htmlFor={`price_${item.id}`}>Precio unitario *</label>
                                                <input
                                                    id={`price_${item.id}`}
                                                    type="number"
                                                    min="0"
                                                    step="0.01"
                                                    name={`items[${index}][unit_price]`}
                                                    value={item.unit_price}
                                                    onChange={(event) => updateItem(item.id, { unit_price: event.target.value })}
                                                    required
                                                />
                                            </div>
                                        </div>

                                        <button type="button" className="fit-action-button danger fit-transfer-remove" onClick={() => removeItem(item.id)} title="Quitar producto">
                                            <i className="ri-delete-bin-line" />
                                        </button>
                                    </div>
                                ))}
                            </div>
                            <FieldError errors={errors} name="items" />
                            <div className="fit-form-field span-2">
                                <label htmlFor="audit_reason">Motivo para bitacora</label>
                                <textarea id="audit_reason" name="audit_reason" rows="3" placeholder="Ej. Precio negociado o promocion temporal autorizada" defaultValue={old?.audit_reason || ''} />
                                <FieldError errors={errors} name="audit_reason" />
                            </div>
                            <div className="fit-sale-total">
                                <span>Total estimado</span>
                                <strong>Bs {totalAmount.toFixed(2)}</strong>
                            </div>
                        </div>

                        <div className="fit-modal-footer">
                            <button type="button" className="fit-outline-button" onClick={() => setCreateQuotationOpen(false)}>Cancelar</button>
                            <button type="submit" className="fit-primary-button">
                                <i className="ri-file-add-line" /> Generar Cotizacion
                            </button>
                        </div>
                    </form>
                </Modal>

                <Modal open={!!detailQuotation} title={detailQuotation ? `Cotizacion #${detailQuotation.id}` : 'Detalle de cotizacion'} onClose={() => setDetailQuotation(null)} wide contentClassName="fit-modal-content">
                    {detailQuotation && (
                        <div className="fit-transfer-detail fit-quotation-detail">
                            <div className="fit-transfer-summary fit-quotation-summary">
                                <div><span>Cliente</span><strong>{detailQuotation.company?.name || detailQuotation.customer?.name || 'Cliente'}</strong></div>
                                <div><span>Tipo</span><strong>{saleTypeLabels[detailQuotation.sale_type] || detailQuotation.sale_type}</strong></div>
                                <div><span>Estado</span><QuotationStatus status={detailQuotation.status} /></div>
                                <div><span>Valido hasta</span><strong>{detailQuotation.valid_until_formatted}</strong></div>
                                <div><span>Total</span><strong className="fit-sale-amount">Bs {Number(detailQuotation.total_amount).toFixed(2)}</strong></div>
                            </div>

                            <div className="fit-transfer-panel">
                                <h4>Datos comerciales</h4>
                                <p>Vendedor: {detailQuotation.seller?.name || '-'}</p>
                                <p>Notas: {detailQuotation.notes || 'Sin notas registradas.'}</p>
                            </div>

                            <div className="fit-transfer-panel">
                                <h4>Productos</h4>
                                <div className="fit-table-scroll">
                                    <table className="fit-users-table fit-quotation-items-table">
                                        <thead>
                                            <tr>
                                                <th>Producto</th>
                                                <th>SKU</th>
                                                <th>Cantidad</th>
                                                <th>Precio unitario</th>
                                                <th>Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {detailQuotation.items?.length ? detailQuotation.items.map((item, index) => (
                                                <tr key={`${item.sku}-${index}`}>
                                                    <td><strong>{item.product}</strong></td>
                                                    <td><code className="fit-code fit-product-sku">{item.sku}</code></td>
                                                    <td>{item.qty} uds</td>
                                                    <td>Bs {Number(item.price || 0).toFixed(2)}</td>
                                                    <td><strong className="fit-sale-amount">Bs {Number(item.subtotal || 0).toFixed(2)}</strong></td>
                                                </tr>
                                            )) : <TableEmpty colSpan={5} text="Sin productos registrados." />}
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div className="fit-modal-footer">
                                <button type="button" className="fit-outline-button" onClick={() => setDetailQuotation(null)}>Cerrar</button>
                                <a className="fit-primary-button" target="_blank" rel="noopener noreferrer" href={detailQuotation.pdf_url}>
                                    <i className="ri-file-download-line" /> Descargar PDF
                                </a>
                            </div>
                        </div>
                    )}
                </Modal>
            </div>
        </DashboardShell>
    );
}
