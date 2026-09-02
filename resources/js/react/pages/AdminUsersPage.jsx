import React, { useState } from 'react';
import DashboardShell from '../components/admin/DashboardShell';
import { FieldError, FlashMessages, Modal, Pagination, TableEmpty } from '../components/admin/common';

function getRoleBadge(roleName) {
    const role = (roleName || '').toLowerCase();
    if (role.includes('admin')) {
        return <span className="fit-role-badge admin"><i className="ri-shield-user-fill" /> {roleName}</span>;
    }
    if (role.includes('vendedor') || role.includes('ventas')) {
        return <span className="fit-role-badge vendor"><i className="ri-store-2-fill" /> {roleName}</span>;
    }
    if (role.includes('almacen') || role.includes('lotes')) {
        return <span className="fit-role-badge warehouse"><i className="ri-archive-drawer-fill" /> {roleName}</span>;
    }
    return <span className="fit-role-badge default"><i className="ri-user-3-fill" /> {roleName || 'Sin rol'}</span>;
}

function UserInitials({ name, muted = false }) {
    const initials = (name || 'Usuario')
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0])
        .join('')
        .toUpperCase();

    return <span className={`fit-user-avatar${muted ? ' muted' : ''}`}>{initials}</span>;
}

function getRoleMetric(roleName = '') {
    return roleName.toLowerCase().includes('admin') ? 'staff' : 'all';
}

function UsersTable({ users, inactive = false, csrfToken, onEdit }) {
    return (
        <div className="fit-table-card">
            <div className="fit-table-scroll">
                <table className="fit-users-table">
                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Email</th>
                            <th>Username</th>
                            <th>Rol</th>
                            <th>Estado</th>
                            <th>{inactive ? 'Ultima modif.' : 'Fecha alta'}</th>
                            <th className="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        {users.length ? users.map((user) => (
                            <tr key={user.id} className={inactive ? 'is-muted' : ''}>
                                <td>
                                    <div className="fit-user-cell">
                                        <UserInitials name={user.name} muted={inactive} />
                                        <div>
                                            <strong>{user.name}</strong>
                                            <small>ID: #{user.id}</small>
                                        </div>
                                    </div>
                                </td>
                                <td><span className="fit-muted-text">{user.email}</span></td>
                                <td><code className="fit-code">@{user.username}</code></td>
                                <td>{getRoleBadge(user.role?.name)}</td>
                                <td>
                                    <span className={`fit-status ${inactive ? 'inactive' : 'active'}`}>
                                        <span /> {inactive ? 'Inactivo' : 'Activo'}
                                    </span>
                                </td>
                                <td><span className="fit-muted-text">{inactive ? user.updated_at_formatted : user.created_at_formatted || '-'}</span></td>
                                <td className="text-right">
                                    <div className="fit-row-actions">
                                        {!inactive && (
                                            <button type="button" className="fit-action-button warning" onClick={() => onEdit(user)} title="Editar">
                                                <i className="ri-edit-2-line" />
                                            </button>
                                        )}
                                        <form method="POST" action={user.toggle_url}>
                                            <input type="hidden" name="_token" value={csrfToken} />
                                            <input type="hidden" name="_method" value="PATCH" />
                                            <button type="submit" className={`fit-action-button ${inactive ? 'success' : 'danger'}`} title={inactive ? 'Reactivar' : 'Desactivar'}>
                                                <i className={inactive ? 'ri-user-follow-line' : 'ri-user-unfollow-line'} />
                                            </button>
                                        </form>
                                        {inactive && (
                                            <form
                                                method="POST"
                                                action={user.destroy_url}
                                                onSubmit={(event) => !window.confirm(`Esta seguro de eliminar definitivamente a ${user.name}?`) && event.preventDefault()}
                                            >
                                                <input type="hidden" name="_token" value={csrfToken} />
                                                <input type="hidden" name="_method" value="DELETE" />
                                                <button type="submit" className="fit-action-button danger" title="Eliminar">
                                                    <i className="ri-delete-bin-line" />
                                                </button>
                                            </form>
                                        )}
                                    </div>
                                </td>
                            </tr>
                        )) : (
                            <TableEmpty colSpan={7} text="No se encontraron usuarios con los criterios seleccionados." />
                        )}
                    </tbody>
                </table>
            </div>
        </div>
    );
}

