import React from 'react';
import DashboardShell from '../components/admin/DashboardShell';
import { FlashMessages, TableEmpty } from '../components/admin/common';

const saleTypeLabels = {
    empresa_institucional: 'Empresa institucional',
    tienda_barrio: 'Tienda de barrio',
    comprador_minorista: 'Comprador minorista',
};

export default function AdminQuotationShowPage({ layout, data, flash, csrfToken, logoutAction }) {
    const quotation = data.quotation;
    const client = quotation.company || quotation.customer;
    const totalQty = quotation.items.reduce((carry, item) => carry + Number(item.qty || 0), 0);

    return (
        <DashboardShell sidebar={layout.sidebar} topbar={layout.topbar} csrfToken={csrfToken} logoutAction={logoutAction}>
            <div className="fit-users-page fit-quotations-page fit-quotation-show-page">
                <FlashMessages flash={flash} />

                <section className="fit-users-header print-hide">
                    <div className="fit-users-header-left">
                        <div className="fit-header-icon"><i className="ri-file-list-3-line" /></div>
                        <div>
                            <h1>Cotizacion #{quotation.id}</h1>
                            <p>Detalle completo de cliente, vigencia comercial y productos cotizados.</p>
                        </div>
                    </div>

                    <div className="fit-users-header-actions">
                        <a href={data.routes.index} className="fit-outline-button">
                            <i className="ri-arrow-left-line" />
                            <span>Volver a cotizaciones</span>
                        </a>
                        <a className="fit-primary-button" target="_blank" rel="noopener noreferrer" href={quotation.pdf_url}>
                            <i className="ri-file-download-line" />
                            <span>Descargar PDF</span>
                        </a>
                    </div>
                </section>

                <main className="fit-quotation-show-layout fit-quotation-print-area">
                    <section className="fit-section fit-quotation-show-main">
                        <div className="fit-quotation-show-heading">
                            <div>
                                <span>Proforma comercial</span>
                                <h4>Cotizacion #{quotation.id}</h4>
                                <p>Valido hasta {quotation.valid_until_formatted || 'Sin fecha'}</p>
                            </div>
                            <strong>Bs {Number(quotation.total_amount).toFixed(2)}</strong>
                        </div>

                        <div className="fit-transfer-summary fit-quotation-summary">
                            <div><span>Cliente</span><strong>{client?.name || 'Cliente'}</strong></div>
                            <div><span>Tipo</span><strong>{saleTypeLabels[quotation.sale_type] || quotation.sale_type}</strong></div>
                            <div><span>Valido hasta</span><strong>{quotation.valid_until_formatted || '-'}</strong></div>
                            <div><span>Vendedor</span><strong>{quotation.seller?.name || '-'}</strong></div>
                            <div><span>Total</span><strong className="fit-sale-amount">Bs {Number(quotation.total_amount).toFixed(2)}</strong></div>
                        </div>

                        <div className="fit-transfer-panel">
                            <h4>Datos comerciales</h4>
                            <div className="fit-quotation-info-grid">
                                <div><span>Vendedor</span><strong>{quotation.seller?.name || '-'}</strong></div>
                                <div><span>Ciudad</span><strong>{client?.city || '-'}</strong></div>
                                <div className="span-2"><span>Notas</span><strong>{quotation.notes || 'Sin notas registradas.'}</strong></div>
                            </div>
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
                                        {quotation.items?.length ? quotation.items.map((item, index) => (
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

                    <aside className="fit-quotation-show-side">
                        <span>Resumen</span>
                        <h2>Proforma comercial</h2>
                        <div className="fit-quotation-side-row">
                            <span>Productos</span>
                            <strong>{quotation.items.length}</strong>
                        </div>
                        <div className="fit-quotation-side-row">
                            <span>Unidades</span>
                            <strong>{totalQty}</strong>
                        </div>
                        <div className="fit-quotation-side-row">
                            <span>Total</span>
                            <strong>Bs {Number(quotation.total_amount).toFixed(2)}</strong>
                        </div>
                        <button type="button" className="fit-outline-button print-hide" onClick={() => window.print()}>
                            <i className="ri-printer-line" />
                            <span>Imprimir</span>
                        </button>
                    </aside>
                </main>
            </div>
        </DashboardShell>
    );
}
