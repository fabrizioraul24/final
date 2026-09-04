import React, { useState } from 'react';
import DashboardShell from '../components/admin/DashboardShell';
import { FlashMessages, Modal, Pagination, TableEmpty } from '../components/admin/common';

function ProductImage({ product }) {
    return (
        <span className="fit-product-image">
            <img src={product.image_url} alt={product.name} />
        </span>
    );
}

function ProductStatus({ active }) {
    return (
        <span className={`fit-status ${active ? 'active' : 'inactive'}`}>
            <span /> {active ? 'Activo' : 'Inactivo'}
        </span>
    );
}

function ProductsTable({ products, inactive = false, csrfToken, onView, onEdit, onToggle }) {
    return (
        <div className="fit-table-card">
            <div className="fit-table-scroll">
                <table className="fit-users-table fit-products-table">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>SKU</th>
                            <th>Categoria</th>
                            <th>Precio publico</th>
                            <th>Institucional</th>
                            <th>Stock</th>
                            <th>Estado</th>
                            <th className="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        {products.length ? products.map((product) => (
                            <tr key={product.id} className={inactive ? 'is-muted' : ''}>
                                <td>
                                    <div className="fit-user-cell fit-product-cell">
                                        <ProductImage product={product} />
                                        <div>
                                            <strong>{product.name}</strong>
                                        </div>
                                    </div>
                                </td>
                                <td><code className="fit-code fit-product-sku">{product.sku}</code></td>
                                <td><span className="fit-muted-text">{product.category?.name || 'Sin categoria'}</span></td>
                                <td><strong className="fit-money">Bs {Number(product.suggested_price_public).toFixed(2)}</strong></td>
                                <td><strong className="fit-money">Bs {Number(product.price_institutional).toFixed(2)}</strong></td>
                                <td><span className="fit-muted-text">{product.stock_total} uds</span></td>
                                <td><ProductStatus active={product.is_active} /></td>
                                <td className="text-right">
                                    <div className="fit-row-actions">
                                        <button type="button" className="fit-action-button success" onClick={() => onView(product)} title="Ver detalle">
                                            <i className="ri-eye-line" />
                                        </button>
                                        {!inactive && (
                                            <button type="button" className="fit-action-button warning" onClick={() => onEdit(product)} title="Editar">
                                                <i className="ri-pencil-line" />
                                            </button>
                                        )}
                                        <button type="button" className={`fit-action-button ${inactive ? 'success' : 'danger'}`} onClick={() => onToggle(product)} title={inactive ? 'Activar' : 'Desactivar'}>
                                            <i className={inactive ? 'ri-refresh-line' : 'ri-prohibited-line'} />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        )) : (
                            <TableEmpty colSpan={8} text={inactive ? 'No hay productos desactivados.' : 'No hay productos con los filtros aplicados.'} />
                        )}
                    </tbody>
                </table>
            </div>
        </div>
    );
}

