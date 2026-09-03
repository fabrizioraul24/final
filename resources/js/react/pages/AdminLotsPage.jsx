import React, { useState } from 'react';
import DashboardShell from '../components/admin/DashboardShell';
import { FieldError, FlashMessages, Modal, Pagination, TableEmpty } from '../components/admin/common';

function LotProductCard({ product, onView }) {
    const critical = Number(product.minimum_stock || 0) > 0 && Number(product.current_stock || 0) <= Number(product.minimum_stock || 0);

    return (
        <article className={`fit-lot-card${critical ? ' critical' : ''}`}>
            <div className="fit-lot-card-main">
                <div className="fit-lot-product-head">
                    <span className="fit-product-image fit-lot-product-image">
                        <img src={product.image} alt={product.name} />
                    </span>
                    <div>
                        <h3>{product.name}</h3>
                        <div className="fit-lot-tags">
                            <code className="fit-code fit-product-sku">{product.sku}</code>
                            <span className="fit-role-badge default"><i className="ri-price-tag-3-line" /> {product.category?.name || 'Sin categoria'}</span>
                            <span className="fit-role-badge warehouse"><i className="ri-stack-line" /> {product.lots_count} lote(s)</span>
                        </div>
                    </div>
                </div>

                <div className="fit-lot-stat-grid">
                    <div><span>Stock actual</span><strong>{product.current_stock} uds</strong></div>
                    <div><span>Stock minimo</span><strong>{product.minimum_stock || 'No definido'}</strong></div>
                    <div><span>Total lotes</span><strong>{product.lots_count}</strong></div>
                    <div><span>Proximo vencimiento</span><strong>{product.next_expiry}</strong></div>
                </div>

                {critical && (
                    <div className="fit-lot-alert">
                        <i className="ri-error-warning-line" />
                        <div>
                            <strong>Necesita reabastecimiento</strong>
                            <span>El stock actual alcanzo o bajo del minimo configurado.</span>
                        </div>
                    </div>
                )}
            </div>

            <div className="fit-lot-card-actions">
                <button type="button" className="fit-primary-button" onClick={() => onView(product)}>
                    <i className="ri-stack-line" /> Ver Lotes
                </button>
            </div>
        </article>
    );
}

