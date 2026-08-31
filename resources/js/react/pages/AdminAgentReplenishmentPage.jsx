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
        <div className="agent-runtime-status" aria-live="polite">
            <div className="agent-runtime-label"><i className="ri-pulse-line" /> Funcionamiento continuo</div>
            <strong>{runningMinutes}</strong>
            <span>minutos activo</span>
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
        <nav className="agent-navigation" aria-label="Secciones del agente">
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

export default function AdminAgentReplenishmentPage({ layout, data, flash, csrfToken, logoutAction }) {
    const [requestModal, setRequestModal] = useState(null);
    const [alertModal, setAlertModal] = useState(null);
    const [activeView, setActiveView] = useState('overview');
    const statusClass = data.agentOnline ? 'online' : 'offline';
    const decisionClass = (severity) => severity === 'critical' ? 'urgent' : severity;

    useEffect(() => {
        const root = document.querySelector('.agent-workspace');
        if (!root) return;
        root.querySelector('.agent-search-island')?.setAttribute('data-agent-view', 'overview');
        root.querySelector('.agent-grid')?.setAttribute('data-agent-view', 'overview');
        ['evaluations', 'requests', 'alerts', 'history'].forEach((view, index) => {
            root.querySelectorAll('.agent-section')[index]?.setAttribute('data-agent-view', view);
        });
    }, []);

    return (
        <DashboardShell sidebar={layout.sidebar} topbar={layout.topbar} csrfToken={csrfToken} logoutAction={logoutAction}>
            <div className={`agent-workspace agent-view-${activeView}`}>
            <FlashMessages flash={flash} />
            <AgentNavigation activeView={activeView} onChange={setActiveView} data={data} />
            <div className="agent-hero"><div><h2 style={{ margin: 0, color: '#fff' }}>Reposicion inteligente</h2><p style={{ margin: '0.25rem 0 0', color: 'rgba(255,255,255,0.75)' }}>Evaluaciones del agente para anticipar faltantes y aprobar traspasos con control humano.</p>{data.error && <p style={{ margin: '0.55rem 0 0', color: '#fecdd3' }}>{data.error}</p>}</div><div className="agent-toolbar"><span className={`status-pill ${statusClass}`}><i className={data.agentOnline ? 'ri-checkbox-circle-line' : 'ri-close-circle-line'} />{data.agentOnline ? 'Agente en linea' : 'Agente sin conexion'}</span><span className="agent-auto-chip"><i className="ri-refresh-line" /> Monitoreo automatico</span><AgentRuntimeStatus data={data} /></div></div>
            <div className="card agent-search-island"><div className="chart-head"><div><h4>Buscar productos evaluados</h4><span className="section-kicker">Filtra evaluaciones, alertas, solicitudes pendientes e historial por nombre, SKU o categoria.</span></div><a className="pill-button" target="_blank" rel="noopener" href={data.routes.report}><i className="ri-file-pdf-line" /> Reporte PDF</a></div><form method="GET" action={data.routes.index} className="form-grid" style={{ marginTop: '1rem' }}><div className="form-group"><label>Producto, SKU o categoria</label><input type="text" name="search" className="input-ghost" defaultValue={data.search || ''} /></div><div className="form-group"><label>Categoria</label><select name="category_id" className="select-light" defaultValue={data.categoryId || ''}><option value="">Todas</option>{data.categories.map((category) => <option key={category.id} value={category.id}>{category.name}</option>)}</select></div><div className="form-group" style={{ alignSelf: 'flex-end' }}><button type="submit" className="pill-button">Buscar</button></div><div className="form-group" style={{ alignSelf: 'flex-end' }}><a href={data.routes.index} className="clean-link">Limpiar</a></div></form></div>
            <div className="agent-grid">{[{ label: 'Productos evaluados', value: data.forecastsTotal, text: 'Analisis de demanda para los proximos 7 dias.' }, { label: 'Alertas de stock bajo', value: data.alerts.low_stock?.length || 0, text: 'Productos que pueden quedar por debajo del nivel seguro.' }, { label: 'Lotes vencidos o por vencer', value: data.alerts.expiring?.length || 0, text: 'Inventario vencido o con fecha de vencimiento cercana.' }, { label: 'Solicitudes por revisar', value: data.pendingRequestsTotal, text: 'Recomendaciones esperando aprobacion o rechazo.' }].map((card) => <div className="card" key={card.label}><h3>{card.label}</h3><div className="value">{card.value}</div><p className="agent-card-label">{card.text}</p></div>)}</div>
            <div className="card agent-section"><div className="chart-head"><div><h4>Evaluaciones de reposicion</h4><span className="section-kicker">Demanda, stock disponible y decision recomendada por producto.</span></div><span className="chip chip-muted">{data.forecasts.total} registros</span></div><div className="table-wrapper"><table className="data-table"><thead><tr><th>Producto</th><th>Demanda 7 dias</th><th>Stock actual</th><th>Traspasos previstos</th><th>Stock final estimado</th><th>Stock minimo</th><th>Decision</th></tr></thead><tbody>{data.forecasts.data.length ? data.forecasts.data.map((item, index) => <tr key={`${item.name}-${index}`} className={String(item.priority || '').toLowerCase() === 'urgente' ? 'urgent-row' : ''}><td>{item.name}</td><td>{Number(item.forecast_7_days).toFixed(0)} uds</td><td>{item.stock} uds</td><td>{item.in_transit} uds</td><td>{item.result < 0 ? `Faltan ${Math.abs(item.result)} uds` : `${Number(item.result).toFixed(0)} uds`}</td><td>{item.safety_threshold} uds</td><td><span className={`decision-chip ${String(item.priority || '').toLowerCase() === 'urgente' ? 'urgent' : ''}`}><i className={String(item.priority || '').toLowerCase() === 'urgente' ? 'ri-alarm-warning-line' : 'ri-lightbulb-flash-line'} />{item.decision}{String(item.priority || '').toLowerCase() === 'urgente' ? ' - Urgente' : ''}</span></td></tr>) : <TableEmpty colSpan={7} text="Sin evaluaciones del agente." />}</tbody></table></div><div className="agent-pagination"><Pagination pagination={data.forecasts} /></div></div>
            <div className="card agent-section"><div className="chart-head"><div><h4>Solicitudes pendientes del agente</h4><span className="section-kicker">Aprobacion humana antes de crear o confirmar el traspaso operativo.</span></div><span className="chip chip-muted">{data.pendingRequests.total} pendientes</span></div><div className="table-wrapper"><table className="data-table"><thead><tr><th>Producto</th><th>Cantidad solicitada</th><th>Prioridad</th><th>Motivo resumido</th><th>Detalle</th></tr></thead><tbody>{data.pendingRequests.data.length ? data.pendingRequests.data.map((request) => { const urgent = String(request.priority || '').toLowerCase() === 'urgente'; const parsed = request.parsedReason; return <tr key={request.id} className={urgent ? 'urgent-row' : ''}><td>{request.product_name}</td><td>{request.requested_qty} uds</td><td><span className={`decision-chip ${urgent ? 'urgent' : ''}`}>{request.priority}</span></td><td style={{ maxWidth: '460px' }}>{parsed ? <div className="reason-summary"><p><strong>Reposicion necesaria.</strong>{parsed.result < 0 ? <> Faltan <strong>{Math.abs(parsed.result)} uds</strong> para completar la demanda prevista y mantener el stock minimo.</> : <> Quedarian <strong>{parsed.result} uds</strong>, por debajo del stock minimo.</>}</p><div className="reason-metrics"><span className="metric-chip">Stock: {parsed.stock} uds</span><span className="metric-chip warn">Stock minimo: {parsed.threshold} uds</span></div></div> : (request.reason || 'El agente recomienda revisar este producto.')}</td><td><button type="button" className="btn-secondary" onClick={() => setRequestModal(request)}>Ver detalles</button></td></tr>; }) : <TableEmpty colSpan={5} text="No hay solicitudes pendientes del agente." />}</tbody></table></div><div className="agent-pagination"><Pagination pagination={data.pendingRequests} /></div></div>
            <div className="card agent-section"><div className="chart-head"><div><h4>Alertas por producto</h4><span className="section-kicker">Revisa stock bajo, lotes por vencer y lotes vencidos por producto.</span></div><span className="chip chip-muted">Verde normal - Amarillo menor a 5 meses - Rojo menor a 2 meses - Morado vencido</span></div><div className="alert-product-grid">{data.alertProductCards.length ? data.alertProductCards.map((productAlert) => <div className={`alert-product-card ${productAlert.severity}`} key={productAlert.id}><div className="alert-card-head"><div className="alert-product-title"><img src={productAlert.image} alt={productAlert.name} /><div><h4>{productAlert.name}</h4><span className="section-kicker">SKU: {productAlert.sku || 'N/D'} - {productAlert.category}</span></div></div><span className={`decision-chip ${decisionClass(productAlert.severity)}`}>{productAlert.severity_label}</span></div><div className="metric-row" style={{ justifyContent: 'flex-start' }}>{Object.entries(productAlert.metrics || {}).map(([label, value]) => <span key={label} className={`metric-chip ${label.toLowerCase().includes('faltante') ? 'danger' : label.toLowerCase().includes('minimo') ? 'warn' : ''}`}>{label}: {value}</span>)}</div><div className="alert-card-list">{(productAlert.problems || []).slice(0, 2).map((item, index) => <div className="alert-card-item" key={`${productAlert.id}-problem-${index}`}><strong>{item.label}</strong><p>{item.message}</p></div>)}</div><div className="alert-card-actions"><button type="button" className="btn-secondary" onClick={() => setAlertModal(productAlert)}>Detalles</button></div></div>) : <div className="alert-product-card"><h4 style={{ margin: 0 }}>Sin alertas operativas</h4><p style={{ margin: 0, color: 'rgba(255,255,255,0.7)' }}>No hay productos criticos en este momento.</p></div>}</div></div>
            <div className="card agent-section"><div className="chart-head"><div><h4>Historial de decisiones</h4><span className="section-kicker">Solicitudes creadas, aprobadas o rechazadas por el flujo del agente.</span></div><span className="chip chip-muted">{data.recentRequests.total} registros</span></div><div className="table-wrapper"><table className="data-table"><thead><tr><th>Fecha</th><th>Producto</th><th>Cantidad</th><th>Estado</th><th>Decision humana</th><th>Traspaso relacionado</th></tr></thead><tbody>{data.recentRequests.data.length ? data.recentRequests.data.map((request) => <tr key={request.id}><td>{request.created_at_formatted}</td><td>{request.product_name}</td><td>{request.requested_qty} uds</td><td>{request.status}</td><td>{request.decision_label}</td><td>{request.transfer_label}</td></tr>) : <TableEmpty colSpan={6} text="Sin historial de solicitudes del agente." />}</tbody></table></div><div className="agent-pagination"><Pagination pagination={data.recentRequests} /></div></div>
            <Modal open={!!requestModal} title={requestModal ? `Solicitud de traspaso #${requestModal.id}` : 'Solicitud'} onClose={() => setRequestModal(null)} wide>{requestModal && (() => { const urgent = String(requestModal.priority || '').toLowerCase() === 'urgente'; const parsed = requestModal.parsedReason; const stock = parsed?.stock ?? 0; const transfers = parsed?.transfers ?? 0; const demand = parsed?.demand ?? 0; const result = parsed?.result ?? null; const threshold = parsed?.threshold ?? 0; const missing = result !== null && result < 0 ? Math.abs(result) : 0; const scale = Math.max(stock, transfers, demand, threshold, missing, 1); const pct = (value) => Math.min(100, Math.round((value / scale) * 100)); return <div className="modal-body"><div className="summary"><div className="summary-card"><strong>Cantidad solicitada</strong><span>{requestModal.requested_qty} uds</span></div><div className="summary-card"><strong>Prioridad</strong><span className={`decision-chip ${urgent ? 'urgent' : ''}`}>{requestModal.priority}</span></div><div className="summary-card"><strong>Estado</strong><span>{requestModal.status}</span></div><div className="summary-card"><strong>Creada</strong><span>{requestModal.created_at_formatted}</span></div></div><div className="agent-detail-section"><h4 style={{ margin: '0 0 0.75rem' }}>Detalle de reposicion</h4>{parsed ? <><p style={{ margin: '0 0 1rem', color: 'rgba(255,255,255,0.78)' }}><strong>Reposicion necesaria.</strong>{missing > 0 ? <> Faltan <strong>{missing} unidades</strong> para completar la demanda prevista de 7 dias y mantener el stock minimo.</> : <> Despues de cubrir la demanda prevista quedarian <strong>{result} uds</strong>, por debajo del stock minimo.</>}</p><div className="agent-bars">{[{ label: 'Stock actual', value: stock, pctClass: '' }, { label: 'Traspasos previstos', value: transfers, pctClass: '' }, { label: 'Demanda 7 dias', value: demand, pctClass: 'warn' }, { label: 'Stock minimo', value: threshold, pctClass: 'warn' }, ...(missing > 0 ? [{ label: 'Unidades faltantes', value: missing, pctClass: 'danger' }] : [])].map((row) => <div className="agent-bar-row" key={row.label}><div className="agent-bar-head"><span>{row.label}</span><span>{row.value} uds</span></div><div className="agent-bar-track"><div className={`agent-bar-fill ${row.pctClass}`} style={{ width: `${pct(row.value)}%` }} /></div></div>)}</div></> : <p style={{ margin: 0, color: 'rgba(255,255,255,0.78)' }}>{requestModal.reason || 'El agente recomienda revisar este producto.'}</p>}</div><div className="agent-detail-section"><h4 style={{ margin: '0 0 0.75rem' }}>Decision humana</h4><div className="agent-decision-actions"><div className="agent-decision-card"><h4>Aprobar traspaso</h4><form method="POST" action={requestModal.approve_url}><input type="hidden" name="_token" value={csrfToken} /><input type="text" name="decision_reason" className="input-ghost" placeholder="Motivo de aprobacion" /><button type="submit" className="pill-button">Aprobar traspaso</button></form></div><div className="agent-decision-card"><h4>Rechazar solicitud</h4><form method="POST" action={requestModal.reject_url}><input type="hidden" name="_token" value={csrfToken} /><input type="text" name="decision_reason" className="input-ghost" placeholder="Motivo de rechazo" /><button type="submit" className="pill-button ghost">Rechazar traspaso</button></form></div></div></div></div>; })()}</Modal>
            <Modal open={!!alertModal} title={alertModal?.name || 'Alerta'} onClose={() => setAlertModal(null)} wide>{alertModal && <div className="modal-body"><div className="summary"><div className="summary-card"><strong>SKU</strong>{alertModal.sku || 'N/D'}</div><div className="summary-card"><strong>Categoria</strong>{alertModal.category}</div><div className="summary-card"><strong>Estado</strong>{alertModal.severity_label}</div></div><div className="agent-detail-section"><h4 style={{ margin: '0 0 0.75rem' }}>Problemas detectados</h4><div className="alert-card-list">{alertModal.problems.map((problem, index) => <div className="alert-card-item" key={`${alertModal.id}-modal-problem-${index}`}><strong>{problem.label}</strong><p>{problem.message}</p>{problem.meta && <div className="metric-row" style={{ justifyContent: 'flex-start', marginTop: '0.55rem' }}>{Object.entries(problem.meta).map(([label, value]) => <span key={label} className={`metric-chip ${label.toLowerCase().includes('faltante') ? 'danger' : label.toLowerCase().includes('minimo') ? 'warn' : ''}`}>{label}: {value}</span>)}</div>}</div>)}</div></div><div className="agent-detail-section"><h4 style={{ margin: '0 0 0.75rem' }}>Lotes del producto</h4><div className="agent-lot-list">{alertModal.lots?.length ? alertModal.lots.map((lot, index) => <div className={`agent-lot-row ${lot.status}`} key={`${lot.code}-${index}`}><div><strong>{lot.label} - {lot.code}</strong><p>{lot.message}</p></div><div className="metric-row"><span className="metric-chip">Cantidad: {lot.quantity} uds</span><span className={`metric-chip ${lot.status === 'expired' ? 'danger' : lot.status === 'warning' ? 'warn' : ''}`}>Vence: {lot.expires_at}</span></div></div>) : <div className="alert-card-item"><strong>Sin lotes activos</strong><p>No hay lotes con cantidad disponible para este producto.</p></div>}</div></div></div>}</Modal>
            </div>
        </DashboardShell>
    );
}
