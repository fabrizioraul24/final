import React, { useState } from 'react';
import DashboardShell from '../components/admin/DashboardShell';
import { FieldError, FlashMessages, Modal, Pagination, TableEmpty } from '../components/admin/common';

function CategoryIcon({ muted = false }) {
    return (
        <span className={`fit-category-icon${muted ? ' muted' : ''}`}>
            <i className="ri-price-tag-3-line" />
        </span>
    );
}

function CategoryIdentity({ category, inactive = false }) {
    return (
        <div className="fit-user-cell fit-category-cell">
            <CategoryIcon muted={inactive} />
            <div>
                <strong>{category.name}</strong>
                <small>ID: #{category.id}</small>
            </div>
        </div>
    );
}

function CategoriesTable({ categories, inactive = false, csrfToken, onEdit, onDeactivate }) {
    return (
        <div className="fit-table-card">
            <div className="fit-table-scroll">
                <table className="fit-users-table fit-categories-table">
                    <thead>
                        <tr>
                            <th>Categoria</th>
                            <th>Descripcion</th>
                            <th>Productos</th>
                            <th>Estado</th>
                            <th>{inactive ? 'Desactivada' : 'Creada'}</th>
                            <th className="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        {categories.length ? categories.map((category) => (
                            <tr key={category.id} className={inactive ? 'is-muted' : ''}>
                                <td><CategoryIdentity category={category} inactive={inactive} /></td>
                                <td><span className="fit-muted-text fit-category-description">{category.description_excerpt}</span></td>
                                <td>
                                    <span className="fit-category-count">
                                        <i className="ri-shopping-bag-3-line" />
                                        <strong>{category.products_count}</strong>
                                        <small>{category.products_count === 1 ? 'producto' : 'productos'}</small>
                                    </span>
                                </td>
                                <td>
                                    <span className={`fit-status ${inactive ? 'inactive' : 'active'}`}>
                                        <span /> {inactive ? 'Inactiva' : 'Activa'}
                                    </span>
                                </td>
                                <td><span className="fit-muted-text">{inactive ? category.deleted_at_formatted : category.created_at_formatted}</span></td>
                                <td className="text-right">
                                    <div className="fit-row-actions">
                                        {!inactive && (
                                            <>
                                                <button type="button" className="fit-action-button warning" onClick={() => onEdit(category)} title="Editar">
                                                    <i className="ri-edit-2-line" />
                                                </button>
                                                <button type="button" className="fit-action-button danger" onClick={() => onDeactivate(category)} title="Desactivar">
                                                    <i className="ri-delete-bin-line" />
                                                </button>
                                            </>
                                        )}
                                        {inactive && (
                                            <form method="POST" action={category.restore_url}>
                                                <input type="hidden" name="_token" value={csrfToken} />
                                                <input type="hidden" name="_method" value="PATCH" />
                                                <button className="fit-action-button success" type="submit" title="Reactivar">
                                                    <i className="ri-refresh-line" />
                                                </button>
                                            </form>
                                        )}
                                    </div>
                                </td>
                            </tr>
                        )) : (
                            <TableEmpty colSpan={6} text={inactive ? 'No hay categorias desactivadas para mostrar.' : 'No hay categorias activas para los filtros aplicados.'} />
                        )}
                    </tbody>
                </table>
            </div>
        </div>
    );
}

