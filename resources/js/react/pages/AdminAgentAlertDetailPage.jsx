import React from 'react';
import DashboardShell from '../components/admin/DashboardShell';
import { FlashMessages, TableEmpty } from '../components/admin/common';
import { AgentModeSwitch } from './AdminAgentReplenishmentPage';

function formatUnits(value) {
    return `${new Intl.NumberFormat('es-BO').format(Number(value || 0))} uds`;
}

function SeverityBadge({ severity, label }) {
    const tone = severity === 'critical' || severity === 'expired' ? 'urgent' : severity === 'warning' ? 'preventive' : 'optimal';

    return <span className={`fit-agent-decision ${tone}`}><i className="ri-alarm-warning-line" />{label || 'Alerta'}</span>;
}

function DetailMetric({ label, value, tone = '' }) {
    return (
        <div className={`summary-card ${tone}`}>
            <strong>{label}</strong>
            <span>{value}</span>
        </div>
    );
}

function ProgressBar({ label, value, max, tone = '' }) {
    const percent = Math.max(3, Math.min(100, Math.round((Number(value || 0) / Math.max(Number(max || 1), 1)) * 100)));

    return (
        <div className="agent-alert-progress-row">
            <div className="agent-alert-progress-head">
                <span>{label}</span>
                <strong>{formatUnits(value)}</strong>
            </div>
            <div className="agent-alert-progress-track">
                <div className={`agent-alert-progress-fill ${tone}`} style={{ width: `${percent}%` }} />
            </div>
        </div>
    );
}

