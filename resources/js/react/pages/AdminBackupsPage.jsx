import React, { useState } from 'react';
import DashboardShell from '../components/admin/DashboardShell';
import { FlashMessages, Modal, Pagination, TableEmpty } from '../components/admin/common';

function BackupStatus({ backup }) {
    const tone = backup.status_class === 'chip-success' ? 'active' : backup.status_label === 'Fallido' ? 'rejected' : 'draft';

    return (
        <span className={`fit-quotation-status ${tone}`}>
            <span /> {backup.status_label}
        </span>
    );
}

function ScheduleStatus({ active }) {
    return (
        <span className={`fit-backup-schedule-status ${active ? 'active' : 'paused'}`}>
            <i className={active ? 'ri-checkbox-circle-line' : 'ri-pause-circle-line'} />
            {active ? 'Activa' : 'Pausada'}
        </span>
    );
}

export default function AdminBackupsPage({ layout, data, flash, old, csrfToken, logoutAction }) {
    const [scheduleOpen, setScheduleOpen] = useState(false);
    const [backupToDelete, setBackupToDelete] = useState(null);

    const metricCards = [
        { label: 'Respaldos Total', value: data.stats.total, hint: 'Historial', icon: 'ri-database-2-line', tone: 'indigo' },
        { label: 'Completados', value: data.stats.completed, hint: 'Disponibles', icon: 'ri-checkbox-circle-line', tone: 'green' },
        { label: 'Automaticos', value: data.stats.automatic, hint: 'Programados', icon: 'ri-time-line', tone: 'rose' },
        { label: 'Ultimo Backup', value: data.stats.last_label, hint: 'Fecha y hora', icon: 'ri-history-line', tone: 'amber' },
    ];

    return (
        <DashboardShell sidebar={layout.sidebar} topbar={layout.topbar} csrfToken={csrfToken} logoutAction={logoutAction}>
            <div className="fit-users-page fit-backups-page">
                <FlashMessages flash={flash} />

                <section className="fit-users-header">
                    <div className="fit-users-header-left">
                        <div className="fit-header-icon"><i className="ri-database-2-line" /></div>
                        <div>
                            <h1>Backups y Respaldo de Datos</h1>
                            <p>Gestiona respaldos manuales, programación automática y descargas del historial.</p>
                        </div>
                    </div>

                    <div className="fit-users-header-actions">
                        <button type="button" className="fit-outline-button fit-backup-schedule-button" onClick={() => setScheduleOpen(true)}>
                            <i className="ri-settings-3-line" />
                            <span>Programacion</span>
                        </button>
                        <form method="POST" action={data.routes.store} className="fit-inline-form">
                            <input type="hidden" name="_token" value={csrfToken} />
                            <button type="submit" className="fit-primary-button">
                                <i className="ri-database-2-line" />
                                <span>Crear Backup</span>
                            </button>
                        </form>
                    </div>
                </section>

                <section className="fit-metric-grid">
                    {metricCards.map((card) => (
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

                <section className="fit-backup-schedule-card">
                    <div className="fit-backup-schedule-main">
                        <span>Programacion automatica</span>
                        <h2>{data.schedule.is_active ? 'Ejecucion Activa' : 'Ejecucion Pausada'}</h2>
                        <p>{data.schedule.is_active ? `Cada ${data.schedule.frequency_days} dias a las ${data.schedule.run_time}` : 'La ejecucion automatica esta pausada.'}</p>
                    </div>
                    <div className="fit-backup-schedule-side">
                        <ScheduleStatus active={data.schedule.is_active} />
                        <button type="button" className="fit-action-button warning" onClick={() => setScheduleOpen(true)} title="Editar programacion">
                            <i className="ri-edit-2-line" />
                        </button>
                    </div>
                </section>

                <section className="fit-backup-info-grid">
                    {data.scheduleCards.map((card) => (
                        <div className="fit-backup-info-card" key={card.label}>
                            <span>{card.label}</span>
                            <strong>{card.value}</strong>
                            <small>{card.chip}</small>
                        </div>
                    ))}
                </section>

                <section className="fit-section">
                    <div className="fit-section-head">
                        <div>
                            <h2>Respaldos Generados</h2>
                            <p>Historial de archivos creados manualmente o por la programación automática.</p>
                        </div>
                        <span className="fit-section-badge green">{data.backups.total} registros</span>
                    </div>

                    <div className="fit-table-card">
                        <div className="fit-table-scroll">
                            <table className="fit-users-table fit-backups-table">
                                <thead>
                                    <tr>
                                        <th>Archivo</th>
                                        <th>Peso</th>
                                        <th>Origen</th>
                                        <th>Estado</th>
                                        <th>Creado por</th>
                                        <th>Fecha</th>
                                        <th className="text-right">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {data.backups.data.length ? data.backups.data.map((backup) => (
                                        <tr key={backup.id}>
                                            <td>
                                                <div className="fit-user-cell fit-backup-file">
                                                    <span className="fit-backup-file-icon"><i className="ri-file-zip-line" /></span>
                                                    <div>
                                                        <strong>{backup.file_name}</strong>
                                                        <small>{backup.disk_label}</small>
                                                        {backup.message && <small>{backup.message}</small>}
                                                    </div>
                                                </div>
                                            </td>
                                            <td><span className="fit-muted-text">{backup.readable_size}</span></td>
                                            <td>
                                                <span className="fit-backup-origin">
                                                    <i className={backup.triggered_by_label === 'Automatico' ? 'ri-time-line' : 'ri-user-line'} />
                                                    {backup.triggered_by_label}
                                                </span>
                                            </td>
                                            <td><BackupStatus backup={backup} /></td>
                                            <td><strong>{backup.creator?.name || 'Sistema'}</strong></td>
                                            <td><span className="fit-muted-text">{backup.created_at_formatted}</span></td>
                                            <td className="text-right">
                                                <div className="fit-row-actions">
                                                    <a
                                                        href={backup.download_url}
                                                        className={`fit-action-button success${backup.can_download ? '' : ' is-disabled'}`}
                                                        aria-disabled={!backup.can_download}
                                                        title="Descargar"
                                                    >
                                                        <i className="ri-download-2-line" />
                                                    </a>
                                                    <button type="button" className="fit-action-button danger" onClick={() => setBackupToDelete(backup)} title="Eliminar">
                                                        <i className="ri-delete-bin-line" />
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    )) : <TableEmpty colSpan={7} text="Aun no generaste backups." />}
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <Pagination pagination={data.backups} />
                </section>

                <Modal open={scheduleOpen} title="Programacion Automatica" onClose={() => setScheduleOpen(false)} contentClassName="fit-modal-content fit-backup-schedule-modal">
                    <form method="POST" action={data.routes.schedule} className="fit-register-form">
                        <input type="hidden" name="_token" value={csrfToken} />
                        <input type="hidden" name="_method" value="PUT" />
                        <input type="hidden" name="schedule_id" value={data.schedule.id} />

                        <div className="fit-form-grid">
                            <div className="fit-form-field">
                                <label htmlFor="frequency_days">Frecuencia en dias *</label>
                                <input id="frequency_days" type="number" name="frequency_days" min="1" max="30" defaultValue={old?.frequency_days || data.schedule.frequency_days} required />
                            </div>

                            <div className="fit-form-field">
                                <label htmlFor="run_time">Hora de ejecucion *</label>
                                <input id="run_time" type="time" name="run_time" defaultValue={old?.run_time || data.schedule.run_time} required />
                            </div>

                            <div className="fit-form-field span-2">
                                <label htmlFor="is_active">Estado</label>
                                <select id="is_active" name="is_active" defaultValue={old?.is_active ?? (data.schedule.is_active ? '1' : '0')}>
                                    <option value="1">Activo</option>
                                    <option value="0">Pausado</option>
                                </select>
                            </div>
                        </div>

                        <div className="fit-modal-footer">
                            <button type="button" className="fit-outline-button" onClick={() => setScheduleOpen(false)}>Cancelar</button>
                            <button type="submit" className="fit-primary-button">
                                <i className="ri-save-3-line" /> Guardar Cambios
                            </button>
                        </div>
                    </form>
                </Modal>

                <Modal open={!!backupToDelete} title="Eliminar Backup" onClose={() => setBackupToDelete(null)} contentClassName="fit-modal-content">
                    {backupToDelete && (
                        <form method="POST" action={backupToDelete.destroy_url} className="fit-confirm-form">
                            <input type="hidden" name="_token" value={csrfToken} />
                            <input type="hidden" name="_method" value="DELETE" />
                            <div className="fit-confirm-icon danger"><i className="ri-delete-bin-line" /></div>
                            <h4>Eliminar este backup?</h4>
                            <p>{backupToDelete.file_name}</p>
                            <div className="fit-modal-footer">
                                <button type="button" className="fit-outline-button" onClick={() => setBackupToDelete(null)}>Cancelar</button>
                                <button type="submit" className="fit-primary-button danger">
                                    <i className="ri-delete-bin-line" /> Eliminar
                                </button>
                            </div>
                        </form>
                    )}
                </Modal>
            </div>
        </DashboardShell>
    );
}
