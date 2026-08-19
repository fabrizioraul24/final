import React, { useState } from 'react';
import DashboardShell from '../components/admin/DashboardShell';
import { FieldError, FlashMessages, Modal, Pagination, StatsGrid, TableEmpty } from '../components/admin/common';

function statusPill(status, label) {
    return <span className={`status-pill ${status}`}>{label}</span>;
}

export default function AdminSalesPage({ layout, data, flash, errors, old, csrfToken, logoutAction }) {
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
        <DashboardShell sidebar={layout.sidebar} topbar={layout.topbar} csrfToken={csrfToken} logoutAction={logoutAction}>
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
                        <div className="form-group" style={{ gridColumn: '1 / -1' }}><label>Almacen asignado</label><input type="text" className="input-ghost" value={data.laPazWarehouse ? `${data.laPazWarehouse.name} (${data.laPazWarehouse.code})` : 'Configura el almacen de La Paz para permitir ventas'} disabled /></div>
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
        </DashboardShell>
    );
}
