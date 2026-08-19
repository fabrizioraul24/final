import React, { useState } from 'react';
import DashboardShell from '../components/admin/DashboardShell';
import { FieldError, FlashMessages, Pagination, StatsGrid, TableEmpty } from '../components/admin/common';

function statusPill(status, label) {
    return <span className={`status-pill ${status}`}>{label}</span>;
}

export default function AdminQuotationsPage({ layout, data, flash, errors, old, csrfToken, logoutAction }) {
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
        <DashboardShell sidebar={layout.sidebar} topbar={layout.topbar} csrfToken={csrfToken} logoutAction={logoutAction}>
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
                                    {data.customers.map((customer) => <option key={customer.id} value={customer.id}>{`${customer.name} - ${customer.city}`}</option>)}
                                </select>
                            </div>
                        </div>
                    ) : (
                        <div className="form-grid">
                            <div className="form-group" style={{ gridColumn: '1 / -1' }}>
                                <label>Empresa / tienda</label>
                                <select name="company_id" className="select-light" defaultValue={old?.company_id || ''}>
                                    <option value="">Seleccionar</option>
                                    {visibleCompanies.map((company) => <option key={company.id} value={company.id}>{`${company.name} - ${company.city} (${company.nit})`}</option>)}
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
        </DashboardShell>
    );
}
