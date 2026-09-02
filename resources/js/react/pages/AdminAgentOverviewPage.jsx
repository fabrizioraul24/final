import React, { useEffect, useState } from 'react';
import DashboardShell from '../components/admin/DashboardShell';
import LazyChart from '../components/admin/LazyChart';
import { Modal, TableEmpty } from '../components/admin/common';

const SESSION_KEY = 'admin-agent-overview-cache-v1';
const chartPalette = {
    indigo: '#4f46e5',
    green: '#10b981',
    amber: '#f59e0b',
    rose: '#f43f5e',
    slate: '#64748b',
};

function readCachedOverview() {
    try {
        const raw = window.sessionStorage.getItem(SESSION_KEY);
        return raw ? JSON.parse(raw) : null;
    } catch {
        return null;
    }
}

function writeCachedOverview(payload) {
    try {
        window.sessionStorage.setItem(SESSION_KEY, JSON.stringify(payload));
    } catch {
        // Session cache is only an optimization.
    }
}

function OverviewSkeleton() {
    return (
        <div className="fit-agent-skeleton">
            <div className="fit-metric-grid">
                {[1, 2, 3, 4].map((item) => <div className="fit-table-card" key={item}><div className="chart-skeleton" /></div>)}
            </div>
            <div className="fit-agent-chart-grid">
                <div className="fit-table-card"><div className="chart-skeleton" /></div>
                <div className="fit-table-card"><div className="chart-skeleton" /></div>
            </div>
        </div>
    );
}

function TrendBadge({ trend }) {
    const normalized = String(trend || '').toLowerCase();
    const tone = normalized === 'alza' ? 'success' : normalized === 'baja' ? 'danger' : 'default';

    return <span className={`fit-log-action ${tone}`}><i className="ri-line-chart-line" /> {trend || 'sin datos'}</span>;
}

function CapacityBadge({ children }) {
    return <span className="fit-agent-capacity-badge"><i className="ri-flashlight-line" /> {children}</span>;
}

function ForecastChart({ pageData }) {
    const configFactory = () => ({
        type: 'bar',
        data: {
            labels: pageData.charts.forecast.labels,
            datasets: [{
                data: pageData.charts.forecast.data,
                backgroundColor: 'rgba(79, 70, 229, 0.82)',
                borderColor: chartPalette.indigo,
                borderWidth: 1,
                borderRadius: 12,
            }],
        },
        options: {
            plugins: { legend: { display: false } },
            scales: {
                x: { ticks: { color: chartPalette.slate }, grid: { display: false } },
                y: { ticks: { color: chartPalette.slate }, grid: { color: 'rgba(148, 163, 184, 0.18)' }, beginAtZero: true },
            },
        },
    });

    return pageData.charts.forecast.labels?.length ? <LazyChart configFactory={configFactory} deps={[pageData.charts.forecast]} minHeight={260} canvasStyle={{ maxHeight: '260px' }} /> : null;
}

function RestockChart({ pageData }) {
    const configFactory = () => ({
        type: 'doughnut',
        data: {
            labels: pageData.charts.restock.labels,
            datasets: [{
                data: pageData.charts.restock.data,
                backgroundColor: [chartPalette.indigo, chartPalette.green, chartPalette.amber, chartPalette.rose, '#06b6d4'],
            }],
        },
        options: { plugins: { legend: { labels: { color: chartPalette.slate } } } },
    });

    return pageData.charts.restock.labels?.length ? <LazyChart configFactory={configFactory} deps={[pageData.charts.restock]} minHeight={260} canvasStyle={{ maxHeight: '260px' }} /> : null;
}

