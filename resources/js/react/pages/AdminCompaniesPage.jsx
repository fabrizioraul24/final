import React, { useState } from 'react';
import DashboardShell from '../components/admin/DashboardShell';
import { FieldError, FlashMessages, Modal, Pagination, TableEmpty } from '../components/admin/common';

function getCompanyTypeBadge(company) {
    const isRetail = company.company_type === 'tienda_barrio';

    return (
        <span className={`fit-role-badge ${isRetail ? 'vendor' : 'warehouse'}`}>
            <i className={isRetail ? 'ri-store-2-fill' : 'ri-building-4-fill'} />
            {isRetail ? 'Tienda' : 'Institucional'}
        </span>
    );
}

function CompanyInitials({ name, muted = false }) {
    const initials = (name || 'Cliente')
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0])
        .join('')
        .toUpperCase();

    return <span className={`fit-user-avatar company-avatar${muted ? ' muted' : ''}`}>{initials}</span>;
}

function CompaniesTable({ companies, inactive = false, csrfToken, onView, onEdit, onDeactivate }) {
    return (
        <div className="fit-table-card">
            <div className="fit-table-scroll">
                <table className="fit-users-table fit-companies-table">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Tipo</th>
                            <th>Contacto</th>
                            <th>Ciudad</th>
                            <th>Email / Telefono</th>
                            <th>Creado por</th>
                            <th>{inactive ? 'Estado' : 'Creado'}</th>
                            <th className="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        {companies.length ? companies.map((company) => (
                            <tr key={company.id} className={inactive ? 'is-muted' : ''}>
                                <td>
                                    <div className="fit-user-cell">
                                        <CompanyInitials name={company.name} muted={inactive} />
                                        <div>
                                            <strong>{company.name}</strong>
                                            <small>NIT: {company.nit || 'Sin NIT'} - ID: #{company.id}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>{getCompanyTypeBadge(company)}</td>
                                <td><span className="fit-muted-text">{company.owner_full_name || 'Sin responsable'}</span></td>
                                <td><code className="fit-code">{company.city || 'Sin ciudad'}</code></td>
                                <td>
                                    <span className="fit-company-contact">
                                        <strong>{company.email || 'Sin correo'}</strong>
                                        <small>{company.phone || 'Sin telefono'}</small>
                                    </span>
                                </td>
                                <td><span className="fit-muted-text">{company.creator?.name || 'Usuario Pil'}</span></td>
                                <td>
                                    {inactive ? (
                                        <span className="fit-status inactive"><span /> Desactivado</span>
                                    ) : (
                                        <span className="fit-muted-text">{company.created_at_formatted || '-'}</span>
                                    )}
                                </td>
                                <td className="text-right">
                                    <div className="fit-row-actions">
                                        <button type="button" className="fit-action-button success" onClick={() => onView(company)} title="Ver detalle">
                                            <i className="ri-eye-line" />
                                        </button>
                                        {!inactive && (
                                            <>
                                                <button type="button" className="fit-action-button warning" onClick={() => onEdit(company)} title="Editar">
                                                    <i className="ri-edit-2-line" />
                                                </button>
                                                <button type="button" className="fit-action-button danger" onClick={() => onDeactivate(company)} title="Desactivar">
                                                    <i className="ri-inbox-archive-line" />
                                                </button>
                                            </>
                                        )}
                                        {inactive && (
                                            <form method="POST" action={company.restore_url}>
                                                <input type="hidden" name="_token" value={csrfToken} />
                                                <input type="hidden" name="_method" value="PATCH" />
                                                <button type="submit" className="fit-action-button success" title="Reactivar">
                                                    <i className="ri-refresh-line" />
                                                </button>
                                            </form>
                                        )}
                                    </div>
                                </td>
                            </tr>
                        )) : (
                            <TableEmpty colSpan={8} text={inactive ? 'No hay clientes desactivados.' : 'No hay clientes para los filtros aplicados.'} />
                        )}
                    </tbody>
                </table>
            </div>
        </div>
    );
}

