import React, { useEffect, useMemo, useState } from 'react';
import DashboardShell from '../components/admin/DashboardShell';
import { FlashMessages, Modal, Pagination, TableEmpty } from '../components/admin/common';

function AgentRuntimeStatus({ data }) {
    const [status, setStatus] = useState({
        agentOnline: data.agentOnline,
        lastRunAtIso: data.lastRunAtIso,
        startedAtIso: data.startedAtIso,
    });
    const [, setTick] = useState(0);

    useEffect(() => {
        const refresh = async () => {
            try {
                const response = await fetch(data.routes.status, { headers: { Accept: 'application/json' } });
                if (response.ok) {
                    const payload = await response.json();
                    setStatus((current) => ({ ...current, ...payload }));
                }
            } catch {
                // El indicador conserva el ultimo estado conocido si la consulta falla.
            }
        };

        refresh();
        const refreshTimer = window.setInterval(refresh, 30000);
        const clockTimer = window.setInterval(() => setTick((value) => value + 1), 60000);

        return () => {
            window.clearInterval(refreshTimer);
            window.clearInterval(clockTimer);
        };
    }, [data.routes.status]);

    const runningMinutes = useMemo(() => {
        if (!status.startedAtIso) return 0;
        return Math.max(0, Math.floor((Date.now() - new Date(status.startedAtIso).getTime()) / 60000));
    }, [status.startedAtIso]);

    const lastRunLabel = status.lastRunAtIso
        ? new Date(status.lastRunAtIso).toLocaleString('es-BO', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' })
        : data.lastRunAt;

    return (
        <div className="fit-agent-runtime" aria-live="polite">
            <span><i className="ri-pulse-line" /> Funcionamiento continuo</span>
            <strong>{runningMinutes} min</strong>
            <small>Ultima ejecucion: {lastRunLabel}</small>
        </div>
    );
}

function AgentNavigation({ activeView, onChange, data }) {
    const items = [
        { id: 'overview', label: 'Resumen', icon: 'ri-dashboard-line' },
        { id: 'evaluations', label: 'Evaluaciones', icon: 'ri-line-chart-line', count: data.forecastsTotal },
        { id: 'requests', label: 'Solicitudes', icon: 'ri-inbox-line', count: data.pendingRequestsTotal },
        { id: 'alerts', label: 'Alertas', icon: 'ri-alarm-warning-line', count: (data.alerts.low_stock?.length || 0) + (data.alerts.expiring?.length || 0) },
        { id: 'history', label: 'Historial', icon: 'ri-history-line', count: data.recentRequestsTotal },
    ];

    return (
        <nav className="fit-agent-tabs" aria-label="Secciones del agente">
            {items.map((item) => (
                <button type="button" key={item.id} className={activeView === item.id ? 'active' : ''} onClick={() => onChange(item.id)}>
                    <i className={item.icon} />
                    <span>{item.label}</span>
                    {item.count !== undefined && <b>{item.count}</b>}
                </button>
            ))}
        </nav>
    );
}

function DecisionChip({ urgent, children, icon }) {
    return (
        <span className={`fit-agent-decision ${urgent ? 'urgent' : ''}`}>
            {icon && <i className={icon} />}
            {children}
        </span>
    );
}

function MetricCards({ data }) {
    const cards = [
        { label: 'Productos Evaluados', value: data.forecastsTotal, hint: 'Demanda a 7 dias', icon: 'ri-line-chart-line', tone: 'indigo' },
        { label: 'Stock Bajo', value: data.alerts.low_stock?.length || 0, hint: 'Nivel seguro', icon: 'ri-alarm-warning-line', tone: 'amber' },
        { label: 'Lotes por Vencer', value: data.alerts.expiring?.length || 0, hint: 'Control operativo', icon: 'ri-calendar-close-line', tone: 'rose' },
        { label: 'Por Revisar', value: data.pendingRequestsTotal, hint: 'Aprobacion humana', icon: 'ri-inbox-line', tone: 'green' },
    ];

    return (
        <section className="fit-metric-grid agent-grid" data-agent-view="overview">
            {cards.map((card) => (
                <div className={`fit-metric-card ${card.tone}`} key={card.label}>
                    <span>
                        <small>{card.label}</small>
                        <strong>{card.value}</strong>
                        <em>{card.hint}</em>
                    </span>
                    <span className="fit-metric-icon"><i className={card.icon} /></span>
                </div>
            ))}
        </section>
    );
}

function SearchPanel({ data }) {
    const hasFilters = Boolean(data.search || data.categoryId);

    return (
        <section className="fit-filter-card agent-search-island" data-agent-view="overview">
            <div className="fit-section-head">
                <div>
                    <h2>Buscar productos evaluados</h2>
                    <p>Filtra evaluaciones, alertas, solicitudes pendientes e historial por nombre, SKU o categoria.</p>
                </div>
                <a className="fit-outline-button" target="_blank" rel="noopener noreferrer" href={data.routes.report}>
                    <i className="ri-file-pdf-line" />
                    <span>Reporte PDF</span>
                </a>
            </div>

            <form method="GET" action={data.routes.index} className="fit-filter-form fit-agent-filter-form">
                <label className="fit-search-control" htmlFor="agent_search">
                    <i className="ri-search-line" />
                    <input id="agent_search" type="text" name="search" placeholder="Producto, SKU o categoria..." defaultValue={data.search || ''} />
                </label>

                <label className="fit-select-control" htmlFor="agent_category">
                    <i className="ri-filter-3-line" />
                    <select id="agent_category" name="category_id" defaultValue={data.categoryId || ''}>
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
    );
}

function EvaluationsSection({ data }) {
    return (
        <section className="fit-section agent-section" data-agent-view="evaluations">
            <div className="fit-section-head">
                <div>
                    <h2>Evaluaciones de Reposicion</h2>
                    <p>Demanda, stock disponible y decision recomendada por producto.</p>
                </div>
                <span className="fit-section-badge indigo">{data.forecasts.total} registros</span>
            </div>

            <div className="fit-table-card">
                <div className="fit-table-scroll">
                    <table className="fit-users-table fit-agent-table">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Demanda 7 dias</th>
                                <th>Stock actual</th>
                                <th>Traspasos previstos</th>
                                <th>Stock final estimado</th>
                                <th>Stock minimo</th>
                                <th>Decision</th>
                            </tr>
                        </thead>
                        <tbody>
                            {data.forecasts.data.length ? data.forecasts.data.map((item, index) => {
                                const urgent = String(item.priority || '').toLowerCase() === 'urgente';

                                return (
                                    <tr key={`${item.name}-${index}`} className={urgent ? 'urgent-row' : ''}>
                                        <td><strong>{item.name}</strong></td>
                                        <td><span className="fit-muted-text">{Number(item.forecast_7_days).toFixed(0)} uds</span></td>
                                        <td><span className="fit-muted-text">{item.stock} uds</span></td>
                                        <td><span className="fit-muted-text">{item.in_transit} uds</span></td>
                                        <td><span className="fit-muted-text">{item.result < 0 ? `Faltan ${Math.abs(item.result)} uds` : `${Number(item.result).toFixed(0)} uds`}</span></td>
                                        <td><span className="fit-muted-text">{item.safety_threshold} uds</span></td>
                                        <td>
                                            <DecisionChip urgent={urgent} icon={urgent ? 'ri-alarm-warning-line' : 'ri-lightbulb-flash-line'}>
                                                {item.decision}{urgent ? ' - Urgente' : ''}
                                            </DecisionChip>
                                        </td>
                                    </tr>
                                );
                            }) : <TableEmpty colSpan={7} text="Sin evaluaciones del agente." />}
                        </tbody>
                    </table>
                </div>
            </div>
            <Pagination pagination={data.forecasts} />
        </section>
    );
}

function RequestsSection({ data, onOpen }) {
    return (
        <section className="fit-section agent-section" data-agent-view="requests">
            <div className="fit-section-head">
                <div>
                    <h2>Solicitudes Pendientes del Agente</h2>
                    <p>Aprobacion humana antes de crear o confirmar el traspaso operativo.</p>
                </div>
                <span className="fit-section-badge rose">{data.pendingRequests.total} pendientes</span>
            </div>

            <div className="fit-table-card">
                <div className="fit-table-scroll">
                    <table className="fit-users-table fit-agent-table">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Cantidad solicitada</th>
                                <th>Prioridad</th>
                                <th>Motivo resumido</th>
                                <th className="text-right">Detalle</th>
                            </tr>
                        </thead>
                        <tbody>
                            {data.pendingRequests.data.length ? data.pendingRequests.data.map((request) => {
                                const urgent = String(request.priority || '').toLowerCase() === 'urgente';
                                const parsed = request.parsedReason;

                                return (
                                    <tr key={request.id} className={urgent ? 'urgent-row' : ''}>
                                        <td><strong>{request.product_name}</strong></td>
                                        <td><span className="fit-muted-text">{request.requested_qty} uds</span></td>
                                        <td><DecisionChip urgent={urgent}>{request.priority}</DecisionChip></td>
                                        <td className="fit-agent-reason-cell">
                                            {parsed ? (
                                                <div className="reason-summary">
                                                    <p>
                                                        <strong>Reposicion necesaria.</strong>
                                                        {parsed.result < 0 ? <> Faltan <strong>{Math.abs(parsed.result)} uds</strong> para completar la demanda prevista y mantener el stock minimo.</> : <> Quedarian <strong>{parsed.result} uds</strong>, por debajo del stock minimo.</>}
                                                    </p>
                                                    <div className="reason-metrics">
                                                        <span className="metric-chip">Stock: {parsed.stock} uds</span>
                                                        <span className="metric-chip warn">Stock minimo: {parsed.threshold} uds</span>
                                                    </div>
                                                </div>
                                            ) : (request.reason || 'El agente recomienda revisar este producto.')}
                                        </td>
                                        <td className="text-right">
                                            <button type="button" className="fit-outline-button" onClick={() => onOpen(request)}>Ver detalles</button>
                                        </td>
                                    </tr>
                                );
                            }) : <TableEmpty colSpan={5} text="No hay solicitudes pendientes del agente." />}
                        </tbody>
                    </table>
                </div>
            </div>
            <Pagination pagination={data.pendingRequests} />
        </section>
    );
}

function AlertsSection({ data, decisionClass, onOpen }) {
    return (
        <section className="fit-section agent-section" data-agent-view="alerts">
            <div className="fit-section-head">
                <div>
                    <h2>Alertas por Producto</h2>
                    <p>Revisa stock bajo, lotes por vencer y lotes vencidos por producto.</p>
                </div>
                <span className="fit-section-badge amber">Control de vencimientos</span>
            </div>

            <div className="alert-product-grid">
                {data.alertProductCards.length ? data.alertProductCards.map((productAlert) => (
                    <div className={`alert-product-card ${productAlert.severity}`} key={productAlert.id}>
                        <div className="alert-card-head">
                            <div className="alert-product-title">
                                <img src={productAlert.image} alt={productAlert.name} />
                                <div>
                                    <h4>{productAlert.name}</h4>
                                    <span className="section-kicker">SKU: {productAlert.sku || 'N/D'} - {productAlert.category}</span>
                                </div>
                            </div>
                            <DecisionChip urgent={decisionClass(productAlert.severity) === 'urgent'}>{productAlert.severity_label}</DecisionChip>
                        </div>

                        <div className="metric-row">
                            {Object.entries(productAlert.metrics || {}).map(([label, value]) => (
                                <span key={label} className={`metric-chip ${label.toLowerCase().includes('faltante') ? 'danger' : label.toLowerCase().includes('minimo') ? 'warn' : ''}`}>{label}: {value}</span>
                            ))}
                        </div>

                        <div className="alert-card-list">
                            {(productAlert.problems || []).slice(0, 2).map((item, index) => (
                                <div className="alert-card-item" key={`${productAlert.id}-problem-${index}`}>
                                    <strong>{item.label}</strong>
                                    <p>{item.message}</p>
                                </div>
                            ))}
                        </div>

                        <div className="alert-card-actions">
                            <button type="button" className="fit-outline-button" onClick={() => onOpen(productAlert)}>Detalles</button>
                        </div>
                    </div>
                )) : (
                    <div className="alert-product-card">
                        <h4>Sin alertas operativas</h4>
                        <p>No hay productos criticos en este momento.</p>
                    </div>
                )}
            </div>
        </section>
    );
}

function HistorySection({ data }) {
    return (
        <section className="fit-section agent-section" data-agent-view="history">
            <div className="fit-section-head">
                <div>
                    <h2>Historial de Decisiones</h2>
                    <p>Solicitudes creadas, aprobadas o rechazadas por el flujo del agente.</p>
                </div>
                <span className="fit-section-badge green">{data.recentRequests.total} registros</span>
            </div>

            <div className="fit-table-card">
                <div className="fit-table-scroll">
                    <table className="fit-users-table fit-agent-table">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Estado</th>
                                <th>Decision humana</th>
                                <th>Traspaso relacionado</th>
                            </tr>
                        </thead>
                        <tbody>
                            {data.recentRequests.data.length ? data.recentRequests.data.map((request) => (
                                <tr key={request.id}>
                                    <td><span className="fit-muted-text">{request.created_at_formatted}</span></td>
                                    <td><strong>{request.product_name}</strong></td>
                                    <td><span className="fit-muted-text">{request.requested_qty} uds</span></td>
                                    <td><DecisionChip>{request.status}</DecisionChip></td>
                                    <td><span className="fit-muted-text">{request.decision_label}</span></td>
                                    <td><span className="fit-muted-text">{request.transfer_label}</span></td>
                                </tr>
                            )) : <TableEmpty colSpan={6} text="Sin historial de solicitudes del agente." />}
                        </tbody>
                    </table>
                </div>
            </div>
            <Pagination pagination={data.recentRequests} />
        </section>
    );
}

function RequestModal({ requestModal, csrfToken, onClose }) {
    if (!requestModal) return null;

    const urgent = String(requestModal.priority || '').toLowerCase() === 'urgente';
    const parsed = requestModal.parsedReason;
    const stock = parsed?.stock ?? 0;
    const transfers = parsed?.transfers ?? 0;
    const demand = parsed?.demand ?? 0;
    const result = parsed?.result ?? null;
    const threshold = parsed?.threshold ?? 0;
    const missing = result !== null && result < 0 ? Math.abs(result) : 0;
    const scale = Math.max(stock, transfers, demand, threshold, missing, 1);
    const pct = (value) => Math.min(100, Math.round((value / scale) * 100));

    const rows = [
        { label: 'Stock actual', value: stock, pctClass: '' },
        { label: 'Traspasos previstos', value: transfers, pctClass: '' },
        { label: 'Demanda 7 dias', value: demand, pctClass: 'warn' },
        { label: 'Stock minimo', value: threshold, pctClass: 'warn' },
        ...(missing > 0 ? [{ label: 'Unidades faltantes', value: missing, pctClass: 'danger' }] : []),
    ];

    return (
        <Modal open title={`Solicitud de traspaso #${requestModal.id}`} onClose={onClose} wide contentClassName="fit-modal-content fit-agent-modal-content">
            <div className="modal-body">
                <div className="summary">
                    <div className="summary-card"><strong>Cantidad solicitada</strong><span>{requestModal.requested_qty} uds</span></div>
                    <div className="summary-card"><strong>Prioridad</strong><DecisionChip urgent={urgent}>{requestModal.priority}</DecisionChip></div>
                    <div className="summary-card"><strong>Estado</strong><span>{requestModal.status}</span></div>
                    <div className="summary-card"><strong>Creada</strong><span>{requestModal.created_at_formatted}</span></div>
                </div>

                <div className="fit-transfer-panel agent-detail-section">
                    <h4>Detalle de reposicion</h4>
                    {parsed ? (
                        <>
                            <p>
                                <strong>Reposicion necesaria.</strong>
                                {missing > 0 ? <> Faltan <strong>{missing} unidades</strong> para completar la demanda prevista de 7 dias y mantener el stock minimo.</> : <> Despues de cubrir la demanda prevista quedarian <strong>{result} uds</strong>, por debajo del stock minimo.</>}
                            </p>
                            <div className="agent-bars">
                                {rows.map((row) => (
                                    <div className="agent-bar-row" key={row.label}>
                                        <div className="agent-bar-head"><span>{row.label}</span><span>{row.value} uds</span></div>
                                        <div className="agent-bar-track"><div className={`agent-bar-fill ${row.pctClass}`} style={{ width: `${pct(row.value)}%` }} /></div>
                                    </div>
                                ))}
                            </div>
                        </>
                    ) : <p>{requestModal.reason || 'El agente recomienda revisar este producto.'}</p>}
                </div>

                <div className="fit-transfer-panel agent-detail-section">
                    <h4>Decision humana</h4>
                    <div className="agent-decision-actions">
                        <div className="agent-decision-card">
                            <h4>Aprobar traspaso</h4>
                            <form method="POST" action={requestModal.approve_url}>
                                <input type="hidden" name="_token" value={csrfToken} />
                                <input type="text" name="decision_reason" className="input-ghost" placeholder="Motivo de aprobacion" />
                                <button type="submit" className="fit-primary-button">Aprobar traspaso</button>
                            </form>
                        </div>
                        <div className="agent-decision-card">
                            <h4>Rechazar solicitud</h4>
                            <form method="POST" action={requestModal.reject_url}>
                                <input type="hidden" name="_token" value={csrfToken} />
                                <input type="text" name="decision_reason" className="input-ghost" placeholder="Motivo de rechazo" />
                                <button type="submit" className="fit-primary-button danger">Rechazar traspaso</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </Modal>
    );
}

function AlertModal({ alertModal, onClose }) {
    if (!alertModal) return null;

    return (
        <Modal open title={alertModal.name || 'Alerta'} onClose={onClose} wide contentClassName="fit-modal-content fit-agent-modal-content">
            <div className="modal-body">
                <div className="summary">
                    <div className="summary-card"><strong>SKU</strong><span>{alertModal.sku || 'N/D'}</span></div>
                    <div className="summary-card"><strong>Categoria</strong><span>{alertModal.category}</span></div>
                    <div className="summary-card"><strong>Estado</strong><span>{alertModal.severity_label}</span></div>
                </div>

                <div className="fit-transfer-panel agent-detail-section">
                    <h4>Problemas detectados</h4>
                    <div className="alert-card-list">
                        {alertModal.problems.map((problem, index) => (
                            <div className="alert-card-item" key={`${alertModal.id}-modal-problem-${index}`}>
                                <strong>{problem.label}</strong>
                                <p>{problem.message}</p>
                                {problem.meta && (
                                    <div className="metric-row">
                                        {Object.entries(problem.meta).map(([label, value]) => (
                                            <span key={label} className={`metric-chip ${label.toLowerCase().includes('faltante') ? 'danger' : label.toLowerCase().includes('minimo') ? 'warn' : ''}`}>{label}: {value}</span>
                                        ))}
                                    </div>
                                )}
                            </div>
                        ))}
                    </div>
                </div>

                <div className="fit-transfer-panel agent-detail-section">
                    <h4>Lotes del producto</h4>
                    <div className="agent-lot-list">
                        {alertModal.lots?.length ? alertModal.lots.map((lot, index) => (
                            <div className={`agent-lot-row ${lot.status}`} key={`${lot.code}-${index}`}>
                                <div>
                                    <strong>{lot.label} - {lot.code}</strong>
                                    <p>{lot.message}</p>
                                </div>
                                <div className="metric-row">
                                    <span className="metric-chip">Cantidad: {lot.quantity} uds</span>
                                    <span className={`metric-chip ${lot.status === 'expired' ? 'danger' : lot.status === 'warning' ? 'warn' : ''}`}>Vence: {lot.expires_at}</span>
                                </div>
                            </div>
                        )) : (
                            <div className="alert-card-item">
                                <strong>Sin lotes activos</strong>
                                <p>No hay lotes con cantidad disponible para este producto.</p>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </Modal>
    );
}

export default function AdminAgentReplenishmentPage({ layout, data, flash, csrfToken, logoutAction }) {
    const [requestModal, setRequestModal] = useState(null);
    const [alertModal, setAlertModal] = useState(null);
    const [activeView, setActiveView] = useState('overview');
    const statusClass = data.agentOnline ? 'active' : 'inactive';
    const decisionClass = (severity) => severity === 'critical' ? 'urgent' : severity;

    return (
        <DashboardShell sidebar={layout.sidebar} topbar={layout.topbar} csrfToken={csrfToken} logoutAction={logoutAction}>
            <div className={`fit-users-page fit-agent-page agent-workspace agent-view-${activeView}`}>
                <FlashMessages flash={flash} />

                <section className="fit-users-header">
                    <div className="fit-users-header-left">
                        <div className="fit-header-icon"><i className="ri-robot-2-line" /></div>
                        <div>
                            <h1>Reposicion Inteligente</h1>
                            <p>Evaluaciones del agente para anticipar faltantes y aprobar traspasos con control humano.</p>
                            {data.error && <p className="fit-agent-error">{data.error}</p>}
                        </div>
                    </div>

                    <div className="fit-users-header-actions fit-agent-header-actions">
                        <span className={`fit-status ${statusClass}`}><span /> {data.agentOnline ? 'Agente en linea' : 'Agente sin conexion'}</span>
                        <span className="fit-section-badge indigo"><i className="ri-refresh-line" /> Monitoreo automatico</span>
                        <AgentRuntimeStatus data={data} />
                    </div>
                </section>

                <AgentNavigation activeView={activeView} onChange={setActiveView} data={data} />
                <SearchPanel data={data} />
                <MetricCards data={data} />
                <EvaluationsSection data={data} />
                <RequestsSection data={data} onOpen={setRequestModal} />
                <AlertsSection data={data} decisionClass={decisionClass} onOpen={setAlertModal} />
                <HistorySection data={data} />

                <RequestModal requestModal={requestModal} csrfToken={csrfToken} onClose={() => setRequestModal(null)} />
                <AlertModal alertModal={alertModal} onClose={() => setAlertModal(null)} />
            </div>
        </DashboardShell>
    );
}
