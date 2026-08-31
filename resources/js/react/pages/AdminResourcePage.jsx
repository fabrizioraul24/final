import React, { useEffect, useMemo, useRef, useState } from 'react';
import Chart from 'chart.js/auto';
import DashboardShell from '../components/admin/DashboardShell';
import { FieldError, FlashMessages, Modal, Pagination, StatsGrid, TableEmpty } from '../components/admin/common';

function statusPill(status, label) {
    return <span className={`status-pill ${status}`}>{label}</span>;
}

function renderObjectDiff(record = {}) {
    return Object.entries(record).map(([key, value]) => (
        <div key={key} style={{ border: '1px solid rgba(255,255,255,0.12)', borderRadius: '1rem', padding: '0.75rem 1rem' }}>
            <p style={{ margin: 0, fontSize: '0.85rem', color: 'rgba(255,255,255,0.7)' }}>{key}</p>
            <p style={{ margin: '0.2rem 0 0' }}>{String(value ?? '-')}</p>
        </div>
    ));
}

function UsersPage({ data, flash, errors, old, csrfToken }) {
    const [editingUser, setEditingUser] = useState(null);

    return (
        <>
            <FlashMessages flash={flash} />

            <div className="card">
                <div className="chart-head"><h4>Crear nuevo usuario</h4></div>
                <form method="POST" action={data.routes.store} className="form-grid">
                    <input type="hidden" name="_token" value={csrfToken} />
                    <div className="form-group">
                        <label htmlFor="name">Nombre completo</label>
                        <input type="text" id="name" name="name" className="input-ghost" defaultValue={old?.name || ''} required />
                        <FieldError errors={errors} name="name" />
                    </div>
                    <div className="form-group">
                        <label htmlFor="email">Correo electronico</label>
                        <input type="email" id="email" name="email" className="input-ghost" defaultValue={old?.email || ''} required />
                        <FieldError errors={errors} name="email" />
                    </div>
                    <div className="form-group">
                        <label htmlFor="username">Nombre de usuario</label>
                        <input type="text" id="username" name="username" className="input-ghost" defaultValue={old?.username || ''} required />
                        <FieldError errors={errors} name="username" />
                    </div>
                    <div className="form-group">
                        <label htmlFor="password">Contrasena</label>
                        <input type="password" id="password" name="password" className="input-ghost" required />
                        <FieldError errors={errors} name="password" />
                    </div>
                    <div className="form-group">
                        <label htmlFor="role_create">Rol</label>
                        <select id="role_create" name="role_id" className="select-light" defaultValue={old?.role_id || ''} required>
                            <option value="">Selecciona un rol</option>
                            {data.roles.map((role) => <option key={role.id} value={role.id}>{role.name}</option>)}
                        </select>
                        <FieldError errors={errors} name="role_id" />
                    </div>
                    <div className="form-group" style={{ alignSelf: 'flex-end' }}>
                        <button type="submit" className="pill-button">Guardar usuario</button>
                    </div>
                </form>
            </div>

            <div className="card">
                <div className="chart-head">
                    <h4>Filtros inteligentes</h4>
                    <a className="pill-button" target="_blank" rel="noopener" href={data.routes.report}>Generar reporte PDF</a>
                </div>
                <form method="GET" className="form-grid" action={data.routes.index}>
                    <div className="form-group">
                        <label htmlFor="search">Buscar por nombre, email o usuario</label>
                        <input type="text" id="search" name="search" className="input-ghost" defaultValue={data.filters.search || ''} />
                    </div>
                    <div className="form-group">
                        <label htmlFor="role_id">Filtrar por rol</label>
                        <select id="role_id" name="role_id" className="select-light" defaultValue={data.filters.role_id || ''}>
                            <option value="">Todos los roles</option>
                            {data.roles.map((role) => <option key={role.id} value={role.id}>{role.name}</option>)}
                        </select>
                    </div>
                    <div className="form-group" style={{ alignSelf: 'flex-end' }}>
                        <a href={data.routes.index} className="clean-link">Limpiar</a>
                    </div>
                </form>
            </div>

            <div className="card">
                <div className="chart-head">
                    <h4>Usuarios activos</h4>
                    <span className="chip">{data.activeUsers.total} registros</span>
                </div>
                <div className="table-wrapper">
                    <table className="data-table">
                        <thead><tr><th>ID</th><th>Nombre</th><th>Email</th><th>Usuario</th><th>Rol</th><th>Estado</th><th>Creado</th><th>Acciones</th></tr></thead>
                        <tbody>
                            {data.activeUsers.data.length ? data.activeUsers.data.map((user) => (
                                <tr key={user.id}>
                                    <td>{user.id}</td>
                                    <td>{user.name}</td>
                                    <td>{user.email}</td>
                                    <td>{user.username}</td>
                                    <td>{user.role?.name || 'Sin rol'}</td>
                                    <td>{statusPill('active', 'Activo')}</td>
                                    <td>{user.created_at_formatted}</td>
                                    <td>
                                        <div className="actions">
                                            <button type="button" className="btn-secondary" onClick={() => setEditingUser(user)}>Editar</button>
                                            <form method="POST" action={user.toggle_url}>
                                                <input type="hidden" name="_token" value={csrfToken} />
                                                <input type="hidden" name="_method" value="PATCH" />
                                                <button type="submit" className="btn-danger">Desactivar</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            )) : <TableEmpty colSpan={8} text="No hay usuarios activos para los filtros aplicados." />}
                        </tbody>
                    </table>
                </div>
                <Pagination pagination={data.activeUsers} />
            </div>

            <div className="card">
                <div className="chart-head">
                    <h4>Usuarios inactivos</h4>
                    <span className="chip">{data.inactiveUsers.total} registros</span>
                </div>
                <div className="table-wrapper">
                    <table className="data-table">
                        <thead><tr><th>ID</th><th>Nombre</th><th>Email</th><th>Usuario</th><th>Rol</th><th>Estado</th><th>Actualizado</th><th>Acciones</th></tr></thead>
                        <tbody>
                            {data.inactiveUsers.data.length ? data.inactiveUsers.data.map((user) => (
                                <tr key={user.id}>
                                    <td>{user.id}</td>
                                    <td>{user.name}</td>
                                    <td>{user.email}</td>
                                    <td>{user.username}</td>
                                    <td>{user.role?.name || 'Sin rol'}</td>
                                    <td>{statusPill('inactive', 'Inactivo')}</td>
                                    <td>{user.updated_at_formatted}</td>
                                    <td>
                                        <div className="actions">
                                            <form method="POST" action={user.toggle_url}>
                                                <input type="hidden" name="_token" value={csrfToken} />
                                                <input type="hidden" name="_method" value="PATCH" />
                                                <button type="submit" className="btn-secondary">Reactivar</button>
                                            </form>
                                            <form method="POST" action={user.destroy_url} onSubmit={(e) => !window.confirm(`Eliminar definitivamente a ${user.name}?`) && e.preventDefault()}>
                                                <input type="hidden" name="_token" value={csrfToken} />
                                                <input type="hidden" name="_method" value="DELETE" />
                                                <button type="submit" className="btn-danger">Eliminar</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            )) : <TableEmpty colSpan={8} text="Sin usuarios inactivos." />}
                        </tbody>
                    </table>
                </div>
                <Pagination pagination={data.inactiveUsers} />
            </div>

            <Modal open={!!editingUser} title="Editar usuario" onClose={() => setEditingUser(null)}>
                {editingUser && (
                    <form method="POST" action={editingUser.update_url}>
                        <input type="hidden" name="_token" value={csrfToken} />
                        <input type="hidden" name="_method" value="PUT" />
                        <div className="form-grid">
                            <div className="form-group"><label>Nombre completo</label><input type="text" name="name" className="input-ghost" defaultValue={editingUser.name} required /></div>
                            <div className="form-group"><label>Correo electronico</label><input type="email" name="email" className="input-ghost" defaultValue={editingUser.email} required /></div>
                            <div className="form-group"><label>Nombre de usuario</label><input type="text" name="username" className="input-ghost" defaultValue={editingUser.username} required /></div>
                            <div className="form-group"><label>Nueva contrasena (opcional)</label><input type="password" name="password" className="input-ghost" /></div>
                            <div className="form-group"><label>Rol</label><select name="role_id" className="select-light" defaultValue={editingUser.role_id} required>{data.roles.map((role) => <option key={role.id} value={role.id}>{role.name}</option>)}</select></div>
                        </div>
                        <div style={{ marginTop: '1.2rem', display: 'flex', justifyContent: 'flex-end', gap: '0.8rem' }}>
                            <button type="button" className="btn-secondary" onClick={() => setEditingUser(null)}>Cancelar</button>
                            <button type="submit" className="pill-button">Guardar cambios</button>
                        </div>
                    </form>
                )}
            </Modal>
        </>
    );
}

function CategoriesPage({ data, flash, errors, old, csrfToken }) {
    const [editingCategory, setEditingCategory] = useState(null);
    const stats = [
        { label: 'Total categorias', value: data.summary.total, chip: 'Activas + desactivadas' },
        { label: 'Con productos asignados', value: data.summary.with_products, chip: 'Operativas', chipClass: 'chip-muted' },
        { label: 'Desactivadas', value: data.summary.inactive, chip: 'En pausa', chipClass: 'chip-muted' },
    ];

    return (
        <>
            <FlashMessages flash={flash} />
            <StatsGrid items={stats} />

            <div className="card">
                <div className="chart-head"><h4>Crear categoria</h4><span className="chip">Agrupa productos y organiza el catalogo</span></div>
                <form method="POST" action={data.routes.store} className="form-grid">
                    <input type="hidden" name="_token" value={csrfToken} />
                    <div className="form-group">
                        <label htmlFor="category_name">Nombre</label>
                        <input type="text" id="category_name" name="name" className="input-ghost" defaultValue={old?.name || ''} required />
                        <FieldError errors={errors} name="name" />
                    </div>
                    <div className="form-group">
                        <label htmlFor="category_description">Descripcion</label>
                        <textarea id="category_description" name="description" rows="2" className="input-ghost" defaultValue={old?.description || ''} />
                        <FieldError errors={errors} name="description" />
                    </div>
                    <div className="form-group" style={{ alignSelf: 'flex-end' }}><button type="submit" className="pill-button">Guardar categoria</button></div>
                </form>
            </div>

            <div className="card">
                <div className="chart-head"><h4>Filtrar categorias</h4><a href={data.routes.report} target="_blank" rel="noopener" className="pill-button">Generar reporte PDF</a></div>
                <form method="GET" action={data.routes.index} className="form-grid">
                    <div className="form-group"><label htmlFor="search">Buscar por nombre</label><input type="text" id="search" name="search" className="input-ghost" defaultValue={data.filters.search || ''} /></div>
                    <div className="form-group" style={{ alignSelf: 'flex-end' }}><a href={data.routes.index} className="clean-link">Limpiar</a></div>
                </form>
            </div>

            {['activeCategories', 'inactiveCategories'].map((key) => {
                const collection = data[key];
                const isActive = key === 'activeCategories';
                return (
                    <div className="card" key={key}>
                        <div className="chart-head">
                            <h4>{isActive ? 'Categorias activas' : 'Categorias desactivadas'}</h4>
                            <span className="chip">{collection.total} registros</span>
                        </div>
                        <div className="table-wrapper">
                            <table className="data-table">
                                <thead><tr><th>Categoria</th><th>Productos</th><th>Estado</th><th>{isActive ? 'Creada' : 'Desactivada'}</th><th>Acciones</th></tr></thead>
                                <tbody>
                                    {collection.data.length ? collection.data.map((category) => (
                                        <tr key={category.id}>
                                            <td><strong>{category.name}</strong><p style={{ margin: 0, color: 'rgba(255,255,255,0.72)', fontSize: '0.85rem' }}>{category.description_excerpt}</p></td>
                                            <td>{category.products_count} productos</td>
                                            <td>{statusPill(isActive ? 'active' : 'inactive', isActive ? 'Activa' : 'Inactiva')}</td>
                                            <td>{isActive ? category.created_at_formatted : category.deleted_at_formatted}</td>
                                            <td>
                                                <div className="actions">
                                                    {isActive ? (
                                                        <>
                                                            <button type="button" className="btn-secondary" onClick={() => setEditingCategory(category)}>Editar</button>
                                                            <form method="POST" action={category.destroy_url} onSubmit={(e) => !window.confirm(`Desactivar la categoria ${category.name}?`) && e.preventDefault()}>
                                                                <input type="hidden" name="_token" value={csrfToken} />
                                                                <input type="hidden" name="_method" value="DELETE" />
                                                                <button className="btn-danger" type="submit">Desactivar</button>
                                                            </form>
                                                        </>
                                                    ) : (
                                                        <form method="POST" action={category.restore_url}>
                                                            <input type="hidden" name="_token" value={csrfToken} />
                                                            <input type="hidden" name="_method" value="PATCH" />
                                                            <button className="btn-secondary" type="submit">Reactivar</button>
                                                        </form>
                                                    )}
                                                </div>
                                            </td>
                                        </tr>
                                    )) : <TableEmpty colSpan={5} text={isActive ? 'No hay categorias activas para los filtros aplicados.' : 'No hay categorias desactivadas para mostrar.'} />}
                                </tbody>
                            </table>
                        </div>
                        <Pagination pagination={collection} />
                    </div>
                );
            })}

            <Modal open={!!editingCategory} title="Editar categoria" onClose={() => setEditingCategory(null)}>
                {editingCategory && (
                    <form method="POST" action={editingCategory.update_url}>
                        <input type="hidden" name="_token" value={csrfToken} />
                        <input type="hidden" name="_method" value="PUT" />
                        <div className="form-group"><label>Nombre</label><input type="text" name="name" className="input-ghost" defaultValue={editingCategory.name} required /></div>
                        <div className="form-group"><label>Descripcion</label><textarea name="description" rows="3" className="input-ghost" defaultValue={editingCategory.description || ''} /></div>
                        <div style={{ display: 'flex', justifyContent: 'flex-end', gap: '0.75rem' }}>
                            <button type="button" className="btn-secondary" onClick={() => setEditingCategory(null)}>Cancelar</button>
                            <button type="submit" className="pill-button" style={{ width: 'auto' }}>Actualizar categoria</button>
                        </div>
                    </form>
                )}
            </Modal>
        </>
    );
}

