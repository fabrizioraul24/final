import React, { useState } from 'react';
import DashboardShell from '../components/admin/DashboardShell';
import { FieldError, FlashMessages, Modal, Pagination, StatsGrid, TableEmpty } from '../components/admin/common';

function statusPill(status, label) {
    return <span className={`status-pill ${status}`}>{label}</span>;
}

export default function AdminProductsPage({ layout, data, flash, errors, old, csrfToken, logoutAction }) {
    const [editingProduct, setEditingProduct] = useState(null);
    const [viewingProduct, setViewingProduct] = useState(null);
    const stats = [
        { label: 'Productos en catalogo', value: data.stats.catalog, chip: 'Total registrados', chipClass: 'chip-muted' },
        { label: 'Activos para venta', value: data.stats.active, chip: 'Disponibles', chipClass: 'chip-success' },
        { label: 'En pausa', value: data.stats.inactive, chip: 'Revision' },
    ];

    const renderForm = (product = null) => (
        <div className="product-form-grid">
            <div className="product-form-section">
                <div className="product-form-section__head">
                    <h5>Datos comerciales</h5>
                    <p>Define identidad, categoría y precios del producto.</p>
                </div>
                <div className="product-form-section__grid">
                    <div className="form-group">
                        <label>Categoria</label>
                        <select name="category_id" className="select-light" defaultValue={product?.category_id ?? old?.category_id ?? ''} required>
                            <option value="">Selecciona</option>
                            {data.categories.map((category) => <option key={category.id} value={category.id}>{category.name}</option>)}
                        </select>
                    </div>
                    <div className="form-group"><label>Nombre</label><input type="text" name="name" className="input-ghost" defaultValue={product?.name ?? old?.name ?? ''} required /></div>
                    <div className="form-group"><label>SKU / Codigo</label><input type="text" name="sku" className="input-ghost" defaultValue={product?.sku ?? old?.sku ?? ''} required /></div>
                    <div className="form-group product-form-span-2"><label>{product ? 'Imagen nueva (opcional)' : 'Imagen del producto'}</label><input type="file" name="image" className="input-ghost" accept="image/*" required={!product} /></div>
                    <div className="form-group"><label>Precio publico sugerido</label><input type="number" step="0.01" min="0" name="suggested_price_public" className="input-ghost" defaultValue={product?.suggested_price_public ?? old?.suggested_price_public ?? ''} required /></div>
                    <div className="form-group"><label>Precio institucional</label><input type="number" step="0.01" min="0" name="price_institutional" className="input-ghost" defaultValue={product?.price_institutional ?? old?.price_institutional ?? ''} required /></div>
                </div>
            </div>
            <div className="product-form-section">
                <div className="product-form-section__head">
                    <h5>Inventario y estado</h5>
                    <p>Configura límites de stock, descripción y visibilidad.</p>
                </div>
                <div className="product-form-section__grid product-form-section__grid--compact">
                    <div className="form-group"><label>Stock minimo</label><input type="number" min="0" name="min_quantity" className="input-ghost" defaultValue={product?.min_quantity ?? old?.min_quantity ?? 0} required /></div>
                    <div className="form-group"><label>Stock maximo</label><input type="number" min="0" name="max_quantity" className="input-ghost" defaultValue={product?.max_quantity ?? old?.max_quantity ?? 0} required /></div>
                    <div className="form-group"><label>Estado</label><select name="is_active" className="select-light" defaultValue={String(product ? (product.is_active ? 1 : 0) : (old?.is_active ?? 1))} required><option value="1">Activo</option><option value="0">Inactivo</option></select></div>
                    <div className="form-group product-form-span-2"><label>Descripcion</label><textarea name="description" className="input-ghost product-form-textarea" rows="4" defaultValue={product?.description ?? old?.description ?? ''} /></div>
                </div>
            </div>
        </div>
    );

    return (
        <DashboardShell sidebar={layout.sidebar} topbar={layout.topbar} csrfToken={csrfToken} logoutAction={logoutAction}>
            <FlashMessages flash={flash} />
            {data.predictionError && <div className="card"><span className="chip"><i className="ri-alert-line" /> Prediccion IA no disponible: {data.predictionError}</span></div>}
            <StatsGrid items={stats} />
            <div className="card product-form-card">
                <div className="chart-head product-form-head"><div><h4>Crear producto</h4><span className="section-kicker">Formulario redistribuido para capturar datos sin que todo quede pegado a la izquierda.</span></div></div>
                <form method="POST" action={data.routes.store} className="product-form-shell" encType="multipart/form-data">
                    <input type="hidden" name="_token" value={csrfToken} />
                    {renderForm()}
                    <div className="product-form-actions"><button type="submit" className="pill-button">Guardar producto</button></div>
                </form>
                {['category_id', 'name', 'sku', 'image', 'suggested_price_public', 'price_institutional', 'min_quantity', 'max_quantity', 'description'].map((field) => <FieldError key={field} errors={errors} name={field} />)}
            </div>
            <div className="card">
                <div className="chart-head"><h4>Filtrar catalogo</h4><a className="pill-button" target="_blank" rel="noopener" href={data.routes.report}>Generar catalogo PDF</a></div>
                <form className="form-grid" method="GET" action={data.routes.index}>
                    <div className="form-group"><label>Buscar por nombre, SKU o descripcion</label><input type="text" name="search" className="input-ghost" defaultValue={data.filters.search || ''} /></div>
                    <div className="form-group"><label>Categoria</label><select name="category_id" className="select-light" defaultValue={data.filters.category_id || ''}><option value="">Todas</option>{data.categories.map((category) => <option key={category.id} value={category.id}>{category.name}</option>)}</select></div>
                    <div className="form-group" style={{ alignSelf: 'flex-end' }}><a href={data.routes.index} className="clean-link">Limpiar</a></div>
                </form>
            </div>
            {[
                ['activeProducts', 'Productos activos', true],
                ['inactiveProducts', 'Productos desactivados', false],
            ].map(([key, title, active]) => (
                <div className="card" key={key}>
                    <div className="chart-head"><h4>{title}</h4><span className="chip">{data[key].total} registros</span></div>
                    <div className="table-wrapper">
                        <table className="data-table">
                            <thead><tr><th>Producto</th><th>SKU</th><th>Categoria</th><th>Precio publico</th><th>Precio institucional</th><th>Estado</th><th>Acciones</th></tr></thead>
                            <tbody>
                                {data[key].data.length ? data[key].data.map((product) => (
                                    <tr key={product.id}>
                                        <td><div style={{ display: 'flex', alignItems: 'center', gap: '0.8rem' }}><img src={product.image_url} alt={product.name} style={{ width: '52px', height: '52px', objectFit: 'cover', borderRadius: '1rem', border: '1px solid rgba(255,255,255,0.1)' }} /><div><strong>{product.name}</strong><p style={{ margin: 0, fontSize: '0.8rem', color: 'rgba(255,255,255,0.7)' }}>{product.description_excerpt}</p></div></div></td>
                                        <td>{product.sku}</td>
                                        <td>{product.category?.name || 'Sin categoria'}</td>
                                        <td>Bs {Number(product.suggested_price_public).toFixed(2)}</td>
                                        <td>Bs {Number(product.price_institutional).toFixed(2)}</td>
                                        <td>{statusPill(active ? 'active' : 'inactive', active ? 'Activo' : 'Inactivo')}</td>
                                        <td><div className="actions"><button type="button" className="btn-secondary" onClick={() => setViewingProduct(product)}>Detalles</button>{active && <button type="button" className="btn-secondary" onClick={() => setEditingProduct(product)}>Editar</button>}<form method="POST" action={product.toggle_url}><input type="hidden" name="_token" value={csrfToken} /><input type="hidden" name="_method" value="PATCH" /><button type="submit" className={active ? 'btn-danger' : 'btn-secondary'}>{active ? 'Desactivar' : 'Activar'}</button></form></div></td>
                                    </tr>
                                )) : <TableEmpty colSpan={7} text={active ? 'No hay productos con los filtros aplicados.' : 'No hay productos desactivados.'} />}
                            </tbody>
                        </table>
                    </div>
                    <Pagination pagination={data[key]} />
                </div>
            ))}
            <Modal open={!!editingProduct} title="Editar producto" onClose={() => setEditingProduct(null)} wide contentClassName="modal-content--product-edit">
                {editingProduct && <form method="POST" action={editingProduct.update_url} encType="multipart/form-data"><input type="hidden" name="_token" value={csrfToken} /><input type="hidden" name="_method" value="PUT" />{renderForm(editingProduct)}<div style={{ marginTop: '1.2rem', display: 'flex', justifyContent: 'flex-end', gap: '0.8rem' }}><button type="button" className="btn-secondary" onClick={() => setEditingProduct(null)}>Cancelar</button><button type="submit" className="pill-button">Guardar cambios</button></div></form>}
            </Modal>
            <Modal open={!!viewingProduct} title="Detalle del producto" onClose={() => setViewingProduct(null)} wide contentClassName="modal-content--product-detail">
                {viewingProduct && <div className="product-detail-layout"><div className="product-detail-media"><img src={viewingProduct.image_url} alt={viewingProduct.name} className="product-detail-image" /></div><div className="product-detail-body"><div className="product-detail-title-row"><div><p className="product-detail-kicker">SKU {viewingProduct.sku}</p><h2>{viewingProduct.name}</h2></div><span className={`status-pill ${viewingProduct.is_active ? 'active' : 'inactive'}`}>{viewingProduct.status_label}</span></div><div className="badge-grid"><span className="chip">Categoria: <strong>{viewingProduct.category?.name || 'Sin categoria'}</strong></span><span className="chip">Stock: <strong>{viewingProduct.stock_total}</strong> uds</span></div><div className="product-detail-section"><h4>Precios</h4><div className="product-detail-grid"><div className="product-detail-field"><span>Publico sugerido</span><strong>Bs {Number(viewingProduct.suggested_price_public).toFixed(2)}</strong></div><div className="product-detail-field"><span>Institucional</span><strong>Bs {Number(viewingProduct.price_institutional).toFixed(2)}</strong></div></div></div><div className="product-detail-section"><h4>Inventario</h4><div className="product-detail-grid"><div className="product-detail-field"><span>Stock minimo</span><strong>{viewingProduct.min_quantity} uds</strong></div><div className="product-detail-field"><span>Stock maximo</span><strong>{viewingProduct.max_quantity} uds</strong></div></div></div><div className="product-detail-section"><h4>Descripcion</h4><p className="product-detail-description">{viewingProduct.description || 'Sin descripcion'}</p></div></div></div>}
            </Modal>
        </DashboardShell>
    );
}