export default function AdminCompaniesPage({ layout, data, flash, errors, old, csrfToken, logoutAction }) {
    const [editingCompany, setEditingCompany] = useState(null);
    const [viewingCompany, setViewingCompany] = useState(null);
    const [companyToDeactivate, setCompanyToDeactivate] = useState(null);
    const [createType, setCreateType] = useState(old?.company_type || 'empresa_institucional');
    const [createCompanyOpen, setCreateCompanyOpen] = useState(() => Object.keys(errors || {}).length > 0);
    const initialMetric = data.filters.type === 'tienda_barrio'
        ? 'retail'
        : data.filters.type === 'empresa_institucional'
            ? 'institutional'
            : 'all';
    const [activeMetric, setActiveMetric] = useState(initialMetric);
    const editType = editingCompany?.company_type || 'empresa_institucional';

    const activeCompanies = data.activeCompanies?.data || [];
    const inactiveCompanies = data.inactiveCompanies?.data || [];
    const activeTotal = data.stats?.active || data.activeCompanies?.total || 0;
    const inactiveTotal = data.stats?.inactive || data.inactiveCompanies?.total || 0;
    const grandTotal = data.stats?.total || activeTotal + inactiveTotal;

    const showActive = activeMetric === 'all' || activeMetric === 'active' || activeMetric === 'institutional' || activeMetric === 'retail';
    const showInactive = activeMetric === 'all' || activeMetric === 'inactive' || activeMetric === 'institutional' || activeMetric === 'retail';
    const hasFilters = data.filters.search || data.filters.type || activeMetric !== 'all';

    const buildIndexUrl = (params = {}) => {
        const url = new URL(data.routes.index, window.location.origin);
        const search = params.search ?? data.filters.search;
        const type = params.type ?? data.filters.type;

        if (search) {
            url.searchParams.set('search', search);
        }

        if (type) {
            url.searchParams.set('type', type);
        }

        return `${url.pathname}${url.search}`;
    };

    const handleMetricClick = (key) => {
        if (key === 'institutional') {
            window.location.href = buildIndexUrl({ type: 'empresa_institucional' });
            return;
        }

        if (key === 'retail') {
            window.location.href = buildIndexUrl({ type: 'tienda_barrio' });
            return;
        }

        if (key === 'all' && data.filters.type) {
            window.location.href = buildIndexUrl({ type: '' });
            return;
        }

        setActiveMetric(key);
    };

    const metricCards = [
        { key: 'all', label: 'Cartera Total', value: grandTotal, hint: 'Haz clic para ver todos', icon: 'ri-community-line', tone: 'indigo' },
        { key: 'active', label: 'Clientes Activos', value: activeTotal, hint: 'Filtrar por activos', icon: 'ri-user-smile-line', tone: 'green' },
        { key: 'inactive', label: 'Desactivados', value: inactiveTotal, hint: 'Ver recuperables', icon: 'ri-archive-line', tone: 'rose' },
        { key: 'institutional', label: 'Institucionales', value: data.stats?.institutional || 0, hint: 'Precios corporativos', icon: 'ri-building-4-line', tone: 'amber' },
        { key: 'retail', label: 'Tiendas Barrio', value: data.stats?.retail || 0, hint: 'Clientes minoristas', icon: 'ri-store-2-line', tone: 'indigo' },
    ];

    const renderCompanyFields = (prefix = '', company = {}) => {
        const isEdit = prefix === 'edit_';
        const type = isEdit ? editType : createType;
        const value = (name, fallback = '') => company?.[name] ?? old?.[name] ?? fallback;
        const ownerLabel = type === 'tienda_barrio' ? 'duena' : 'representante';

        return (
            <div className="fit-form-grid">
                <div className="fit-form-field">
                    <label htmlFor={`${prefix}company_type`}>{type === 'tienda_barrio' ? 'Tipo de tienda *' : 'Tipo de empresa *'}</label>
                    <select
                        id={`${prefix}company_type`}
                        name="company_type"
                        defaultValue={value('company_type', 'empresa_institucional')}
                        onChange={(event) => isEdit ? setEditingCompany((current) => ({ ...current, company_type: event.target.value })) : setCreateType(event.target.value)}
                        required
                    >
                        {Object.entries(data.companyTypes).map(([key, label]) => <option key={key} value={key}>{label}</option>)}
                    </select>
                    <FieldError errors={errors} name="company_type" />
                </div>

                <div className="fit-form-field">
                    <label htmlFor={`${prefix}name`}>Nombre comercial / Razon social *</label>
                    <input id={`${prefix}name`} type="text" name="name" placeholder="Ej. Supermercado Victoria" defaultValue={value('name')} required />
                    <FieldError errors={errors} name="name" />
                </div>

                <div className="fit-form-field">
                    <label htmlFor={`${prefix}nit`}>NIT *</label>
                    <input id={`${prefix}nit`} type="text" name="nit" placeholder="Ej. 1234567890" defaultValue={value('nit')} required />
                    <FieldError errors={errors} name="nit" />
                </div>

                <div className="fit-form-field">
                    <label htmlFor={`${prefix}email`}>Correo Electronico</label>
                    <input id={`${prefix}email`} type="email" name="email" placeholder="compras@cliente.com" defaultValue={value('email')} />
                    <FieldError errors={errors} name="email" />
                </div>

                <div className="fit-form-field">
                    <label htmlFor={`${prefix}phone`}>Telefono</label>
                    <input id={`${prefix}phone`} type="text" name="phone" placeholder="Ej. 70000000" defaultValue={value('phone')} />
                    <FieldError errors={errors} name="phone" />
                </div>

                <div className="fit-form-field">
                    <label htmlFor={`${prefix}city`}>Ciudad *</label>
                    <input id={`${prefix}city`} type="text" name="city" placeholder="Ej. La Paz" defaultValue={value('city')} required />
                    <FieldError errors={errors} name="city" />
                </div>

                <div className="fit-form-field span-2">
                    <label htmlFor={`${prefix}address`}>{type === 'tienda_barrio' ? 'Direccion de entrega *' : 'Direccion fiscal *'}</label>
                    <input id={`${prefix}address`} type="text" name="address" placeholder="Direccion completa del cliente" defaultValue={value('address')} required />
                    <FieldError errors={errors} name="address" />
                </div>

                <div className="fit-form-field">
                    <label htmlFor={`${prefix}owner_first_name`}>Nombre del {ownerLabel} *</label>
                    <input id={`${prefix}owner_first_name`} type="text" name="owner_first_name" defaultValue={value('owner_first_name')} required />
                    <FieldError errors={errors} name="owner_first_name" />
                </div>

                <div className="fit-form-field">
                    <label htmlFor={`${prefix}owner_last_name_paterno`}>Apellido paterno *</label>
                    <input id={`${prefix}owner_last_name_paterno`} type="text" name="owner_last_name_paterno" defaultValue={value('owner_last_name_paterno')} required />
                    <FieldError errors={errors} name="owner_last_name_paterno" />
                </div>

                <div className="fit-form-field span-2">
                    <label htmlFor={`${prefix}owner_last_name_materno`}>Apellido materno</label>
                    <input id={`${prefix}owner_last_name_materno`} type="text" name="owner_last_name_materno" defaultValue={value('owner_last_name_materno')} />
                    <FieldError errors={errors} name="owner_last_name_materno" />
                </div>
            </div>
        );
    };

    return (
        <DashboardShell sidebar={layout.sidebar} topbar={layout.topbar} csrfToken={csrfToken} logoutAction={logoutAction}>
            <div className="fit-users-page fit-companies-page">
                <FlashMessages flash={flash} />

                <section className="fit-users-header">
                    <div className="fit-users-header-left">
                        <div className="fit-header-icon"><i className="ri-user-smile-line" /></div>
                        <div>
                            <h1>Directorio y Metricas de Clientes</h1>
                            <p>Filtra, consulta, gestiona y exporta la cartera completa de clientes empresariales.</p>
                        </div>
                    </div>

                    <div className="fit-users-header-actions">
                        <a className="fit-outline-button" target="_blank" rel="noopener noreferrer" href={data.routes.report}>
                            <i className="ri-download-2-line" />
                            <span>Descargar Reporte PDF</span>
                        </a>
                        <button type="button" className="fit-primary-button" onClick={() => setCreateCompanyOpen(true)}>
                            <i className="ri-building-2-line" />
                            <span>Crear Cliente</span>
                        </button>
                    </div>
                </section>

                <section className="fit-metric-grid fit-company-metric-grid">
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
                                placeholder="Buscar nombre, NIT, ciudad o contacto..."
                                defaultValue={data.filters.search || ''}
                            />
                        </label>

                        <label className="fit-select-control" htmlFor="type">
                            <i className="ri-filter-3-line" />
                            <select id="type" name="type" defaultValue={data.filters.type || ''}>
                                <option value="">Todos los tipos</option>
                                {Object.entries(data.companyTypes).map(([key, label]) => (
                                    <option key={key} value={key}>{label}</option>
                                ))}
                            </select>
                        </label>

                        <button type="submit" className="fit-primary-button compact">
                            <i className="ri-search-line" /> Buscar
                        </button>

                        {hasFilters && (
                            <a href={data.routes.index} className="fit-clear-button">Limpiar Filtros</a>
                        )}
                    </form>
                </section>

                {showActive && (
                    <section className="fit-section">
                        <div className="fit-section-head">
                            <div>
                                <h2>Clientes Activos en Cartera</h2>
                                <p>Empresas y tiendas habilitadas para ventas, cotizaciones y seguimiento comercial.</p>
                            </div>
                            <span className="fit-section-badge green">{data.activeCompanies.total} activos</span>
                        </div>
                        <CompaniesTable
                            companies={activeCompanies}
                            csrfToken={csrfToken}
                            onView={setViewingCompany}
                            onEdit={setEditingCompany}
                            onDeactivate={setCompanyToDeactivate}
                        />
                        {(activeMetric === 'all' || activeMetric === 'active') && <Pagination pagination={data.activeCompanies} />}
                    </section>
                )}

                {showInactive && (
                    <section className="fit-section">
                        <div className="fit-section-head">
                            <div>
                                <h2>Clientes Desactivados</h2>
                                <p>Registros en papelera que pueden reactivarse cuando vuelvan a operar.</p>
                            </div>
                            <span className="fit-section-badge rose">{data.inactiveCompanies.total} desactivados</span>
                        </div>
                        <CompaniesTable
                            companies={inactiveCompanies}
                            inactive
                            csrfToken={csrfToken}
                            onView={setViewingCompany}
                            onEdit={setEditingCompany}
                            onDeactivate={setCompanyToDeactivate}
                        />
                        {(activeMetric === 'all' || activeMetric === 'inactive') && <Pagination pagination={data.inactiveCompanies} />}
                    </section>
                )}

                <Modal open={createCompanyOpen} title="Registro Oficial de Clientes" onClose={() => setCreateCompanyOpen(false)} wide contentClassName="fit-modal-content">
                    <form method="POST" action={data.routes.store} className="fit-register-form">
                        <input type="hidden" name="_token" value={csrfToken} />
                        {renderCompanyFields()}
                        <div className="fit-modal-footer">
                            <button type="button" className="fit-outline-button" onClick={() => setCreateCompanyOpen(false)}>Cancelar</button>
                            <button type="submit" className="fit-primary-button">
                                <i className="ri-checkbox-circle-line" /> Registrar Cliente
                            </button>
                        </div>
                    </form>
                </Modal>

                <Modal open={!!editingCompany} title="Editar Cliente" onClose={() => setEditingCompany(null)} wide contentClassName="fit-modal-content">
                    {editingCompany && (
                        <form method="POST" action={editingCompany.update_url} className="fit-register-form">
                            <input type="hidden" name="_token" value={csrfToken} />
                            <input type="hidden" name="_method" value="PUT" />
                            {renderCompanyFields('edit_', editingCompany)}
                            <div className="fit-modal-footer">
                                <button type="button" className="fit-outline-button" onClick={() => setEditingCompany(null)}>Cancelar</button>
                                <button type="submit" className="fit-primary-button">
                                    <i className="ri-save-3-line" /> Guardar Cambios
                                </button>
                            </div>
                        </form>
                    )}
                </Modal>

                <Modal open={!!viewingCompany} title="Detalle del Cliente" onClose={() => setViewingCompany(null)} wide contentClassName="fit-modal-content">
                    {viewingCompany && (
                        <div className="fit-company-detail">
                            <div className="fit-company-detail-head">
                                <CompanyInitials name={viewingCompany.name} />
                                <div>
                                    <strong>{viewingCompany.name}</strong>
                                    <span>NIT: {viewingCompany.nit || 'Sin NIT'}</span>
                                </div>
                                {getCompanyTypeBadge(viewingCompany)}
                            </div>

                            <div className="fit-company-detail-grid">
                                <div><span>Responsable</span><strong>{viewingCompany.owner_full_name || 'Sin datos'}</strong></div>
                                <div><span>Ciudad</span><strong>{viewingCompany.city || 'N/D'}</strong></div>
                                <div><span>Email</span><strong>{viewingCompany.email || 'N/D'}</strong></div>
                                <div><span>Telefono</span><strong>{viewingCompany.phone || 'N/D'}</strong></div>
                                <div className="span-2"><span>Direccion</span><strong>{viewingCompany.address || 'N/D'}</strong></div>
                                <div><span>Creado por</span><strong>{viewingCompany.creator?.name || 'Usuario Pil'}</strong></div>
                                <div><span>Fecha de alta</span><strong>{viewingCompany.created_at_formatted || 'N/D'}</strong></div>
                            </div>
                        </div>
                    )}
                </Modal>

                <Modal open={!!companyToDeactivate} title="Desactivar Cliente" onClose={() => setCompanyToDeactivate(null)} contentClassName="fit-modal-content">
                    {companyToDeactivate && (
                        <form method="POST" action={companyToDeactivate.destroy_url} className="fit-confirm-form">
                            <input type="hidden" name="_token" value={csrfToken} />
                            <input type="hidden" name="_method" value="DELETE" />
                            <div className="fit-confirm-icon"><i className="ri-alert-line" /></div>
                            <h4>Desactivar a {companyToDeactivate.name}?</h4>
                            <p>El registro pasara a la lista de clientes desactivados y podra reactivarse despues.</p>
                            <div className="fit-modal-footer">
                                <button type="button" className="fit-outline-button" onClick={() => setCompanyToDeactivate(null)}>Cancelar</button>
                                <button type="submit" className="fit-primary-button danger">
                                    <i className="ri-inbox-archive-line" /> Desactivar
                                </button>
                            </div>
                        </form>
                    )}
                </Modal>
            </div>
        </DashboardShell>
    );
}