function CompaniesPage({ data, flash, errors, old, csrfToken }) {
    const [editingCompany, setEditingCompany] = useState(null);
    const [viewingCompany, setViewingCompany] = useState(null);
    const stats = [
        { label: 'Cartera total', value: data.stats.total, chip: 'Activos + desactivados' },
        { label: 'Empresas institucionales', value: data.stats.institutional, chip: 'Usan precios corporativos', chipClass: 'chip-muted' },
        { label: 'Tiendas de barrio', value: data.stats.retail, chip: 'Con duenas registradas', chipClass: 'chip-muted' },
        { label: 'Desactivados', value: data.stats.inactive, chip: 'En papelera (recuperables)', chipClass: 'chip-muted' },
    ];
    const [createType, setCreateType] = useState(old?.company_type || 'empresa_institucional');
    const editType = editingCompany?.company_type || 'empresa_institucional';
    const typeLabel = (type) => type === 'tienda_barrio' ? 'Tienda de barrio' : 'Empresa institucional';

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
        <>
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
        </>
    );
}

function LogsPage({ data, flash }) {
    const [detailLog, setDetailLog] = useState(null);

    return (
        <>
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
        </>
    );
}

function BackupsPage({ data, flash, old, csrfToken }) {
    const stats = [
        { label: 'Total respaldos', value: data.stats.total, chip: 'Historial', chipClass: 'chip-muted' },
        { label: 'Completados', value: data.stats.completed, chip: 'OK', chipClass: 'chip-success' },
        { label: 'Automaticos', value: data.stats.automatic, chip: 'Scheduler' },
        { label: 'Ultimo backup', value: data.stats.last_label, chip: 'Fecha/hora', chipClass: 'chip-muted' },
    ];

    return (
        <>
            <FlashMessages flash={flash} />
            <StatsGrid items={stats} />
            <div className="card">
                <div className="chart-head"><div><h4>Programacion automatica</h4></div><span className={`chip ${data.schedule.is_active ? 'chip-success' : ''}`}>{data.schedule.is_active ? 'Activa' : 'Pausada'}</span></div>
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
                                            <form method="POST" action={backup.destroy_url} onSubmit={(e) => !window.confirm('Eliminar este backup del historial?') && e.preventDefault()}>
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
        </>
    );
}

function QuotationsPage({ data, flash, errors, old, csrfToken }) {
    const saleTypeLabels = {
        empresa_institucional: 'Empresa institucional',
        tienda_barrio: 'Tienda de barrio',
        comprador_minorista: 'Comprador minorista',
    };
    const statusLabels = {
        borrador: 'Borrador',
        enviada: 'Enviada',
        aceptada: 'Aceptada',
        rechazada: 'Rechazada',
    };
    const [saleType, setSaleType] = useState(old?.sale_type || 'empresa_institucional');
    const [items, setItems] = useState([{ id: Date.now(), sku: '', product_id: '', product_name: '', quantity: 1, unit_price: 0 }]);
    const stats = [
        { label: 'Total cotizaciones', value: data.stats.total, chip: 'Historico', chipClass: 'chip-muted' },
        { label: 'Enviadas', value: data.stats.sent, chip: 'En ruta' },
        { label: 'Aceptadas', value: data.stats.accepted, chip: 'Ganadas', chipClass: 'chip-success' },
    ];
    const totalAmount = items.reduce((sum, item) => sum + (Number(item.quantity) * Number(item.unit_price)), 0);
    const visibleCompanies = data.companies.filter((company) => saleType === 'tienda_barrio' ? company.company_type === 'tienda_barrio' : company.company_type === 'empresa_institucional');

    const updateItem = (id, patch) => setItems((current) => current.map((item) => item.id === id ? { ...item, ...patch } : item));
    const addItem = () => setItems((current) => [...current, { id: Date.now() + Math.random(), sku: '', product_id: '', product_name: '', quantity: 1, unit_price: 0 }]);
    const removeItem = (id) => setItems((current) => current.length > 1 ? current.filter((item) => item.id !== id) : current);

    const lookupItem = async (id, sku) => {
        if (!sku) return;
        const params = new URLSearchParams({ sku, sale_type: saleType });
        const response = await fetch(`${data.routes.lookup}?${params.toString()}`);
        const payload = await response.json();
        if (!response.ok) {
            updateItem(id, { product_id: '', product_name: payload.message || 'Producto no encontrado.' });
            return;
        }
        updateItem(id, {
            product_id: payload.product_id,
            product_name: `${payload.name} (${payload.sku})`,
            unit_price: payload.price ?? 0,
            quantity: 1,
        });
    };

    return (
        <>
            <FlashMessages flash={flash} />
            <StatsGrid items={stats} />
            <div className="card">
                <div className="chart-head"><h4>Nueva cotizacion</h4></div>
                <form method="POST" action={data.routes.store}>
                    <input type="hidden" name="_token" value={csrfToken} />
                    <div className="form-grid">
                        <div className="form-group">
                            <label>Tipo</label>
                            <select name="sale_type" className="select-light" defaultValue={saleType} onChange={(e) => setSaleType(e.target.value)} required>
                                {Object.entries(saleTypeLabels).map(([key, label]) => <option key={key} value={key}>{label}</option>)}
                            </select>
                        </div>
                        <div className="form-group"><label>Valido hasta</label><input type="date" name="valid_until" className="input-ghost" defaultValue={old?.valid_until || ''} required /></div>
                        <div className="form-group"><label>Estado</label><select name="status" className="select-light" defaultValue={old?.status || 'borrador'} required>{Object.entries(statusLabels).map(([key, label]) => <option key={key} value={key}>{label}</option>)}</select></div>
                        <div className="form-group" style={{ gridColumn: '1 / -1' }}><label>Notas</label><textarea name="notes" className="input-ghost" rows="2" defaultValue={old?.notes || ''} /></div>
                    </div>

                    {saleType === 'comprador_minorista' ? (
                        <div className="form-grid">
                            <div className="form-group" style={{ gridColumn: '1 / -1' }}>
                                <label>Comprador minorista</label>
                                <select name="customer_id" className="select-light" defaultValue={old?.customer_id || ''}>
                                    <option value="">Seleccionar</option>
                                    {data.customers.map((customer) => <option key={customer.id} value={customer.id}>{customer.name} · {customer.city}</option>)}
                                </select>
                            </div>
                        </div>
                    ) : (
                        <div className="form-grid">
                            <div className="form-group" style={{ gridColumn: '1 / -1' }}>
                                <label>Empresa / tienda</label>
                                <select name="company_id" className="select-light" defaultValue={old?.company_id || ''}>
                                    <option value="">Seleccionar</option>
                                    {visibleCompanies.map((company) => <option key={company.id} value={company.id}>{company.name} · {company.city} ({company.nit})</option>)}
                                </select>
                            </div>
                        </div>
                    )}

                    <div className="transfer-items-wrapper" style={{ marginTop: '1.5rem' }}>
                        <div className="chart-head"><h4>Productos de la cotizacion</h4><button type="button" className="pill-button" onClick={addItem}>Agregar producto</button></div>
                        <div>
                            {items.map((item, index) => (
                                <div className="transfer-item-row" key={item.id}>
                                    <div className="form-grid">
                                        <div className="form-group">
                                            <label>Codigo (SKU)</label>
                                            <input type="text" className="input-ghost" value={item.sku} onChange={(e) => updateItem(item.id, { sku: e.target.value })} onBlur={(e) => lookupItem(item.id, e.target.value.trim())} />
                                            <input type="hidden" name={`items[${index}][product_id]`} value={item.product_id} required />
                                        </div>
                                        <div className="form-group"><label>Producto</label><input type="text" className="input-ghost" value={item.product_name} readOnly /></div>
                                        <div className="form-group"><label>Cantidad</label><input type="number" min="1" className="input-ghost" name={`items[${index}][quantity]`} value={item.quantity} onChange={(e) => updateItem(item.id, { quantity: e.target.value })} required /></div>
                                        <div className="form-group"><label>Precio unitario</label><input type="number" min="0" step="0.01" className="input-ghost" name={`items[${index}][unit_price]`} value={item.unit_price} onChange={(e) => updateItem(item.id, { unit_price: e.target.value })} required /></div>
                                    </div>
                                    <button type="button" className="btn-danger remove-quotation-item" style={{ marginTop: '0.8rem' }} onClick={() => removeItem(item.id)}>Quitar</button>
                                </div>
                            ))}
                        </div>
                        <FieldError errors={errors} name="items" />
                        <div style={{ display: 'flex', justifyContent: 'flex-end', marginTop: '1rem' }}><span className="chip">Total estimado: <strong>Bs {totalAmount.toFixed(2)}</strong></span></div>
                    </div>
                    <div style={{ display: 'flex', justifyContent: 'flex-end', marginTop: '1.4rem' }}><button type="submit" className="pill-button">Generar cotizacion</button></div>
                </form>
            </div>
            <div className="card">
                <div className="chart-head"><h4>Filtrar cotizaciones</h4></div>
                <form method="GET" action={data.routes.index} className="form-grid">
                    <div className="form-group"><label>Buscar por ID o cliente</label><input type="text" name="search" className="input-ghost" defaultValue={data.filters.search || ''} /></div>
                    <div className="form-group"><label>Tipo</label><select name="sale_type" className="select-light" defaultValue={data.filters.sale_type || ''}><option value="">Todos</option>{Object.entries(saleTypeLabels).map(([key, label]) => <option key={key} value={key}>{label}</option>)}</select></div>
                    <div className="form-group"><label>Estado</label><select name="status" className="select-light" defaultValue={data.filters.status || ''}><option value="">Todos</option>{Object.entries(statusLabels).map(([key, label]) => <option key={key} value={key}>{label}</option>)}</select></div>
                    <div className="form-group" style={{ alignSelf: 'flex-end' }}><a href={data.routes.index} className="clean-link">Limpiar</a></div>
                </form>
            </div>
            <div className="card">
                <div className="chart-head"><h4>Cotizaciones recientes</h4><span className="chip">{data.quotations.total} registros</span></div>
                <div className="table-wrapper">
                    <table className="data-table">
                        <thead><tr><th>ID</th><th>Cliente</th><th>Tipo</th><th>Estado</th><th>Total</th><th>Valido hasta</th><th>Acciones</th></tr></thead>
                        <tbody>
                            {data.quotations.data.length ? data.quotations.data.map((quotation) => (
                                <tr key={quotation.id}>
                                    <td>#{quotation.id}</td>
                                    <td>{quotation.company ? <><strong>{quotation.company.name}</strong><br /><small>{quotation.company.city}</small></> : <><strong>{quotation.customer?.name || 'Cliente'}</strong><br /><small>{quotation.customer?.city}</small></>}</td>
                                    <td>{saleTypeLabels[quotation.sale_type] || quotation.sale_type}</td>
                                    <td>{statusPill(quotation.status, statusLabels[quotation.status] || quotation.status)}</td>
                                    <td>Bs {Number(quotation.total_amount).toFixed(2)}</td>
                                    <td>{quotation.valid_until_formatted}</td>
                                    <td><a className="btn-secondary" target="_blank" rel="noopener" href={quotation.pdf_url}>PDF</a></td>
                                </tr>
                            )) : <TableEmpty colSpan={7} text="No hay cotizaciones registradas." />}
                        </tbody>
                    </table>
                </div>
                <Pagination pagination={data.quotations} />
            </div>
        </>
    );
}