export default function AdminProductsPage({ layout, data, flash, errors, old, csrfToken, logoutAction }) {
    const [viewingProduct, setViewingProduct] = useState(null);
    const [productToToggle, setProductToToggle] = useState(null);
    const [activeMetric, setActiveMetric] = useState('all');

    const showActive = activeMetric === 'all' || activeMetric === 'active';
    const showInactive = activeMetric === 'all' || activeMetric === 'inactive';
    const hasFilters = data.filters.search || data.filters.category_id || activeMetric !== 'all';

    const metricCards = [
        { key: 'all', label: 'Catalogo Total', value: data.stats.catalog, hint: 'Haz clic para ver todos', icon: 'ri-shopping-bag-3-line', tone: 'indigo' },
        { key: 'active', label: 'Activos Venta', value: data.stats.active, hint: 'Disponibles', icon: 'ri-checkbox-circle-line', tone: 'green' },
        { key: 'inactive', label: 'En Pausa', value: data.stats.inactive, hint: 'Fuera de catalogo', icon: 'ri-pause-circle-line', tone: 'rose' },
        { key: 'categories', label: 'Categorias', value: data.categories.length, hint: 'Clasificacion', icon: 'ri-price-tag-3-line', tone: 'amber' },
    ];

    const handleMetricClick = (key) => {
        setActiveMetric(key === 'categories' ? 'all' : key);
    };

    return (
        <DashboardShell sidebar={layout.sidebar} topbar={layout.topbar} csrfToken={csrfToken} logoutAction={logoutAction}>
            <div className="fit-users-page fit-products-page">
                <FlashMessages flash={flash} />

                {data.predictionError && (
                    <div className="fit-filter-card">
                        <span className="fit-status inactive"><span /> Prediccion IA no disponible: {data.predictionError}</span>
                    </div>
                )}

                <section className="fit-users-header">
                    <div className="fit-users-header-left">
                        <div className="fit-header-icon"><i className="ri-shopping-bag-3-line" /></div>
                        <div>
                            <h1>Catalogo y Metricas de Productos</h1>
                            <p>Filtra, administra precios, revisa inventario y exporta el catalogo completo.</p>
                        </div>
                    </div>

                    <div className="fit-users-header-actions">
                        <a className="fit-outline-button" target="_blank" rel="noopener noreferrer" href={data.routes.report}>
                            <i className="ri-download-2-line" />
                            <span>Descargar Reporte PDF</span>
                        </a>
                        <a className="fit-primary-button" href={data.routes.create}>
                            <i className="ri-add-box-line" />
                            <span>Crear Producto</span>
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

                <section className="fit-filter-card">
                    <form className="fit-filter-form" method="GET" action={data.routes.index}>
                        <label className="fit-search-control" htmlFor="search">
                            <i className="ri-search-line" />
                            <input id="search" type="text" name="search" placeholder="Buscar nombre, SKU o descripcion..." defaultValue={data.filters.search || ''} />
                        </label>

                        <label className="fit-select-control" htmlFor="category_id">
                            <i className="ri-filter-3-line" />
                            <select id="category_id" name="category_id" defaultValue={data.filters.category_id || ''}>
                                <option value="">Todas las categorias</option>
                                {data.categories.map((category) => <option key={category.id} value={category.id}>{category.name}</option>)}
                            </select>
                        </label>

                        <button type="submit" className="fit-primary-button compact">
                            <i className="ri-search-line" /> Buscar
                        </button>

                        {hasFilters && <a href={data.routes.index} className="fit-clear-button">Limpiar Filtros</a>}
                    </form>
                </section>

                {showActive && (
                    <section className="fit-section">
                        <div className="fit-section-head">
                            <div>
                                <h2>Productos Activos para Venta</h2>
                                <p>Catalogo disponible para ventas, cotizaciones y abastecimiento.</p>
                            </div>
                            <span className="fit-section-badge green">{data.activeProducts.total} activos</span>
                        </div>
                        <ProductsTable
                            products={data.activeProducts.data}
                            csrfToken={csrfToken}
                            onView={setViewingProduct}
                            onEdit={(product) => { window.location.href = product.edit_url; }}
                            onToggle={setProductToToggle}
                        />
                        <Pagination pagination={data.activeProducts} />
                    </section>
                )}

                {showInactive && (
                    <section className="fit-section">
                        <div className="fit-section-head">
                            <div>
                                <h2>Productos Desactivados</h2>
                                <p>Productos en pausa que pueden volver al catalogo cuando sea necesario.</p>
                            </div>
                            <span className="fit-section-badge rose">{data.inactiveProducts.total} inactivos</span>
                        </div>
                        <ProductsTable
                            products={data.inactiveProducts.data}
                            inactive
                            csrfToken={csrfToken}
                            onView={setViewingProduct}
                            onEdit={(product) => { window.location.href = product.edit_url; }}
                            onToggle={setProductToToggle}
                        />
                        <Pagination pagination={data.inactiveProducts} />
                    </section>
                )}

                <Modal open={!!viewingProduct} title="Detalle del Producto" onClose={() => setViewingProduct(null)} wide contentClassName="fit-modal-content">
                    {viewingProduct && (
                        <div className="fit-product-detail">
                            <div className="fit-product-detail-media">
                                <img src={viewingProduct.image_url} alt={viewingProduct.name} />
                            </div>
                            <div className="fit-product-detail-body">
                                <div className="fit-product-detail-head">
                                    <div>
                                        <span>SKU {viewingProduct.sku}</span>
                                        <strong>{viewingProduct.name}</strong>
                                    </div>
                                    <ProductStatus active={viewingProduct.is_active} />
                                </div>

                                <div className="fit-company-detail-grid">
                                    <div><span>Categoria</span><strong>{viewingProduct.category?.name || 'Sin categoria'}</strong></div>
                                    <div><span>Stock total</span><strong>{viewingProduct.stock_total} uds</strong></div>
                                    <div><span>Precio publico</span><strong>Bs {Number(viewingProduct.suggested_price_public).toFixed(2)}</strong></div>
                                    <div><span>Precio institucional</span><strong>Bs {Number(viewingProduct.price_institutional).toFixed(2)}</strong></div>
                                    <div><span>Stock minimo</span><strong>{viewingProduct.min_quantity} uds</strong></div>
                                    <div><span>Stock maximo</span><strong>{viewingProduct.max_quantity} uds</strong></div>
                                    <div className="span-2"><span>Descripcion</span><strong>{viewingProduct.description || 'Sin descripcion'}</strong></div>
                                </div>
                            </div>
                        </div>
                    )}
                </Modal>

                <Modal open={!!productToToggle} title={productToToggle?.is_active ? 'Desactivar Producto' : 'Activar Producto'} onClose={() => setProductToToggle(null)} contentClassName="fit-modal-content">
                    {productToToggle && (
                        <form method="POST" action={productToToggle.toggle_url} className="fit-confirm-form">
                            <input type="hidden" name="_token" value={csrfToken} />
                            <input type="hidden" name="_method" value="PATCH" />
                            <div className="fit-confirm-icon"><i className={productToToggle.is_active ? 'ri-prohibited-line' : 'ri-refresh-line'} /></div>
                            <h4>{productToToggle.is_active ? `Desactivar ${productToToggle.name}?` : `Activar ${productToToggle.name}?`}</h4>
                            <p>{productToToggle.is_active ? 'El producto dejara de estar disponible en el catalogo.' : 'El producto volvera a estar disponible en el catalogo.'}</p>
                            <div className="fit-modal-footer">
                                <button type="button" className="fit-outline-button" onClick={() => setProductToToggle(null)}>Cancelar</button>
                                <button type="submit" className={`fit-primary-button ${productToToggle.is_active ? 'danger' : ''}`}>
                                    <i className={productToToggle.is_active ? 'ri-prohibited-line' : 'ri-refresh-line'} /> {productToToggle.is_active ? 'Desactivar' : 'Activar'}
                                </button>
                            </div>
                        </form>
                    )}
                </Modal>
            </div>
        </DashboardShell>
    );
}
