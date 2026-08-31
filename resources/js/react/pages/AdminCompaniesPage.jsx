import React, { useState } from 'react';
import DashboardShell from '../components/admin/DashboardShell';
import { FieldError, FlashMessages, Modal, Pagination, StatsGrid, TableEmpty } from '../components/admin/common';

function statusPill(status, label) {
    return <span className={`status-pill ${status}`}>{label}</span>;
}

export default function AdminCompaniesPage({ layout, data, flash, errors, old, csrfToken, logoutAction }) {
    const [editingCompany, setEditingCompany] = useState(null);
    const [viewingCompany, setViewingCompany] = useState(null);
    const [companyToDeactivate, setCompanyToDeactivate] = useState(null);
    const [createType, setCreateType] = useState(old?.company_type || 'empresa_institucional');
    const [createCompanyOpen, setCreateCompanyOpen] = useState(() => Object.keys(errors || {}).length > 0);
    const editType = editingCompany?.company_type || 'empresa_institucional';
    const stats = [
        { label: 'Cartera total', value: data.stats.total, chip: 'Activos + desactivados', cardClass: 'company-stat-card company-stat-card--total', icon: 'ri-community-line' },
        { label: 'Empresas institucionales', value: data.stats.institutional, chip: 'Usan precios corporativos', cardClass: 'company-stat-card company-stat-card--institutional', icon: 'ri-building-4-line' },
        { label: 'Tiendas de barrio', value: data.stats.retail, chip: 'Con duenas registradas', cardClass: 'company-stat-card company-stat-card--retail', icon: 'ri-store-2-line' },
        { label: 'Desactivados', value: data.stats.inactive, chip: 'En papelera (recuperables)', cardClass: 'company-stat-card company-stat-card--inactive', icon: 'ri-archive-line' },
    ];

    const renderCompanyFields = (prefix = '', company = {}) => {
        const isEdit = prefix === 'edit_';
        const type = isEdit ? editType : createType;
        const value = (name, fallback = '') => company?.[name] ?? old?.[name] ?? fallback;

        return (
            <>
                <div className="form-group">
                    <label>{type === 'tienda_barrio' ? 'Tipo de tienda' : 'Tipo de empresa'}</label>
                    <select
                        name="company_type"
                        className="select-light"
                        defaultValue={value('company_type', 'empresa_institucional')}
                        onChange={(event) => isEdit ? setEditingCompany((current) => ({ ...current, company_type: event.target.value })) : setCreateType(event.target.value)}
                        required
                    >
                        {Object.entries(data.companyTypes).map(([key, label]) => <option key={key} value={key}>{label}</option>)}
                    </select>
                </div>
                <div className="form-group"><label>Nombre comercial / Razon social</label><input type="text" name="name" className="input-ghost" defaultValue={value('name')} required /></div>
                <div className="form-group"><label>NIT</label><input type="text" name="nit" className="input-ghost" defaultValue={value('nit')} required /></div>
                <div className="form-group"><label>Correo electronico</label><input type="email" name="email" className="input-ghost" defaultValue={value('email')} /></div>
                <div className="form-group"><label>Telefono</label><input type="text" name="phone" className="input-ghost" defaultValue={value('phone')} /></div>
                <div className="form-group"><label>{type === 'tienda_barrio' ? 'Direccion de entrega' : 'Direccion fiscal'}</label><input type="text" name="address" className="input-ghost" defaultValue={value('address')} required /></div>
                <div className="form-group"><label>Ciudad</label><input type="text" name="city" className="input-ghost" defaultValue={value('city')} required /></div>
                <div className="form-group"><label>{type === 'tienda_barrio' ? 'Nombre de la duena' : 'Nombre del representante'}</label><input type="text" name="owner_first_name" className="input-ghost" defaultValue={value('owner_first_name')} required /></div>
                <div className="form-group"><label>{type === 'tienda_barrio' ? 'Apellido paterno de la duena' : 'Apellido paterno'}</label><input type="text" name="owner_last_name_paterno" className="input-ghost" defaultValue={value('owner_last_name_paterno')} required /></div>
                <div className="form-group"><label>{type === 'tienda_barrio' ? 'Apellido materno de la duena' : 'Apellido materno'}</label><input type="text" name="owner_last_name_materno" className="input-ghost" defaultValue={value('owner_last_name_materno')} /></div>
            </>
        );
    };

    return (
        <DashboardShell sidebar={layout.sidebar} topbar={layout.topbar} csrfToken={csrfToken} logoutAction={logoutAction}>
            <FlashMessages flash={flash} />
            <StatsGrid items={stats} />
            <div className="card user-directory-header">
                <div>
                    <h2>Clientes</h2>
                </div>
                <button type="button" className="pill-button user-create-open-button" onClick={() => setCreateCompanyOpen(true)}>
                    <i className="ri-building-2-line" /> Crear cliente
                </button>
            </div>

            <Modal open={createCompanyOpen} title="Crear cliente" onClose={() => setCreateCompanyOpen(false)} wide contentClassName="user-create-modal">
                <form method="POST" action={data.routes.store} className="user-create-form">
                    <input type="hidden" name="_token" value={csrfToken} />
                    <div className="user-create-fields">
                        {renderCompanyFields()}
                    </div>
                    <div className="user-create-actions">
                        <button type="button" className="btn-secondary user-create-cancel" onClick={() => setCreateCompanyOpen(false)}>Cancelar</button>
                        <button type="submit" className="pill-button user-create-submit"><i className="ri-building-2-line" /> Crear cliente</button>
                    </div>
                </form>
                <FieldError errors={errors} name="company_type" />
                <FieldError errors={errors} name="name" />
                <FieldError errors={errors} name="nit" />
            </Modal>
            <div className="card user-filter-card">
                <div className="chart-head"><div><span className="section-kicker">Directorio</span><h4>Buscar clientes</h4></div><a className="pill-button user-report-button" target="_blank" rel="noopener" href={data.routes.report}><i className="ri-file-chart-line" /> Reporte PDF</a></div>
                <form method="GET" action={data.routes.index} className="form-grid user-filter-form">
                    <div className="form-group"><label><i className="ri-search-line" /> Nombre, NIT, ciudad o contacto</label><input type="text" name="search" className="input-ghost" placeholder="Ej. Supermercado Victoria o 1234567890" defaultValue={data.filters.search || ''} /></div>
                    <div className="form-group"><label><i className="ri-filter-3-line" /> Tipo de cliente</label><select name="type" className="select-light" defaultValue={data.filters.type || ''}><option value="">Todos</option>{Object.entries(data.companyTypes).map(([key, label]) => <option key={key} value={key}>{label}</option>)}</select></div>
                    <div className="user-filter-actions"><a href={data.routes.index} className="clean-link"><i className="ri-refresh-line" /> Limpiar</a><button type="submit" className="pill-button user-filter-submit"><i className="ri-search-line" /> Buscar</button></div>
                </form>
            </div>
            {[
                ['activeCompanies', 'Clientes activos', true],
                ['inactiveCompanies', 'Clientes desactivados', false],
            ].map(([key, title, active]) => (
                <div className={`card user-table-card ${active ? 'user-table-card--active' : 'user-table-card--inactive'}`} key={key}>
                    <div className="chart-head"><h4>{title}</h4><span className="chip">{data[key].total} registros</span></div>
                    <div className="table-wrapper">
                        <table className="data-table">
                            <thead><tr><th>Tipo</th><th>Nombre / NIT</th><th>Contacto</th><th>Ciudad</th><th>Email / Telefono</th><th>Creado por</th><th>Creado</th><th>Acciones</th></tr></thead>
                            <tbody>
                                {data[key].data.length ? data[key].data.map((company) => (
                                    <tr key={company.id}>
                                        <td>{statusPill(company.company_type === 'tienda_barrio' ? 'retail' : 'institutional', company.company_type === 'tienda_barrio' ? 'Tienda' : 'Institucional')}</td>
                                        <td><strong>{company.name}</strong><p style={{ margin: 0, color: 'rgba(255,255,255,0.7)', fontSize: '0.85rem' }}>NIT: {company.nit}</p></td>
                                        <td>{company.owner_full_name || 'Sin datos'}</td>
                                        <td>{company.city}</td>
                                        <td><p style={{ margin: 0 }}>{company.email || 'Sin correo'}</p><p style={{ margin: 0, color: 'rgba(255,255,255,0.7)', fontSize: '0.85rem' }}>{company.phone || 'Sin telefono'}</p></td>
                                        <td>{company.creator?.name || 'Usuario Pil'}</td>
                                        <td>{company.created_at_formatted}</td>
                                        <td>
                                            <div className="actions">
                                                {active ? (
                                                    <>
                                                        <button type="button" className="btn-secondary" onClick={() => setViewingCompany(company)}>Ver</button>
                                                        <button type="button" className="btn-secondary" onClick={() => setEditingCompany(company)}>Editar</button>
                                                        <form method="POST" action={company.destroy_url}>
                                                            <input type="hidden" name="_token" value={csrfToken} />
                                                            <input type="hidden" name="_method" value="DELETE" />
                                                            <button type="button" className="btn-danger" onClick={() => setCompanyToDeactivate(company)}>Desactivar</button>
                                                        </form>
                                                    </>
                                                ) : (
                                                    <form method="POST" action={company.restore_url}>
                                                        <input type="hidden" name="_token" value={csrfToken} />
                                                        <input type="hidden" name="_method" value="PATCH" />
                                                        <button type="submit" className="btn-secondary">Reactivar</button>
                                                    </form>
                                                )}
                                            </div>
                                        </td>
                                    </tr>
                                )) : <TableEmpty colSpan={8} text={active ? 'No hay clientes para los filtros aplicados.' : 'No hay clientes desactivados.'} />}
                            </tbody>
                        </table>
                    </div>
                    <Pagination pagination={data[key]} />
                </div>
            ))}
            <Modal open={!!companyToDeactivate} title="Desactivar cliente" onClose={() => setCompanyToDeactivate(null)} contentClassName="user-edit-modal company-confirm-modal">
                {companyToDeactivate && (
                    <form method="POST" action={companyToDeactivate.destroy_url} className="company-confirm-form">
                        <input type="hidden" name="_token" value={csrfToken} />
                        <input type="hidden" name="_method" value="DELETE" />
                        <div className="company-confirm-icon"><i className="ri-alert-line" /></div>
                        <h4>Desactivar a {companyToDeactivate.name}?</h4>
                        <p>El registro pasara a la lista de clientes desactivados.</p>
                        <div className="company-confirm-actions">
                            <button type="button" className="btn-secondary user-edit-cancel" onClick={() => setCompanyToDeactivate(null)}>Cancelar</button>
                            <button type="submit" className="btn-danger company-confirm-submit">Desactivar</button>
                        </div>
                    </form>
                )}
            </Modal>
            <Modal open={!!editingCompany} title="Editar cliente" onClose={() => setEditingCompany(null)} wide contentClassName="user-edit-modal company-edit-modal">
                {editingCompany && (
                    <form method="POST" action={editingCompany.update_url} className="user-edit-form">
                        <input type="hidden" name="_token" value={csrfToken} />
                        <input type="hidden" name="_method" value="PUT" />
                        <div className="user-edit-fields">{renderCompanyFields('edit_', editingCompany)}</div>
                        <div className="user-edit-actions">
                            <button type="button" className="btn-secondary user-edit-cancel" onClick={() => setEditingCompany(null)}>Cancelar</button>
                            <button type="submit" className="pill-button user-edit-submit"><i className="ri-save-3-line" /> Guardar cambios</button>
                        </div>
                    </form>
                )}
            </Modal>
            <Modal open={!!viewingCompany} title="Detalle del cliente" onClose={() => setViewingCompany(null)} wide contentClassName="user-edit-modal company-view-modal">
                {viewingCompany && (
                    <div className="company-view-grid">
                        <div className="company-view-item"><span>Nombre</span><strong>{viewingCompany.name}</strong></div>
                        <div className="company-view-item"><span>NIT</span><strong>{viewingCompany.nit}</strong></div>
                        <div className="company-view-item"><span>Tipo</span><strong>{viewingCompany.company_type === 'tienda_barrio' ? 'Tienda' : 'Institucional'}</strong></div>
                        <div className="company-view-item"><span>Ciudad</span><strong>{viewingCompany.city}</strong></div>
                        <div className="company-view-item"><span>Email</span><strong>{viewingCompany.email || 'N/D'}</strong></div>
                        <div className="company-view-item"><span>Telefono</span><strong>{viewingCompany.phone || 'N/D'}</strong></div>
                        <div className="company-view-item"><span>Direccion</span><strong>{viewingCompany.address}</strong></div>
                        <div className="company-view-item"><span>Responsable</span><strong>{viewingCompany.owner_full_name}</strong></div>
                    </div>
                )}
            </Modal>
        </DashboardShell>
    );
}
