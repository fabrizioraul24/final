import React, { useState } from 'react';
import DashboardShell from '../components/admin/DashboardShell';
import { FieldError, FlashMessages } from '../components/admin/common';

const saleTypeLabels = {
    empresa_institucional: 'Empresa institucional',
    tienda_barrio: 'Tienda de barrio',
    comprador_minorista: 'Comprador minorista',
};

const statusLabels = { sin_entregar: 'Sin entregar', entregado: 'Entregado' };

function blankItem() {
    return {
        id: Date.now() + Math.random(),
        sku: '',
        product_id: '',
        product_name: '',
        available: '',
        quantity: '',
        unit_price: '',
    };
}

export default function AdminSaleCreatePage({ layout, data, flash, errors, old, csrfToken, logoutAction }) {
    const [saleType, setSaleType] = useState(old?.sale_type || 'empresa_institucional');
    const [items, setItems] = useState([blankItem()]);

    const total = items.reduce((sum, item) => sum + Number(item.quantity || 0) * Number(item.unit_price || 0), 0);
    const visibleCompanies = data.companies.filter((company) => (
        saleType === 'tienda_barrio'
            ? company.company_type === 'tienda_barrio'
            : company.company_type === 'empresa_institucional'
    ));

    const updateItem = (id, patch) => {
        setItems((current) => current.map((item) => (item.id === id ? { ...item, ...patch } : item)));
    };

    const addItem = () => setItems((current) => [...current, blankItem()]);
    const removeItem = (id) => setItems((current) => (current.length > 1 ? current.filter((item) => item.id !== id) : current));

    const lookupItem = async (id, sku) => {
        const warehouseId = data.laPazWarehouse?.id;
        if (!warehouseId || !sku) return;

        const params = new URLSearchParams({ sku, sale_type: saleType, warehouse_id: warehouseId });
        const response = await fetch(`${data.routes.lookup}?${params.toString()}`);
        const payload = await response.json();

        if (!response.ok) {
            updateItem(id, {
                product_id: '',
                product_name: payload.message || 'No pudimos encontrar el producto.',
                available: 'Fuera de stock',
                quantity: '',
                unit_price: '',
            });
            return;
        }

        const available = payload.available_quantity ?? 0;
        updateItem(id, {
            product_id: payload.product_id,
            product_name: `${payload.name} (${payload.sku})`,
            available: available > 0 ? `${available} uds` : 'Fuera de stock',
            quantity: available > 0 ? 1 : '',
            unit_price: payload.price ?? 0,
        });
    };

    return (
        <DashboardShell sidebar={layout.sidebar} topbar={layout.topbar} csrfToken={csrfToken} logoutAction={logoutAction}>
            <div className="fit-users-page fit-sales-page fit-sale-create-page">
                <FlashMessages flash={flash} />

                <section className="fit-users-header">
                    <div className="fit-users-header-left">
                        <div className="fit-header-icon"><i className="ri-add-box-line" /></div>
                        <div>
                            <h1>Crear Venta</h1>
                            <p>Registra productos vendidos desde el almacen de La Paz y descuenta inventario por lotes.</p>
                        </div>
                    </div>
                    <div className="fit-users-header-actions">
                        <a href={data.routes.index} className="fit-outline-button">
                            <i className="ri-arrow-left-line" />
                            <span>Volver a ventas</span>
                        </a>
                    </div>
                </section>

                <form method="POST" action={data.routes.store} className="fit-sale-create-layout">
                    <input type="hidden" name="_token" value={csrfToken} />
                    <input type="hidden" name="warehouse_id" value={data.laPazWarehouse?.id || ''} />

                    <main className="fit-sale-create-main">
                        <section className="fit-section fit-sale-create-panel">
                            <div className="fit-section-head">
                                <div>
                                    <h2>Datos de la venta</h2>
                                    <p>Tipo, estado, pago y destino de entrega.</p>
                                </div>
                                <span className="fit-section-badge green">Paso 1</span>
                            </div>

                            <div className="fit-form-grid fit-sale-create-grid">
                                <div className="fit-form-field">
                                    <label htmlFor="sale_type_create">Tipo de venta *</label>
                                    <select id="sale_type_create" name="sale_type" value={saleType} onChange={(event) => setSaleType(event.target.value)} required>
                                        {Object.entries(saleTypeLabels).map(([key, label]) => <option key={key} value={key}>{label}</option>)}
                                    </select>
                                    <FieldError errors={errors} name="sale_type" />
                                </div>

                                <div className="fit-form-field">
                                    <label htmlFor="status_create">Estado de venta *</label>
                                    <select id="status_create" name="status" defaultValue={old?.status || 'sin_entregar'} required>
                                        {Object.entries(statusLabels).map(([key, label]) => <option key={key} value={key}>{label}</option>)}
                                    </select>
                                    <FieldError errors={errors} name="status" />
                                </div>

                                <div className="fit-form-field">
                                    <label htmlFor="payment_method">Metodo de pago *</label>
                                    <select id="payment_method" name="payment_method" defaultValue={old?.payment_method || ''} required>
                                        <option value="">Seleccionar</option>
                                        {Object.entries(data.paymentLabels).map(([key, label]) => <option key={key} value={key}>{label}</option>)}
                                    </select>
                                    <FieldError errors={errors} name="payment_method" />
                                </div>

                                <div className="fit-form-field">
                                    <label htmlFor="warehouse_label">Almacen asignado</label>
                                    <input
                                        id="warehouse_label"
                                        type="text"
                                        value={data.laPazWarehouse ? `${data.laPazWarehouse.name} (${data.laPazWarehouse.code})` : 'Configura el almacen de La Paz para permitir ventas'}
                                        readOnly
                                    />
                                    <FieldError errors={errors} name="warehouse_id" />
                                </div>

                                <div className="fit-form-field">
                                    <label htmlFor="delivery_city_id">Ciudad entrega *</label>
                                    <select id="delivery_city_id" name="delivery_city_id" defaultValue={old?.delivery_city_id || ''} required>
                                        <option value="">Seleccionar</option>
                                        {data.cities.map((city) => <option key={city.id} value={city.id}>{city.name}</option>)}
                                    </select>
                                    <FieldError errors={errors} name="delivery_city_id" />
                                </div>

                                <div className="fit-form-field">
                                    <label htmlFor="delivery_address">Direccion entrega</label>
                                    <input id="delivery_address" type="text" name="delivery_address" defaultValue={old?.delivery_address || ''} />
                                    <FieldError errors={errors} name="delivery_address" />
                                </div>

                                <div className="fit-form-field span-2">
                                    {saleType === 'comprador_minorista' ? (
                                        <>
                                            <label htmlFor="customer_id">Comprador minorista *</label>
                                            <select id="customer_id" name="customer_id" defaultValue={old?.customer_id || ''}>
                                                <option value="">Seleccionar</option>
                                                {data.customers.map((customer) => (
                                                    <option key={customer.id} value={customer.id}>
                                                        {customer.name} - {customer.city}{customer.nit ? ` (NIT: ${customer.nit})` : ''}
                                                    </option>
                                                ))}
                                            </select>
                                            <FieldError errors={errors} name="customer_id" />
                                        </>
                                    ) : (
                                        <>
                                            <label htmlFor="company_id">Empresa / Tienda *</label>
                                            <select id="company_id" name="company_id" defaultValue={old?.company_id || ''}>
                                                <option value="">Seleccionar</option>
                                                {visibleCompanies.map((company) => (
                                                    <option key={company.id} value={company.id}>
                                                        {company.name} - {company.city} (NIT: {company.nit})
                                                    </option>
                                                ))}
                                            </select>
                                            <FieldError errors={errors} name="company_id" />
                                        </>
                                    )}
                                </div>
                            </div>
                        </section>

                        <section className="fit-section fit-sale-create-panel">
                            <div className="fit-section-head">
                                <div>
                                    <h2>Productos de la venta</h2>
                                    <p>Ingresa SKU, cantidad y precio unitario.</p>
                                </div>
                                <button type="button" className="fit-outline-button" onClick={addItem}>
                                    <i className="ri-add-line" />
                                    <span>Agregar Producto</span>
                                </button>
                            </div>

                            <div className="fit-transfer-items fit-sale-items">
                                <div className="fit-transfer-items-list">
                                    {items.map((item, index) => (
                                        <div className="fit-transfer-item-row fit-sale-item-row" key={item.id}>
                                            <div className="fit-form-grid">
                                                <div className="fit-form-field">
                                                    <label htmlFor={`sku_${item.id}`}>Codigo SKU *</label>
                                                    <input
                                                        id={`sku_${item.id}`}
                                                        type="text"
                                                        value={item.sku}
                                                        onChange={(event) => updateItem(item.id, { sku: event.target.value })}
                                                        onBlur={(event) => lookupItem(item.id, event.target.value.trim())}
                                                    />
                                                    <input type="hidden" name={`items[${index}][product_id]`} value={item.product_id} required />
                                                </div>

                                                <div className="fit-form-field">
                                                    <label htmlFor={`product_${item.id}`}>Producto</label>
                                                    <input id={`product_${item.id}`} type="text" value={item.product_name} readOnly />
                                                </div>

                                                <div className="fit-form-field">
                                                    <label htmlFor={`available_${item.id}`}>Disponible</label>
                                                    <input id={`available_${item.id}`} type="text" value={item.available} readOnly />
                                                </div>

                                                <div className="fit-form-field">
                                                    <label htmlFor={`qty_${item.id}`}>Cantidad *</label>
                                                    <input
                                                        id={`qty_${item.id}`}
                                                        type="number"
                                                        min="1"
                                                        name={`items[${index}][quantity]`}
                                                        value={item.quantity}
                                                        onChange={(event) => updateItem(item.id, { quantity: event.target.value })}
                                                        required
                                                    />
                                                </div>

                                                <div className="fit-form-field">
                                                    <label htmlFor={`price_${item.id}`}>Precio unitario *</label>
                                                    <input
                                                        id={`price_${item.id}`}
                                                        type="number"
                                                        min="0"
                                                        step="0.01"
                                                        name={`items[${index}][unit_price]`}
                                                        value={item.unit_price}
                                                        onChange={(event) => updateItem(item.id, { unit_price: event.target.value })}
                                                        required
                                                    />
                                                </div>
                                            </div>

                                            <button type="button" className="fit-action-button danger fit-transfer-remove" onClick={() => removeItem(item.id)} title="Quitar producto">
                                                <i className="ri-delete-bin-line" />
                                            </button>
                                        </div>
                                    ))}
                                </div>
                                <FieldError errors={errors} name="items" />
                            </div>
                        </section>
                    </main>

                    <aside className="fit-sale-create-summary">
                        <span>Resumen</span>
                        <h2>Nueva venta</h2>
                        <div className="fit-sale-create-summary-row">
                            <span>Total estimado</span>
                            <strong>Bs {total.toFixed(2)}</strong>
                        </div>
                        <div className="fit-sale-create-summary-row">
                            <span>Almacen</span>
                            <strong>{data.laPazWarehouse?.code || 'N/D'}</strong>
                        </div>
                        <div className="fit-form-field">
                            <label htmlFor="audit_reason">Motivo para bitacora</label>
                            <textarea id="audit_reason" name="audit_reason" rows="4" placeholder="Ej. Precio especial autorizado para este cliente" defaultValue={old?.audit_reason || ''} />
                            <FieldError errors={errors} name="audit_reason" />
                        </div>
                        <div className="fit-sale-create-actions">
                            <button type="submit" className="fit-primary-button">
                                <i className="ri-checkbox-circle-line" />
                                <span>Registrar Venta</span>
                            </button>
                            <a href={data.routes.index} className="fit-outline-button">Cancelar</a>
                        </div>
                    </aside>
                </form>
            </div>
        </DashboardShell>
    );
}
