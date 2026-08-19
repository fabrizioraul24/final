import React, { useState } from 'react';
import DashboardShell from '../components/admin/DashboardShell';
import { FieldError, FlashMessages, Modal, Pagination, StatsGrid, TableEmpty } from '../components/admin/common';

function statusPill(status, label) {
    return <span className={`status-pill ${status}`}>{label}</span>;
}

export default function AdminCategoriesPage({ layout, data, flash, errors, old, csrfToken, logoutAction }) {
    const [editingCategory, setEditingCategory] = useState(null);
    const stats = [
        { label: 'Total categorias', value: data.summary.total, chip: 'Activas + desactivadas' },
        { label: 'Con productos asignados', value: data.summary.with_products, chip: 'Operativas', chipClass: 'chip-muted' },
        { label: 'Desactivadas', value: data.summary.inactive, chip: 'En pausa', chipClass: 'chip-muted' },
    ];

    return (
        <DashboardShell sidebar={layout.sidebar} topbar={layout.topbar} csrfToken={csrfToken} logoutAction={logoutAction}>
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
        </DashboardShell>
    );
}