export default function AdminAgentAlertDetailPage({ layout, data, flash, csrfToken, logoutAction }) {
    const product = data.product;
    const alert = data.alert || {};
    const forecast = data.forecast || {};
    const requests = data.requests || [];
    const stock = Number(forecast.stock ?? 0);
    const demand = Number(forecast.forecast_7_days ?? 0);
    const transit = Number(forecast.in_transit ?? 0);
    const result = Number(forecast.result ?? (stock + transit - demand));
    const minimum = Number(forecast.safety_threshold ?? product.min_quantity ?? 0);
    const lots = alert.lots || [];
    const problems = alert.problems || [];
    const shortage = Math.max(0, minimum - result);
    const suggested = Number(requests[0]?.requested_qty ?? 0);
    const coverageWeeks = demand > 0 ? result / demand : 0;
    const scale = Math.max(stock, demand, transit, result, minimum, product.max_quantity, suggested, 1);
    const statusLabel = shortage > 0 ? 'Reposicion necesaria' : 'Stock cubierto';
    const statusText = shortage > 0
        ? `Luego de cubrir la demanda quedarian ${formatUnits(result)}, por debajo del minimo ${formatUnits(minimum)}.`
        : `Luego de cubrir la demanda quedarian ${formatUnits(result)}, por encima del minimo ${formatUnits(minimum)}.`;

    return (
        <DashboardShell sidebar={layout.sidebar} topbar={layout.topbar} csrfToken={csrfToken} logoutAction={logoutAction}>
            <div className="fit-users-page fit-agent-page agent-alert-detail-page">
                <FlashMessages flash={flash} />

                <section className="fit-users-header">
                    <div className="fit-users-header-left">
                        <div className="fit-header-icon"><i className="ri-alert-line" /></div>
                        <div>
                            <h1>Detalle de Alerta</h1>
                            <p>Revision completa del producto, demanda prevista, stock, lotes y solicitudes del agente.</p>
                            <AgentModeSwitch mode={data.agentMode || 'replenishment'} data={data} />
                        </div>
                    </div>
                    <div className="fit-users-header-actions fit-agent-header-actions">
                        <a className="fit-outline-button" href={data.routes.index_replenishment}>
                            <i className="ri-arrow-left-line" />
                            <span>Volver al agente</span>
                        </a>
                    </div>
                </section>

                <section className="fit-section agent-alert-hero">
                    <div className="agent-alert-hero-grid">
                        <div className="agent-alert-product-panel">
                            <div className="alert-product-title">
                                <img src={product.image} alt={product.name} />
                                <div>
                                    <span className="section-kicker">Producto observado</span>
                                    <h2>{product.name}</h2>
                                    <div className="metric-row">
                                        <span className="metric-chip">SKU: {product.sku}</span>
                                        <span className="metric-chip">{product.category}</span>
                                        <span className="metric-chip">{product.is_active ? 'Activo' : 'Inactivo'}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div className={`agent-alert-verdict ${shortage > 0 ? 'danger' : 'ok'}`}>
                            <SeverityBadge severity={alert.severity} label={alert.severity_label} />
                            <strong>{statusLabel}</strong>
                            <p>{statusText}</p>
                            <div className="agent-alert-formula">
                                <span>{formatUnits(stock)}</span>
                                <b>+</b>
                                <span>{formatUnits(transit)}</span>
                                <b>-</b>
                                <span>{formatUnits(demand)}</span>
                                <b>=</b>
                                <strong>{formatUnits(result)}</strong>
                            </div>
                        </div>
                    </div>
                </section>

                <section className="fit-section agent-section">
                    <div className="fit-section-head">
                        <div>
                            <h2>Lectura Rapida</h2>
                            <p>Resumen para entender si el producto necesita reposicion.</p>
                        </div>
                        <span className="fit-section-badge indigo">Cobertura: {coverageWeeks.toFixed(1)} semanas</span>
                    </div>
                    <div className="summary">
                        <DetailMetric label="Stock actual" value={formatUnits(stock)} tone={shortage > 0 ? 'rose' : 'green'} />
                        <DetailMetric label="Demanda 7 dias" value={formatUnits(demand)} tone="amber" />
                        <DetailMetric label="Stock final estimado" value={formatUnits(result)} tone={shortage > 0 ? 'rose' : 'green'} />
                        <DetailMetric label="Faltante contra minimo" value={formatUnits(shortage)} tone={shortage > 0 ? 'rose' : 'green'} />
                    </div>
                    <div className="agent-alert-bars">
                        <ProgressBar label="Stock actual" value={stock} max={scale} tone="green" />
                        <ProgressBar label="Demanda prevista 7 dias" value={demand} max={scale} tone="amber" />
                        <ProgressBar label="Stock minimo operativo" value={minimum} max={scale} tone="rose" />
                        <ProgressBar label="Reposicion sugerida" value={suggested} max={scale} tone="blue" />
                    </div>
                </section>

                <section className="fit-section agent-section">
                    <div className="fit-section-head">
                        <div>
                            <h2>Recomendacion del Agente</h2>
                            <p>Accion sugerida antes de que el producto quede por debajo del nivel seguro.</p>
                        </div>
                        <span className="fit-section-badge green">{forecast.decision || 'Revision operativa'}</span>
                    </div>
                    <div className="agent-alert-recommendation">
                        <div>
                            <span>Reposicion sugerida</span>
                            <strong>{formatUnits(suggested)}</strong>
                            <p>{requests[0]?.status ? `Solicitud ${requests[0].status.toLowerCase()} registrada el ${requests[0].created_at_formatted}.` : 'No existe solicitud pendiente para este producto.'}</p>
                        </div>
                        <div>
                            <span>Motivo operativo</span>
                            <strong>{shortage > 0 ? 'Evitar quiebre de stock' : 'Mantener observacion'}</strong>
                            <p>{shortage > 0 ? `El faltante contra minimo es ${formatUnits(shortage)} despues de la demanda esperada.` : 'El stock final estimado cubre el minimo actual.'}</p>
                        </div>
                    </div>
                </section>

                <section className="fit-section agent-section">
                    <div className="fit-section-head">
                        <div>
                            <h2>Por Que Aparece</h2>
                            <p>Motivos detectados por el agente en stock, demanda o vencimientos.</p>
                        </div>
                        <span className="fit-section-badge amber">{problems.length} hallazgo(s)</span>
                    </div>
                    <div className="alert-card-list">
                        {problems.length ? problems.map((problem, index) => (
                            <div className="alert-card-item" key={`${problem.label}-${index}`}>
                                <strong>{problem.label}</strong>
                                <p>{problem.message}</p>
                                {problem.meta && (
                                    <div className="metric-row">
                                        {Object.entries(problem.meta).map(([label, value]) => (
                                            <span key={label} className="metric-chip">{label}: {value}</span>
                                        ))}
                                    </div>
                                )}
                            </div>
                        )) : (
                            <div className="alert-card-item">
                                <strong>Sin problemas activos</strong>
                                <p>El producto no registra alertas operativas en este momento.</p>
                            </div>
                        )}
                    </div>
                </section>

                <section className="fit-section agent-section">
                    <div className="fit-section-head">
                        <div>
                            <h2>Lotes Para Revisar</h2>
                            <p>Unidades disponibles por lote y fecha de vencimiento.</p>
                        </div>
                        <span className="fit-section-badge indigo">{lots.length} lote(s)</span>
                    </div>
                    <div className="agent-lot-list">
                        {lots.length ? lots.map((lot, index) => (
                            <div className={`agent-lot-row ${lot.status}`} key={`${lot.code}-${index}`}>
                                <div>
                                    <strong>{lot.label} - {lot.code}</strong>
                                    <p>{lot.message}</p>
                                </div>
                                <div className="metric-row">
                                    <span className="metric-chip">Cantidad: {formatUnits(lot.quantity)}</span>
                                    <span className="metric-chip warn">Vence: {lot.expires_at}</span>
                                </div>
                            </div>
                        )) : (
                            <div className="alert-card-item">
                                <strong>Sin lotes activos</strong>
                                <p>No hay lotes disponibles para mostrar.</p>
                            </div>
                        )}
                    </div>
                </section>

                <section className="fit-section agent-section">
                    <div className="fit-section-head">
                        <div>
                            <h2>Solicitudes Relacionadas</h2>
                            <p>Historial del agente para este producto.</p>
                        </div>
                        <span className="fit-section-badge green">{requests.length} solicitud(es)</span>
                    </div>
                    <div className="fit-table-card">
                        <div className="fit-table-scroll">
                            <table className="fit-users-table fit-agent-table">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Reposicion sugerida</th>
                                        <th>Estado</th>
                                        <th>Decision humana</th>
                                        <th>Traspaso relacionado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {requests.length ? requests.map((request) => (
                                        <tr key={request.id}>
                                            <td><span className="fit-muted-text">{request.created_at_formatted}</span></td>
                                            <td><strong>{formatUnits(request.requested_qty)}</strong></td>
                                            <td><span className="fit-muted-text">{request.status}</span></td>
                                            <td><span className="fit-muted-text">{request.decision_label}</span></td>
                                            <td><span className="fit-muted-text">{request.transfer_label}</span></td>
                                        </tr>
                                    )) : <TableEmpty colSpan={5} text="Sin solicitudes relacionadas." />}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>
            </div>
        </DashboardShell>
    );
}