function ProductsPage({ data, flash, errors, old, csrfToken }) {
    const [editingProduct, setEditingProduct] = useState(null);
    const [viewingProduct, setViewingProduct] = useState(null);
    const stats = [
        { label: 'Productos en catalogo', value: data.stats.catalog, chip: 'Total registrados', chipClass: 'chip-muted' },
        { label: 'Activos para venta', value: data.stats.active, chip: 'Disponibles', chipClass: 'chip-success' },
        { label: 'En pausa', value: data.stats.inactive, chip: 'Revision' },
    ];

    const renderForm = (product = null) => (
        <div className="form-grid">
            <div className="form-group">
                <label>Categoria</label>
                <select name="category_id" className="select-light" defaultValue={product?.category_id ?? old?.category_id ?? ''} required>
                    <option value="">Selecciona</option>
                    {data.categories.map((category) => <option key={category.id} value={category.id}>{category.name}</option>)}
                </select>
            </div>
            <div className="form-group"><label>Nombre</label><input type="text" name="name" className="input-ghost" defaultValue={product?.name ?? old?.name ?? ''} required /></div>
            <div className="form-group"><label>SKU / Codigo</label><input type="text" name="sku" className="input-ghost" defaultValue={product?.sku ?? old?.sku ?? ''} required /></div>
            <div className="form-group"><label>{product ? 'Imagen nueva (opcional)' : 'Imagen del producto'}</label><input type="file" name="image" className="input-ghost" accept="image/*" required={!product} /></div>
            <div className="form-group"><label>Precio publico sugerido</label><input type="number" step="0.01" min="0" name="suggested_price_public" className="input-ghost" defaultValue={product?.suggested_price_public ?? old?.suggested_price_public ?? ''} required /></div>
            <div className="form-group"><label>Precio institucional</label><input type="number" step="0.01" min="0" name="price_institutional" className="input-ghost" defaultValue={product?.price_institutional ?? old?.price_institutional ?? ''} required /></div>
            <div className="form-group"><label>Stock minimo</label><input type="number" min="0" name="min_quantity" className="input-ghost" defaultValue={product?.min_quantity ?? old?.min_quantity ?? 0} required /></div>
            <div className="form-group"><label>Stock maximo</label><input type="number" min="0" name="max_quantity" className="input-ghost" defaultValue={product?.max_quantity ?? old?.max_quantity ?? 0} required /></div>
            <div className="form-group"><label>Descripcion</label><textarea name="description" className="input-ghost" rows="2" defaultValue={product?.description ?? old?.description ?? ''} /></div>
            <div className="form-group"><label>Estado</label><select name="is_active" className="select-light" defaultValue={String(product ? (product.is_active ? 1 : 0) : (old?.is_active ?? 1))} required><option value="1">Activo</option><option value="0">Inactivo</option></select></div>
        </div>
    );

    return (
        <>
            <FlashMessages flash={flash} />
            {data.predictionError && <div className="card"><span className="chip"><i className="ri-alert-line" /> Prediccion IA no disponible: {data.predictionError}</span></div>}
            <StatsGrid items={stats} />
            <div className="card">
                <div className="chart-head"><h4>Crear producto</h4></div>
                <form method="POST" action={data.routes.store} className="form-grid" encType="multipart/form-data">
                    <input type="hidden" name="_token" value={csrfToken} />
                    {renderForm()}
                    <div className="form-group" style={{ alignSelf: 'flex-end' }}><button type="submit" className="pill-button">Guardar producto</button></div>
                </form>
                {['category_id', 'name', 'sku', 'image', 'suggested_price_public', 'price_institutional', 'min_quantity', 'max_quantity', 'description'].map((field) => <FieldError key={field} errors={errors} name={field} />)}
            </div>
            <div className="card">
                <div className="chart-head"><h4>Filtrar catalogo</h4><a className="pill-button" target="_blank" rel="noopener" href={data.routes.report}>Generar catalogo PDF</a></div>
                <form className="form-grid" method="GET" action={data.routes.index}>
                    <div className="form-group"><label>Buscar por nombre, SKU o descripcion</label><input type="text" name="search" className="input-ghost" defaultValue={data.filters.search || ''} /></div>
                    <div className="form-group"><label>Categoria</label><select name="category_id" className="select-light" defaultValue={data.filters.category_id || ''}><option value="">Todas</option>{data.categories.map((category) => <option key={category.id} value={category.id}>{category.name}</option>)}</select></div>
                    <div className="form-group" style={{ alignSelf: 'flex-end' }}><a href={data.routes.index} className="clean-link">Limpiar</a></div>
                </form>
            </div>
            {[
                ['activeProducts', 'Productos activos', true],
                ['inactiveProducts', 'Productos desactivados', false],
            ].map(([key, title, active]) => (
                <div className="card" key={key}>
                    <div className="chart-head"><h4>{title}</h4><span className="chip">{data[key].total} registros</span></div>
                    <div className="table-wrapper">
                        <table className="data-table">
                            <thead><tr><th>Producto</th><th>SKU</th><th>Categoria</th><th>Precio publico</th><th>Precio institucional</th><th>Estado</th><th>Acciones</th></tr></thead>
                            <tbody>
                                {data[key].data.length ? data[key].data.map((product) => (
                                    <tr key={product.id}>
                                        <td><div style={{ display: 'flex', alignItems: 'center', gap: '0.8rem' }}><img src={product.image_url} alt={product.name} style={{ width: '52px', height: '52px', objectFit: 'cover', borderRadius: '1rem', border: '1px solid rgba(255,255,255,0.1)' }} /><div><strong>{product.name}</strong><p style={{ margin: 0, fontSize: '0.8rem', color: 'rgba(255,255,255,0.7)' }}>{product.description_excerpt}</p></div></div></td>
                                        <td>{product.sku}</td>
                                        <td>{product.category?.name || 'Sin categoria'}</td>
                                        <td>Bs {Number(product.suggested_price_public).toFixed(2)}</td>
                                        <td>Bs {Number(product.price_institutional).toFixed(2)}</td>
                                        <td>{statusPill(active ? 'active' : 'inactive', active ? 'Activo' : 'Inactivo')}</td>
                                        <td><div className="actions"><button type="button" className="btn-secondary" onClick={() => setViewingProduct(product)}>Detalles</button>{active && <button type="button" className="btn-secondary" onClick={() => setEditingProduct(product)}>Editar</button>}<form method="POST" action={product.toggle_url}><input type="hidden" name="_token" value={csrfToken} /><input type="hidden" name="_method" value="PATCH" /><button type="submit" className={active ? 'btn-danger' : 'btn-secondary'}>{active ? 'Desactivar' : 'Activar'}</button></form></div></td>
                                    </tr>
                                )) : <TableEmpty colSpan={7} text={active ? 'No hay productos con los filtros aplicados.' : 'No hay productos desactivados.'} />}
                            </tbody>
                        </table>
                    </div>
                    <Pagination pagination={data[key]} />
                </div>
            ))}
            <Modal open={!!editingProduct} title="Editar producto" onClose={() => setEditingProduct(null)}>
                {editingProduct && <form method="POST" action={editingProduct.update_url} encType="multipart/form-data"><input type="hidden" name="_token" value={csrfToken} /><input type="hidden" name="_method" value="PUT" />{renderForm(editingProduct)}<div style={{ marginTop: '1.2rem', display: 'flex', justifyContent: 'flex-end', gap: '0.8rem' }}><button type="button" className="btn-secondary" onClick={() => setEditingProduct(null)}>Cancelar</button><button type="submit" className="pill-button">Guardar cambios</button></div></form>}
            </Modal>
            <Modal open={!!viewingProduct} title="Detalle del producto" onClose={() => setViewingProduct(null)} wide>
                {viewingProduct && <div className="product-detail-layout"><div className="product-detail-media"><img src={viewingProduct.image_url} alt={viewingProduct.name} className="product-detail-image" /></div><div className="product-detail-body"><div className="product-detail-title-row"><div><p className="product-detail-kicker">SKU {viewingProduct.sku}</p><h2>{viewingProduct.name}</h2></div><span className={`status-pill ${viewingProduct.is_active ? 'active' : 'inactive'}`}>{viewingProduct.status_label}</span></div><div className="badge-grid"><span className="chip">Categoria: <strong>{viewingProduct.category?.name || 'Sin categoria'}</strong></span><span className="chip">Stock: <strong>{viewingProduct.stock_total}</strong> uds</span></div><div className="product-detail-section"><h4>Precios</h4><div className="product-detail-grid"><div className="product-detail-field"><span>Publico sugerido</span><strong>Bs {Number(viewingProduct.suggested_price_public).toFixed(2)}</strong></div><div className="product-detail-field"><span>Institucional</span><strong>Bs {Number(viewingProduct.price_institutional).toFixed(2)}</strong></div></div></div><div className="product-detail-section"><h4>Inventario</h4><div className="product-detail-grid"><div className="product-detail-field"><span>Stock minimo</span><strong>{viewingProduct.min_quantity} uds</strong></div><div className="product-detail-field"><span>Stock maximo</span><strong>{viewingProduct.max_quantity} uds</strong></div></div></div><div className="product-detail-section"><h4>Descripcion</h4><p className="product-detail-description">{viewingProduct.description || 'Sin descripcion'}</p></div></div></div>}
            </Modal>
        </>
    );
}

