import React, { useState } from 'react';
import DashboardShell from '../components/admin/DashboardShell';
import { FieldError, FlashMessages, Modal, Pagination, TableEmpty } from '../components/admin/common';

function statusPill(status, label) {
    return <span className={`status-pill ${status}`}>{label}</span>;
}

export default function AdminUsersPage({ layout, data, flash, errors, old, csrfToken, logoutAction }) {
    const [editingUser, setEditingUser] = useState(null);
    const [createUserOpen, setCreateUserOpen] = useState(() => Object.keys(errors || {}).length > 0);

    return (
        <DashboardShell sidebar={layout.sidebar} topbar={layout.topbar} csrfToken={csrfToken} logoutAction={logoutAction}>
            <FlashMessages flash={flash} />

            <div className="card user-directory-header">
                <div>
                    <h2>Usuarios</h2>
                </div>
                <button type="button" className="pill-button user-create-open-button" onClick={() => setCreateUserOpen(true)}>
                    <i className="ri-user-add-line" /> Crear usuario
                </button>
            </div>

            <Modal open={createUserOpen} title="Crear nuevo usuario" onClose={() => setCreateUserOpen(false)} wide contentClassName="user-create-modal">
                <form method="POST" action={data.routes.store} className="user-create-form">
                    <input type="hidden" name="_token" value={csrfToken} />
                    <div className="user-create-layout">
                        <div className="user-create-fields">
                            <div className="form-group">
                                <label htmlFor="name">Nombre completo</label>
                                <input type="text" id="name" name="name" className="input-ghost" placeholder="Ej. Camila Rojas" defaultValue={old?.name || ''} required />
                                <FieldError errors={errors} name="name" />
                            </div>
                            <div className="form-group">
                                <label htmlFor="email">Correo electronico</label>
                                <input type="email" id="email" name="email" className="input-ghost" placeholder="nombre@pil.com" defaultValue={old?.email || ''} required />
                                <FieldError errors={errors} name="email" />
                            </div>
                            <div className="form-group">
                                <label htmlFor="username">Nombre de usuario</label>
                                <input type="text" id="username" name="username" className="input-ghost" placeholder="camila.rojas" defaultValue={old?.username || ''} required />
                                <FieldError errors={errors} name="username" />
                            </div>
                            <div className="form-group">
                                <label htmlFor="password">Contrasena</label>
                                <input type="password" id="password" name="password" className="input-ghost" placeholder="Minimo 8 caracteres" required />
                                <FieldError errors={errors} name="password" />
                            </div>
                            <div className="form-group user-create-role-field">
                                <label htmlFor="role_create">Rol</label>
                                <select id="role_create" name="role_id" className="select-light" defaultValue={old?.role_id || ''} required>
                                    <option value="">Selecciona un rol</option>
                                    {data.roles.map((role) => <option key={role.id} value={role.id}>{role.name}</option>)}
                                </select>
                                <FieldError errors={errors} name="role_id" />
                            </div>
                        </div>
                    </div>

                    <div className="user-create-actions">
                        <button type="button" className="btn-secondary user-create-cancel" onClick={() => setCreateUserOpen(false)}>Cancelar</button>
                        <button type="submit" className="pill-button user-create-submit"><i className="ri-user-add-line" /> Crear usuario</button>
                    </div>
                </form>
            </Modal>

            <div className="card user-filter-card">
                <div className="chart-head">
                    <div><span className="section-kicker">Directorio</span><h4>Buscar usuarios</h4></div>
                    <a className="pill-button user-report-button" target="_blank" rel="noopener" href={data.routes.report}><i className="ri-file-chart-line" /> Reporte PDF</a>
                </div>
                <form method="GET" className="form-grid user-filter-form" action={data.routes.index}>
                    <div className="form-group">
                        <label htmlFor="search"><i className="ri-search-line" /> Nombre, email o usuario</label>
                        <input type="text" id="search" name="search" className="input-ghost" placeholder="Ej. camila o camila@pil.com" defaultValue={data.filters.search || ''} />
                    </div>
                    <div className="form-group">
                        <label htmlFor="role_id"><i className="ri-filter-3-line" /> Rol</label>
                        <select id="role_id" name="role_id" className="select-light" defaultValue={data.filters.role_id || ''}>
                            <option value="">Todos los roles</option>
                            {data.roles.map((role) => <option key={role.id} value={role.id}>{role.name}</option>)}
                        </select>
                    </div>
                    <div className="user-filter-actions">
                        <a href={data.routes.index} className="clean-link"><i className="ri-refresh-line" /> Limpiar</a>
                        <button type="submit" className="pill-button user-filter-submit"><i className="ri-search-line" /> Buscar</button>
                    </div>
                </form>
            </div>

            <div className="card user-table-card user-table-card--active">
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

            <div className="card user-table-card user-table-card--inactive">
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

            <Modal open={!!editingUser} title="Editar usuario" onClose={() => setEditingUser(null)} wide contentClassName="user-edit-modal">
                {editingUser && (
                    <form method="POST" action={editingUser.update_url} className="user-edit-form">
                        <input type="hidden" name="_token" value={csrfToken} />
                        <input type="hidden" name="_method" value="PUT" />
                        <div className="user-edit-fields">
                            <div className="form-group"><label htmlFor="edit_name">Nombre completo</label><input id="edit_name" type="text" name="name" className="input-ghost" defaultValue={editingUser.name} required /></div>
                            <div className="form-group"><label htmlFor="edit_email">Correo electronico</label><input id="edit_email" type="email" name="email" className="input-ghost" defaultValue={editingUser.email} required /></div>
                            <div className="form-group"><label htmlFor="edit_username">Nombre de usuario</label><input id="edit_username" type="text" name="username" className="input-ghost" defaultValue={editingUser.username} required /></div>
                            <div className="form-group"><label htmlFor="edit_password">Nueva contrasena</label><input id="edit_password" type="password" name="password" className="input-ghost" placeholder="Sin cambios" /></div>
                            <div className="form-group user-edit-role-field"><label htmlFor="edit_role">Rol</label><select id="edit_role" name="role_id" className="select-light" defaultValue={editingUser.role_id} required>{data.roles.map((role) => <option key={role.id} value={role.id}>{role.name}</option>)}</select></div>
                        </div>
                        <div className="user-edit-actions">
                            <button type="button" className="btn-secondary user-edit-cancel" onClick={() => setEditingUser(null)}>Cancelar</button>
                            <button type="submit" className="pill-button user-edit-submit"><i className="ri-save-3-line" /> Guardar cambios</button>
                        </div>
                    </form>
                )}
            </Modal>
        </DashboardShell>
    );
}
