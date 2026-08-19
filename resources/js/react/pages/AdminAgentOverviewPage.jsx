import React, { useEffect, useState } from 'react';
import DashboardShell from '../components/admin/DashboardShell';
import LazyChart from '../components/admin/LazyChart';
import { Modal, StatsGrid, TableEmpty } from '../components/admin/common';

const SESSION_KEY = 'admin-agent-overview-cache-v1';
const chartPalette = {
    primary: '#566d30',
    light: '#7b814f',
    accent: '#b9be96',
    soft: '#e0e3c7',
    warm: '#f6f6f3',
    plum: '#7b814f',
    line: '#b9be96',
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
        // ignore session cache errors
    }
}

function OverviewSkeleton() {
    return (
        <>
            <div className="stats-grid">
                {[1, 2, 3, 4].map((item) => (
                    <div className="card" key={item}>
                        <div className="chart-skeleton" style={{ minHeight: '110px' }} />
                    </div>
                ))}
            </div>
            <div className="charts-grid" style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(280px, 1fr))', gap: '1rem', marginTop: '1.5rem' }}>
                <div className="card"><div className="chart-skeleton" style={{ minHeight: '300px' }} /></div>
                <div className="card"><div className="chart-skeleton" style={{ minHeight: '300px' }} /></div>
            </div>
            <div className="card" style={{ marginTop: '1rem' }}>
                <div className="chart-skeleton" style={{ minHeight: '360px' }} />
            </div>
        </>
    );
}