function OverviewContent({ pageData, openDetails }) {
    const hasCapacityAlerts = Array.isArray(pageData.raw?.capacity_alerts) && pageData.raw.capacity_alerts.length > 0;
    const metricCards = [
        { label: 'Restock Sugeridos', value: pageData.stats.restock, hint: 'Ordenes recomendadas', icon: 'ri-lightbulb-flash-line', tone: 'indigo' },
        { label: 'Stock Bajo', value: pageData.stats.alerts_low, hint: 'Bajo inventario', icon: 'ri-alarm-warning-line', tone: 'amber' },
        { label: 'Lotes por Vencer', value: pageData.stats.alerts_expiring, hint: '30 dias', icon: 'ri-calendar-close-line', tone: 'rose' },
        { label: 'Capacidad', value: pageData.stats.capacity, hint: 'Aumentar limite', icon: 'ri-expand-diagonal-line', tone: 'green' },
    ];

    return (
        <>
            <section className="fit-metric-grid">
                {metricCards.map((card) => (
                    <div className={`fit-metric-card ${card.tone}`} key={card.label}>
                        <span>
                            <small>{card.label}</small>
                            <strong>{card.value}</strong>
                            <em>{card.hint}</em>
                        </span>
                        <span className="fit-metric-icon"><i className={card.icon} /></span>
                    </div>
                ))}
            </section>

            <section className="fit-agent-chart-grid" id="chartsSection">
                <div className="fit-section">
                    <div className="fit-section-head">
                        <div>
                            <h2>Top Productos por Pronostico</h2>
                            <p>Demanda estimada para priorizar abastecimiento.</p>
                        </div>
                        <span className="fit-section-badge indigo">Demanda semanal</span>
                    </div>
                    <ForecastChart pageData={pageData} />
                </div>

                <div className="fit-section">
                    <div className="fit-section-head">
                        <div>
                            <h2>Distribucion de Restock</h2>
                            <p>Cantidad sugerida por producto priorizado.</p>
                        </div>
                        <span className="fit-section-badge green">Sugerencias</span>
                    </div>
                    <RestockChart pageData={pageData} />
                </div>
            </section>

            <section className="fit-section">
                <div className="fit-section-head">
                    <div>
                        <h2>Predicciones IA por Producto</h2>
                        <p>Pronostico, tendencia, stock y ventas recientes por producto.</p>
                    </div>
                    <span className="fit-section-badge green">{pageData.forecastItems.length} registros</span>
                </div>

                <div className="fit-table-card">
                    <div className="fit-table-scroll">
                        <table className="fit-users-table fit-agent-table">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Pronostico</th>
                                    <th>Tendencia</th>
                                    <th>Stock</th>
                                    <th>Ventas 6 sem</th>
                                    <th>Ventas 6 mes</th>
                                    <th className="text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                {pageData.forecastItems.length ? pageData.forecastItems.map((item) => (
                                    <tr key={item.product_id || item.name}>
                                        <td><strong>{item.name}</strong></td>
                                        <td><span className="fit-muted-text">{item.forecast} uds</span></td>
                                        <td><TrendBadge trend={item.trend} /></td>
                                        <td><span className="fit-muted-text">{item.stock} uds</span></td>
                                        <td><span className="fit-muted-text">{item.weekly_recent_total} uds</span></td>
                                        <td><span className="fit-muted-text">{item.monthly_recent_total} uds</span></td>
                                        <td className="text-right">
                                            <div className="fit-row-actions">
                                                <button type="button" className="fit-action-button success" onClick={() => openDetails(item)} title="Ver detalles">
                                                    <i className="ri-eye-line" />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                )) : <TableEmpty colSpan={7} text="Sin datos de prediccion." />}
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            {hasCapacityAlerts && (
                <section className="fit-section">
                    <div className="fit-section-head">
                        <div>
                            <h2>Capacidad al Limite</h2>
                            <p>Productos con sugerencia de ampliar capacidad máxima.</p>
                        </div>
                        <span className="fit-section-badge rose">{pageData.raw.capacity_alerts.length} productos</span>
                    </div>

                    <div className="fit-agent-capacity-grid">
                        {pageData.raw.capacity_alerts.map((cap) => (
                            <div className="fit-agent-capacity-card" key={cap.name}>
                                <div>
                                    <strong>{cap.name}</strong>
                                    <span>Limite: {cap.max_quantity ?? 'N/D'} uds</span>
                                    <p>{cap.note ?? 'Sugerencia: aumentar capacidad'}</p>
                                </div>
                                <CapacityBadge>IA</CapacityBadge>
                            </div>
                        ))}
                    </div>
                </section>
            )}

            <section className="fit-section">
                <div className="fit-section-head">
                    <div>
                        <h2>Demanda por Producto</h2>
                        <p>Series recientes de los principales productos evaluados.</p>
                    </div>
                    <span className="fit-section-badge indigo">Ultimas semanas</span>
                </div>

                <div className="fit-agent-spark-grid">
                    {pageData.forecastItems.slice(0, 8).map((item, index) => (
                        <div className="fit-agent-spark-card" key={`${item.name}-${index}`}>
                            <div>
                                <strong>{item.name}</strong>
                                <p>Demanda prevista: {item.forecast} uds</p>
                                {item.capacity_flag ? <CapacityBadge>limite alcanzado</CapacityBadge> : <TrendBadge trend={item.trend} />}
                            </div>
                            {item.history?.length ? (
                                <LazyChart
                                    configFactory={() => ({
                                        type: 'line',
                                        data: {
                                            labels: item.history.map((_, historyIndex) => `S${historyIndex + 1}`),
                                            datasets: [{ data: item.history, borderColor: chartPalette.green, backgroundColor: 'rgba(16, 185, 129, 0.12)', tension: 0.35, fill: true, pointRadius: 0 }],
                                        },
                                        options: { plugins: { legend: { display: false } }, scales: { x: { display: false }, y: { display: false } }, elements: { line: { borderWidth: 2 } } },
                                    })}
                                    deps={[item.history]}
                                    minHeight={110}
                                    canvasStyle={{ maxHeight: '110px' }}
                                />
                            ) : null}
                        </div>
                    ))}
                </div>
            </section>
        </>
    );
}

