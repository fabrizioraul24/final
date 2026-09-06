import React from 'react';
import DashboardShell from '../components/admin/DashboardShell';
import { FlashMessages, TableEmpty } from '../components/admin/common';

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

export default function AdminSaleShowPage({ layout, data, flash, csrfToken, logoutAction }) {
    const sale = data.sale;
    const client = sale.company || sale.customer;
    const totalQty = sale.items.reduce((carry, item) => carry + Number(item.qty || 0), 0);

    return (
        <DashboardShell sidebar={layout.sidebar} topbar={layout.topbar} csrfToken={csrfToken} logoutAction={logoutAction}>
            <div className="fit-users-page fit-sales-page fit-sale-show-page">
                <FlashMessages flash={flash} />

                <section className="fit-users-header print-hide">
                    <div className="fit-users-header-left">
                        <div className="fit-header-icon"><i className="ri-file-list-3-line" /></div>
                        <div>
                            <h1>Venta #{sale.id}</h1>
                            <p>Detalle completo de la venta, entrega, pago y productos vendidos.</p>
                        </div>
                    </div>

                    <div className="fit-users-header-actions">
                        <a href={data.routes.index} className="fit-outline-button">
                            <i className="ri-arrow-left-line" />
                            <span>Volver a ventas</span>
                        </a>
                        <button type="button" className="fit-primary-button" onClick={() => window.print()}>
                            <i className="ri-printer-line" />
                            <span>Imprimir</span>
                        </button>
                    </div>
                </section>

                <main className="fit-sale-show-layout fit-sale-print-area">
                    <section className="fit-section fit-sale-show-main">
                        <div className="fit-sale-report-heading">
                            <div>
                                <span>Reporte oficial</span>
                                <h4>Venta #{sale.id}</h4>
                                <p>{sale.created_at_formatted}</p>
                            </div>
                            <strong>Bs {Number(sale.total_amount).toFixed(2)}</strong>
                        </div>

                        <div className="fit-transfer-summary fit-sale-summary fit-sale-report-summary">
                            <div><span>Cliente</span><strong>{client?.name || 'Sin cliente'}</strong></div>
                            <div><span>Tipo</span><strong>{saleTypeLabels[sale.sale_type] || sale.sale_type}</strong></div>
                            <div><span>Estado</span><strong>{statusLabels[sale.status] || sale.status}</strong></div>
                            <div><span>Pago</span><strong>{sale.payment_label}</strong></div>
                            <div><span>Vendedor</span><strong>{sale.seller?.name || '-'}</strong></div>
                        </div>

                        <div className="fit-transfer-panel">
                            <h4>Entrega</h4>
                            <div className="fit-sale-report-info">
                                <div><span>Almacen</span><strong>{sale.warehouse?.name || '-'}</strong></div>
                                <div><span>Ciudad</span><strong>{sale.delivery_city || '-'}</strong></div>
                                <div><span>Direccion</span><strong>{sale.delivery_address || 'Sin direccion registrada.'}</strong></div>
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
                                        {sale.items.length ? sale.items.map((item, index) => (
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
                    </section>

                    <aside className="fit-sale-show-side">
                        <span>Resumen</span>
                        <h2>Venta comercial</h2>
                        <div className="fit-sale-create-summary-row">
                            <span>Estado</span>
                            <SaleStatus status={sale.status} />
                        </div>
                        <div className="fit-sale-create-summary-row">
                            <span>Productos</span>
                            <strong>{sale.items.length}</strong>
                        </div>
                        <div className="fit-sale-create-summary-row">
                            <span>Unidades</span>
                            <strong>{totalQty}</strong>
                        </div>
                        <div className="fit-sale-create-summary-row">
                            <span>Total</span>
                            <strong>Bs {Number(sale.total_amount).toFixed(2)}</strong>
                        </div>
                    </aside>
                </main>
            </div>
        </DashboardShell>
    );
}
