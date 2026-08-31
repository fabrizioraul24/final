import React, { useState } from 'react';
import DashboardShell from '../components/admin/DashboardShell';
import { FieldError, FlashMessages, Modal, Pagination, StatsGrid, TableEmpty } from '../components/admin/common';

function statusPill(status, label) {
    return <span className={`status-pill ${status}`}>{label}</span>;
}

function CategoryIdentity({ category }) {
    return (
        <div className="category-identity">
            <span className="category-identity__icon"><i className="ri-price-tag-3-line" /></span>
            <div><strong>{category.name}</strong><p>{category.description_excerpt}</p></div>
        </div>
    );
}

export default function AdminCategoriesPage({ layout, data, flash, errors, old, csrfToken, logoutAction }) {
    const [createCategoryOpen, setCreateCategoryOpen] = useState(Boolean(errors && Object.keys(errors).length));
    const [editingCategory, setEditingCategory] = useState(null);
    const [categoryToDeactivate, setCategoryToDeactivate] = useState(null);
    const stats = [
        { label: 'Total categorias', value: data.summary.total, chip: 'Activas + desactivadas', cardClass: 'category-stat-card category-stat-card--total', icon: 'ri-price-tag-3-line' },
        { label: 'Con productos asignados', value: data.summary.with_products, chip: 'Operativas', chipClass: 'chip-success', cardClass: 'category-stat-card category-stat-card--active', icon: 'ri-checkbox-circle-line' },
        { label: 'Desactivadas', value: data.summary.inactive, chip: 'En pausa', chipClass: 'chip-muted', cardClass: 'category-stat-card category-stat-card--inactive', icon: 'ri-pause-circle-line' },
    ];

    return (
        <DashboardShell sidebar={layout.sidebar} topbar={layout.topbar} csrfToken={csrfToken} logoutAction={logoutAction}>
            <FlashMessages flash={flash} />
            <StatsGrid items={stats} />

            <div className="card user-directory-header">
                <div><h2>Categorias</h2></div>
                <button type="button" className="pill-button user-create-open-button" onClick={() => setCreateCategoryOpen(true)}><i className="ri-price-tag-3-line" /> Crear categoria</button>
            </div>

            <section className="card user-filter-card category-filter-card">
                <div className="chart-head"><div><span className="eyebrow">Consulta</span><h4>Buscar categorias</h4></div><a href={data.routes.report} target="_blank" rel="noopener" className="user-report-button"><i className="ri-file-chart-line" />Generar reporte PDF</a></div>
                <form method="GET" action={data.routes.index} className="user-filter-form">
                    <div className="form-group"><label htmlFor="search"><i className="ri-search-line" /> Nombre de categoria</label><input type="search" id="search" name="search" className="input-ghost" placeholder="Ej. Bebidas, limpieza, snacks" defaultValue={data.filters.search || ''} /></div>
                    <div className="user-filter-actions"><button type="submit" className="user-filter-submit"><i className="ri-search-line" />Buscar</button><a href={data.routes.index} className="clean-link"><i className="ri-refresh-line" />Limpiar</a></div>
                </form>
            </section>

            {['activeCategories', 'inactiveCategories'].map((key) => {
                const collection = data[key];
                const isActive = key === 'activeCategories';

                return (
                    <section className={`card user-table-card category-table-card ${isActive ? 'user-table-card--active' : 'user-table-card--inactive'}`} key={key}>
                        <div className="chart-head"><div><span className="eyebrow">Gestion</span><h4>{isActive ? 'Categorias activas' : 'Categorias desactivadas'}</h4></div><span className="chip">{collection.total} registros</span></div>
                        <div className="table-wrapper"><table className="data-table"><thead><tr><th>Categoria</th><th>Productos</th><th>Estado</th><th>{isActive ? 'Creada' : 'Desactivada'}</th><th aria-label="Acciones" /></tr></thead><tbody>
                            {collection.data.length ? collection.data.map((category) => (
                                <tr key={category.id}>
                                    <td><CategoryIdentity category={category} /></td>
                                    <td><span className="category-products-count"><i className="ri-shopping-bag-3-line" />{category.products_count}<small>{category.products_count === 1 ? 'producto' : 'productos'}</small></span></td>
                                    <td>{statusPill(isActive ? 'active' : 'inactive', isActive ? 'Activa' : 'Inactiva')}</td>
                                    <td><span className="category-date"><i className={isActive ? 'ri-calendar-check-line' : 'ri-calendar-close-line'} />{isActive ? category.created_at_formatted : category.deleted_at_formatted}</span></td>
                                    <td><div className="actions">{isActive ? <><button type="button" className="btn-secondary" onClick={() => setEditingCategory(category)}>Editar</button><button type="button" className="btn-danger" onClick={() => setCategoryToDeactivate(category)}>Desactivar</button></> : <form method="POST" action={category.restore_url}><input type="hidden" name="_token" value={csrfToken} /><input type="hidden" name="_method" value="PATCH" /><button className="btn-secondary" type="submit">Reactivar</button></form>}</div></td>
                                </tr>
                            )) : <TableEmpty colSpan={5} text={isActive ? 'No hay categorias activas para los filtros aplicados.' : 'No hay categorias desactivadas para mostrar.'} />}
                        </tbody></table></div>
                        <Pagination pagination={collection} />
                    </section>
                );
            })}

            <Modal open={createCategoryOpen} title="Crear categoria" onClose={() => setCreateCategoryOpen(false)} contentClassName="user-create-modal">
                <form method="POST" action={data.routes.store} className="user-create-form">
                    <input type="hidden" name="_token" value={csrfToken} />
                    <div className="user-create-fields"><div className="form-group"><label htmlFor="category_name">Nombre</label><input type="text" id="category_name" name="name" className="input-ghost" placeholder="Nombre de la categoria" defaultValue={old?.name || ''} required autoFocus /><FieldError errors={errors} name="name" /></div><div className="form-group"><label htmlFor="category_description">Descripcion</label><textarea id="category_description" name="description" rows="4" className="input-ghost" placeholder="Descripcion opcional" defaultValue={old?.description || ''} /><FieldError errors={errors} name="description" /></div></div>
                    <div className="user-create-actions"><button type="button" className="btn-secondary user-create-cancel" onClick={() => setCreateCategoryOpen(false)}>Cancelar</button><button type="submit" className="pill-button user-create-submit"><i className="ri-check-line" /> Guardar categoria</button></div>
                </form>
            </Modal>

            <Modal open={!!editingCategory} title="Editar categoria" onClose={() => setEditingCategory(null)} contentClassName="user-edit-modal">
                {editingCategory && <form method="POST" action={editingCategory.update_url} className="user-edit-form"><input type="hidden" name="_token" value={csrfToken} /><input type="hidden" name="_method" value="PUT" /><div className="user-edit-fields"><div className="form-group"><label>Nombre</label><input type="text" name="name" className="input-ghost" defaultValue={editingCategory.name} required autoFocus /></div><div className="form-group"><label>Descripcion</label><textarea name="description" rows="4" className="input-ghost" defaultValue={editingCategory.description || ''} /></div></div><div className="user-edit-actions"><button type="button" className="btn-secondary user-edit-cancel" onClick={() => setEditingCategory(null)}>Cancelar</button><button type="submit" className="pill-button user-edit-submit"><i className="ri-save-line" /> Guardar cambios</button></div></form>}
            </Modal>

            <Modal open={!!categoryToDeactivate} title="Desactivar categoria" onClose={() => setCategoryToDeactivate(null)} contentClassName="user-edit-modal company-confirm-modal">
                {categoryToDeactivate && <form method="POST" action={categoryToDeactivate.destroy_url} className="company-confirm-form"><input type="hidden" name="_token" value={csrfToken} /><input type="hidden" name="_method" value="DELETE" /><div className="company-confirm-icon"><i className="ri-pause-circle-line" /></div><h4>Desactivar {categoryToDeactivate.name}?</h4><p>La categoria dejara de estar disponible para nuevas asignaciones. Podras reactivarla despues.</p><div className="company-confirm-actions"><button type="button" className="btn-secondary user-edit-cancel" onClick={() => setCategoryToDeactivate(null)}>Cancelar</button><button type="submit" className="btn-danger company-confirm-submit">Desactivar</button></div></form>}
            </Modal>
        </DashboardShell>
    );
}
