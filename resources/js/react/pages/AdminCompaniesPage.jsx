import React, { useState } from 'react';
import DashboardShell from '../components/admin/DashboardShell';
import { FieldError, FlashMessages, Modal, Pagination, StatsGrid, TableEmpty } from '../components/admin/common';

function statusPill(status, label) {
    return <span className={`status-pill ${status}`}>{label}</span>;
}

export default function AdminCompaniesPage({ layout, data, flash, errors, old, csrfToken, logoutAction }) {
    const [editingCompany, setEditingCompany] = useState(null);
    const [viewingCompany, setViewingCompany] = useState(null);
    const [createType, setCreateType] = useState(old?.company_type || 'empresa_institucional');
    const editType = editingCompany?.company_type || 'empresa_institucional';
    const typeLabel = (type) => type === 'tienda_barrio' ? 'Tienda de barrio' : 'Empresa institucional';
    const stats = [
        { label: 'Cartera total', value: data.stats.total, chip: 'Activos + desactivados' },
        { label: 'Empresas institucionales', value: data.stats.institutional, chip: 'Usan precios corporativos', chipClass: 'chip-muted' },
        { label: 'Tiendas de barrio', value: data.stats.retail, chip: 'Con duenas registradas', chipClass: 'chip-muted' },
        { label: 'Desactivados', value: data.stats.inactive, chip: 'En papelera (recuperables)', chipClass: 'chip-muted' },
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
                <div className="form-group" style={{ gridColumn: '1 / -1' }}><hr style={{ borderColor: 'rgba(255,255,255,0.1)' }} /><p style={{ margin: '0.6rem 0', color: 'rgba(255,255,255,0.7)' }}>{type === 'tienda_barrio' ? 'captura los datos de la duena para coordinar entregas.' : 'Registra responsable y datos fiscales para la empresa.'}</p></div>
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
            <div className="card">
                <div className="chart-head"><h4>Registrar cliente</h4><span className="chip">Modo {typeLabel(createType)}</span></div>
                <form method="POST" action={data.routes.store} className="form-grid">
                    <input type="hidden" name="_token" value={csrfToken} />
                    {renderCompanyFields()}
                    <div className="form-group" style={{ alignSelf: 'flex-end' }}><button type="submit" className="pill-button">Guardar cliente</button></div>
                </form>
                <FieldError errors={errors} name="company_type" />
                <FieldError errors={errors} name="name" />
                <FieldError errors={errors} name="nit" />
            </div>
            <div className="card">
                <div className="chart-head"><h4>Filtrar cartera</h4><a className="pill-button" target="_blank" rel="noopener" href={data.routes.report}>Generar reporte PDF</a></div>
                <form method="GET" action={data.routes.index} className="form-grid">
                    <div className="form-group"><label>Buscar por nombre, NIT, ciudad o contacto</label><input type="text" name="search" className="input-ghost" defaultValue={data.filters.search || ''} /></div>
                    <div className="form-group"><label>Tipo</label><select name="type" className="select-light" defaultValue={data.filters.type || ''}><option value="">Todos</option>{Object.entries(data.companyTypes).map(([key, label]) => <option key={key} value={key}>{label}</option>)}</select></div>
                    <div className="form-group" style={{ alignSelf: 'flex-end' }}><a href={data.routes.index} className="clean-link">Limpiar</a></div>
                </form>
            </div>
            {[
                ['activeCompanies', 'Clientes activos', true],
                ['inactiveCompanies', 'Clientes desactivados', false],
            ].map(([key, title, active]) => (
                <div className="card" key={key}>
                    <div className="chart-head"><h4>{title}</h4><span className="chip">{data[key].total} registros</span></div>
                    <div className="table-wrapper">
                        <table className="data-table">
                            <thead><tr><th>Tipo</th><th>Nombre / NIT</th><th>Contacto</th><th>Ciudad</th><th>Email / Telefono</th><th>Creado por</th><th>Creado</th><th>Acciones</th></tr></thead>
                            <tbody>
                                {data[key].data.length ? data[key].data.map((company) => (
                                    <tr key={company.id}>
                                        <td>{statusPill(company.company_type === 'tienda_barrio' ? 'retail' : 'institutional', company.type_label)}</td>
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
                                                        <form method="POST" action={company.destroy_url} onSubmit={(e) => !window.confirm(`Desactivar a ${company.name}?`) && e.preventDefault()}>
                                                            <input type="hidden" name="_token" value={csrfToken} />
                                                            <input type="hidden" name="_method" value="DELETE" />
                                                            <button type="submit" className="btn-danger">Desactivar</button>
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
            <Modal open={!!editingCompany} title="Editar cliente" onClose={() => setEditingCompany(null)}>
                {editingCompany && (
                    <form method="POST" action={editingCompany.update_url}>
                        <input type="hidden" name="_token" value={csrfToken} />
                        <input type="hidden" name="_method" value="PUT" />
                        <div className="form-grid">{renderCompanyFields('edit_', editingCompany)}</div>
                        <div style={{ marginTop: '1.2rem', display: 'flex', justifyContent: 'flex-end', gap: '0.8rem' }}>
                            <button type="button" className="btn-secondary" onClick={() => setEditingCompany(null)}>Cancelar</button>
                            <button type="submit" className="pill-button">Guardar cambios</button>
                        </div>
                    </form>
                )}
            </Modal>
            <Modal open={!!viewingCompany} title="Detalle del cliente" onClose={() => setViewingCompany(null)} wide>
                {viewingCompany && (
                    <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit,minmax(240px,1fr))', gap: '12px' }}>
                        <div>
                            <p><strong>Nombre:</strong> {viewingCompany.name}</p>
                            <p><strong>NIT:</strong> {viewingCompany.nit}</p>
                            <p><strong>Tipo:</strong> {viewingCompany.type_label}</p>
                            <p><strong>Ciudad:</strong> {viewingCompany.city}</p>
                        </div>
                        <div>
                            <p><strong>Email:</strong> {viewingCompany.email || 'N/D'}</p>
                            <p><strong>Telefono:</strong> {viewingCompany.phone || 'N/D'}</p>
                            <p><strong>Direccion:</strong> {viewingCompany.address}</p>
                            <p><strong>Responsable:</strong> {viewingCompany.owner_full_name}</p>
                        </div>
                    </div>
                )}
            </Modal>
        </DashboardShell>
    );
}