function ProductDetailModal({ selectedSummary, selectedDetail, detailState, closeDetails }) {
    if (!selectedSummary) return null;

    return (
        <Modal open={!!selectedSummary} title="Detalle de Prediccion" onClose={closeDetails} wide contentClassName="fit-modal-content">
            {detailState.loading ? (
                <div className="fit-agent-modal-grid single">
                    <div className="fit-transfer-panel">
                        <h4>Cargando detalle</h4>
                        <p>{selectedSummary.name}</p>
                        <div className="chart-skeleton" />
                    </div>
                </div>
            ) : detailState.error ? (
                <div className="fit-transfer-panel">
                    <h4>No se pudo cargar</h4>
                    <p>{detailState.error}</p>
                </div>
            ) : selectedDetail ? (
                <div className="fit-agent-modal-grid">
                    <div className="fit-transfer-panel">
                        <h4>Producto</h4>
                        <div className="fit-agent-detail-title">
                            <strong>{selectedDetail.name}</strong>
                            <TrendBadge trend={selectedDetail.trend} />
                        </div>
                        <div className="fit-agent-detail-badges">
                            <span>Pronostico: <strong>{selectedDetail.forecast}</strong> uds</span>
                            <span>Stock: <strong>{selectedDetail.stock}</strong> uds</span>
                            <span>Min: <strong>{selectedDetail.min_quantity || 'N/D'}</strong></span>
                            <span>Max: <strong>{selectedDetail.max_quantity || 'N/D'}</strong></span>
                        </div>

                        <div className="fit-agent-note">
                            <span>Nota IA</span>
                            <p>{selectedDetail.capacity_note || 'Prediccion generada con historico reciente y topes de stock.'}</p>
                        </div>

                        <div className="fit-agent-note">
                            <span>Lotes por vencer</span>
                            <ul>
                                {selectedDetail.expiring_lots?.length ? selectedDetail.expiring_lots.map((lot, index) => (
                                    <li key={`${lot.code}-${index}`}>{`Lote ${lot.code} - ${lot.expires_in_days} dias (${lot.quantity} uds${lot.warehouse ? ` - ${lot.warehouse}` : ''})`}</li>
                                )) : <li>Sin lotes proximos.</li>}
                            </ul>
                        </div>

                        <div className="fit-agent-note">
                            <span>Recomendacion de restock</span>
                            {selectedDetail.restock?.suggested_qty ? (
                                <p>Cantidad sugerida: <strong>{selectedDetail.restock.suggested_qty} uds</strong>. {selectedDetail.restock.reason || ''}</p>
                            ) : <p>Sin sugerencia.</p>}
                        </div>
                    </div>

                    <div className="fit-agent-chart-stack">
                        <div className="fit-transfer-panel">
                            <h4>Ventas por semana</h4>
                            <LazyChart
                                configFactory={() => ({
                                    type: 'bar',
                                    data: { labels: selectedDetail.weekly?.labels || [], datasets: [{ data: selectedDetail.weekly?.data || [], backgroundColor: 'rgba(79, 70, 229, 0.82)', borderRadius: 10, borderSkipped: false }] },
                                    options: { plugins: { legend: { display: false } }, scales: { x: { ticks: { color: chartPalette.slate }, grid: { display: false } }, y: { ticks: { color: chartPalette.slate }, grid: { color: 'rgba(148, 163, 184, 0.18)' }, beginAtZero: true } } },
                                })}
                                deps={[selectedDetail.weekly]}
                                minHeight={180}
                            />
                        </div>

                        <div className="fit-transfer-panel">
                            <h4>Ventas por mes</h4>
                            <LazyChart
                                configFactory={() => ({
                                    type: 'bar',
                                    data: { labels: selectedDetail.monthly?.labels || [], datasets: [{ data: selectedDetail.monthly?.data || [], backgroundColor: 'rgba(16, 185, 129, 0.82)', borderRadius: 10, borderSkipped: false }] },
                                    options: { plugins: { legend: { display: false } }, scales: { x: { ticks: { color: chartPalette.slate }, grid: { display: false } }, y: { ticks: { color: chartPalette.slate }, grid: { color: 'rgba(148, 163, 184, 0.18)' }, beginAtZero: true } } },
                                })}
                                deps={[selectedDetail.monthly]}
                                minHeight={180}
                            />
                        </div>
                    </div>
                </div>
            ) : null}
        </Modal>
    );
}