function LotsPage({ data, flash, errors, old, csrfToken }) {
    const [viewProduct, setViewProduct] = useState(null);
    const [editLot, setEditLot] = useState(null);

    return (
        <>
            <FlashMessages flash={flash} />
            {data.modalError && <div className="card"><span className="chip">{data.modalError}</span></div>}
            <div className="card">
                <div className="chart-head"><h4>Crear lote</h4><span className="chip chip-muted">Prueba 1 · Vista por producto</span></div>
                <form method="POST" action={data.routes.store} className="form-grid" id="lotCreateForm">
                    <input type="hidden" name="_token" value={csrfToken} />
                    <div className="form-group"><label>Producto</label><select name="product_id" className="select-light" defaultValue={old?.product_id || ''} required><option value="">Seleccionar</option>{data.products.map((product) => <option key={product.id} value={product.id}>{product.name} ({product.sku})</option>)}</select><FieldError errors={errors} name="product_id" /></div>
                    <div className="form-group"><label>Bodega</label><select name="warehouse_id" className="select-light" defaultValue={old?.warehouse_id || data.filters.warehouse_id || ''} required><option value="">Seleccionar</option>{data.warehouses.map((warehouse) => <option key={warehouse.id} value={warehouse.id}>{warehouse.name}</option>)}</select><FieldError errors={errors} name="warehouse_id" /></div>
                    <div className="form-group"><label>Codigo de lote</label><input type="text" name="lote_code" className="input-ghost" defaultValue={old?.lote_code || ''} /></div>
                    <div className="form-group"><label>Cantidad</label><input type="number" min="1" name="quantity" className="input-ghost" defaultValue={old?.quantity || ''} required /><FieldError errors={errors} name="quantity" /></div>
                    <div className="form-group"><label>Fecha expiracion</label><input type="date" name="expires_at" className="input-ghost" defaultValue={old?.expires_at || ''} required /><FieldError errors={errors} name="expires_at" /></div>
                    <div className="form-group" style={{ alignSelf: 'flex-end' }}><button type="submit" className="pill-button">Guardar lote</button></div>
                </form>
            </div>
            <div className="card" style={{ marginTop: '1rem' }}>
                <div className="chart-head"><h4>Explorar lotes por producto</h4><span className="chip">{data.productsWithLots.total} productos con lotes</span></div>
                <form method="GET" action={data.routes.index} className="lot-filter-bar" style={{ marginTop: '1rem' }}>
                    <div className="form-group"><label>Buscar producto</label><input type="text" name="search" className="input-ghost" defaultValue={data.filters.search || ''} /></div>
                    <div className="form-group"><label>Producto</label><select name="product_id" className="select-light" defaultValue={data.filters.product_id || ''}><option value="">Todos</option>{data.products.map((product) => <option key={product.id} value={product.id}>{product.name}</option>)}</select></div>
                    <div className="form-group"><label>Bodega</label><select name="warehouse_id" className="select-light" defaultValue={data.filters.warehouse_id || ''}><option value="">Todas</option>{data.warehouses.map((warehouse) => <option key={warehouse.id} value={warehouse.id}>{warehouse.name}</option>)}</select></div>
                    <div className="form-group"><label>Vence el</label><input type="date" name="expires_at" className="input-ghost" defaultValue={data.filters.expires_at || ''} /></div>
                    <div className="form-group" style={{ alignSelf: 'flex-end' }}><a href={data.routes.report} className="pill-button" target="_blank" rel="noopener">Generar reporte PDF</a></div>
                    <div className="form-group" style={{ alignSelf: 'flex-end' }}><a href={data.routes.index} className="clean-link">Limpiar</a></div>
                </form>
            </div>
            <div className="lot-product-stack" style={{ marginTop: '1rem' }}>
                {data.productsWithLots.data.length ? data.productsWithLots.data.map((product) => {
                    const critical = Number(product.minimum_stock || 0) > 0 && Number(product.current_stock || 0) <= Number(product.minimum_stock || 0);
                    return (
                        <div className="card lot-product-card" key={product.id}>
                            <div className="lot-product-main">
                                <div className="lot-product-head"><img src={product.image} alt={product.name} className="lot-product-cover" /><div><h3 className="lot-product-title">{product.name}</h3><div className="lot-product-meta"><span className="chip">SKU: {product.sku}</span><span className="chip">{product.category?.name || 'Sin categoria'}</span><span className="chip">{product.lots_count} lote(s)</span></div></div></div>
                                <div className="lot-product-separator" />
                                <div className="lot-stat-grid">
                                    <div className="lot-stat-box"><small>Stock actual</small><strong>{product.current_stock} unidades</strong></div>
                                    <div className="lot-stat-box"><small>Stock minimo</small><strong>{product.minimum_stock || 'No definido'}</strong></div>
                                    <div className="lot-stat-box"><small>Total lotes</small><strong>{product.lots_count}</strong></div>
                                    <div className="lot-stat-box"><small>Proximo vencimiento</small><strong>{product.next_expiry}</strong></div>
                                </div>
                                {critical && <div className="lot-inline-alert"><div><strong>Atencion: este producto necesita reabastecimiento.</strong><div style={{ color: 'rgba(255,255,255,0.76)', marginTop: '0.2rem' }}>El stock actual alcanzo o bajo del minimo configurado.</div></div></div>}
                            </div>
                            <div className="lot-action-panel"><button type="button" className="lot-action-button view" onClick={() => setViewProduct(product)}><i className="ri-stack-line" /> Ver lotes</button></div>
                        </div>
                    );
                }) : <div className="card"><p style={{ margin: 0, textAlign: 'center' }}>No encontramos productos con lotes para esos filtros.</p></div>}
            </div>
            <Pagination pagination={data.productsWithLots} />
            <Modal open={!!viewProduct} title="Detalle de lotes por producto" onClose={() => setViewProduct(null)} wide>
                {viewProduct && <div className="lot-detail-layout"><div className="lot-detail-column"><div className="lot-detail-panel"><div className="lot-detail-header"><div className="lot-product-head"><img src={viewProduct.image} alt={viewProduct.name} className="lot-product-cover" /><div><h2 className="lot-product-title">{viewProduct.name}</h2><div className="lot-product-meta"><span className="chip">SKU: {viewProduct.sku}</span><span className="chip">{viewProduct.category?.name || 'Sin categoria'}</span><span className="chip">{viewProduct.lots_count} lote(s)</span></div></div></div></div></div><div className="lot-detail-panel"><h4>Estado de inventario</h4><div className="lot-detail-grid"><div className="lot-stat-box"><small>Stock actual</small><strong>{viewProduct.current_stock}</strong></div><div className="lot-stat-box"><small>Stock minimo</small><strong>{viewProduct.minimum_stock || 'No definido'}</strong></div><div className="lot-stat-box"><small>Total lotes</small><strong>{viewProduct.lots_count}</strong></div><div className="lot-stat-box"><small>Proximo vencimiento</small><strong>{viewProduct.next_expiry}</strong></div></div></div><div className="lot-detail-panel"><h4>Caracteristicas del producto</h4><p style={{ margin: 0, color: 'rgba(255,255,255,0.78)' }}>{viewProduct.description}</p></div></div><div className="lot-detail-column"><div className="lot-detail-panel"><h4>Historial de lotes</h4><div className="lot-history-scroll"><table className="lot-history-table"><thead><tr><th>Codigo</th><th>Stock</th><th>Bodega</th><th>Vence</th><th>Accion</th></tr></thead><tbody>{viewProduct.history_rows?.length ? viewProduct.history_rows.map((row) => <tr key={row.id}><td>{row.code}</td><td>{row.quantity}</td><td>{row.warehouse}</td><td>{row.expires_at}</td><td><button type="button" className="btn-secondary" onClick={() => setEditLot(row)}>Editar</button></td></tr>) : <TableEmpty colSpan={5} text="Sin lotes registrados." />}</tbody></table></div></div><div className="lot-detail-panel"><h4>Movimientos recientes</h4><div className="lot-movement-list">{viewProduct.movement_history?.length ? viewProduct.movement_history.map((item, index) => <div className="lot-movement-item" key={`${item.lot_code}-${index}`}><strong>{item.type} · {item.quantity > 0 ? '+' : ''}{item.quantity}</strong><div style={{ color: 'rgba(255,255,255,0.78)' }}>Lote: {item.lot_code}</div><div style={{ color: 'rgba(255,255,255,0.62)', marginTop: '0.2rem' }}>{item.note}</div><div style={{ color: 'rgba(255,255,255,0.55)', marginTop: '0.35rem', fontSize: '0.82rem' }}>{item.user} · {item.date}</div></div>) : <p className="lot-empty-state">Sin movimientos recientes.</p>}</div></div></div></div>}
            </Modal>
            <Modal open={!!editLot} title="Editar lote" onClose={() => setEditLot(null)}>
                {editLot && <form method="POST" action={editLot.action} className="form-grid" style={{ gridTemplateColumns: '1fr' }}><input type="hidden" name="_token" value={csrfToken} /><div className="form-group"><label>Codigo de lote</label><input type="text" name="lote_code" className="input-ghost" defaultValue={editLot.code === 'Sin codigo' ? '' : editLot.code} /></div><div className="form-group"><label>Fecha de expiracion</label><input type="date" name="expires_at" className="input-ghost" defaultValue={editLot.raw_expires_at || ''} required /></div><div className="form-group"><label>Cantidad total del lote</label><input type="number" name="quantity" className="input-ghost" defaultValue={editLot.quantity || 0} required /></div><div style={{ display: 'flex', justifyContent: 'flex-end', gap: '0.5rem' }}><button type="button" className="btn-secondary" onClick={() => setEditLot(null)}>Cancelar</button><button type="submit" className="pill-button">Guardar</button></div></form>}
            </Modal>
        </>
    );
}