export default function AdminLotsPage({ layout, data, flash, errors, old, csrfToken, logoutAction }) {
    const [viewProduct, setViewProduct] = useState(null);
    const [editLot, setEditLot] = useState(null);
    const [createLotOpen, setCreateLotOpen] = useState(() => Object.keys(errors || {}).length > 0 || Boolean(data.modalError));
    const initialMetric = data.filters.scope === 'expiring' ? 'expiring' : 'all';
    const [activeMetric, setActiveMetric] = useState(initialMetric);
    const hasFilters = data.filters.search || data.filters.product_id || data.filters.expires_at || data.filters.scope;

    const buildIndexUrl = (params = {}) => {
        const url = new URL(data.routes.index, window.location.origin);
        const search = params.search ?? data.filters.search;
        const productId = params.product_id ?? data.filters.product_id;
        const warehouseId = params.warehouse_id ?? data.filters.warehouse_id;
        const expiresAt = params.expires_at ?? data.filters.expires_at;
        const scope = params.scope ?? data.filters.scope;

        if (search) url.searchParams.set('search', search);
        if (productId) url.searchParams.set('product_id', productId);
        if (warehouseId) url.searchParams.set('warehouse_id', warehouseId);
        if (expiresAt) url.searchParams.set('expires_at', expiresAt);
        if (scope) url.searchParams.set('scope', scope);

        return `${url.pathname}${url.search}`;
    };

    const handleMetricClick = (key) => {
        if (key === 'expiring') {
            window.location.href = buildIndexUrl({ scope: 'expiring' });
            return;
        }

        if (key === 'all' && data.filters.scope) {
            window.location.href = buildIndexUrl({ scope: '' });
            return;
        }

        setActiveMetric('all');
    };

    const metricCards = [
        { key: 'all', label: 'Productos con Lotes', value: data.stats.products, hint: 'Ver todos', icon: 'ri-shopping-bag-3-line', tone: 'indigo' },
        { key: 'lots', label: 'Lotes Registrados', value: data.stats.lots, hint: 'Trazabilidad activa', icon: 'ri-stack-line', tone: 'amber' },
        { key: 'stock', label: 'Stock en Lotes', value: data.stats.stock, hint: 'Unidades disponibles', icon: 'ri-box-3-line', tone: 'green' },
        { key: 'expiring', label: 'Vencen 30 Dias', value: data.stats.expiring, hint: 'Filtrar seguimiento', icon: 'ri-calendar-close-line', tone: 'rose' },
    ];

    return (
        <DashboardShell sidebar={layout.sidebar} topbar={layout.topbar} csrfToken={csrfToken} logoutAction={logoutAction}>
            <div className="fit-users-page fit-lots-page">
                <FlashMessages flash={flash} />

                {data.modalError && (
                    <div className="fit-filter-card">
                        <span className="fit-status inactive"><span /> {data.modalError}</span>
                    </div>
                )}

                <section className="fit-users-header">
                    <div className="fit-users-header-left">
                        <div className="fit-header-icon"><i className="ri-archive-2-line" /></div>
                        <div>
                            <h1>Lotes y Trazabilidad FEFO</h1>
                            <p>Controla stock por lote, vencimientos, movimientos y reabastecimiento de productos.</p>
                        </div>
                    </div>

                    <div className="fit-users-header-actions">
                        <a className="fit-outline-button" target="_blank" rel="noopener noreferrer" href={data.routes.report}>
                            <i className="ri-download-2-line" />
                            <span>Descargar Reporte PDF</span>
                        </a>
                        <button type="button" className="fit-primary-button" onClick={() => setCreateLotOpen(true)}>
                            <i className="ri-add-box-line" />
                            <span>Crear Lote</span>
                        </button>
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

                <section className="fit-filter-card">
                    <form method="GET" action={data.routes.index} className="fit-lot-filter-form">
                        <label className="fit-search-control" htmlFor="search">
                            <i className="ri-search-line" />
                            <input id="search" type="text" name="search" placeholder="Buscar producto o SKU..." defaultValue={data.filters.search || ''} />
                        </label>

                        <label className="fit-select-control" htmlFor="product_id">
                            <i className="ri-shopping-bag-3-line" />
                            <select id="product_id" name="product_id" defaultValue={data.filters.product_id || ''}>
                                <option value="">Todos los productos</option>
                                {data.products.map((product) => <option key={product.id} value={product.id}>{product.name} ({product.sku})</option>)}
                            </select>
                        </label>

                        <label className="fit-select-control" htmlFor="warehouse_id">
                            <i className="ri-store-2-line" />
                            <select id="warehouse_id" name="warehouse_id" defaultValue={data.filters.warehouse_id || ''}>
                                <option value="">Todas las bodegas</option>
                                {data.warehouses.map((warehouse) => <option key={warehouse.id} value={warehouse.id}>{warehouse.name}</option>)}
                            </select>
                        </label>

                        <label className="fit-search-control" htmlFor="expires_at">
                            <i className="ri-calendar-line" />
                            <input id="expires_at" type="date" name="expires_at" defaultValue={data.filters.expires_at || ''} />
                        </label>

                        {data.filters.scope && <input type="hidden" name="scope" value={data.filters.scope} />}

                        <button type="submit" className="fit-primary-button compact">
                            <i className="ri-search-line" /> Buscar
                        </button>

                        {hasFilters && <a href={data.routes.index} className="fit-clear-button">Limpiar Filtros</a>}
                    </form>
                </section>

                <section className="fit-section">
                    <div className="fit-section-head">
                        <div>
                            <h2>Productos con Lotes Registrados</h2>
                            <p>Vista por producto con stock acumulado, proximo vencimiento y acceso al historial.</p>
                        </div>
                        <span className="fit-section-badge green">{data.productsWithLots.total} productos</span>
                    </div>

                    <div className="fit-lot-stack">
                        {data.productsWithLots.data.length ? data.productsWithLots.data.map((product) => (
                            <LotProductCard key={product.id} product={product} onView={setViewProduct} />
                        )) : (
                            <div className="fit-table-card fit-empty-card">
                                <p>No encontramos productos con lotes para esos filtros.</p>
                            </div>
                        )}
                    </div>

                    <Pagination pagination={data.productsWithLots} />
                </section>

                <Modal open={createLotOpen} title="Registro Oficial de Lotes" onClose={() => setCreateLotOpen(false)} wide contentClassName="fit-modal-content">
                    <form method="POST" action={data.routes.store} className="fit-register-form">
                        <input type="hidden" name="_token" value={csrfToken} />
                        <div className="fit-form-grid">
                            <div className="fit-form-field span-2">
                                <label htmlFor="product_id_create">Producto *</label>
                                <select id="product_id_create" name="product_id" defaultValue={old?.product_id || ''} required>
                                    <option value="">Selecciona un producto</option>
                                    {data.products.map((product) => <option key={product.id} value={product.id}>{product.name} ({product.sku})</option>)}
                                </select>
                                <FieldError errors={errors} name="product_id" />
                            </div>

                            <div className="fit-form-field">
                                <label htmlFor="lote_code">Codigo de lote</label>
                                <input id="lote_code" type="text" name="lote_code" placeholder="Ej. LOT-2026-001" defaultValue={old?.lote_code || ''} />
                            </div>

                            <div className="fit-form-field">
                                <label htmlFor="quantity">Cantidad *</label>
                                <input id="quantity" type="number" min="1" name="quantity" defaultValue={old?.quantity || ''} required />
                                <FieldError errors={errors} name="quantity" />
                            </div>

                            <div className="fit-form-field span-2">
                                <label htmlFor="expires_at_create">Fecha expiracion *</label>
                                <input id="expires_at_create" type="date" name="expires_at" defaultValue={old?.expires_at || ''} required />
                                <FieldError errors={errors} name="expires_at" />
                            </div>
                        </div>

                        <div className="fit-modal-footer">
                            <button type="button" className="fit-outline-button" onClick={() => setCreateLotOpen(false)}>Cancelar</button>
                            <button type="submit" className="fit-primary-button">
                                <i className="ri-checkbox-circle-line" /> Registrar Lote
                            </button>
                        </div>
                    </form>
                </Modal>

                <Modal open={!!viewProduct} title="Detalle de Lotes por Producto" onClose={() => setViewProduct(null)} wide contentClassName="fit-modal-content">
                    {viewProduct && (
                        <div className="fit-lot-detail">
                            <div className="fit-lot-detail-summary">
                                <div className="fit-lot-product-head">
                                    <span className="fit-product-image fit-lot-product-image large">
                                        <img src={viewProduct.image} alt={viewProduct.name} />
                                    </span>
                                    <div>
                                        <h3>{viewProduct.name}</h3>
                                        <div className="fit-lot-tags">
                                            <code className="fit-code fit-product-sku">{viewProduct.sku}</code>
                                            <span className="fit-role-badge default"><i className="ri-price-tag-3-line" /> {viewProduct.category?.name || 'Sin categoria'}</span>
                                        </div>
                                    </div>
                                </div>

                                <div className="fit-lot-stat-grid">
                                    <div><span>Stock actual</span><strong>{viewProduct.current_stock}</strong></div>
                                    <div><span>Stock minimo</span><strong>{viewProduct.minimum_stock || 'No definido'}</strong></div>
                                    <div><span>Total lotes</span><strong>{viewProduct.lots_count}</strong></div>
                                    <div><span>Proximo vencimiento</span><strong>{viewProduct.next_expiry}</strong></div>
                                </div>

                                <div className="fit-lot-panel">
                                    <h4>Caracteristicas del producto</h4>
                                    <p>{viewProduct.description}</p>
                                </div>
                            </div>

                            <div className="fit-lot-detail-grid">
                                <div className="fit-lot-panel">
                                    <h4>Historial de lotes</h4>
                                    <div className="fit-table-scroll">
                                        <table className="fit-users-table fit-lot-history-table">
                                            <thead>
                                                <tr>
                                                    <th>Codigo</th>
                                                    <th>Stock</th>
                                                    <th>Bodega</th>
                                                    <th>Vence</th>
                                                    <th className="text-right">Accion</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {viewProduct.history_rows?.length ? viewProduct.history_rows.map((row) => (
                                                    <tr key={row.id}>
                                                        <td><code className="fit-code">{row.code}</code></td>
                                                        <td>{row.quantity}</td>
                                                        <td><span className="fit-muted-text">{row.warehouse}</span></td>
                                                        <td>{row.expires_at}</td>
                                                        <td className="text-right">
                                                            <button type="button" className="fit-action-button warning" onClick={() => setEditLot(row)} title="Editar lote">
                                                                <i className="ri-pencil-line" />
                                                            </button>
                                                        </td>
                                                    </tr>
                                                )) : <TableEmpty colSpan={5} text="Sin lotes registrados." />}
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div className="fit-lot-panel">
                                    <h4>Movimientos recientes</h4>
                                    <div className="fit-lot-movement-list">
                                        {viewProduct.movement_history?.length ? viewProduct.movement_history.map((item, index) => (
                                            <div className="fit-lot-movement-item" key={`${item.lot_code}-${index}`}>
                                                <strong>{`${item.type} - ${item.quantity > 0 ? '+' : ''}${item.quantity}`}</strong>
                                                <span>Lote: {item.lot_code}</span>
                                                <p>{item.note}</p>
                                                <time>{`${item.user} - ${item.date}`}</time>
                                            </div>
                                        )) : <p className="fit-lot-empty">Sin movimientos recientes.</p>}
                                    </div>
                                </div>
                            </div>
                        </div>
                    )}
                </Modal>

                <Modal open={!!editLot} title="Editar Lote" onClose={() => setEditLot(null)} contentClassName="fit-modal-content">
                    {editLot && (
                        <form method="POST" action={editLot.action} className="fit-register-form">
                            <input type="hidden" name="_token" value={csrfToken} />
                            <div className="fit-form-grid">
                                <div className="fit-form-field span-2">
                                    <label htmlFor="edit_lote_code">Codigo de lote</label>
                                    <input id="edit_lote_code" type="text" name="lote_code" defaultValue={editLot.code === 'Sin codigo' ? '' : editLot.code} />
                                </div>
                                <div className="fit-form-field">
                                    <label htmlFor="edit_expires_at">Fecha de expiracion *</label>
                                    <input id="edit_expires_at" type="date" name="expires_at" defaultValue={editLot.raw_expires_at || ''} required />
                                </div>
                                <div className="fit-form-field">
                                    <label htmlFor="edit_quantity">Cantidad total *</label>
                                    <input id="edit_quantity" type="number" name="quantity" defaultValue={editLot.quantity || 0} required />
                                </div>
                            </div>

                            <div className="fit-modal-footer">
                                <button type="button" className="fit-outline-button" onClick={() => setEditLot(null)}>Cancelar</button>
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
