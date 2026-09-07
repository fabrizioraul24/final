import React from 'react';
import DashboardShell from '../components/admin/DashboardShell';
import { FlashMessages, TableEmpty } from '../components/admin/common';
import { AgentRequestBreakdown, TransferStatus, TransferSource } from './AdminTransfersPage';

export default function AdminTransferShowPage({ layout, data, flash, csrfToken, logoutAction }) {
    const transfer = data.transfer;

    return (
        <DashboardShell sidebar={layout.sidebar} topbar={layout.topbar} csrfToken={csrfToken} logoutAction={logoutAction}>
            <div className="fit-users-page fit-transfers-page fit-transfer-show-page">
                <FlashMessages flash={flash} />

                <section className="fit-users-header">
                    <div className="fit-users-header-left">
                        <div className="fit-header-icon"><i className="ri-arrow-left-right-line" /></div>
                        <div>
                            <h1>Traspaso #{transfer.id}</h1>
                            <p>{transfer.fromWarehouse?.name || 'Sin origen'} hacia {transfer.toWarehouse?.name || 'Sin destino'}.</p>
                        </div>
                    </div>

                    <div className="fit-users-header-actions">
                        <a className="fit-outline-button" href={data.routes.index}>
                            <i className="ri-arrow-left-line" />
                            <span>Volver a traspasos</span>
                        </a>
                        <a className="fit-primary-button" target="_blank" rel="noopener noreferrer" href={data.routes.report}>
                            <i className="ri-file-download-line" />
                            <span>Generar Reporte PDF</span>
                        </a>
                    </div>
                </section>

                <section className="fit-transfer-show-hero">
                    <div className="fit-transfer-show-title">
                        <span className="fit-section-badge indigo">Movimiento interno</span>
                        <h2>{transfer.fromWarehouse?.name || 'Sin origen'} a {transfer.toWarehouse?.name || 'Sin destino'}</h2>
                        <p>Creado el {transfer.created_at_formatted}. Fecha estimada de llegada: {transfer.expected_date_formatted}.</p>
                    </div>
                    <TransferStatus status={transfer.status} />
                </section>

                <section className="fit-transfer-summary">
                    <div><span>Estado</span><TransferStatus status={transfer.status} /></div>
                    <div><span>Fecha estimada</span><strong>{transfer.expected_date_formatted}</strong></div>
                    <div><span>Solicitado por</span><strong>{transfer.requested_by_label}</strong></div>
                    <div><span>Aprobado por</span><strong>{transfer.agentRequest ? transfer.approved_by_label : '-'}</strong></div>
                    <div><span>Productos</span><strong>{transfer.items_count} item(s)</strong></div>
                </section>

                <div className="fit-transfer-show-layout">
                    <section className="fit-section fit-transfer-show-main">
                        <div className="fit-section-head">
                            <div>
                                <h2>Productos Transferidos</h2>
                                <p>Cantidades solicitadas, recibidas, danadas y notas por item.</p>
                            </div>
                            <TransferSource transfer={transfer} />
                        </div>

                        <div className="fit-table-card">
                            <div className="fit-table-scroll">
                                <table className="fit-users-table fit-transfer-items-table">
                                    <thead>
                                        <tr>
                                            <th>Producto</th>
                                            <th>SKU</th>
                                            <th>Solicitado</th>
                                            <th>Recibido</th>
                                            <th>Danado</th>
                                            <th>Notas</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {transfer.items.length ? transfer.items.map((item, index) => (
                                            <tr key={`${item.sku}-${index}`}>
                                                <td><strong>{item.product_name}</strong></td>
                                                <td><code className="fit-code fit-product-sku">{item.sku}</code></td>
                                                <td>{item.requested_qty} uds</td>
                                                <td>{item.received_qty} uds</td>
                                                <td>{item.damaged_qty} uds</td>
                                                <td><span className="fit-muted-text">{item.notes}</span></td>
                                            </tr>
                                        )) : <TableEmpty colSpan={6} text="Sin productos registrados." />}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>

                    <aside className="fit-transfer-show-side">
                        <div className="fit-transfer-panel">
                            <h4>Origen de solicitud</h4>
                            <AgentRequestBreakdown transfer={transfer} />
                        </div>

                        <div className="fit-transfer-panel">
                            <h4>{transfer.agentRequest ? 'Nota de aprobacion' : 'Notas generales'}</h4>
                            <p>
                                {transfer.agentRequest
                                    ? (transfer.agentRequest.decision_reason || 'El usuario aprobo la sugerencia del agente sin agregar una nota adicional.')
                                    : (transfer.notes || 'Sin notas generales.')}
                            </p>
                        </div>
                    </aside>
                </div>
            </div>
        </DashboardShell>
    );
}
