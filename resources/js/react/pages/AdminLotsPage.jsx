import React, { useState } from 'react';
import DashboardShell from '../components/admin/DashboardShell';
import { FieldError, FlashMessages, Modal, Pagination, TableEmpty } from '../components/admin/common';

export default function AdminLotsPage({ layout, data, flash, errors, old, csrfToken, logoutAction }) {
    const [viewProduct, setViewProduct] = useState(null);
    const [editLot, setEditLot] = useState(null);

    return (
        <DashboardShell sidebar={layout.sidebar} topbar={layout.topbar} csrfToken={csrfToken} logoutAction={logoutAction}>
            <FlashMessages flash={flash} />
            {data.modalError && <div className="card"><span className="chip">{data.modalError}</span></div>}
            <div className="card">
                <div className="chart-head"><h4>Crear lote</h4><span className="chip chip-muted">Prueba 1 - Vista por producto</span></div>
                <form method="POST" action={data.routes.store} className="form-grid" id="lotCreateForm">
                    <input type="hidden" name="_token" value={csrfToken} />
                    <div className="form-group"><label>Producto</label><select name="product_id" className="select-light" defaultValue={old?.product_id || ''} required><option value="">Seleccionar</option>{data.products.map((product) => <option key={product.id} value={product.id}>{product.name} ({product.sku})</option>)}</select><FieldError errors={errors} name="product_id" /></div>
                    <div className="form-group"><label>Bodega</label><select name="warehouse_id" className="select-light" defaultValue={old?.warehouse_id || data.filters.warehouse_id || ''} required><option value="">Seleccionar</option>{data.warehouses.map((warehouse) => <option key={warehouse.id} value={warehouse.id}>{warehouse.name}</option>)}</select><FieldError errors={errors} name="warehouse_id" /></div>
                    <div className="form-group"><label>Codigo de lote</label><input type="text" name="lote_code" className="input-ghost" defaultValue={old?.lote_code || ''} /></div>
                    <div className="form-group"><label>Cantidad</label><input type="number" min="1" name="quantity" className="input-ghost" defaultValue={old?.quantity || ''} required /><FieldError errors={errors} name="quantity" /></div>
                    <div className="form-group"><label>Fecha expiracion</label><input type="date" name="expires_at" className="input-ghost" defaultValue={old?.expires_at || ''} required /><FieldError errors={errors} name="expires_at" /></div>
                    <div className="form-group" style={{ alignSelf: 'flex-end' }}><button type="submit" className="pill-button">Guardar lote</button></div>
                </form>
            </div>
            <div className="card" style={{ marginTop: '1rem' }}>
                <div className="chart-head"><h4>Explorar lotes por producto</h4><span className="chip">{data.productsWithLots.total} productos con lotes</span></div>
                <form method="GET" action={data.routes.index} className="lot-filter-bar" style={{ marginTop: '1rem' }}>
                    <div className="form-group"><label>Buscar producto</label><input type="text" name="search" className="input-ghost" defaultValue={data.filters.search || ''} /></div>
                    <div className="form-group"><label>Producto</label><select name="product_id" className="select-light" defaultValue={data.filters.product_id || ''}><option value="">Todos</option>{data.products.map((product) => <option key={product.id} value={product.id}>{product.name}</option>)}</select></div>
                    <div className="form-group"><label>Bodega</label><select name="warehouse_id" className="select-light" defaultValue={data.filters.warehouse_id || ''}><option value="">Todas</option>{data.warehouses.map((warehouse) => <option key={warehouse.id} value={warehouse.id}>{warehouse.name}</option>)}</select></div>
                    <div className="form-group"><label>Vence el</label><input type="date" name="expires_at" className="input-ghost" defaultValue={data.filters.expires_at || ''} /></div>
                    <div className="form-group" style={{ alignSelf: 'flex-end' }}><a href={data.routes.report} className="pill-button" target="_blank" rel="noopener">Generar reporte PDF</a></div>
                    <div className="form-group" style={{ alignSelf: 'flex-end' }}><a href={data.routes.index} className="clean-link">Limpiar</a></div>
                </form>
            </div>
            <div className="lot-product-stack" style={{ marginTop: '1rem' }}>
                {data.productsWithLots.data.length ? data.productsWithLots.data.map((product) => {
                    const critical = Number(product.minimum_stock || 0) > 0 && Number(product.current_stock || 0) <= Number(product.minimum_stock || 0);
                    return (
                        <div className="card lot-product-card" key={product.id}>
                            <div className="lot-product-main">
                                <div className="lot-product-head"><img src={product.image} alt={product.name} className="lot-product-cover" /><div><h3 className="lot-product-title">{product.name}</h3><div className="lot-product-meta"><span className="chip">SKU: {product.sku}</span><span className="chip">{product.category?.name || 'Sin categoria'}</span><span className="chip">{product.lots_count} lote(s)</span></div></div></div>
                                <div className="lot-product-separator" />
                                <div className="lot-stat-grid">
                                    <div className="lot-stat-box"><small>Stock actual</small><strong>{product.current_stock} unidades</strong></div>
                                    <div className="lot-stat-box"><small>Stock minimo</small><strong>{product.minimum_stock || 'No definido'}</strong></div>
                                    <div className="lot-stat-box"><small>Total lotes</small><strong>{product.lots_count}</strong></div>
                                    <div className="lot-stat-box"><small>Proximo vencimiento</small><strong>{product.next_expiry}</strong></div>
                                </div>
                                {critical && <div className="lot-inline-alert"><div><strong>Atencion: este producto necesita reabastecimiento.</strong><div style={{ color: 'rgba(255,255,255,0.76)', marginTop: '0.2rem' }}>El stock actual alcanzo o bajo del minimo configurado.</div></div></div>}
                            </div>
                            <div className="lot-action-panel"><button type="button" className="lot-action-button view" onClick={() => setViewProduct(product)}><i className="ri-stack-line" /> Ver lotes</button></div>
                        </div>
                    );
                }) : <div className="card"><p style={{ margin: 0, textAlign: 'center' }}>No encontramos productos con lotes para esos filtros.</p></div>}
            </div>
            <Pagination pagination={data.productsWithLots} />
            <Modal open={!!viewProduct} title="Detalle de lotes por producto" onClose={() => setViewProduct(null)} wide contentClassName="modal-content--lot-detail">
                {viewProduct && <div className="lot-detail-layout"><div className="lot-detail-column"><div className="lot-detail-panel"><div className="lot-detail-header"><div className="lot-product-head"><img src={viewProduct.image} alt={viewProduct.name} className="lot-product-cover" /><div><h2 className="lot-product-title">{viewProduct.name}</h2><div className="lot-product-meta"><span className="chip">SKU: {viewProduct.sku}</span><span className="chip">{viewProduct.category?.name || 'Sin categoria'}</span><span className="chip">{viewProduct.lots_count} lote(s)</span></div></div></div></div></div><div className="lot-detail-panel"><h4>Estado de inventario</h4><div className="lot-detail-grid"><div className="lot-stat-box"><small>Stock actual</small><strong>{viewProduct.current_stock}</strong></div><div className="lot-stat-box"><small>Stock minimo</small><strong>{viewProduct.minimum_stock || 'No definido'}</strong></div><div className="lot-stat-box"><small>Total lotes</small><strong>{viewProduct.lots_count}</strong></div><div className="lot-stat-box"><small>Proximo vencimiento</small><strong>{viewProduct.next_expiry}</strong></div></div></div><div className="lot-detail-panel"><h4>Caracteristicas del producto</h4><p style={{ margin: 0, color: 'rgba(255,255,255,0.78)' }}>{viewProduct.description}</p></div></div><div className="lot-detail-column"><div className="lot-detail-panel"><h4>Historial de lotes</h4><div className="lot-history-scroll"><table className="lot-history-table"><thead><tr><th>Codigo</th><th>Stock</th><th>Bodega</th><th>Vence</th><th>Accion</th></tr></thead><tbody>{viewProduct.history_rows?.length ? viewProduct.history_rows.map((row) => <tr key={row.id}><td>{row.code}</td><td>{row.quantity}</td><td>{row.warehouse}</td><td>{row.expires_at}</td><td><button type="button" className="btn-secondary" onClick={() => setEditLot(row)}>Editar</button></td></tr>) : <TableEmpty colSpan={5} text="Sin lotes registrados." />}</tbody></table></div></div><div className="lot-detail-panel"><h4>Movimientos recientes</h4><div className="lot-movement-list">{viewProduct.movement_history?.length ? viewProduct.movement_history.map((item, index) => <div className="lot-movement-item" key={`${item.lot_code}-${index}`}><strong>{`${item.type} - ${item.quantity > 0 ? '+' : ''}${item.quantity}`}</strong><div style={{ color: 'rgba(255,255,255,0.78)' }}>Lote: {item.lot_code}</div><div style={{ color: 'rgba(255,255,255,0.62)', marginTop: '0.2rem' }}>{item.note}</div><div style={{ color: 'rgba(255,255,255,0.55)', marginTop: '0.35rem', fontSize: '0.82rem' }}>{`${item.user} - ${item.date}`}</div></div>) : <p className="lot-empty-state">Sin movimientos recientes.</p>}</div></div></div></div>}
            </Modal>
            <Modal open={!!editLot} title="Editar lote" onClose={() => setEditLot(null)}>
                {editLot && <form method="POST" action={editLot.action} className="form-grid" style={{ gridTemplateColumns: '1fr' }}><input type="hidden" name="_token" value={csrfToken} /><div className="form-group"><label>Codigo de lote</label><input type="text" name="lote_code" className="input-ghost" defaultValue={editLot.code === 'Sin codigo' ? '' : editLot.code} /></div><div className="form-group"><label>Fecha de expiracion</label><input type="date" name="expires_at" className="input-ghost" defaultValue={editLot.raw_expires_at || ''} required /></div><div className="form-group"><label>Cantidad total del lote</label><input type="number" name="quantity" className="input-ghost" defaultValue={editLot.quantity || 0} required /></div><div style={{ display: 'flex', justifyContent: 'flex-end', gap: '0.5rem' }}><button type="button" className="btn-secondary" onClick={() => setEditLot(null)}>Cancelar</button><button type="submit" className="pill-button">Guardar</button></div></form>}
            </Modal>
        </DashboardShell>
    );
}