function OverviewContent({ pageData, openDetails }) {
    const hasCapacityAlerts = Array.isArray(pageData.raw?.capacity_alerts) && pageData.raw.capacity_alerts.length > 0;

    const forecastChartConfig = () => ({
        type: 'bar',
        data: {
            labels: pageData.charts.forecast.labels,
            datasets: [{ data: pageData.charts.forecast.data, backgroundColor: 'rgba(86,109,48,0.82)', borderColor: chartPalette.primary, borderWidth: 1, borderRadius: 12 }],
        },
        options: { plugins: { legend: { display: false } }, scales: { x: { ticks: { color: '#fff' }, grid: { display: false } }, y: { ticks: { color: '#fff' }, grid: { color: 'rgba(255,255,255,0.08)' } } } },
    });

    const restockChartConfig = () => ({
        type: 'doughnut',
        data: {
            labels: pageData.charts.restock.labels,
            datasets: [{ data: pageData.charts.restock.data, backgroundColor: [chartPalette.primary, chartPalette.light, chartPalette.accent, chartPalette.soft, chartPalette.warm, chartPalette.plum] }],
        },
        options: { plugins: { legend: { labels: { color: '#fff' } } } },
    });

    return (
        <>
            <StatsGrid items={[
                { label: 'Restock sugeridos', value: pageData.stats.restock, chip: 'Ordenes recomendadas', chipClass: 'chip-muted' },
                { label: 'Alertas de stock', value: pageData.stats.alerts_low, chip: 'Bajo inventario' },
                { label: 'Lotes por vencer', value: pageData.stats.alerts_expiring, chip: '30 dias' },
                { label: 'Sugerencias de capacidad', value: pageData.stats.capacity, chip: 'Aumentar limite', chipClass: 'chip-muted' },
            ]} />

            <div className="charts-grid" style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(280px, 1fr))', gap: '1rem', marginTop: '1.5rem' }} id="chartsSection">
                <div className="card chart-placeholder">
                    <div className="chart-head"><h4>Top productos (pronostico)</h4><span className="chip">Demanda semanal</span></div>
                    {pageData.charts.forecast.labels?.length ? <LazyChart configFactory={forecastChartConfig} deps={[pageData.charts.forecast]} minHeight={260} canvasStyle={{ maxHeight: '260px' }} /> : null}
                </div>
                <div className="card chart-placeholder">
                    <div className="chart-head"><h4>Distribucion de restock</h4><span className="chip">Sugerencias</span></div>
                    {pageData.charts.restock.labels?.length ? <LazyChart configFactory={restockChartConfig} deps={[pageData.charts.restock]} minHeight={260} canvasStyle={{ maxHeight: '260px' }} /> : null}
                </div>
            </div>

            <div className="card" style={{ marginTop: '1rem' }}>
                <div className="chart-head"><h4>Predicciones IA por producto</h4><span className="chip">{pageData.forecastItems.length} registros</span></div>
                <div className="table-wrapper">
                    <table className="data-table">
                        <thead><tr><th>Producto</th><th>Pronostico</th><th>Tendencia</th><th>Stock</th><th>Ventas (6 sem)</th><th>Ventas (6 mes)</th><th>Acciones</th></tr></thead>
                        <tbody>
                            {pageData.forecastItems.length ? pageData.forecastItems.map((item) => (
                                <tr key={item.product_id || item.name}>
                                    <td>{item.name}</td>
                                    <td>{item.forecast} uds</td>
                                    <td><span className={`trend-pill ${item.trend === 'alza' ? 'up' : item.trend === 'baja' ? 'down' : 'steady'}`}><i className="ri-line-chart-line" />{item.trend}</span></td>
                                    <td>{item.stock} uds</td>
                                    <td>{item.weekly_recent_total} uds</td>
                                    <td>{item.monthly_recent_total} uds</td>
                                    <td><button type="button" className="pill-button ghost" onClick={() => openDetails(item)}>Detalles</button></td>
                                </tr>
                            )) : <TableEmpty colSpan={7} text="Sin datos de Prediccion." />}
                        </tbody>
                    </table>
                </div>
            </div>

            {hasCapacityAlerts && (
                <div className="card" style={{ marginTop: '1rem' }}>
                    <div className="chart-head"><h4>Capacidad al limite</h4><span className="chip">{pageData.raw.capacity_alerts.length} productos</span></div>
                    <div className="alert-grid">
                        {pageData.raw.capacity_alerts.map((cap) => (
                            <div className="summary-card" key={cap.name} style={{ background: 'rgba(251,191,36,0.06)', border: '1px solid rgba(251,191,36,0.25)' }}>
                                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', gap: '0.5rem' }}>
                                    <div>
                                        <strong>{cap.name}</strong>
                                        <p style={{ margin: '0.15rem 0', color: 'rgba(255,255,255,0.75)' }}>Limite: {cap.max_quantity ?? 'N/D'} uds</p>
                                        <p style={{ margin: 0, color: '#fcd34d' }}>{cap.note ?? 'Sugerencia: aumentar capacidad'}</p>
                                    </div>
                                    <span className="capacity-badge"><i className="ri-flashlight-line" /> IA</span>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            )}

            <div className="card">
                <div className="chart-head"><h4>Demanda por producto</h4><span className="chip chip-muted">Series de las ultimas semanas</span></div>
                <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit,minmax(280px,1fr))', gap: '0.8rem' }}>
                    {pageData.forecastItems.slice(0, 8).map((item, index) => (
                        <div className="spark-card" key={`${item.name}-${index}`}>
                            <div>
                                <strong>{item.name}</strong>
                                <p style={{ margin: '0.3rem 0 0', color: 'rgba(255,255,255,0.8)' }}>Demanda prevista: {item.forecast} uds</p>
                                {item.capacity_flag
                                    ? <span className="capacity-badge" style={{ marginTop: '0.4rem' }}><i className="ri-alert-line" /> limite alcanzado</span>
                                    : <span className="chip chip-muted" style={{ marginTop: '0.4rem', display: 'inline-block' }}>{item.trend}</span>}
                            </div>
                            {item.history?.length ? (
                                <LazyChart
                                    configFactory={() => ({
                                        type: 'line',
                                        data: {
                                            labels: item.history.map((_, historyIndex) => `S${historyIndex + 1}`),
                                            datasets: [{ data: item.history, borderColor: chartPalette.line, backgroundColor: 'rgba(185,190,150,0.16)', tension: 0.35, fill: true, pointRadius: 0 }],
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
            </div>
        </>
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
                if (controller.signal.aborted) {
                    return;
                }

                setLoadError(error.message || 'No se pudo cargar el resumen del agente.');
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
            <div className="hero">
                <div>
                    <h2 style={{ margin: 0, color: '#fff' }}>Resumen del agente inteligente</h2>
                    <p style={{ margin: '0.25rem 0 0', color: 'rgba(255,255,255,0.75)' }}>Prediccion con limites de capacidad y alertas mejoradas.</p>
                    {hasCapacityAlerts && <div style={{ marginTop: '0.6rem' }}><span className="capacity-badge"><i className="ri-rocket-2-line" /> Sugerencia IA: aumenta capacidad</span></div>}
                    {loadingRefresh && !loading && <p style={{ margin: '0.5rem 0 0', color: 'rgba(255,255,255,0.65)' }}>Actualizando datos del agente...</p>}
                    {loadError && pageData && <p style={{ margin: '0.5rem 0 0', color: '#fecdd3' }}>{loadError}</p>}
                </div>
                <div style={{ display: 'flex', gap: '0.5rem', justifyContent: 'flex-end', flexWrap: 'wrap' }}>
                    <a href="#chartsSection" className="pill-button ghost"><i className="ri-line-chart-line" />Ver graficos</a>
                    <a href={data.routes.report} className="pill-button" target="_blank" rel="noopener"><i className="ri-file-text-line" />Descargar reporte</a>
                </div>
            </div>

            {loading && !pageData ? <OverviewSkeleton /> : null}

            {!loading && !pageData && loadError ? (
                <div className="card">
                    <h4 style={{ marginTop: 0 }}>No se pudo cargar el agente</h4>
                    <p style={{ color: 'rgba(255,255,255,0.75)' }}>{loadError}</p>
                    <button type="button" className="pill-button" onClick={() => window.location.reload()}>Reintentar</button>
                </div>
            ) : null}

            {pageData ? <OverviewContent pageData={pageData} openDetails={openDetails} /> : null}

            <Modal open={!!selectedSummary} title="Detalle de Prediccion" onClose={closeDetails} wide>
                {selectedSummary && (
                    detailState.loading ? (
                        <div className="ai-modal-body">
                            <div className="ai-card">
                                <p style={{ margin: 0, color: 'rgba(255,255,255,0.7)' }}>Cargando detalle de {selectedSummary.name}...</p>
                                <div className="chart-skeleton" style={{ minHeight: '220px', marginTop: '1rem' }} />
                            </div>
                        </div>
                    ) : detailState.error ? (
                        <div className="ai-modal-body">
                            <div className="ai-card">
                                <p style={{ margin: 0, color: '#fecdd3' }}>{detailState.error}</p>
                            </div>
                        </div>
                    ) : selectedDetail ? (
                        <div className="ai-modal-body">
                            <div className="ai-card">
                                <p style={{ margin: 0, color: 'rgba(255,255,255,0.7)' }}>Producto</p>
                                <h2 style={{ margin: '0.2rem 0 0.6rem' }}>{selectedDetail.name}</h2>
                                <div className="badge-grid">
                                    <span className={`trend-pill ${selectedDetail.trend === 'alza' ? 'up' : selectedDetail.trend === 'baja' ? 'down' : 'steady'}`}><i className="ri-line-chart-line" />{selectedDetail.trend}</span>
                                    <span className="chip">Pronostico: <strong>{selectedDetail.forecast}</strong> uds</span>
                                    <span className="chip">Stock: <strong>{selectedDetail.stock}</strong> uds</span>
                                    <span className="chip">Min: <strong>{selectedDetail.min_quantity || 'N/D'}</strong></span>
                                    <span className="chip">Max: <strong>{selectedDetail.max_quantity || 'N/D'}</strong></span>
                                </div>
                                <div className="ai-highlight" style={{ marginTop: '0.6rem' }}>
                                    <p style={{ margin: 0, color: 'rgba(255,255,255,0.7)' }}>Nota IA</p>
                                    <p style={{ margin: '0.25rem 0 0', color: 'rgba(255,255,255,0.85)' }}>{selectedDetail.capacity_note || 'Prediccion generada con historico reciente y topes de stock.'}</p>
                                    <div className="badge-grid" style={{ marginTop: '0.4rem' }}>
                                        <span className="chip">Ventas 6 sem: <strong>{selectedDetail.weekly?.recent_total || 0}</strong> uds</span>
                                        <span className="chip">Ventas 6 mes: <strong>{selectedDetail.monthly?.recent_total || 0}</strong> uds</span>
                                    </div>
                                </div>
                                <div className="ai-highlight" style={{ marginTop: '0.6rem' }}>
                                    <p style={{ margin: 0, color: 'rgba(255,255,255,0.7)' }}>Lotes por vencer</p>
                                    <ul style={{ margin: '0.35rem 0 0', paddingLeft: '1rem', color: 'rgba(255,255,255,0.9)' }}>
                                        {selectedDetail.expiring_lots?.length ? selectedDetail.expiring_lots.map((lot, index) => <li key={`${lot.code}-${index}`}>{`Lote ${lot.code} - ${lot.expires_in_days} dias (${lot.quantity} uds${lot.warehouse ? ` - ${lot.warehouse}` : ''})`}</li>) : <li style={{ color: 'rgba(255,255,255,0.7)' }}>Sin lotes proximos.</li>}
                                    </ul>
                                </div>
                                <div className="ai-highlight" style={{ marginTop: '0.6rem' }}>
                                    <p style={{ margin: 0, color: 'rgba(255,255,255,0.7)' }}>Recomendacion de restock</p>
                                    {selectedDetail.restock?.suggested_qty ? (
                                        <div style={{ marginTop: '0.35rem' }}>
                                            <p style={{ margin: 0 }}>Cantidad sugerida: <strong>{selectedDetail.restock.suggested_qty} uds</strong></p>
                                            <p style={{ margin: '0.2rem 0 0', color: 'rgba(255,255,255,0.8)' }}>{selectedDetail.restock.reason || ''}</p>
                                        </div>
                                    ) : <p style={{ margin: '0.35rem 0 0', color: 'rgba(255,255,255,0.7)' }}>Sin sugerencia.</p>}
                                </div>
                            </div>
                            <div style={{ display: 'flex', flexDirection: 'column', gap: '0.75rem' }}>
                                <div className="ai-chart-mini">
                                    <div className="chart-head" style={{ marginBottom: '0.25rem' }}><h4 style={{ margin: 0 }}>Ventas por semana</h4><span className="chip chip-muted">Serie reciente</span></div>
                                    <LazyChart
                                        configFactory={() => ({
                                            type: 'bar',
                                            data: { labels: selectedDetail.weekly?.labels || [], datasets: [{ data: selectedDetail.weekly?.data || [], backgroundColor: 'rgba(86,109,48,0.82)', borderRadius: 10, borderSkipped: false }] },
                                            options: { plugins: { legend: { display: false } }, scales: { x: { ticks: { color: '#f6f6f3' }, grid: { display: false } }, y: { ticks: { color: 'rgba(255,255,255,0.85)' }, grid: { color: 'rgba(255,255,255,0.08)' }, beginAtZero: true } } },
                                        })}
                                        deps={[selectedDetail.weekly]}
                                        minHeight={180}
                                    />
                                </div>
                                <div className="ai-chart-mini">
                                    <div className="chart-head" style={{ marginBottom: '0.25rem' }}><h4 style={{ margin: 0 }}>Ventas por mes</h4><span className="chip chip-muted">Ultimos meses</span></div>
                                    <LazyChart
                                        configFactory={() => ({
                                            type: 'bar',
                                            data: { labels: selectedDetail.monthly?.labels || [], datasets: [{ data: selectedDetail.monthly?.data || [], backgroundColor: 'rgba(224,227,199,0.86)', borderRadius: 10, borderSkipped: false }] },
                                            options: { plugins: { legend: { display: false } }, scales: { x: { ticks: { color: '#f6f6f3' }, grid: { display: false } }, y: { ticks: { color: 'rgba(255,255,255,0.85)' }, grid: { color: 'rgba(255,255,255,0.08)' }, beginAtZero: true } } },
                                        })}
                                        deps={[selectedDetail.monthly]}
                                        minHeight={180}
                                    />
                                </div>
                            </div>
                        </div>
                    ) : null
                )}
            </Modal>
        </DashboardShell>
    );
}