function TransfersPage({ data, flash, errors, old, csrfToken }) {
    const [items, setItems] = useState([{ id: Date.now(), sku: '', product_id: '', product_name: '', available: '', requested_qty: '', notes: '' }]);
    const [viewTransfer, setViewTransfer] = useState(null);
    const fromWarehouseId = old?.from_warehouse_id || '';
    const stats = [
        { label: 'Traspasos registrados', value: data.stats.total, chip: 'Total historico', chipClass: 'chip-muted' },
        { label: 'Pendientes', value: data.stats.pending, chip: 'Por atender' },
        { label: 'En transito', value: data.stats.in_transit, chip: 'Moviendose' },
        { label: 'Recibidos', value: data.stats.received, chip: 'Confirmados', chipClass: 'chip-success' },
    ];
    const statusLabels = { pendiente: 'Pendiente', en_transito: 'En transito', recibido: 'Recibido' };

    const addItem = () => setItems((current) => [...current, { id: Date.now() + Math.random(), sku: '', product_id: '', product_name: '', available: '', requested_qty: '', notes: '' }]);
    const removeItem = (id) => setItems((current) => current.length > 1 ? current.filter((item) => item.id !== id) : current);
    const updateItem = (id, patch) => setItems((current) => current.map((item) => item.id === id ? { ...item, ...patch } : item));
    const lookupItem = async (id, sku, warehouseId) => {
        if (!sku) return;
        const params = new URLSearchParams({ sku });
        if (warehouseId) params.append('warehouse_id', warehouseId);
        const response = await fetch(`${data.routes.lookup}?${params.toString()}`);
        const payload = await response.json();
        if (!response.ok) {
            updateItem(id, { product_id: '', product_name: payload.message || 'No pudimos encontrar el producto.', available: '0', requested_qty: '' });
            return;
        }
        updateItem(id, {
            product_id: payload.product_id,
            product_name: `${payload.name} (${payload.sku})`,
            available: `${payload.available_quantity ?? 0} uds`,
            requested_qty: payload.available_quantity && payload.available_quantity > 0 ? payload.available_quantity : 1,
        });
    };

    return (
        <>
            <FlashMessages flash={flash} />
            <StatsGrid items={stats} />
            <div className="card">
                <div className="chart-head"><h4>Reportes ejecutivos</h4><a className="pill-button" target="_blank" rel="noopener" href={data.routes.report}>Generar reporte PDF</a></div>
                <p style={{ color: 'rgba(255,255,255,0.7)' }}>Descarga un resumen profesional listo para compartir con los responsables logisticos.</p>
            </div>
            <div className="card">
                <div className="chart-head"><h4>Nuevo traspaso</h4></div>
                <form method="POST" action={data.routes.store}>
                    <input type="hidden" name="_token" value={csrfToken} />
                    <div className="form-grid">
                        <div className="form-group"><label>Almacen origen</label><select name="from_warehouse_id" className="select-light" defaultValue={fromWarehouseId}><option value="">Seleccionar Santa Cruz o Cochabamba</option>{data.sourceWarehouses.map((warehouse) => <option key={warehouse.id} value={warehouse.id}>{warehouse.name} ({warehouse.code})</option>)}</select><FieldError errors={errors} name="from_warehouse_id" /></div>
                        <div className="form-group"><label>Almacen destino</label><input type="text" className="input-ghost" value={`${data.targetWarehouse?.name || 'Deposito La Paz'} (${data.targetWarehouse?.code || 'LPZ'})`} readOnly /></div>
                        <div className="form-group"><label>Fecha estimada</label><input type="date" name="expected_date" className="input-ghost" defaultValue={old?.expected_date || ''} /><FieldError errors={errors} name="expected_date" /></div>
                        <div className="form-group"><label>Estado inicial</label><select name="status" className="select-light" defaultValue={old?.status || 'pendiente'}>{data.statuses.map((status) => <option key={status} value={status}>{statusLabels[status] || status}</option>)}</select><FieldError errors={errors} name="status" /></div>
                        <div className="form-group" style={{ gridColumn: '1 / -1' }}><label>Notas generales</label><textarea name="notes" className="input-ghost" rows="2" defaultValue={old?.notes || ''} /></div>
                    </div>
                    <div className="transfer-items-wrapper">
                        <div className="chart-head" style={{ marginTop: '1.2rem' }}><h4>Productos a traspasar</h4><button type="button" className="pill-button" onClick={addItem}>Agregar producto</button></div>
                        <p style={{ color: 'rgba(255,255,255,0.7)', marginBottom: '1rem' }}>Introduce el codigo (SKU) para rellenar automaticamente los datos y la cantidad disponible en el almacen de origen.</p>
                        <div>{items.map((item, index) => <div className="transfer-item-row" key={item.id}><div className="form-grid"><div className="form-group"><label>Codigo (SKU)</label><input type="text" className="input-ghost" value={item.sku} onChange={(e) => updateItem(item.id, { sku: e.target.value })} onBlur={(e) => lookupItem(item.id, e.target.value.trim(), fromWarehouseId)} /><input type="hidden" name={`items[${index}][product_id]`} value={item.product_id} required /></div><div className="form-group"><label>Producto</label><input type="text" className="input-ghost" value={item.product_name} readOnly /></div><div className="form-group"><label>Disponible en origen</label><input type="text" className="input-ghost" value={item.available} readOnly /></div><div className="form-group"><label>Cantidad solicitada</label><input type="number" min="1" className="input-ghost" name={`items[${index}][requested_qty]`} value={item.requested_qty} onChange={(e) => updateItem(item.id, { requested_qty: e.target.value })} required /></div><div className="form-group" style={{ gridColumn: '1 / -1' }}><label>Notas</label><textarea className="input-ghost" name={`items[${index}][notes]`} rows="1" value={item.notes} onChange={(e) => updateItem(item.id, { notes: e.target.value })} /></div></div><button type="button" className="btn-danger remove-item" onClick={() => removeItem(item.id)}>Quitar</button></div>)}</div>
                        <FieldError errors={errors} name="items" />
                    </div>
                    <div style={{ marginTop: '1.5rem', display: 'flex', justifyContent: 'flex-end' }}><button type="submit" className="pill-button">Guardar traspaso</button></div>
                </form>
            </div>
            <div className="card">
                <div className="chart-head"><h4>Traspasos recientes</h4><span className="chip">{data.transfers.total} registros · mayor a menor</span></div>
                <div className="table-wrapper"><table className="data-table"><thead><tr><th>ID</th><th>Origen</th><th>Destino</th><th>Estado</th><th>Origen de solicitud</th><th>Detalles</th></tr></thead><tbody>{data.transfers.data.length ? data.transfers.data.map((transfer) => <tr key={transfer.id}><td>#{transfer.id}</td><td>{transfer.fromWarehouse?.name || 'No definido'}</td><td>{transfer.toWarehouse?.name || 'N/A'}</td><td>{statusPill(transfer.status, statusLabels[transfer.status] || transfer.status)}</td><td>{transfer.agentRequest ? <span className="source-chip"><i className="ri-robot-2-line" />Sugerencia de agente inteligente</span> : <span className="chip chip-muted">Registro manual</span>}</td><td><button type="button" className="btn-secondary" onClick={() => setViewTransfer(transfer)}>Ver detalles</button></td></tr>) : <TableEmpty colSpan={6} text="Sin traspasos registrados." />}</tbody></table></div>
                <Pagination pagination={data.transfers} />
            </div>
            <Modal open={!!viewTransfer} title={viewTransfer ? `Traspaso #${viewTransfer.id}` : 'Traspaso'} onClose={() => setViewTransfer(null)} wide>
                {viewTransfer && <div style={{ display: 'grid', gap: '1rem' }}><div className="summary">{[{ label: 'Estado', value: statusPill(viewTransfer.status, statusLabels[viewTransfer.status] || viewTransfer.status) }, { label: 'Fecha estimada', value: viewTransfer.expected_date_formatted }, { label: 'Solicitado por', value: viewTransfer.requested_by_label }, { label: 'Aprobado por', value: viewTransfer.agentRequest ? viewTransfer.approved_by_label : '-' }, { label: 'Productos', value: `${viewTransfer.items_count} item(s)` }].map((card) => <div className="summary-card" key={card.label}><strong>{card.label}</strong><span>{card.value}</span></div>)}</div><div className="detail-section"><h4>Origen de solicitud</h4>{viewTransfer.agentRequest ? <><span className="source-chip"><i className="ri-robot-2-line" />Sugerencia de agente inteligente</span><p className="transfer-detail">Solicitud creada: {viewTransfer.agentRequest.created_at_formatted}</p><p className="transfer-detail">Aprobado por: {viewTransfer.approved_by_label}</p>{viewTransfer.agentRequest.priority && <p className="transfer-detail">Prioridad: {viewTransfer.agentRequest.priority}</p>}{viewTransfer.agentRequest.reason && <p className="transfer-detail">Motivo: {viewTransfer.agentRequest.reason}</p>}</> : <span className="chip chip-muted">Registro manual</span>}</div><div className="table-wrapper"><table className="data-table"><thead><tr><th>Producto</th><th>SKU</th><th>Solicitado</th><th>Recibido</th><th>Danado</th><th>Notas</th></tr></thead><tbody>{viewTransfer.items.length ? viewTransfer.items.map((item, index) => <tr key={`${item.sku}-${index}`}><td><strong>{item.product_name}</strong></td><td>{item.sku}</td><td>{item.requested_qty} uds</td><td>{item.received_qty} uds</td><td>{item.damaged_qty} uds</td><td>{item.notes}</td></tr>) : <TableEmpty colSpan={6} text="Sin productos registrados." />}</tbody></table></div><div className="detail-section"><h4>Notas generales</h4><p style={{ margin: 0, color: 'rgba(255,255,255,0.74)' }}>{viewTransfer.notes}</p></div><div style={{ display: 'flex', justifyContent: 'flex-end', gap: '0.75rem' }}><button type="button" className="btn-secondary" onClick={() => setViewTransfer(null)}>Cerrar</button><a className="pill-button" target="_blank" rel="noopener" href={viewTransfer.report_url}>Generar reporte PDF</a></div></div>}
            </Modal>
        </>
    );
}

function SalesPage({ data, flash, errors, old, csrfToken }) {
    const saleTypeLabels = { empresa_institucional: 'Empresa institucional', tienda_barrio: 'Tienda de barrio', comprador_minorista: 'Comprador minorista' };
    const statusLabels = { sin_entregar: 'Sin entregar', entregado: 'Entregado' };
    const [saleType, setSaleType] = useState(old?.sale_type || 'empresa_institucional');
    const [items, setItems] = useState([{ id: Date.now(), sku: '', product_id: '', product_name: '', available: '', quantity: '', unit_price: '' }]);
    const [detailSale, setDetailSale] = useState(null);
    const [statusSale, setStatusSale] = useState(null);
    const stats = [
        { label: 'Ventas registradas', value: data.stats.count, chip: 'Total historico', chipClass: 'chip-muted' },
        { label: 'Ventas entregadas', value: data.stats.delivered, chip: 'Completadas', chipClass: 'chip-success' },
        { label: 'Monto total', value: `Bs ${Number(data.stats.total_amount).toFixed(2)}`, chip: 'Historico', chipClass: 'chip-muted' },
    ];
    const total = items.reduce((sum, item) => sum + Number(item.quantity || 0) * Number(item.unit_price || 0), 0);
    const visibleCompanies = data.companies.filter((company) => saleType === 'tienda_barrio' ? company.company_type === 'tienda_barrio' : company.company_type === 'empresa_institucional');
    const updateItem = (id, patch) => setItems((current) => current.map((item) => item.id === id ? { ...item, ...patch } : item));
    const addItem = () => setItems((current) => [...current, { id: Date.now() + Math.random(), sku: '', product_id: '', product_name: '', available: '', quantity: '', unit_price: '' }]);
    const removeItem = (id) => setItems((current) => current.length > 1 ? current.filter((item) => item.id !== id) : current);
    const lookupItem = async (id, sku) => {
        const warehouseId = data.laPazWarehouse?.id;
        if (!warehouseId || !sku) return;
        const params = new URLSearchParams({ sku, sale_type: saleType, warehouse_id: warehouseId });
        const response = await fetch(`${data.routes.lookup}?${params.toString()}`);
        const payload = await response.json();
        if (!response.ok) {
            updateItem(id, { product_id: '', product_name: payload.message || 'No pudimos encontrar el producto.', available: 'Fuera de stock', quantity: '', unit_price: '' });
            return;
        }
        const available = payload.available_quantity ?? 0;
        updateItem(id, { product_id: payload.product_id, product_name: `${payload.name} (${payload.sku})`, available: available > 0 ? `${available} uds` : 'Fuera de stock', quantity: available > 0 ? 1 : '', unit_price: payload.price ?? 0 });
    };

    return (
        <>
            <FlashMessages flash={flash} />
            <StatsGrid items={stats} />
            <div className="card">
                <div className="chart-head"><h4>Nueva venta</h4></div>
                <form method="POST" action={data.routes.store}>
                    <input type="hidden" name="_token" value={csrfToken} />
                    <div className="form-grid">
                        <div className="form-group"><label>Tipo de venta</label><select name="sale_type" className="select-light" defaultValue={saleType} onChange={(e) => setSaleType(e.target.value)} required>{Object.entries(saleTypeLabels).map(([key, label]) => <option key={key} value={key}>{label}</option>)}</select></div>
                        <div className="form-group"><label>Estado de la venta</label><select name="status" className="select-light" defaultValue={old?.status || 'sin_entregar'} required>{Object.entries(statusLabels).map(([key, label]) => <option key={key} value={key}>{label}</option>)}</select></div>
                        <input type="hidden" name="warehouse_id" value={data.laPazWarehouse?.id || ''} />
                        <div className="form-group" style={{ gridColumn: '1 / -1' }}><label>Almacen asignado</label><input type="text" className="input-ghost" value={data.laPazWarehouse ? `${data.laPazWarehouse.name} (${data.laPazWarehouse.code})` : 'Configura el almacén de La Paz para permitir ventas'} disabled /></div>
                        <div className="form-group"><label>Metodo de pago</label><select name="payment_method" className="select-light" defaultValue={old?.payment_method || ''} required><option value="">Seleccionar</option>{Object.entries(data.paymentLabels).map(([key, label]) => <option key={key} value={key}>{label}</option>)}</select></div>
                        <div className="form-group"><label>Direccion entrega</label><input type="text" name="delivery_address" className="input-ghost" defaultValue={old?.delivery_address || ''} /></div>
                        <div className="form-group"><label>Ciudad entrega</label><select name="delivery_city_id" className="select-light" defaultValue={old?.delivery_city_id || ''} required><option value="">Seleccionar</option>{data.cities.map((city) => <option key={city.id} value={city.id}>{city.name}</option>)}</select></div>
                    </div>
                    {saleType === 'comprador_minorista' ? (
                        <div className="form-grid" id="customerFieldset"><div className="form-group" style={{ gridColumn: '1 / -1' }}><label>Comprador minorista</label><select name="customer_id" className="select-light" defaultValue={old?.customer_id || ''}><option value="">Seleccionar</option>{data.customers.map((customer) => <option key={customer.id} value={customer.id}>{customer.name} - {customer.city}{customer.nit ? ` (NIT: ${customer.nit})` : ''}</option>)}</select></div></div>
                    ) : (
                        <div className="form-grid" id="companyFieldset"><div className="form-group" style={{ gridColumn: '1 / -1' }}><label>Empresa / Tienda</label><select name="company_id" className="select-light" defaultValue={old?.company_id || ''}><option value="">Seleccionar</option>{visibleCompanies.map((company) => <option key={company.id} value={company.id}>{company.name} - {company.city} (NIT: {company.nit})</option>)}</select></div></div>
                    )}
                    <div className="transfer-items-wrapper" style={{ marginTop: '1.5rem' }}>
                        <div className="chart-head"><h4>Productos de la venta</h4><button type="button" className="pill-button" onClick={addItem}>Agregar producto</button></div>
                        <p style={{ color: 'rgba(255,255,255,0.7)', marginBottom: '1rem' }}>Ingresa el codigo (SKU) para obtener el precio sugerido y la disponibilidad del almacen seleccionado.</p>
                        <div>{items.map((item, index) => <div className="transfer-item-row" key={item.id}><div className="form-grid"><div className="form-group"><label>Codigo (SKU)</label><input type="text" className="input-ghost" value={item.sku} onChange={(e) => updateItem(item.id, { sku: e.target.value })} onBlur={(e) => lookupItem(item.id, e.target.value.trim())} /><input type="hidden" name={`items[${index}][product_id]`} value={item.product_id} required /></div><div className="form-group"><label>Producto</label><input type="text" className="input-ghost" value={item.product_name} readOnly /></div><div className="form-group"><label>Disponible</label><input type="text" className="input-ghost" value={item.available} readOnly /></div><div className="form-group"><label>Cantidad</label><input type="number" min="1" className="input-ghost" name={`items[${index}][quantity]`} value={item.quantity} onChange={(e) => updateItem(item.id, { quantity: e.target.value })} required /></div><div className="form-group"><label>Precio unitario</label><input type="number" min="0" step="0.01" className="input-ghost" name={`items[${index}][unit_price]`} value={item.unit_price} onChange={(e) => updateItem(item.id, { unit_price: e.target.value })} required /></div></div><button type="button" className="btn-danger remove-item" style={{ marginTop: '0.8rem' }} onClick={() => removeItem(item.id)}>Quitar</button></div>)}</div>
                        <FieldError errors={errors} name="items" />
                        <div style={{ display: 'flex', justifyContent: 'flex-end', marginTop: '1rem' }}><span className="chip">Total estimado: <strong>Bs {total.toFixed(2)}</strong></span></div>
                    </div>
                    <div style={{ display: 'flex', justifyContent: 'flex-end', marginTop: '1.5rem' }}><button type="submit" className="pill-button">Registrar venta</button></div>
                </form>
            </div>
            <div className="card"><div className="chart-head"><h4>Filtrar ventas</h4></div><form method="GET" action={data.routes.index} className="form-grid"><div className="form-group"><label>Buscar por ID o cliente</label><input type="text" name="search" className="input-ghost" defaultValue={data.filters.search || ''} /></div><div className="form-group"><label>Tipo de venta</label><select name="sale_type" className="select-light" defaultValue={data.filters.sale_type || ''}><option value="">Todas</option>{Object.entries(saleTypeLabels).map(([key, label]) => <option key={key} value={key}>{label}</option>)}</select></div><div className="form-group"><label>Estado</label><select name="status" className="select-light" defaultValue={data.filters.status || ''}><option value="">Todos</option>{Object.entries(statusLabels).map(([key, label]) => <option key={key} value={key}>{label}</option>)}</select></div><div className="form-group" style={{ alignSelf: 'flex-end' }}><a href={data.routes.index} className="clean-link">Limpiar</a></div></form></div>
            <div className="card"><div className="chart-head"><h4>Ventas recientes</h4><span className="chip">{data.sales.total} registros</span></div><div className="table-wrapper"><table className="data-table"><thead><tr><th>ID</th><th>Cliente</th><th>Tipo</th><th>Estado</th><th>Pago</th><th>Monto</th><th>Almacen</th><th>Fecha</th><th>Acciones</th></tr></thead><tbody>{data.sales.data.length ? data.sales.data.map((sale) => <tr key={sale.id}><td>#{sale.id}</td><td>{sale.company ? <><strong>{sale.company.name}</strong><br /><small>{sale.company.city}</small></> : sale.customer ? <><strong>{sale.customer.name}</strong><br /><small>{sale.customer.city}</small></> : '-'}</td><td>{saleTypeLabels[sale.sale_type] || sale.sale_type}</td><td>{statusPill(sale.status, statusLabels[sale.status] || sale.status)}</td><td>{sale.payment_label}</td><td>Bs {Number(sale.total_amount).toFixed(2)}</td><td>{sale.warehouse?.name || '-'}</td><td>{sale.created_at_formatted}</td><td><button type="button" className="pill-button ghost" onClick={() => setDetailSale(sale)}>Ver</button> <button type="button" className="btn-secondary" onClick={() => setStatusSale(sale)}>Actualizar</button></td></tr>) : <TableEmpty colSpan={9} text="No hay ventas registradas." />}</tbody></table></div><Pagination pagination={data.sales} /></div>
            <Modal open={!!detailSale} title="Detalle de venta" onClose={() => setDetailSale(null)} wide>{detailSale && <div style={{ display: 'grid', gap: '1rem' }}><div><p style={{ margin: 0 }}><strong>Cliente:</strong> {detailSale.company?.name || detailSale.customer?.name || 'Sin cliente'}</p><p style={{ margin: 0 }}><strong>Tipo:</strong> {detailSale.sale_type}</p><p style={{ margin: 0 }}><strong>Estado:</strong> {detailSale.status}</p><p style={{ margin: 0 }}><strong>Pago:</strong> {detailSale.payment_label}</p><p style={{ margin: 0 }}><strong>Almacen:</strong> {detailSale.warehouse?.name || ''}</p><p style={{ margin: 0 }}><strong>Total:</strong> Bs {Number(detailSale.total_amount).toFixed(2)}</p></div><div>{detailSale.items.map((item, index) => <div key={`${item.sku}-${index}`} style={{ border: '1px solid rgba(255,255,255,0.12)', borderRadius: '0.75rem', padding: '0.75rem 1rem', marginBottom: '0.5rem' }}><p style={{ margin: 0 }}><strong>{item.product}</strong> ({item.sku})</p><p style={{ margin: '0.2rem 0 0' }}>Cantidad: {item.qty}</p><p style={{ margin: '0.1rem 0 0' }}>Precio: Bs {Number(item.price || 0).toFixed(2)}</p></div>)}</div></div>}</Modal>
            <Modal open={!!statusSale} title="Actualizar venta" onClose={() => setStatusSale(null)}>{statusSale && <form method="POST" action={statusSale.update_url}><input type="hidden" name="_token" value={csrfToken} /><input type="hidden" name="_method" value="PUT" /><div className="form-grid"><div className="form-group"><label>Estado</label><select name="status" className="select-light" defaultValue={statusSale.status} required>{Object.entries(statusLabels).map(([key, label]) => <option key={key} value={key}>{label}</option>)}</select></div></div><div style={{ display: 'flex', justifyContent: 'flex-end', gap: '0.75rem', marginTop: '1rem' }}><button type="button" className="btn-secondary" onClick={() => setStatusSale(null)}>Cancelar</button><button type="submit" className="pill-button">Guardar</button></div></form>}</Modal>
        </>
    );
}

function ChartCanvas({ configFactory, deps = [], style }) {
    const canvasRef = useRef(null);

    useEffect(() => {
        if (!canvasRef.current) return undefined;
        const chart = new Chart(canvasRef.current, configFactory());
        return () => chart.destroy();
    }, deps); // eslint-disable-line react-hooks/exhaustive-deps

    return <canvas ref={canvasRef} style={style} />;
}

function AgentOverviewPage({ data }) {
    const [selected, setSelected] = useState(null);
    const hasCapacityAlerts = Array.isArray(data.raw?.capacity_alerts) && data.raw.capacity_alerts.length > 0;
    const chartPalette = {
        primary: '#566d30',
        light: '#7b814f',
        accent: '#b9be96',
        soft: '#e0e3c7',
        warm: '#f6f6f3',
        plum: '#7b814f',
        line: '#b9be96',
    };

    const forecastChartConfig = () => ({
        type: 'bar',
        data: {
            labels: data.charts.forecast.labels,
            datasets: [{ data: data.charts.forecast.data, backgroundColor: 'rgba(86,109,48,0.82)', borderColor: chartPalette.primary, borderWidth: 1, borderRadius: 12 }],
        },
        options: { plugins: { legend: { display: false } }, scales: { x: { ticks: { color: '#fff' }, grid: { display: false } }, y: { ticks: { color: '#fff' }, grid: { color: 'rgba(255,255,255,0.08)' } } } },
    });
    const restockChartConfig = () => ({
        type: 'doughnut',
        data: {
            labels: data.charts.restock.labels,
            datasets: [{ data: data.charts.restock.data, backgroundColor: [chartPalette.primary, chartPalette.light, chartPalette.accent, chartPalette.soft, chartPalette.warm, chartPalette.plum] }],
        },
        options: { plugins: { legend: { labels: { color: '#fff' } } } },
    });

    return (
        <>
            <div className="hero">
                <div>
                    <h2 style={{ margin: 0, color: '#fff' }}>Resumen del agente inteligente</h2>
                    <p style={{ margin: '0.25rem 0 0', color: 'rgba(255,255,255,0.75)' }}>Prediccion con limites de capacidad y alertas mejoradas.</p>
                    {hasCapacityAlerts && <div style={{ marginTop: '0.6rem' }}><span className="capacity-badge"><i className="ri-rocket-2-line" /> Sugerencia IA: aumenta capacidad</span></div>}
                </div>
                <div style={{ display: 'flex', gap: '0.5rem', justifyContent: 'flex-end', flexWrap: 'wrap' }}>
                    <a href="#chartsSection" className="pill-button ghost"><i className="ri-line-chart-line" />Ver graficos</a>
                    <a href={data.routes.report} className="pill-button" target="_blank" rel="noopener"><i className="ri-file-text-line" />Descargar reporte</a>
                </div>
            </div>
            <StatsGrid items={[
                { label: 'Restock sugeridos', value: data.stats.restock, chip: 'Ordenes recomendadas', chipClass: 'chip-muted' },
                { label: 'Alertas de stock', value: data.stats.alerts_low, chip: 'Bajo inventario' },
                { label: 'Lotes por vencer', value: data.stats.alerts_expiring, chip: '30 dias' },
                { label: 'Sugerencias de capacidad', value: data.stats.capacity, chip: 'Aumentar limite', chipClass: 'chip-muted' },
            ]} />
            <div className="charts-grid" style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(280px, 1fr))', gap: '1rem', marginTop: '1.5rem' }} id="chartsSection">
                <div className="card chart-placeholder"><div className="chart-head"><h4>Top productos (pronostico)</h4><span className="chip">Demanda semanal</span></div>{data.charts.forecast.labels?.length ? <ChartCanvas configFactory={forecastChartConfig} deps={[data.charts.forecast]} style={{ maxHeight: '260px' }} /> : null}</div>
                <div className="card chart-placeholder"><div className="chart-head"><h4>Distribucion de restock</h4><span className="chip">Sugerencias</span></div>{data.charts.restock.labels?.length ? <ChartCanvas configFactory={restockChartConfig} deps={[data.charts.restock]} style={{ maxHeight: '260px' }} /> : null}</div>
            </div>
            <div className="card" style={{ marginTop: '1rem' }}>
                <div className="chart-head"><h4>Predicciones IA por producto</h4><span className="chip">{data.forecastItems.length} registros</span></div>
                <div className="table-wrapper"><table className="data-table"><thead><tr><th>Producto</th><th>Pronostico</th><th>Tendencia</th><th>Stock</th><th>Ventas (6 sem)</th><th>Ventas (6 mes)</th><th>Acciones</th></tr></thead><tbody>{data.forecastItems.length ? data.forecastItems.map((item) => <tr key={item.name}><td>{item.name}</td><td>{item.forecast} uds</td><td><span className={`trend-pill ${item.trend === 'alza' ? 'up' : item.trend === 'baja' ? 'down' : 'steady'}`}><i className="ri-line-chart-line" />{item.trend}</span></td><td>{item.stock} uds</td><td>{item.weekly.recent_total} uds</td><td>{item.monthly.recent_total} uds</td><td><button type="button" className="pill-button ghost" onClick={() => setSelected(item)}>Detalles</button></td></tr>) : <TableEmpty colSpan={7} text="Sin datos de Prediccion." />}</tbody></table></div>
            </div>
            {hasCapacityAlerts && <div className="card" style={{ marginTop: '1rem' }}><div className="chart-head"><h4>Capacidad al limite</h4><span className="chip">{data.raw.capacity_alerts.length} productos</span></div><div className="alert-grid">{data.raw.capacity_alerts.map((cap) => <div className="summary-card" key={cap.name} style={{ background: 'rgba(251,191,36,0.06)', border: '1px solid rgba(251,191,36,0.25)' }}><div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', gap: '0.5rem' }}><div><strong>{cap.name}</strong><p style={{ margin: '0.15rem 0', color: 'rgba(255,255,255,0.75)' }}>Limite: {cap.max_quantity ?? 'N/D'} uds</p><p style={{ margin: 0, color: '#fcd34d' }}>{cap.note ?? 'Sugerencia: aumentar capacidad'}</p></div><span className="capacity-badge"><i className="ri-flashlight-line" /> IA</span></div></div>)}</div></div>}
            <div className="card"><div className="chart-head"><h4>Demanda por producto</h4><span className="chip chip-muted">Series de las ultimas semanas</span></div><div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit,minmax(280px,1fr))', gap: '0.8rem' }}>{data.forecastItems.slice(0, 8).map((item, idx) => <div className="spark-card" key={`${item.name}-${idx}`}><div><strong>{item.name}</strong><p style={{ margin: '0.3rem 0 0', color: 'rgba(255,255,255,0.8)' }}>Demanda prevista: {item.forecast} uds</p>{item.capacity_flag ? <span className="capacity-badge" style={{ marginTop: '0.4rem' }}><i className="ri-alert-line" /> limite alcanzado</span> : <span className="chip chip-muted" style={{ marginTop: '0.4rem', display: 'inline-block' }}>{item.trend}</span>}</div>{item.history?.length ? <ChartCanvas configFactory={() => ({ type: 'line', data: { labels: item.history.map((_, i) => `S${i + 1}`), datasets: [{ data: item.history, borderColor: chartPalette.line, backgroundColor: 'rgba(185,190,150,0.16)', tension: 0.35, fill: true, pointRadius: 0 }] }, options: { plugins: { legend: { display: false } }, scales: { x: { display: false }, y: { display: false } }, elements: { line: { borderWidth: 2 } } } })} deps={[item.history]} /> : null}</div>)}</div></div>
            <Modal open={!!selected} title="Detalle de Prediccion" onClose={() => setSelected(null)} wide>{selected && <div className="ai-modal-body"><div className="ai-card"><p style={{ margin: 0, color: 'rgba(255,255,255,0.7)' }}>Producto</p><h2 style={{ margin: '0.2rem 0 0.6rem' }}>{selected.name}</h2><div className="badge-grid"><span className={`trend-pill ${selected.trend === 'alza' ? 'up' : selected.trend === 'baja' ? 'down' : 'steady'}`}><i className="ri-line-chart-line" />{selected.trend}</span><span className="chip">Pronostico: <strong>{selected.forecast}</strong> uds</span><span className="chip">Stock: <strong>{selected.stock}</strong> uds</span><span className="chip">Min: <strong>{selected.min_quantity || 'N/D'}</strong></span><span className="chip">Max: <strong>{selected.max_quantity || 'N/D'}</strong></span></div><div className="ai-highlight" style={{ marginTop: '0.6rem' }}><p style={{ margin: 0, color: 'rgba(255,255,255,0.7)' }}>Nota IA</p><p style={{ margin: '0.25rem 0 0', color: 'rgba(255,255,255,0.85)' }}>{selected.capacity_note || 'Prediccion generada con historico reciente y topes de stock.'}</p><div className="badge-grid" style={{ marginTop: '0.4rem' }}><span className="chip">Ventas 6 sem: <strong>{selected.weekly.recent_total}</strong> uds</span><span className="chip">Ventas 6 mes: <strong>{selected.monthly.recent_total}</strong> uds</span></div></div><div className="ai-highlight" style={{ marginTop: '0.6rem' }}><p style={{ margin: 0, color: 'rgba(255,255,255,0.7)' }}>Lotes por vencer</p><ul style={{ margin: '0.35rem 0 0', paddingLeft: '1rem', color: 'rgba(255,255,255,0.9)' }}>{selected.expiring_lots?.length ? selected.expiring_lots.map((lot, index) => <li key={`${lot.code}-${index}`}>{`Lote ${lot.code} - ${lot.expires_in_days} dias (${lot.quantity} uds${lot.warehouse ? ` - ${lot.warehouse}` : ''})`}</li>) : <li style={{ color: 'rgba(255,255,255,0.7)' }}>Sin lotes proximos.</li>}</ul></div><div className="ai-highlight" style={{ marginTop: '0.6rem' }}><p style={{ margin: 0, color: 'rgba(255,255,255,0.7)' }}>Recomendacion de restock</p>{selected.restock?.suggested_qty ? <div style={{ marginTop: '0.35rem' }}><p style={{ margin: 0 }}>Cantidad sugerida: <strong>{selected.restock.suggested_qty} uds</strong></p><p style={{ margin: '0.2rem 0 0', color: 'rgba(255,255,255,0.8)' }}>{selected.restock.reason || ''}</p></div> : <p style={{ margin: '0.35rem 0 0', color: 'rgba(255,255,255,0.7)' }}>Sin sugerencia.</p>}</div></div><div style={{ display: 'flex', flexDirection: 'column', gap: '0.75rem' }}><div className="ai-chart-mini"><div className="chart-head" style={{ marginBottom: '0.25rem' }}><h4 style={{ margin: 0 }}>Ventas por semana</h4><span className="chip chip-muted">Serie reciente</span></div><ChartCanvas configFactory={() => ({ type: 'bar', data: { labels: selected.weekly.labels, datasets: [{ data: selected.weekly.data, backgroundColor: 'rgba(86,109,48,0.82)', borderRadius: 10, borderSkipped: false }] }, options: { plugins: { legend: { display: false } }, scales: { x: { ticks: { color: '#f6f6f3' }, grid: { display: false } }, y: { ticks: { color: 'rgba(255,255,255,0.85)' }, grid: { color: 'rgba(255,255,255,0.08)' }, beginAtZero: true } } } })} deps={[selected.weekly]} /></div><div className="ai-chart-mini"><div className="chart-head" style={{ marginBottom: '0.25rem' }}><h4 style={{ margin: 0 }}>Ventas por mes</h4><span className="chip chip-muted">Ultimos meses</span></div><ChartCanvas configFactory={() => ({ type: 'bar', data: { labels: selected.monthly.labels, datasets: [{ data: selected.monthly.data, backgroundColor: 'rgba(224,227,199,0.86)', borderRadius: 10, borderSkipped: false }] }, options: { plugins: { legend: { display: false } }, scales: { x: { ticks: { color: '#f6f6f3' }, grid: { display: false } }, y: { ticks: { color: 'rgba(255,255,255,0.85)' }, grid: { color: 'rgba(255,255,255,0.08)' }, beginAtZero: true } } } })} deps={[selected.monthly]} /></div></div></div>}</Modal>
        </>
    );
}

function AgentReplenishmentPage({ data, flash, csrfToken }) {
    const [requestModal, setRequestModal] = useState(null);
    const [alertModal, setAlertModal] = useState(null);
    const statusClass = data.agentOnline ? 'online' : 'offline';

    const decisionClass = (severity) => severity === 'critical' ? 'urgent' : severity;

    return (
        <>
            <FlashMessages flash={flash} />
            <div className="agent-hero"><div><h2 style={{ margin: 0, color: '#fff' }}>Reposicion inteligente</h2><p style={{ margin: '0.25rem 0 0', color: 'rgba(255,255,255,0.75)' }}>Evaluaciones del agente para anticipar faltantes y aprobar traspasos con control humano.</p>{data.error && <p style={{ margin: '0.55rem 0 0', color: '#fecdd3' }}>{data.error}</p>}</div><div className="agent-toolbar"><span className={`status-pill ${statusClass}`}><i className={data.agentOnline ? 'ri-checkbox-circle-line' : 'ri-close-circle-line'} />{data.agentOnline ? 'Agente en linea' : 'Agente sin conexion'}</span><span className="agent-auto-chip"><i className="ri-refresh-line" /> Monitoreo automatico</span><span className="chip chip-muted">Ultima revision: {data.lastRunAt}</span></div></div>
            <div className="card agent-search-island"><div className="chart-head"><div><h4>Buscar productos evaluados</h4><span className="section-kicker">Filtra evaluaciones, alertas, solicitudes pendientes e historial por nombre, SKU o categoria.</span></div><a className="pill-button" target="_blank" rel="noopener" href={data.routes.report}><i className="ri-file-pdf-line" /> Reporte PDF</a></div><form method="GET" action={data.routes.index} className="form-grid" style={{ marginTop: '1rem' }}><div className="form-group"><label>Producto, SKU o categoria</label><input type="text" name="search" className="input-ghost" defaultValue={data.search || ''} /></div><div className="form-group"><label>Categoria</label><select name="category_id" className="select-light" defaultValue={data.categoryId || ''}><option value="">Todas</option>{data.categories.map((category) => <option key={category.id} value={category.id}>{category.name}</option>)}</select></div><div className="form-group" style={{ alignSelf: 'flex-end' }}><button type="submit" className="pill-button">Buscar</button></div><div className="form-group" style={{ alignSelf: 'flex-end' }}><a href={data.routes.index} className="clean-link">Limpiar</a></div></form></div>
            <div className="agent-grid">{[{ label: 'Productos evaluados', value: data.forecastsTotal, text: 'Analisis de demanda para los proximos 7 dias.' }, { label: 'Alertas de stock bajo', value: data.alerts.low_stock?.length || 0, text: 'Productos que pueden quedar por debajo del nivel seguro.' }, { label: 'Lotes vencidos o por vencer', value: data.alerts.expiring?.length || 0, text: 'Inventario vencido o con fecha de vencimiento cercana.' }, { label: 'Solicitudes por revisar', value: data.pendingRequestsTotal, text: 'Recomendaciones esperando aprobacion o rechazo.' }].map((card) => <div className="card" key={card.label}><h3>{card.label}</h3><div className="value">{card.value}</div><p className="agent-card-label">{card.text}</p></div>)}</div>
            <div className="card agent-section"><div className="chart-head"><div><h4>Evaluaciones de reposicion</h4><span className="section-kicker">Demanda, stock disponible y decision recomendada por producto.</span></div><span className="chip chip-muted">{data.forecasts.total} registros</span></div><div className="table-wrapper"><table className="data-table"><thead><tr><th>Producto</th><th>Demanda 7 dias</th><th>Stock actual</th><th>Traspasos previstos</th><th>Stock final estimado</th><th>Stock minimo</th><th>Decision</th></tr></thead><tbody>{data.forecasts.data.length ? data.forecasts.data.map((item, index) => <tr key={`${item.name}-${index}`} className={String(item.priority || '').toLowerCase() === 'urgente' ? 'urgent-row' : ''}><td>{item.name}</td><td>{Number(item.forecast_7_days).toFixed(0)} uds</td><td>{item.stock} uds</td><td>{item.in_transit} uds</td><td>{item.result < 0 ? `Faltan ${Math.abs(item.result)} uds` : `${Number(item.result).toFixed(0)} uds`}</td><td>{item.safety_threshold} uds</td><td><span className={`decision-chip ${String(item.priority || '').toLowerCase() === 'urgente' ? 'urgent' : ''}`}><i className={String(item.priority || '').toLowerCase() === 'urgente' ? 'ri-alarm-warning-line' : 'ri-lightbulb-flash-line'} />{item.decision}{String(item.priority || '').toLowerCase() === 'urgente' ? ' - Urgente' : ''}</span></td></tr>) : <TableEmpty colSpan={7} text="Sin evaluaciones del agente." />}</tbody></table></div><div className="agent-pagination"><Pagination pagination={data.forecasts} /></div></div>
            <div className="card agent-section"><div className="chart-head"><div><h4>Solicitudes pendientes del agente</h4><span className="section-kicker">Aprobacion humana antes de crear o confirmar el traspaso operativo.</span></div><span className="chip chip-muted">{data.pendingRequests.total} pendientes</span></div><div className="table-wrapper"><table className="data-table"><thead><tr><th>Producto</th><th>Cantidad solicitada</th><th>Prioridad</th><th>Motivo resumido</th><th>Detalle</th></tr></thead><tbody>{data.pendingRequests.data.length ? data.pendingRequests.data.map((request) => { const urgent = String(request.priority || '').toLowerCase() === 'urgente'; const parsed = request.parsedReason; return <tr key={request.id} className={urgent ? 'urgent-row' : ''}><td>{request.product_name}</td><td>{request.requested_qty} uds</td><td><span className={`decision-chip ${urgent ? 'urgent' : ''}`}>{request.priority}</span></td><td style={{ maxWidth: '460px' }}>{parsed ? <div className="reason-summary"><p><strong>Reposicion necesaria.</strong>{parsed.result < 0 ? <> Faltan <strong>{Math.abs(parsed.result)} uds</strong> para completar la demanda prevista y mantener el stock minimo.</> : <> Quedarian <strong>{parsed.result} uds</strong>, por debajo del stock minimo.</>}</p><div className="reason-metrics"><span className="metric-chip">Stock: {parsed.stock} uds</span><span className="metric-chip warn">Stock minimo: {parsed.threshold} uds</span></div></div> : (request.reason || 'El agente recomienda revisar este producto.')}</td><td><button type="button" className="btn-secondary" onClick={() => setRequestModal(request)}>Ver detalles</button></td></tr>; }) : <TableEmpty colSpan={5} text="No hay solicitudes pendientes del agente." />}</tbody></table></div><div className="agent-pagination"><Pagination pagination={data.pendingRequests} /></div></div>
            <div className="card agent-section"><div className="chart-head"><div><h4>Alertas por producto</h4><span className="section-kicker">Revisa stock bajo, lotes por vencer y lotes vencidos por producto.</span></div><span className="chip chip-muted">Verde normal - Amarillo menor a 5 meses - Rojo menor a 2 meses - Morado vencido</span></div><div className="alert-product-grid">{data.alertProductCards.length ? data.alertProductCards.map((productAlert) => <div className={`alert-product-card ${productAlert.severity}`} key={productAlert.id}><div className="alert-card-head"><div className="alert-product-title"><img src={productAlert.image} alt={productAlert.name} /><div><h4>{productAlert.name}</h4><span className="section-kicker">SKU: {productAlert.sku || 'N/D'} - {productAlert.category}</span></div></div><span className={`decision-chip ${decisionClass(productAlert.severity)}`}>{productAlert.severity_label}</span></div><div className="metric-row" style={{ justifyContent: 'flex-start' }}>{Object.entries(productAlert.metrics || {}).map(([label, value]) => <span key={label} className={`metric-chip ${label.toLowerCase().includes('faltante') ? 'danger' : label.toLowerCase().includes('minimo') ? 'warn' : ''}`}>{label}: {value}</span>)}</div><div className="alert-card-list">{(productAlert.problems || []).slice(0, 2).map((item, index) => <div className="alert-card-item" key={`${productAlert.id}-problem-${index}`}><strong>{item.label}</strong><p>{item.message}</p></div>)}</div><div className="alert-card-actions"><button type="button" className="btn-secondary" onClick={() => setAlertModal(productAlert)}>Detalles</button></div></div>) : <div className="alert-product-card"><h4 style={{ margin: 0 }}>Sin alertas operativas</h4><p style={{ margin: 0, color: 'rgba(255,255,255,0.7)' }}>No hay productos criticos en este momento.</p></div>}</div></div>
            <div className="card agent-section"><div className="chart-head"><div><h4>Historial de decisiones</h4><span className="section-kicker">Solicitudes creadas, aprobadas o rechazadas por el flujo del agente.</span></div><span className="chip chip-muted">{data.recentRequests.total} registros</span></div><div className="table-wrapper"><table className="data-table"><thead><tr><th>Fecha</th><th>Producto</th><th>Cantidad</th><th>Estado</th><th>Decision humana</th><th>Traspaso relacionado</th></tr></thead><tbody>{data.recentRequests.data.length ? data.recentRequests.data.map((request) => <tr key={request.id}><td>{request.created_at_formatted}</td><td>{request.product_name}</td><td>{request.requested_qty} uds</td><td>{request.status}</td><td>{request.decision_label}</td><td>{request.transfer_label}</td></tr>) : <TableEmpty colSpan={6} text="Sin historial de solicitudes del agente." />}</tbody></table></div><div className="agent-pagination"><Pagination pagination={data.recentRequests} /></div></div>
            <Modal open={!!requestModal} title={requestModal ? `Solicitud de traspaso #${requestModal.id}` : 'Solicitud'} onClose={() => setRequestModal(null)} wide>{requestModal && (() => { const urgent = String(requestModal.priority || '').toLowerCase() === 'urgente'; const parsed = requestModal.parsedReason; const stock = parsed?.stock ?? 0; const transfers = parsed?.transfers ?? 0; const demand = parsed?.demand ?? 0; const result = parsed?.result ?? null; const threshold = parsed?.threshold ?? 0; const missing = result !== null && result < 0 ? Math.abs(result) : 0; const scale = Math.max(stock, transfers, demand, threshold, missing, 1); const pct = (n) => Math.min(100, Math.round((n / scale) * 100)); return <div className="modal-body"><div className="summary"><div className="summary-card"><strong>Cantidad solicitada</strong><span>{requestModal.requested_qty} uds</span></div><div className="summary-card"><strong>Prioridad</strong><span className={`decision-chip ${urgent ? 'urgent' : ''}`}>{requestModal.priority}</span></div><div className="summary-card"><strong>Estado</strong><span>{requestModal.status}</span></div><div className="summary-card"><strong>Creada</strong><span>{requestModal.created_at_formatted}</span></div></div><div className="agent-detail-section"><h4 style={{ margin: '0 0 0.75rem' }}>Detalle de reposicion</h4>{parsed ? <><p style={{ margin: '0 0 1rem', color: 'rgba(255,255,255,0.78)' }}><strong>Reposicion necesaria.</strong>{missing > 0 ? <> Faltan <strong>{missing} unidades</strong> para completar la demanda prevista de 7 dias y mantener el stock minimo.</> : <> Despues de cubrir la demanda prevista quedarian <strong>{result} uds</strong>, por debajo del stock minimo.</>}</p><div className="agent-bars">{[{ label: 'Stock actual', value: stock, pctClass: '' }, { label: 'Traspasos previstos', value: transfers, pctClass: '' }, { label: 'Demanda 7 dias', value: demand, pctClass: 'warn' }, { label: 'Stock minimo', value: threshold, pctClass: 'warn' }, ...(missing > 0 ? [{ label: 'Unidades faltantes', value: missing, pctClass: 'danger' }] : [])].map((row) => <div className="agent-bar-row" key={row.label}><div className="agent-bar-head"><span>{row.label}</span><span>{row.value} uds</span></div><div className="agent-bar-track"><div className={`agent-bar-fill ${row.pctClass}`} style={{ width: `${pct(row.value)}%` }} /></div></div>)}</div></> : <p style={{ margin: 0, color: 'rgba(255,255,255,0.78)' }}>{requestModal.reason || 'El agente recomienda revisar este producto.'}</p>}</div><div className="agent-detail-section"><h4 style={{ margin: '0 0 0.75rem' }}>Decision humana</h4><div className="agent-decision-actions"><div className="agent-decision-card"><h4>Aprobar traspaso</h4><form method="POST" action={requestModal.approve_url}><input type="hidden" name="_token" value={csrfToken} /><input type="text" name="decision_reason" className="input-ghost" placeholder="Motivo de aprobacion" /><button type="submit" className="pill-button">Aprobar traspaso</button></form></div><div className="agent-decision-card"><h4>Rechazar solicitud</h4><form method="POST" action={requestModal.reject_url}><input type="hidden" name="_token" value={csrfToken} /><input type="text" name="decision_reason" className="input-ghost" placeholder="Motivo de rechazo" /><button type="submit" className="pill-button ghost">Rechazar traspaso</button></form></div></div></div></div>; })()}</Modal>
            <Modal open={!!alertModal} title={alertModal?.name || 'Alerta'} onClose={() => setAlertModal(null)} wide>{alertModal && <div className="modal-body"><div className="summary"><div className="summary-card"><strong>SKU</strong>{alertModal.sku || 'N/D'}</div><div className="summary-card"><strong>Categoria</strong>{alertModal.category}</div><div className="summary-card"><strong>Estado</strong>{alertModal.severity_label}</div></div><div className="agent-detail-section"><h4 style={{ margin: '0 0 0.75rem' }}>Problemas detectados</h4><div className="alert-card-list">{alertModal.problems.map((problem, index) => <div className="alert-card-item" key={`${alertModal.id}-modal-problem-${index}`}><strong>{problem.label}</strong><p>{problem.message}</p>{problem.meta && <div className="metric-row" style={{ justifyContent: 'flex-start', marginTop: '0.55rem' }}>{Object.entries(problem.meta).map(([label, value]) => <span key={label} className={`metric-chip ${label.toLowerCase().includes('faltante') ? 'danger' : label.toLowerCase().includes('minimo') ? 'warn' : ''}`}>{label}: {value}</span>)}</div>}</div>)}</div></div><div className="agent-detail-section"><h4 style={{ margin: '0 0 0.75rem' }}>Lotes del producto</h4><div className="agent-lot-list">{alertModal.lots?.length ? alertModal.lots.map((lot, index) => <div className={`agent-lot-row ${lot.status}`} key={`${lot.code}-${index}`}><div><strong>{lot.label} - {lot.code}</strong><p>{lot.message}</p></div><div className="metric-row"><span className="metric-chip">Cantidad: {lot.quantity} uds</span><span className={`metric-chip ${lot.status === 'expired' ? 'danger' : lot.status === 'warning' ? 'warn' : ''}`}>Vence: {lot.expires_at}</span></div></div>) : <div className="alert-card-item"><strong>Sin lotes activos</strong><p>No hay lotes con cantidad disponible para este producto.</p></div>}</div></div></div>}</Modal>
        </>
    );
}

export default function AdminResourcePage(props) {
    const { resource, layout, csrfToken, logoutAction } = props;

    const content = useMemo(() => {
        switch (resource) {
            case 'users':
                return <UsersPage {...props} />;
            case 'companies':
                return <CompaniesPage {...props} />;
            case 'categories':
                return <CategoriesPage {...props} />;
            case 'logs':
                return <LogsPage {...props} />;
            case 'backups':
                return <BackupsPage {...props} />;
            case 'quotations':
                return <QuotationsPage {...props} />;
            case 'products':
                return <ProductsPage {...props} />;
            case 'lots':
                return <LotsPage {...props} />;
            case 'transfers':
                return <TransfersPage {...props} />;
            case 'sales':
                return <SalesPage {...props} />;
            case 'agentOverview':
                return <AgentOverviewPage {...props} />;
            case 'agentReplenishment':
                return <AgentReplenishmentPage {...props} />;
            default:
                return <div className="card"><h3>Pendiente de migracion</h3><p>Esta pantalla admin aun no se monto en React.</p></div>;
        }
    }, [resource, props]);

    return (
        <DashboardShell sidebar={layout.sidebar} topbar={layout.topbar} csrfToken={csrfToken} logoutAction={logoutAction}>
            {content}
        </DashboardShell>
    );
}