export default function AdminCategoriesPage({ layout, data, flash, errors, old, csrfToken, logoutAction }) {
    const [createCategoryOpen, setCreateCategoryOpen] = useState(Boolean(errors && Object.keys(errors).length));
    const [editingCategory, setEditingCategory] = useState(null);
    const [categoryToDeactivate, setCategoryToDeactivate] = useState(null);
    const initialMetric = data.filters.scope === 'with_products' ? 'with_products' : 'all';
    const [activeMetric, setActiveMetric] = useState(initialMetric);

    const showActive = activeMetric === 'all' || activeMetric === 'active' || activeMetric === 'with_products';
    const showInactive = activeMetric === 'all' || activeMetric === 'inactive' || activeMetric === 'with_products';
    const hasFilters = data.filters.search || data.filters.scope || activeMetric !== 'all';

    const buildIndexUrl = (params = {}) => {
        const url = new URL(data.routes.index, window.location.origin);
        const search = params.search ?? data.filters.search;
        const scope = params.scope ?? data.filters.scope;

        if (search) {
            url.searchParams.set('search', search);
        }

        if (scope) {
            url.searchParams.set('scope', scope);
        }

        return `${url.pathname}${url.search}`;
    };

    const handleMetricClick = (key) => {
        if (key === 'with_products') {
            window.location.href = buildIndexUrl({ scope: 'with_products' });
            return;
        }

        if (key === 'all' && data.filters.scope) {
            window.location.href = buildIndexUrl({ scope: '' });
            return;
        }

        setActiveMetric(key);
    };

    const metricCards = [
        { key: 'all', label: 'Total Categorias', value: data.summary.total, hint: 'Haz clic para ver todas', icon: 'ri-price-tag-3-line', tone: 'indigo' },
        { key: 'active', label: 'Categorias Activas', value: data.summary.active, hint: 'Filtrar activas', icon: 'ri-checkbox-circle-line', tone: 'green' },
        { key: 'with_products', label: 'Con Productos', value: data.summary.with_products, hint: 'Filtrar asignadas', icon: 'ri-shopping-bag-3-line', tone: 'amber' },
        { key: 'inactive', label: 'Desactivadas', value: data.summary.inactive, hint: 'Ver en pausa', icon: 'ri-pause-circle-line', tone: 'rose' },
    ];

    const renderForm = (category = null) => (
        <div className="fit-form-grid">
            <div className="fit-form-field span-2">
                <label htmlFor={category ? 'edit_category_name' : 'category_name'}>Nombre *</label>
                <input
                    type="text"
                    id={category ? 'edit_category_name' : 'category_name'}
                    name="name"
                    placeholder="Nombre de la categoria"
                    defaultValue={category?.name ?? old?.name ?? ''}
                    required
                    autoFocus
                />
                <FieldError errors={errors} name="name" />
            </div>

            <div className="fit-form-field span-2">
                <label htmlFor={category ? 'edit_category_description' : 'category_description'}>Descripcion</label>
                <textarea
                    id={category ? 'edit_category_description' : 'category_description'}
                    name="description"
                    rows="4"
                    placeholder="Descripcion opcional"
                    defaultValue={category?.description ?? old?.description ?? ''}
                />
                <FieldError errors={errors} name="description" />
            </div>
        </div>
    );

    return (
        <DashboardShell sidebar={layout.sidebar} topbar={layout.topbar} csrfToken={csrfToken} logoutAction={logoutAction}>
            <div className="fit-users-page fit-categories-page">
                <FlashMessages flash={flash} />

                <section className="fit-users-header">
                    <div className="fit-users-header-left">
                        <div className="fit-header-icon"><i className="ri-price-tag-3-line" /></div>
                        <div>
                            <h1>Directorio y Metricas de Categorias</h1>
                            <p>Organiza familias de productos, revisa asignaciones y controla categorias activas.</p>
                        </div>
                    </div>

                    <div className="fit-users-header-actions">
                        <a className="fit-outline-button" target="_blank" rel="noopener noreferrer" href={data.routes.report}>
                            <i className="ri-download-2-line" />
                            <span>Descargar Reporte PDF</span>
                        </a>
                        <button type="button" className="fit-primary-button" onClick={() => setCreateCategoryOpen(true)}>
                            <i className="ri-add-box-line" />
                            <span>Crear Categoria</span>
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
                    <form method="GET" action={data.routes.index} className="fit-filter-form fit-category-filter-form">
                        <label className="fit-search-control" htmlFor="search">
                            <i className="ri-search-line" />
                            <input
                                type="search"
                                id="search"
                                name="search"
                                placeholder="Buscar categoria..."
                                defaultValue={data.filters.search || ''}
                            />
                        </label>

                        {data.filters.scope && <input type="hidden" name="scope" value={data.filters.scope} />}

                        <button type="submit" className="fit-primary-button compact">
                            <i className="ri-search-line" /> Buscar
                        </button>

                        {hasFilters && <a href={data.routes.index} className="fit-clear-button">Limpiar Filtros</a>}
                    </form>
                </section>

                {showActive && (
                    <section className="fit-section">
                        <div className="fit-section-head">
                            <div>
                                <h2>Categorias Activas</h2>
                                <p>Familias disponibles para clasificar productos del catalogo.</p>
                            </div>
                            <span className="fit-section-badge green">{data.activeCategories.total} activas</span>
                        </div>
                        <CategoriesTable
                            categories={data.activeCategories.data}
                            csrfToken={csrfToken}
                            onEdit={setEditingCategory}
                            onDeactivate={setCategoryToDeactivate}
                        />
                        <Pagination pagination={data.activeCategories} />
                    </section>
                )}

                {showInactive && (
                    <section className="fit-section">
                        <div className="fit-section-head">
                            <div>
                                <h2>Categorias Desactivadas</h2>
                                <p>Categorias en pausa que pueden reactivarse para nuevas asignaciones.</p>
                            </div>
                            <span className="fit-section-badge rose">{data.inactiveCategories.total} desactivadas</span>
                        </div>
                        <CategoriesTable
                            categories={data.inactiveCategories.data}
                            inactive
                            csrfToken={csrfToken}
                            onEdit={setEditingCategory}
                            onDeactivate={setCategoryToDeactivate}
                        />
                        <Pagination pagination={data.inactiveCategories} />
                    </section>
                )}

                <Modal open={createCategoryOpen} title="Registro Oficial de Categorias" onClose={() => setCreateCategoryOpen(false)} wide contentClassName="fit-modal-content">
                    <form method="POST" action={data.routes.store} className="fit-register-form">
                        <input type="hidden" name="_token" value={csrfToken} />
                        {renderForm()}
                        <div className="fit-modal-footer">
                            <button type="button" className="fit-outline-button" onClick={() => setCreateCategoryOpen(false)}>Cancelar</button>
                            <button type="submit" className="fit-primary-button">
                                <i className="ri-checkbox-circle-line" /> Guardar Categoria
                            </button>
                        </div>
                    </form>
                </Modal>

                <Modal open={!!editingCategory} title="Editar Categoria" onClose={() => setEditingCategory(null)} wide contentClassName="fit-modal-content">
                    {editingCategory && (
                        <form method="POST" action={editingCategory.update_url} className="fit-register-form">
                            <input type="hidden" name="_token" value={csrfToken} />
                            <input type="hidden" name="_method" value="PUT" />
                            {renderForm(editingCategory)}
                            <div className="fit-modal-footer">
                                <button type="button" className="fit-outline-button" onClick={() => setEditingCategory(null)}>Cancelar</button>
                                <button type="submit" className="fit-primary-button">
                                    <i className="ri-save-3-line" /> Guardar Cambios
                                </button>
                            </div>
                        </form>
                    )}
                </Modal>

                <Modal open={!!categoryToDeactivate} title="Desactivar Categoria" onClose={() => setCategoryToDeactivate(null)} contentClassName="fit-modal-content">
                    {categoryToDeactivate && (
                        <form method="POST" action={categoryToDeactivate.destroy_url} className="fit-confirm-form">
                            <input type="hidden" name="_token" value={csrfToken} />
                            <input type="hidden" name="_method" value="DELETE" />
                            <div className="fit-confirm-icon"><i className="ri-pause-circle-line" /></div>
                            <h4>Desactivar {categoryToDeactivate.name}?</h4>
                            <p>La categoria dejara de estar disponible para nuevas asignaciones. Podras reactivarla despues.</p>
                            <div className="fit-modal-footer">
                                <button type="button" className="fit-outline-button" onClick={() => setCategoryToDeactivate(null)}>Cancelar</button>
                                <button type="submit" className="fit-primary-button danger">
                                    <i className="ri-delete-bin-line" /> Desactivar
                                </button>
                            </div>
                        </form>
                    )}
                </Modal>
            </div>
        </DashboardShell>
    );
}
