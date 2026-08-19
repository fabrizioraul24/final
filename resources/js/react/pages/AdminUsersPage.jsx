import React, { useState } from 'react';
import DashboardShell from '../components/admin/DashboardShell';
import { FieldError, FlashMessages, Modal, Pagination, TableEmpty } from '../components/admin/common';

function statusPill(status, label) {
    return <span className={`status-pill ${status}`}>{label}</span>;
}

export default function AdminUsersPage({ layout, data, flash, errors, old, csrfToken, logoutAction }) {
    const [editingUser, setEditingUser] = useState(null);

    return (
        <DashboardShell sidebar={layout.sidebar} topbar={layout.topbar} csrfToken={csrfToken} logoutAction={logoutAction}>
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
        </DashboardShell>
    );
}