export default function AdminUsersPage({ layout, data, flash, errors, old, csrfToken, logoutAction }) {
    const [editingUser, setEditingUser] = useState(null);
    const [createUserOpen, setCreateUserOpen] = useState(() => Object.keys(errors || {}).length > 0);
    const adminRole = data.roles.find((role) => getRoleMetric(role.name) === 'staff');
    const initialMetric = adminRole && String(data.filters.role_id || '') === String(adminRole.id) ? 'staff' : 'all';
    const [activeMetric, setActiveMetric] = useState(initialMetric);

    const activeTotal = data?.activeUsers?.total || 0;
    const inactiveTotal = data?.inactiveUsers?.total || 0;
    const grandTotal = activeTotal + inactiveTotal;
    const staffTotal = data?.stats?.staff || (activeMetric === 'staff' ? grandTotal : 0);

    const showActive = activeMetric === 'all' || activeMetric === 'active' || activeMetric === 'staff';
    const showInactive = activeMetric === 'all' || activeMetric === 'inactive' || activeMetric === 'staff';

    const buildIndexUrl = (params = {}) => {
        const url = new URL(data.routes.index, window.location.origin);
        const search = params.search ?? data.filters.search;
        const roleId = params.role_id ?? data.filters.role_id;

        if (search) {
            url.searchParams.set('search', search);
        }

        if (roleId) {
            url.searchParams.set('role_id', roleId);
        }

        return `${url.pathname}${url.search}`;
    };

    const handleMetricClick = (key) => {
        if (key === 'staff' && adminRole) {
            window.location.href = buildIndexUrl({ role_id: adminRole.id });
            return;
        }

        if (key === 'all' && data.filters.role_id) {
            window.location.href = buildIndexUrl({ role_id: '' });
            return;
        }

        setActiveMetric(key);
    };

    const metricCards = [
        { key: 'all', label: 'Total Usuarios', value: grandTotal, hint: 'Haz clic para ver todos', icon: 'ri-group-line', tone: 'indigo' },
        { key: 'active', label: 'Usuarios Activos', value: activeTotal, hint: 'Filtrar por activos', icon: 'ri-user-follow-line', tone: 'green' },
        { key: 'inactive', label: 'Usuarios Inactivos', value: inactiveTotal, hint: 'Filtrar por inactivos', icon: 'ri-user-forbid-line', tone: 'rose' },
        { key: 'staff', label: 'Administradores', value: staffTotal, hint: 'Personal autorizado', icon: 'ri-shield-check-line', tone: 'amber' },
    ];

    return (
        <DashboardShell sidebar={layout.sidebar} topbar={layout.topbar} csrfToken={csrfToken} logoutAction={logoutAction}>
            <div className="fit-users-page">
                <FlashMessages flash={flash} />

                <section className="fit-users-header">
                    <div className="fit-users-header-left">
                        <div className="fit-header-icon"><i className="ri-group-line" /></div>
                        <div>
                            <h1>Directorio y Metricas de Usuarios</h1>
                            <p>Filtra, busca, gestiona y exporta el reporte completo de usuarios del sistema.</p>
                        </div>
                    </div>

                    <div className="fit-users-header-actions">
                        <a className="fit-outline-button" target="_blank" rel="noopener noreferrer" href={data.routes.report}>
                            <i className="ri-download-2-line" />
                            <span>Descargar Reporte PDF</span>
                        </a>
                        <button type="button" className="fit-primary-button" onClick={() => setCreateUserOpen(true)}>
                            <i className="ri-user-add-line" />
                            <span>Crear Usuario</span>
                        </button>
                    </div>
                </section>

                <section className="fit-metric-grid">
                    {metricCards.map((card) => (
                        <button
                            type="button"
                            key={card.key}
                            className={`fit-metric-card ${card.tone}${activeMetric === card.key ? ' active' : ''}`}
                            onClick={() => handleMetricClick(card.key)}
                        >
                            <span>
                                <small>{card.label}</small>
                                <strong>{card.value}</strong>
                                <em>{card.hint}</em>
                            </span>
                            <span className="fit-metric-icon"><i className={card.icon} /></span>
                        </button>
                    ))}
                </section>

                <section className="fit-filter-card">
                    <form method="GET" action={data.routes.index} className="fit-filter-form">
                        <label className="fit-search-control" htmlFor="search">
                            <i className="ri-search-line" />
                            <input
                                type="text"
                                id="search"
                                name="search"
                                placeholder="Buscar usuario, correo o username..."
                                defaultValue={data.filters.search || ''}
                            />
                        </label>

                        <label className="fit-select-control" htmlFor="role_id">
                            <i className="ri-filter-3-line" />
                            <select id="role_id" name="role_id" defaultValue={data.filters.role_id || ''}>
                                <option value="">Todos los Roles</option>
                                {data.roles.map((role) => (
                                    <option key={role.id} value={role.id}>{role.name}</option>
                                ))}
                            </select>
                        </label>

                        <button type="submit" className="fit-primary-button compact">
                            <i className="ri-search-line" /> Buscar
                        </button>

                        {(data.filters.search || data.filters.role_id || activeMetric !== 'all') && (
                            <a href={data.routes.index} className="fit-clear-button">Limpiar Filtros</a>
                        )}
                    </form>
                </section>

                {showActive && (
                    <section className="fit-section">
                        <div className="fit-section-head">
                            <div>
                                <h2>Personal Activo en Sistema</h2>
                                <p>Directorio principal de usuarios autorizados con acceso al panel.</p>
                            </div>
                            <span className="fit-section-badge green">{data.activeUsers.total} activos</span>
                        </div>
                        <UsersTable users={data.activeUsers.data} csrfToken={csrfToken} onEdit={setEditingUser} />
                        <Pagination pagination={data.activeUsers} />
                    </section>
                )}

                {showInactive && (
                    <section className="fit-section">
                        <div className="fit-section-head">
                            <div>
                                <h2>Usuarios Inactivos o Suspendidos</h2>
                                <p>Cuentas desactivadas temporalmente o pendientes de eliminacion.</p>
                            </div>
                            <span className="fit-section-badge rose">{data.inactiveUsers.total} inactivos</span>
                        </div>
                        <UsersTable users={data.inactiveUsers.data} inactive csrfToken={csrfToken} onEdit={setEditingUser} />
                        <Pagination pagination={data.inactiveUsers} />
                    </section>
                )}

                <Modal open={createUserOpen} title="Registro Oficial de Usuarios" onClose={() => setCreateUserOpen(false)} wide contentClassName="fit-modal-content">
                    <form method="POST" action={data.routes.store} className="fit-register-form">
                        <input type="hidden" name="_token" value={csrfToken} />

                        <div className="fit-form-grid">
                            <div className="fit-form-field">
                                <label htmlFor="name">Nombre Completo *</label>
                                <input id="name" type="text" name="name" placeholder="Ej. Camila Rojas" defaultValue={old?.name || ''} required />
                                <FieldError errors={errors} name="name" />
                            </div>

                            <div className="fit-form-field">
                                <label htmlFor="email">Correo Electronico *</label>
                                <input id="email" type="email" name="email" placeholder="nombre@pilbolivia.com.bo" defaultValue={old?.email || ''} required />
                                <FieldError errors={errors} name="email" />
                            </div>

                            <div className="fit-form-field">
                                <label htmlFor="username">Nombre de Usuario *</label>
                                <input id="username" type="text" name="username" placeholder="camila.rojas" defaultValue={old?.username || ''} required />
                                <FieldError errors={errors} name="username" />
                            </div>

                            <div className="fit-form-field">
                                <label htmlFor="password">Contrasena *</label>
                                <input id="password" type="password" name="password" placeholder="Minimo 8 caracteres" required />
                                <FieldError errors={errors} name="password" />
                            </div>

                            <div className="fit-form-field span-2">
                                <label htmlFor="role_create">Rol de Usuario *</label>
                                <select id="role_create" name="role_id" defaultValue={old?.role_id || ''} required>
                                    <option value="">Selecciona un rol para el usuario</option>
                                    {data.roles.map((role) => (
                                        <option key={role.id} value={role.id}>{role.name}</option>
                                    ))}
                                </select>
                                <FieldError errors={errors} name="role_id" />
                            </div>
                        </div>

                        <div className="fit-modal-footer">
                            <button type="button" className="fit-outline-button" onClick={() => setCreateUserOpen(false)}>Cancelar</button>
                            <button type="submit" className="fit-primary-button">
                                <i className="ri-checkbox-circle-line" /> Registrar Usuario
                            </button>
                        </div>
                    </form>
                </Modal>

                <Modal open={!!editingUser} title="Editar Usuario" onClose={() => setEditingUser(null)} wide contentClassName="fit-modal-content">
                    {editingUser && (
                        <form method="POST" action={editingUser.update_url} className="fit-register-form">
                            <input type="hidden" name="_token" value={csrfToken} />
                            <input type="hidden" name="_method" value="PUT" />

                            <div className="fit-form-grid">
                                <div className="fit-form-field">
                                    <label htmlFor="edit_name">Nombre Completo *</label>
                                    <input id="edit_name" type="text" name="name" defaultValue={editingUser.name} required />
                                </div>
                                <div className="fit-form-field">
                                    <label htmlFor="edit_email">Correo Electronico *</label>
                                    <input id="edit_email" type="email" name="email" defaultValue={editingUser.email} required />
                                </div>
                                <div className="fit-form-field">
                                    <label htmlFor="edit_username">Nombre de Usuario *</label>
                                    <input id="edit_username" type="text" name="username" defaultValue={editingUser.username} required />
                                </div>
                                <div className="fit-form-field">
                                    <label htmlFor="edit_password">Nueva Contrasena (Opcional)</label>
                                    <input id="edit_password" type="password" name="password" placeholder="Dejar en blanco para mantener actual" />
                                </div>
                                <div className="fit-form-field span-2">
                                    <label htmlFor="edit_role">Rol Asignado *</label>
                                    <select id="edit_role" name="role_id" defaultValue={editingUser.role_id} required>
                                        {data.roles.map((role) => (
                                            <option key={role.id} value={role.id}>{role.name}</option>
                                        ))}
                                    </select>
                                </div>
                            </div>

                            <div className="fit-modal-footer">
                                <button type="button" className="fit-outline-button" onClick={() => setEditingUser(null)}>Cancelar</button>
                                <button type="submit" className="fit-primary-button">
                                    <i className="ri-save-3-line" /> Guardar Cambios
                                </button>
                            </div>
                        </form>
                    )}
                </Modal>
            </div>
        </DashboardShell>
    );
}
