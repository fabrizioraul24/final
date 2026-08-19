import React, { useState } from 'react';
import DashboardShell from '../components/admin/DashboardShell';
import { FlashMessages, Modal, Pagination, TableEmpty } from '../components/admin/common';

function renderObjectDiff(record = {}) {
    return Object.entries(record).map(([key, value]) => (
        <div key={key} style={{ border: '1px solid rgba(255,255,255,0.12)', borderRadius: '1rem', padding: '0.75rem 1rem' }}>
            <p style={{ margin: 0, fontSize: '0.85rem', color: 'rgba(255,255,255,0.7)' }}>{key}</p>
            <p style={{ margin: '0.2rem 0 0' }}>{String(value ?? '-')}</p>
        </div>
    ));
}

export default function AdminLogsPage({ layout, data, flash, csrfToken, logoutAction }) {
    const [detailLog, setDetailLog] = useState(null);

    return (
        <DashboardShell sidebar={layout.sidebar} topbar={layout.topbar} csrfToken={csrfToken} logoutAction={logoutAction}>
            <FlashMessages flash={flash} />
            <div className="card">
                <div className="chip" style={{ marginBottom: '1rem', display: 'flex', gap: '0.5rem', flexWrap: 'wrap' }}>
                    {data.scopes.map((scope) => (
                        <a key={scope.key} href={scope.url} className="btn-secondary" style={{ textDecoration: 'none', padding: '0.4rem 0.9rem', borderRadius: '999px', background: scope.active ? 'rgba(255,255,255,0.12)' : undefined }}>{scope.label}</a>
                    ))}
                </div>
                <div className="chart-head"><h4>Filtros</h4></div>
                <form method="GET" action={data.routes.index} className="form-grid">
                    <div className="form-group"><label>Usuario (actor)</label><select name="actor_id" className="select-light" defaultValue={data.filters.actor_id || ''}><option value="">Todos</option>{data.actors.map((actor) => <option key={actor.id} value={actor.id}>{actor.name}</option>)}</select></div>
                    <div className="form-group"><label>Entidad</label><select name="entity_type" className="select-light" defaultValue={data.filters.entity_type || ''}><option value="">Todas</option>{data.entityTypes.map((type) => <option key={type} value={type}>{type.split('\\').pop()}</option>)}</select></div>
                    <div className="form-group"><label>Accion</label><select name="action" className="select-light" defaultValue={data.filters.action || ''}><option value="">Todas</option>{data.actions.map((action) => <option key={action} value={action}>{action}</option>)}</select></div>
                    <div className="form-group" style={{ display: 'flex', alignItems: 'flex-end' }}>
                        <div style={{ display: 'flex', gap: '0.65rem', flexWrap: 'wrap', width: '100%' }}>
                            <button type="submit" className="pill-button">Aplicar</button>
                            <a href={data.routes.report} target="_blank" rel="noopener" className="btn-secondary">PDF</a>
                            <a href={data.routes.index} className="clean-link">Limpiar</a>
                        </div>
                    </div>
                </form>
            </div>
            <div className="card">
                <div className="chart-head"><h4>Listado de logs</h4><span className="chip">{data.logs.total} registros</span></div>
                <div className="table-wrapper">
                    <table className="data-table">
                        <thead><tr><th>Fecha</th><th>Actor</th><th>Entidad</th><th>Accion</th><th>Descripcion</th><th>Detalle</th></tr></thead>
                        <tbody>
                            {data.logs.data.length ? data.logs.data.map((log) => (
                                <tr key={log.id}>
                                    <td>{log.created_at_formatted}</td>
                                    <td>{log.user?.name || 'Sistema'}</td>
                                    <td>{log.entity_label}</td>
                                    <td><span className="status-pill">{log.action}</span></td>
                                    <td>{log.description || '-'}</td>
                                    <td>{log.old_values || log.new_values ? <button type="button" className="btn-secondary" onClick={() => setDetailLog(log)}>Ver</button> : <span style={{ color: 'rgba(255,255,255,0.6)' }}>-</span>}</td>
                                </tr>
                            )) : <TableEmpty colSpan={6} text="Sin registros para los filtros." />}
                        </tbody>
                    </table>
                </div>
                <Pagination pagination={data.logs} />
            </div>
            <Modal open={!!detailLog} title="Detalle de cambio" onClose={() => setDetailLog(null)} wide>
                {detailLog && (
                    <div style={{ display: 'grid', gap: '1rem' }}>
                        <p style={{ margin: 0, color: 'rgba(255,255,255,0.8)' }}><strong>Entidad:</strong> {detailLog.entity_label}</p>
                        {detailLog.pdf_url && <a href={detailLog.pdf_url} target="_blank" rel="noopener" className="pill-button" style={{ display: 'inline-flex', width: 'fit-content' }}>Abrir PDF del traspaso</a>}
                        <div style={{ display: 'grid', gap: '0.6rem', gridTemplateColumns: 'repeat(auto-fit, minmax(220px, 1fr))' }}>
                            {renderObjectDiff(detailLog.old_values || {}).map((node) => node)}
                            {Object.keys(detailLog.old_values || {}).length === 0 && Object.keys(detailLog.new_values || {}).length > 0 && renderObjectDiff(detailLog.new_values || {})}
                        </div>
                    </div>
                )}
            </Modal>
        </DashboardShell>
    );
}