export default function AdminAgentOverviewPage({ layout, data, csrfToken, logoutAction }) {
    const [pageData, setPageData] = useState(() => data.initialData || (typeof window !== 'undefined' ? readCachedOverview() : null));
    const [loading, setLoading] = useState(!pageData);
    const [loadingRefresh, setLoadingRefresh] = useState(!!pageData);
    const [loadError, setLoadError] = useState(null);
    const [selectedSummary, setSelectedSummary] = useState(null);
    const [selectedDetail, setSelectedDetail] = useState(null);
    const [detailState, setDetailState] = useState({ loading: false, error: null });

    useEffect(() => {
        const controller = new AbortController();

        async function loadOverview() {
            try {
                const response = await fetch(data.routes.data, {
                    headers: { Accept: 'application/json' },
                    credentials: 'same-origin',
                    signal: controller.signal,
                });
                const payload = await response.json();

                if (!response.ok) {
                    throw new Error(payload.message || 'No se pudo cargar el resumen del agente.');
                }

                setPageData(payload.data);
                writeCachedOverview(payload.data);
                setLoadError(null);
            } catch (error) {
                if (!controller.signal.aborted) {
                    setLoadError(error.message || 'No se pudo cargar el resumen del agente.');
                }
            } finally {
                if (!controller.signal.aborted) {
                    setLoading(false);
                    setLoadingRefresh(false);
                }
            }
        }

        loadOverview();

        return () => controller.abort();
    }, [data.routes.data]);

    const openDetails = async (item) => {
        setSelectedSummary(item);
        setSelectedDetail(null);
        setDetailState({ loading: true, error: null });

        try {
            const response = await fetch(item.detail_url, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            });
            const payload = await response.json();

            if (!response.ok) {
                throw new Error(payload.message || 'No se pudo cargar el detalle del producto.');
            }

            setSelectedDetail(payload.data);
            setDetailState({ loading: false, error: null });
        } catch (error) {
            setDetailState({ loading: false, error: error.message || 'No se pudo cargar el detalle del producto.' });
        }
    };

    const closeDetails = () => {
        setSelectedSummary(null);
        setSelectedDetail(null);
        setDetailState({ loading: false, error: null });
    };

    const hasCapacityAlerts = Array.isArray(pageData?.raw?.capacity_alerts) && pageData.raw.capacity_alerts.length > 0;

    return (
        <DashboardShell sidebar={layout.sidebar} topbar={layout.topbar} csrfToken={csrfToken} logoutAction={logoutAction}>
            <div className="fit-users-page fit-agent-page">
                <section className="fit-users-header">
                    <div className="fit-users-header-left">
                        <div className="fit-header-icon"><i className="ri-robot-2-line" /></div>
                        <div>
                            <h1>Agente Inteligente</h1>
                            <p>Predicciones de demanda, alertas de inventario y recomendaciones de restock.</p>
                            {hasCapacityAlerts && <div className="fit-agent-header-alert"><CapacityBadge>Sugerencia IA: aumenta capacidad</CapacityBadge></div>}
                            {loadingRefresh && !loading && <p>Actualizando datos del agente...</p>}
                            {loadError && pageData && <p className="fit-agent-error">{loadError}</p>}
                        </div>
                    </div>

                    <div className="fit-users-header-actions">
                        <a href="#chartsSection" className="fit-outline-button">
                            <i className="ri-line-chart-line" />
                            <span>Ver Graficos</span>
                        </a>
                        <a href={data.routes.report} className="fit-primary-button" target="_blank" rel="noopener noreferrer">
                            <i className="ri-file-text-line" />
                            <span>Descargar Reporte</span>
                        </a>
                    </div>
                </section>

                {loading && !pageData ? <OverviewSkeleton /> : null}

                {!loading && !pageData && loadError ? (
                    <section className="fit-section">
                        <div className="fit-section-head">
                            <div>
                                <h2>No se pudo cargar el agente</h2>
                                <p>{loadError}</p>
                            </div>
                            <button type="button" className="fit-primary-button" onClick={() => window.location.reload()}>Reintentar</button>
                        </div>
                    </section>
                ) : null}

                {pageData ? <OverviewContent pageData={pageData} openDetails={openDetails} /> : null}

                <ProductDetailModal
                    selectedSummary={selectedSummary}
                    selectedDetail={selectedDetail}
                    detailState={detailState}
                    closeDetails={closeDetails}
                />
            </div>
        </DashboardShell>
    );
}
