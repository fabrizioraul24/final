import React, { useState } from 'react';
import DashboardShell from '../components/admin/DashboardShell';
import { FieldError, FlashMessages, Modal, Pagination, TableEmpty } from '../components/admin/common';

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
    const [saleType, setSaleType] = useState(old?.sale_type || 'empresa_institucional');
    const [items, setItems] = useState([{ id: Date.now(), sku: '', product_id: '', product_name: '', available: '', quantity: '', unit_price: '' }]);
    const [createSaleOpen, setCreateSaleOpen] = useState(Boolean(errors && Object.keys(errors).length));
    const [detailSale, setDetailSale] = useState(null);
    const [statusSale, setStatusSale] = useState(null);
    const initialMetric = data.filters?.status || 'all';
    const [activeMetric, setActiveMetric] = useState(initialMetric);
    const hasFilters = Boolean(data.filters?.search || data.filters?.sale_type || data.filters?.status);

    const total = items.reduce((sum, item) => sum + Number(item.quantity || 0) * Number(item.unit_price || 0), 0);
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
    const addItem = () => setItems((current) => [...current, { id: Date.now() + Math.random(), sku: '', product_id: '', product_name: '', available: '', quantity: '', unit_price: '' }]);
    const removeItem = (id) => setItems((current) => current.length > 1 ? current.filter((item) => item.id !== id) : current);
    const printSaleDetail = () => window.print();

    const lookupItem = async (id, sku) => {
        const warehouseId = data.laPazWarehouse?.id;
        if (!warehouseId || !sku) return;

        const params = new URLSearchParams({ sku, sale_type: saleType, warehouse_id: warehouseId });
        const response = await fetch(`${data.routes.lookup}?${params.toString()}`);
        const payload = await response.json();

        if (!response.ok) {
            updateItem(id, { product_id: '', product_name: payload.message || 'No pudimos encontrar el producto.', available: 'Fuera de stock', quantity: '', unit_price: '' });
            return;
        }

        const available = payload.available_quantity ?? 0;
        updateItem(id, {
            product_id: payload.product_id,
            product_name: `${payload.name} (${payload.sku})`,
            available: available > 0 ? `${available} uds` : 'Fuera de stock',
            quantity: available > 0 ? 1 : '',
            unit_price: payload.price ?? 0,
        });
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
                        <button type="button" className="fit-primary-button" onClick={() => setCreateSaleOpen(true)}>
                            <i className="ri-add-box-line" />
                            <span>Crear Venta</span>
                        </button>
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
                                                    <button type="button" className="fit-action-button success" onClick={() => setDetailSale(sale)} title="Ver detalles">
                                                        <i className="ri-eye-line" />
                                                    </button>
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

                <Modal open={createSaleOpen} title="Registro Oficial de Ventas" onClose={() => setCreateSaleOpen(false)} wide contentClassName="fit-modal-content fit-sale-create-modal">
                    <form method="POST" action={data.routes.store} className="fit-register-form">
                        <input type="hidden" name="_token" value={csrfToken} />

                        <div className="fit-form-grid">
                            <div className="fit-form-field">
                                <label htmlFor="sale_type_create">Tipo de venta *</label>
                                <select id="sale_type_create" name="sale_type" value={saleType} onChange={(event) => setSaleType(event.target.value)} required>
                                    {Object.entries(saleTypeLabels).map(([key, label]) => <option key={key} value={key}>{label}</option>)}
                                </select>
                                <FieldError errors={errors} name="sale_type" />
                            </div>

                            <div className="fit-form-field">
                                <label htmlFor="status_create">Estado de venta *</label>
                                <select id="status_create" name="status" defaultValue={old?.status || 'sin_entregar'} required>
                                    {Object.entries(statusLabels).map(([key, label]) => <option key={key} value={key}>{label}</option>)}
                                </select>
                                <FieldError errors={errors} name="status" />
                            </div>

                            <input type="hidden" name="warehouse_id" value={data.laPazWarehouse?.id || ''} />

                            <div className="fit-form-field span-2">
                                <label htmlFor="warehouse_id">Almacen asignado</label>
                                <input
                                    id="warehouse_id"
                                    type="text"
                                    value={data.laPazWarehouse ? `${data.laPazWarehouse.name} (${data.laPazWarehouse.code})` : 'Configura el almacen de La Paz para permitir ventas'}
                                    readOnly
                                />
                            </div>

                            <div className="fit-form-field">
                                <label htmlFor="payment_method">Metodo de pago *</label>
                                <select id="payment_method" name="payment_method" defaultValue={old?.payment_method || ''} required>
                                    <option value="">Seleccionar</option>
                                    {Object.entries(data.paymentLabels).map(([key, label]) => <option key={key} value={key}>{label}</option>)}
                                </select>
                                <FieldError errors={errors} name="payment_method" />
                            </div>

                            <div className="fit-form-field">
                                <label htmlFor="delivery_city_id">Ciudad entrega *</label>
                                <select id="delivery_city_id" name="delivery_city_id" defaultValue={old?.delivery_city_id || ''} required>
                                    <option value="">Seleccionar</option>
                                    {data.cities.map((city) => <option key={city.id} value={city.id}>{city.name}</option>)}
                                </select>
                                <FieldError errors={errors} name="delivery_city_id" />
                            </div>

                            <div className="fit-form-field span-2">
                                <label htmlFor="delivery_address">Direccion entrega</label>
                                <input id="delivery_address" type="text" name="delivery_address" defaultValue={old?.delivery_address || ''} />
                                <FieldError errors={errors} name="delivery_address" />
                            </div>

                            <div className="fit-form-field span-2">
                                {saleType === 'comprador_minorista' ? (
                                    <>
                                        <label htmlFor="customer_id">Comprador minorista *</label>
                                        <select id="customer_id" name="customer_id" defaultValue={old?.customer_id || ''}>
                                            <option value="">Seleccionar</option>
                                            {data.customers.map((customer) => <option key={customer.id} value={customer.id}>{customer.name} - {customer.city}{customer.nit ? ` (NIT: ${customer.nit})` : ''}</option>)}
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
                        </div>

                        <div className="fit-transfer-items fit-sale-items">
                            <div className="fit-transfer-items-head">
                                <div>
                                    <span>Detalle</span>
                                    <h4>Productos de la venta</h4>
                                </div>
                                <button type="button" className="fit-outline-button" onClick={addItem}>
                                    <i className="ri-add-line" /> Agregar Producto
                                </button>
                            </div>

                            <div className="fit-transfer-items-list">
                                {items.map((item, index) => (
                                    <div className="fit-transfer-item-row fit-sale-item-row" key={item.id}>
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
                                                <label htmlFor={`available_${item.id}`}>Disponible</label>
                                                <input id={`available_${item.id}`} type="text" value={item.available} readOnly />
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
                                <textarea id="audit_reason" name="audit_reason" rows="3" placeholder="Ej. Precio especial autorizado para este cliente" defaultValue={old?.audit_reason || ''} />
                                <FieldError errors={errors} name="audit_reason" />
                            </div>
                            <div className="fit-sale-total">
                                <span>Total estimado</span>
                                <strong>Bs {total.toFixed(2)}</strong>
                            </div>
                        </div>

                        <div className="fit-modal-footer">
                            <button type="button" className="fit-outline-button" onClick={() => setCreateSaleOpen(false)}>Cancelar</button>
                            <button type="submit" className="fit-primary-button">
                                <i className="ri-checkbox-circle-line" /> Registrar Venta
                            </button>
                        </div>
                    </form>
                </Modal>

                <Modal
                    open={!!detailSale}
                    title={detailSale ? `Reporte de Venta #${detailSale.id}` : 'Detalle de venta'}
                    onClose={() => setDetailSale(null)}
                    wide
                    contentClassName="fit-modal-content fit-sale-report-modal"
                    actions={detailSale && (
                        <button type="button" className="fit-outline-button compact print-hide" onClick={printSaleDetail}>
                            <i className="ri-printer-line" /> Imprimir reporte
                        </button>
                    )}
                >
                    {detailSale && (
                        <div className="fit-transfer-detail fit-sale-detail fit-sale-report-detail">
                            <div className="fit-sale-report-heading">
                                <div>
                                    <span>Reporte oficial</span>
                                    <h4>Venta #{detailSale.id}</h4>
                                    <p>{detailSale.created_at_formatted}</p>
                                </div>
                                <strong>Bs {Number(detailSale.total_amount).toFixed(2)}</strong>
                            </div>

                            <div className="fit-transfer-summary fit-sale-summary fit-sale-report-summary">
                                <div><span>Cliente</span><strong>{detailSale.company?.name || detailSale.customer?.name || 'Sin cliente'}</strong></div>
                                <div><span>Tipo</span><strong>{saleTypeLabels[detailSale.sale_type] || detailSale.sale_type}</strong></div>
                                <div><span>Estado</span><strong>{statusLabels[detailSale.status] || detailSale.status}</strong></div>
                                <div><span>Pago</span><strong>{detailSale.payment_label}</strong></div>
                                <div><span>Vendedor</span><strong>{detailSale.seller?.name || '-'}</strong></div>
                            </div>

                            <div className="fit-transfer-panel">
                                <h4>Entrega</h4>
                                <div className="fit-sale-report-info">
                                    <div><span>Almacen</span><strong>{detailSale.warehouse?.name || '-'}</strong></div>
                                    <div><span>Ciudad</span><strong>{detailSale.delivery_city || '-'}</strong></div>
                                    <div><span>Direccion</span><strong>{detailSale.delivery_address || 'Sin direccion registrada.'}</strong></div>
                                </div>
                            </div>

                            <div className="fit-transfer-panel">
                                <h4>Productos</h4>
                                <div className="fit-table-scroll">
                                    <table className="fit-users-table fit-sale-items-table">
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
                                            {detailSale.items.length ? detailSale.items.map((item, index) => (
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
                                <button type="button" className="fit-primary-button print-hide" onClick={printSaleDetail}>
                                    <i className="ri-printer-line" /> Imprimir reporte
                                </button>
                                <button type="button" className="fit-outline-button" onClick={() => setDetailSale(null)}>Cerrar</button>
                            </div>
                        </div>
                    )}
                </Modal>

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
