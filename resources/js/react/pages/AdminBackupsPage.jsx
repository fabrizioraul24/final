import React from 'react';
import DashboardShell from '../components/admin/DashboardShell';
import { FlashMessages, Pagination, StatsGrid, TableEmpty } from '../components/admin/common';

export default function AdminBackupsPage({ layout, data, flash, old, csrfToken, logoutAction }) {
    const stats = [
        { label: 'Total respaldos', value: data.stats.total, chip: 'Historial', chipClass: 'chip-muted' },
        { label: 'Completados', value: data.stats.completed, chip: 'OK', chipClass: 'chip-success' },
        { label: 'Automaticos', value: data.stats.automatic, chip: 'Scheduler' },
        { label: 'Ultimo backup', value: data.stats.last_label, chip: 'Fecha/hora', chipClass: 'chip-muted' },
    ];

    return (
        <DashboardShell sidebar={layout.sidebar} topbar={layout.topbar} csrfToken={csrfToken} logoutAction={logoutAction}>
            <FlashMessages flash={flash} />
            <StatsGrid items={stats} />

            <div className="card">
                <div className="chart-head">
                    <div><h4>Programacion automatica</h4></div>
                    <span className={`chip ${data.schedule.is_active ? 'chip-success' : ''}`}>{data.schedule.is_active ? 'Activa' : 'Pausada'}</span>
                </div>
                <form method="POST" action={data.routes.schedule} className="form-grid" style={{ marginTop: '1rem' }}>
                    <input type="hidden" name="_token" value={csrfToken} />
                    <input type="hidden" name="_method" value="PUT" />
                    <input type="hidden" name="schedule_id" value={data.schedule.id} />
                    <div className="form-group"><label>Frecuencia en dias</label><input type="number" name="frequency_days" min="1" max="30" className="input-ghost" defaultValue={old?.frequency_days || data.schedule.frequency_days} required /></div>
                    <div className="form-group"><label>Hora de ejecucion</label><input type="time" name="run_time" className="input-ghost" defaultValue={old?.run_time || data.schedule.run_time} required /></div>
                    <div className="form-group"><label>Estado</label><select name="is_active" className="select-light" defaultValue={old?.is_active ?? (data.schedule.is_active ? '1' : '0')}><option value="1">Activo</option><option value="0">Pausado</option></select></div>
                    <div className="form-group" style={{ alignSelf: 'flex-end' }}><button type="submit" className="pill-button">Guardar programacion</button></div>
                </form>
                <StatsGrid items={data.scheduleCards} />
            </div>

            <div className="card">
                <div className="chart-head" style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                    <h4>Generar nuevo backup</h4>
                    <form method="POST" action={data.routes.store}>
                        <input type="hidden" name="_token" value={csrfToken} />
                        <button type="submit" className="pill-button">Crear backup</button>
                    </form>
                </div>
                <div className="table-wrapper">
                    <table className="data-table">
                        <thead><tr><th>Archivo</th><th>Peso</th><th>Origen</th><th>Estado</th><th>Creado por</th><th>Fecha</th><th>Acciones</th></tr></thead>
                        <tbody>
                            {data.backups.data.length ? data.backups.data.map((backup) => (
                                <tr key={backup.id}>
                                    <td><strong>{backup.file_name}</strong><p style={{ margin: 0, color: 'rgba(255,255,255,0.6)' }}>{backup.disk_label}</p>{backup.message && <small style={{ color: 'rgba(255,255,255,0.7)' }}>{backup.message}</small>}</td>
                                    <td>{backup.readable_size}</td>
                                    <td><span className="chip">{backup.triggered_by_label}</span></td>
                                    <td><span className={`chip ${backup.status_class}`}>{backup.status_label}</span></td>
                                    <td>{backup.creator?.name || 'Sistema'}</td>
                                    <td>{backup.created_at_formatted}</td>
                                    <td>
                                        <div className="actions">
                                            <a href={backup.download_url} className="pill-button ghost" style={!backup.can_download ? { pointerEvents: 'none', opacity: 0.4 } : undefined}>Descargar</a>
                                            <form method="POST" action={backup.destroy_url} onSubmit={(event) => !window.confirm('Eliminar este backup del historial?') && event.preventDefault()}>
                                                <input type="hidden" name="_token" value={csrfToken} />
                                                <input type="hidden" name="_method" value="DELETE" />
                                                <button type="submit" className="btn-danger">Eliminar</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            )) : <TableEmpty colSpan={7} text="Aun no generaste backups." />}
                        </tbody>
                    </table>
                </div>
                <Pagination pagination={data.backups} />
            </div>
        </DashboardShell>
    );
}
