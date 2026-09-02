import React, { useState } from 'react';
import DashboardShell from '../components/admin/DashboardShell';
import { FieldError, FlashMessages, Modal, Pagination, TableEmpty } from '../components/admin/common';

const statusLabels = { pendiente: 'Pendiente', en_transito: 'En transito', recibido: 'Recibido' };

function TransferStatus({ status }) {
    const tone = status === 'recibido' ? 'active' : status === 'en_transito' ? 'transit' : 'pending';

    return (
        <span className={`fit-transfer-status ${tone}`}>
            <span /> {statusLabels[status] || status}
        </span>
    );
}

function TransferSource({ transfer }) {
    if (transfer.agentRequest) {
        return <span className="fit-role-badge default"><i className="ri-robot-2-line" /> Agente inteligente</span>;
    }

    return <span className="fit-role-badge warehouse"><i className="ri-file-list-3-line" /> Registro manual</span>;
}

export default function AdminTransfersPage({ layout, data, flash, errors, old, csrfToken, logoutAction }) {
    const [items, setItems] = useState([{ id: Date.now(), sku: '', product_id: '', product_name: '', available: '', requested_qty: '', notes: '' }]);
    const [createTransferOpen, setCreateTransferOpen] = useState(Boolean(errors && Object.keys(errors).length));
    const [viewTransfer, setViewTransfer] = useState(null);
    const [fromWarehouseId, setFromWarehouseId] = useState(old?.from_warehouse_id || '');
    const initialMetric = data.filters?.status || 'all';
    const [activeMetric, setActiveMetric] = useState(initialMetric);
    const hasFilters = Boolean(data.filters?.status);

    const buildIndexUrl = (status = '') => {
        const url = new URL(data.routes.index, window.location.origin);
        if (status) {
            url.searchParams.set('status', status);
        }
        return `${url.pathname}${url.search}`;
    };

    const handleMetricClick = (key) => {
        if (key === 'all') {
            if (data.filters?.status) {
                window.location.href = buildIndexUrl('');
                return;
            }
            setActiveMetric('all');
            return;
        }

        window.location.href = buildIndexUrl(key);
    };

    const addItem = () => setItems((current) => [...current, { id: Date.now() + Math.random(), sku: '', product_id: '', product_name: '', available: '', requested_qty: '', notes: '' }]);
    const removeItem = (id) => setItems((current) => current.length > 1 ? current.filter((item) => item.id !== id) : current);
    const updateItem = (id, patch) => setItems((current) => current.map((item) => item.id === id ? { ...item, ...patch } : item));

    const lookupItem = async (id, sku, warehouseId) => {
        if (!sku) return;

        const params = new URLSearchParams({ sku });
        if (warehouseId) params.append('warehouse_id', warehouseId);

        const response = await fetch(`${data.routes.lookup}?${params.toString()}`);
        const payload = await response.json();

        if (!response.ok) {
            updateItem(id, { product_id: '', product_name: payload.message || 'No pudimos encontrar el producto.', available: '0', requested_qty: '' });
            return;
        }

        updateItem(id, {
            product_id: payload.product_id,
            product_name: `${payload.name} (${payload.sku})`,
            available: `${payload.available_quantity ?? 0} uds`,
            requested_qty: payload.available_quantity && payload.available_quantity > 0 ? payload.available_quantity : 1,
        });
    };

    const metricCards = [
        { key: 'all', label: 'Traspasos Total', value: data.stats.total, hint: 'Ver todos', icon: 'ri-arrow-left-right-line', tone: 'indigo' },
        { key: 'pendiente', label: 'Pendientes', value: data.stats.pending, hint: 'Por atender', icon: 'ri-time-line', tone: 'amber' },
        { key: 'en_transito', label: 'En Transito', value: data.stats.in_transit, hint: 'Moviendose', icon: 'ri-truck-line', tone: 'rose' },
        { key: 'recibido', label: 'Recibidos', value: data.stats.received, hint: 'Confirmados', icon: 'ri-checkbox-circle-line', tone: 'green' },
    ];

    return (
        <DashboardShell sidebar={layout.sidebar} topbar={layout.topbar} csrfToken={csrfToken} logoutAction={logoutAction}>
            <div className="fit-users-page fit-transfers-page">
                <FlashMessages flash={flash} />

                <section className="fit-users-header">
                    <div className="fit-users-header-left">
                        <div className="fit-header-icon"><i className="ri-arrow-left-right-line" /></div>
                        <div>
                            <h1>Traspasos y Movimientos Internos</h1>
                            <p>Registra traslados hacia La Paz, revisa estados y descarga reportes de trazabilidad.</p>
                        </div>
                    </div>

                    <div className="fit-users-header-actions">
                        <a className="fit-outline-button" target="_blank" rel="noopener noreferrer" href={data.routes.report}>
                            <i className="ri-download-2-line" />
                            <span>Descargar Reporte PDF</span>
                        </a>
                        <button type="button" className="fit-primary-button" onClick={() => setCreateTransferOpen(true)}>
                            <i className="ri-add-box-line" />
                            <span>Crear Traspaso</span>
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

                {hasFilters && (
                    <section className="fit-filter-card">
                        <form method="GET" action={data.routes.index} className="fit-filter-form fit-transfer-filter-form">
                            <label className="fit-select-control" htmlFor="status">
                                <i className="ri-filter-3-line" />
                                <select id="status" name="status" defaultValue={data.filters.status || ''}>
                                    <option value="">Todos los estados</option>
                                    {data.statuses.map((status) => <option key={status} value={status}>{statusLabels[status] || status}</option>)}
                                </select>
                            </label>
                            <button type="submit" className="fit-primary-button compact"><i className="ri-search-line" /> Buscar</button>
                            <a href={data.routes.index} className="fit-clear-button">Limpiar Filtros</a>
                        </form>
                    </section>
                )}

                <section className="fit-section">
                    <div className="fit-section-head">
                        <div>
                            <h2>Traspasos Recientes</h2>
                            <p>Movimientos ordenados del mas reciente al mas antiguo.</p>
                        </div>
                        <span className="fit-section-badge green">{data.transfers.total} registros</span>
                    </div>

                    <div className="fit-table-card">
                        <div className="fit-table-scroll">
                            <table className="fit-users-table fit-transfers-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Origen</th>
                                        <th>Destino</th>
                                        <th>Estado</th>
                                        <th>Solicitud</th>
                                        <th>Productos</th>
                                        <th>Fecha estimada</th>
                                        <th className="text-right">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {data.transfers.data.length ? data.transfers.data.map((transfer) => (
                                        <tr key={transfer.id}>
                                            <td><code className="fit-code fit-transfer-id">#{transfer.id}</code></td>
                                            <td><span className="fit-muted-text">{transfer.fromWarehouse?.name || 'No definido'}</span></td>
                                            <td><span className="fit-muted-text">{transfer.toWarehouse?.name || 'N/A'}</span></td>
                                            <td><TransferStatus status={transfer.status} /></td>
                                            <td><TransferSource transfer={transfer} /></td>
                                            <td><span className="fit-muted-text">{transfer.items_count} item(s)</span></td>
                                            <td><span className="fit-muted-text">{transfer.expected_date_formatted}</span></td>
                                            <td className="text-right">
                                                <div className="fit-row-actions">
                                                    <button type="button" className="fit-action-button success" onClick={() => setViewTransfer(transfer)} title="Ver detalles">
                                                        <i className="ri-eye-line" />
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    )) : <TableEmpty colSpan={8} text="Sin traspasos registrados." />}
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <Pagination pagination={data.transfers} />
                </section>

                <Modal open={createTransferOpen} title="Registro Oficial de Traspasos" onClose={() => setCreateTransferOpen(false)} wide contentClassName="fit-modal-content">
                    <form method="POST" action={data.routes.store} className="fit-register-form">
                        <input type="hidden" name="_token" value={csrfToken} />

                        <div className="fit-form-grid">
                            <div className="fit-form-field">
                                <label htmlFor="from_warehouse_id">Almacen origen *</label>
                                <select id="from_warehouse_id" name="from_warehouse_id" value={fromWarehouseId} onChange={(event) => setFromWarehouseId(event.target.value)} required>
                                    <option value="">Seleccionar Santa Cruz o Cochabamba</option>
                                    {data.sourceWarehouses.map((warehouse) => <option key={warehouse.id} value={warehouse.id}>{warehouse.name} ({warehouse.code})</option>)}
                                </select>
                                <FieldError errors={errors} name="from_warehouse_id" />
                            </div>

                            <div className="fit-form-field">
                                <label htmlFor="target_warehouse">Almacen destino</label>
                                <input id="target_warehouse" type="text" value={`${data.targetWarehouse?.name || 'Deposito La Paz'} (${data.targetWarehouse?.code || 'LPZ'})`} readOnly />
                            </div>

                            <div className="fit-form-field">
                                <label htmlFor="expected_date">Fecha estimada</label>
                                <input id="expected_date" type="date" name="expected_date" defaultValue={old?.expected_date || ''} />
                                <FieldError errors={errors} name="expected_date" />
                            </div>

                            <div className="fit-form-field">
                                <label htmlFor="status_create">Estado inicial</label>
                                <select id="status_create" name="status" defaultValue={old?.status || 'pendiente'}>
                                    {data.statuses.map((status) => <option key={status} value={status}>{statusLabels[status] || status}</option>)}
                                </select>
                                <FieldError errors={errors} name="status" />
                            </div>

                            <div className="fit-form-field span-2">
                                <label htmlFor="notes">Notas generales</label>
                                <textarea id="notes" name="notes" rows="3" defaultValue={old?.notes || ''} />
                            </div>
                        </div>

                        <div className="fit-transfer-items">
                            <div className="fit-transfer-items-head">
                                <div>
                                    <span>Detalle</span>
                                    <h4>Productos a traspasar</h4>
                                </div>
                                <button type="button" className="fit-outline-button" onClick={addItem}>
                                    <i className="ri-add-line" /> Agregar Producto
                                </button>
                            </div>

                            <div className="fit-transfer-items-list">
                                {items.map((item, index) => (
                                    <div className="fit-transfer-item-row" key={item.id}>
                                        <div className="fit-form-grid">
                                            <div className="fit-form-field">
                                                <label htmlFor={`sku_${item.id}`}>Codigo SKU *</label>
                                                <input
                                                    id={`sku_${item.id}`}
                                                    type="text"
                                                    value={item.sku}
                                                    onChange={(event) => updateItem(item.id, { sku: event.target.value })}
                                                    onBlur={(event) => lookupItem(item.id, event.target.value.trim(), fromWarehouseId)}
                                                />
                                                <input type="hidden" name={`items[${index}][product_id]`} value={item.product_id} required />
                                            </div>

                                            <div className="fit-form-field">
                                                <label htmlFor={`product_${item.id}`}>Producto</label>
                                                <input id={`product_${item.id}`} type="text" value={item.product_name} readOnly />
                                            </div>

                                            <div className="fit-form-field">
                                                <label htmlFor={`available_${item.id}`}>Disponible en origen</label>
                                                <input id={`available_${item.id}`} type="text" value={item.available} readOnly />
                                            </div>

                                            <div className="fit-form-field">
                                                <label htmlFor={`qty_${item.id}`}>Cantidad solicitada *</label>
                                                <input
                                                    id={`qty_${item.id}`}
                                                    type="number"
                                                    min="1"
                                                    name={`items[${index}][requested_qty]`}
                                                    value={item.requested_qty}
                                                    onChange={(event) => updateItem(item.id, { requested_qty: event.target.value })}
                                                    required
                                                />
                                            </div>

                                            <div className="fit-form-field span-2">
                                                <label htmlFor={`item_notes_${item.id}`}>Notas</label>
                                                <textarea
                                                    id={`item_notes_${item.id}`}
                                                    name={`items[${index}][notes]`}
                                                    rows="2"
                                                    value={item.notes}
                                                    onChange={(event) => updateItem(item.id, { notes: event.target.value })}
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

                        <div className="fit-modal-footer">
                            <button type="button" className="fit-outline-button" onClick={() => setCreateTransferOpen(false)}>Cancelar</button>
                            <button type="submit" className="fit-primary-button">
                                <i className="ri-checkbox-circle-line" /> Guardar Traspaso
                            </button>
                        </div>
                    </form>
                </Modal>

                <Modal open={!!viewTransfer} title={viewTransfer ? `Traspaso #${viewTransfer.id}` : 'Traspaso'} onClose={() => setViewTransfer(null)} wide contentClassName="fit-modal-content">
                    {viewTransfer && (
                        <div className="fit-transfer-detail">
                            <div className="fit-transfer-summary">
                                <div><span>Estado</span><TransferStatus status={viewTransfer.status} /></div>
                                <div><span>Fecha estimada</span><strong>{viewTransfer.expected_date_formatted}</strong></div>
                                <div><span>Solicitado por</span><strong>{viewTransfer.requested_by_label}</strong></div>
                                <div><span>Aprobado por</span><strong>{viewTransfer.agentRequest ? viewTransfer.approved_by_label : '-'}</strong></div>
                                <div><span>Productos</span><strong>{viewTransfer.items_count} item(s)</strong></div>
                            </div>

                            <div className="fit-transfer-panel">
                                <h4>Origen de solicitud</h4>
                                {viewTransfer.agentRequest ? (
                                    <div className="fit-transfer-source-detail">
                                        <TransferSource transfer={viewTransfer} />
                                        <p>Solicitud creada: {viewTransfer.agentRequest.created_at_formatted}</p>
                                        <p>Aprobado por: {viewTransfer.approved_by_label}</p>
                                        {viewTransfer.agentRequest.priority && <p>Prioridad: {viewTransfer.agentRequest.priority}</p>}
                                        {viewTransfer.agentRequest.reason && <p>Motivo: {viewTransfer.agentRequest.reason}</p>}
                                    </div>
                                ) : <TransferSource transfer={viewTransfer} />}
                            </div>

                            <div className="fit-transfer-panel">
                                <h4>Productos</h4>
                                <div className="fit-table-scroll">
                                    <table className="fit-users-table fit-transfer-items-table">
                                        <thead>
                                            <tr>
                                                <th>Producto</th>
                                                <th>SKU</th>
                                                <th>Solicitado</th>
                                                <th>Recibido</th>
                                                <th>Danado</th>
                                                <th>Notas</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {viewTransfer.items.length ? viewTransfer.items.map((item, index) => (
                                                <tr key={`${item.sku}-${index}`}>
                                                    <td><strong>{item.product_name}</strong></td>
                                                    <td><code className="fit-code fit-product-sku">{item.sku}</code></td>
                                                    <td>{item.requested_qty} uds</td>
                                                    <td>{item.received_qty} uds</td>
                                                    <td>{item.damaged_qty} uds</td>
                                                    <td><span className="fit-muted-text">{item.notes}</span></td>
                                                </tr>
                                            )) : <TableEmpty colSpan={6} text="Sin productos registrados." />}
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div className="fit-transfer-panel">
                                <h4>Notas generales</h4>
                                <p>{viewTransfer.notes || 'Sin notas generales.'}</p>
                            </div>

                            <div className="fit-modal-footer">
                                <button type="button" className="fit-outline-button" onClick={() => setViewTransfer(null)}>Cerrar</button>
                                <a className="fit-primary-button" target="_blank" rel="noopener noreferrer" href={viewTransfer.report_url}>
                                    <i className="ri-file-download-line" /> Generar Reporte PDF
                                </a>
                            </div>
                        </div>
                    )}
                </Modal>
            </div>
        </DashboardShell>
    );
}
