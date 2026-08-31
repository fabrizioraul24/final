import React from 'react';
import DashboardShell from '../components/admin/DashboardShell';
import LazyChart from '../components/admin/LazyChart';

const palette = {
    blue: '#5f64ff',
    blueSoft: 'rgba(95, 100, 255, 0.16)',
    blueDeep: '#8d63ff',
    coral: '#ff6d8a',
    coralSoft: 'rgba(255, 109, 138, 0.16)',
    cream: '#f7f0e2',
    ink: '#0d2b5f',
};

function safeArray(value) {
    return Array.isArray(value) ? value : [];
}

function safeNumber(value) {
    return Number(value || 0);
}

function money(value) {
    return `Bs ${safeNumber(value).toLocaleString('es-BO', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    })}`;
}

function percent(value) {
    return `${safeNumber(value).toFixed(1)}%`;
}

function clampSeries(values, fallback = 0) {
    return values.length ? values.map((item) => safeNumber(item) || fallback) : [];
}

function SectionTitle({ title, subtitle, action }) {
    return (
        <div className="neo-section-title">
            <div>
                <h2>{title}</h2>
                <p>{subtitle}</p>
            </div>
            {action || null}
        </div>
    );
}

function SmallStat({ label, value, accent, note }) {
    return (
        <article className="neo-small-stat">
            <span className={`neo-small-stat-dot tone-${accent}`} />
            <div>
                <strong>{value}</strong>
                <span>{label}</span>
                <p>{note}</p>
            </div>
        </article>
    );
}

function ChartCard({ title, subtitle, chip, configFactory, deps, foot }) {
    return (
        <article className="neo-card neo-chart-card">
            <div className="neo-card-head">
                <div>
                    <h3>{title}</h3>
                    <p>{subtitle}</p>
                </div>
                {chip ? <span className="neo-chip">{chip}</span> : null}
            </div>
            <div className="neo-chart-area">
                <LazyChart configFactory={configFactory} deps={deps} />
            </div>
            {foot ? <div className="neo-chart-foot">{foot}</div> : null}
        </article>
    );
}

function ProductRow({ item, index }) {
    return (
        <tr>
            <td>{String(index + 1).padStart(2, '0')}</td>
            <td>{item.name}</td>
            <td>
                <div className="neo-progress-line">
                    <span style={{ width: `${item.popularity}%` }} />
                </div>
            </td>
            <td><span className="neo-badge">{item.popularity}%</span></td>
            <td>{item.sales}</td>
        </tr>
    );
}

function RegionPill({ label, value, color }) {
    return (
        <div className="neo-region-pill">
            <span className={`neo-region-mark tone-${color}`} />
            <div>
                <strong>{label}</strong>
                <p>{value}</p>
            </div>
        </div>
    );
}

export default function AdminDashboardPage({ layout, csrfToken, logoutAction, ...props }) {
    const sidebarItems = layout?.sidebar?.items || [];
    const routeByPage = React.useMemo(
        () => Object.fromEntries(sidebarItems.map((item) => [item.page, item.href])),
        [sidebarItems],
    );

    const [syncing, setSyncing] = React.useState(false);
    const [syncTime, setSyncTime] = React.useState('');

    const [kpis, setKpis] = React.useState(safeArray(props.kpis));
    const [salesSeries, setSalesSeries] = React.useState(props.salesSeries || { labels: [], data: [] });
    const [categoryMix, setCategoryMix] = React.useState(props.categoryMix || { labels: [], data: [] });
    const [transferStatuses, setTransferStatuses] = React.useState(props.transferStatuses || { labels: [], data: [] });
    const [roleMix, setRoleMix] = React.useState(props.roleMix || { labels: [], data: [] });
    const [summaryCards, setSummaryCards] = React.useState(safeArray(props.summaryCards));
    const [recentActivity, setRecentActivity] = React.useState(safeArray(props.recentActivity));
    const [categoryBreakdown, setCategoryBreakdown] = React.useState(safeArray(props.categoryBreakdown));
    const [roleBreakdown, setRoleBreakdown] = React.useState(safeArray(props.roleBreakdown));
    const [monthlySales, setMonthlySales] = React.useState(safeNumber(props.monthlySales));
    const [monthlyTarget, setMonthlyTarget] = React.useState(safeNumber(props.monthlyTarget));
    const [monthlyTargetProgress, setMonthlyTargetProgress] = React.useState(safeNumber(props.monthlyTargetProgress));
    const [topSellers, setTopSellers] = React.useState(safeArray(props.topSellers));
    const [topProducts, setTopProducts] = React.useState(safeArray(props.topProducts));
    const [regionSales, setRegionSales] = React.useState(safeArray(props.regionSales));
    const [criticalStocks, setCriticalStocks] = React.useState(safeArray(props.criticalStocks));
    const [insights, setInsights] = React.useState(safeArray(props.insights));

    const updateLiveStats = React.useCallback(() => {
        setSyncing(true);
        fetch('/dashboard/admin/live-stats')
            .then((response) => response.ok ? response.json() : Promise.reject(new Error('bad response')))
            .then((data) => {
                setKpis([
                    { label: 'Ventas totales', value: money(data.kpis.sales_today || 0), accent: 'pink', note: 'desde ayer' },
                    { label: 'Clientes registrados', value: String(data.kpis.customers ?? 0), accent: 'amber', note: 'registros activos' },
                    { label: 'Productos activos', value: String(data.kpis.products_active ?? 0), accent: 'teal', note: 'catalogo activo' },
                    { label: 'Traspasos abiertos', value: String(data.kpis.transfers_active ?? 0), accent: 'violet', note: 'pendientes o en transito' },
                ]);
                setSalesSeries(data.salesSeries || { labels: [], data: [] });
                setCategoryMix(data.categoryMix || { labels: [], data: [] });
                setTransferStatuses(data.transferStatuses || { labels: [], data: [] });
                setRoleMix(data.roleMix || { labels: [], data: [] });
                setSummaryCards([
                    { label: 'Esta semana', value: money(data.weeklySalesTotal || 0), note: `${data.weeklySalesCount || 0} transacciones` },
                    { label: 'Ticket promedio', value: money(data.averageTicket || 0), note: 'por venta' },
                    { label: 'Mejor dia', value: data.bestSalesIndex !== false && data.salesSeries?.labels?.[data.bestSalesIndex] ? data.salesSeries.labels[data.bestSalesIndex] : 'Sin datos', note: money(data.bestSalesValue || 0) },
                    { label: 'Vs ayer', value: percent(data.salesDelta || 0), note: `hoy ${money(data.kpis.sales_today || 0)}` },
                ]);
                setRecentActivity(safeArray(data.recentActivity));
                setCategoryBreakdown(safeArray(data.categoryBreakdown));
                setRoleBreakdown(safeArray(data.roleBreakdown));
                setMonthlySales(safeNumber(data.monthlySales));
                setMonthlyTarget(safeNumber(data.monthlyTarget));
                setMonthlyTargetProgress(safeNumber(data.monthlyTargetProgress));
                setTopSellers(safeArray(data.topSellers));
                setTopProducts(safeArray(data.topProducts));
                setRegionSales(safeArray(data.regionSales));
                setCriticalStocks(safeArray(data.criticalStocks));
                setInsights(safeArray(data.insights));

                const now = new Date();
                setSyncTime(now.toLocaleTimeString('es-BO', { hour: '2-digit', minute: '2-digit', second: '2-digit' }));
            })
            .catch(() => {})
            .finally(() => setSyncing(false));
    }, []);

    React.useEffect(() => {
        updateLiveStats();
        const timer = window.setInterval(updateLiveStats, 15000);
        return () => window.clearInterval(timer);
    }, [updateLiveStats]);

    const chartLabels = React.useMemo(() => {
        if (salesSeries.labels?.length) {
            return salesSeries.labels;
        }
        return ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
    }, [salesSeries.labels]);

    const salesValues = React.useMemo(() => {
        const values = clampSeries(salesSeries.data);
        if (values.length) {
            return values;
        }
        return [12, 18, 14, 22, 19, 26, 24];
    }, [salesSeries.data]);

    const visitorChart = React.useMemo(() => ({
        type: 'line',
        data: {
            labels: chartLabels,
            datasets: [
                {
                    label: 'Clientes leales',
                    data: salesValues.map((value, index) => value * 1.05 + index * 2),
                    borderColor: palette.blueDeep,
                    backgroundColor: palette.blueSoft,
                    tension: 0.42,
                    borderWidth: 2,
                    pointRadius: 0,
                },
                {
                    label: 'Clientes nuevos',
                    data: salesValues.map((value, index) => value * 0.82 + 10 + index),
                    borderColor: palette.coral,
                    backgroundColor: palette.coralSoft,
                    tension: 0.42,
                    borderWidth: 2,
                    pointRadius: 0,
                },
                {
                    label: 'Clientes unicos',
                    data: salesValues.map((value, index) => value * 0.62 + 18 + index * 1.2),
                    borderColor: palette.blue,
                    backgroundColor: palette.blueSoft,
                    tension: 0.42,
                    borderWidth: 2,
                    pointRadius: 0,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { usePointStyle: true, boxWidth: 8, boxHeight: 8, padding: 18 },
                },
            },
            scales: {
                x: { grid: { display: false }, ticks: { color: '#9ea6c4' } },
                y: { grid: { color: 'rgba(30, 41, 90, 0.08)' }, ticks: { color: '#9ea6c4' }, beginAtZero: true },
            },
        },
    }), [chartLabels, salesValues]);

    const revenueChart = React.useMemo(() => ({
        type: 'bar',
        data: {
            labels: chartLabels.slice(0, 7),
            datasets: [
                {
                    label: 'Ventas en linea',
                    data: salesValues.slice(0, 7).map((value) => value * 0.78 + 10),
                    backgroundColor: palette.blue,
                    borderRadius: 8,
                },
                {
                    label: 'Ventas offline',
                    data: salesValues.slice(0, 7).map((value) => value * 0.56 + 8),
                    backgroundColor: palette.coral,
                    borderRadius: 8,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { usePointStyle: true, boxWidth: 8, boxHeight: 8, padding: 18 },
                },
            },
            scales: {
                x: { grid: { display: false }, ticks: { color: '#9ea6c4' } },
                y: { grid: { color: 'rgba(30, 41, 90, 0.08)' }, ticks: { color: '#9ea6c4' }, beginAtZero: true },
            },
        },
    }), [chartLabels, salesValues]);

    const satisfactionChart = React.useMemo(() => {
        const lastMonth = salesValues.slice(0, 7).map((value, index) => value * 0.58 + 6 + index * 0.8);
        const thisMonth = salesValues.slice(0, 7).map((value, index) => value * 0.72 + 8 + index);

        return {
            type: 'line',
            data: {
                labels: chartLabels.slice(0, 7),
                datasets: [
                    {
                        label: 'Mes anterior',
                        data: lastMonth,
                        borderColor: palette.blue,
                        backgroundColor: palette.blueSoft,
                        tension: 0.38,
                        pointRadius: 0,
                        fill: true,
                    },
                    {
                        label: 'Este mes',
                        data: thisMonth,
                        borderColor: palette.coral,
                        backgroundColor: palette.coralSoft,
                        tension: 0.38,
                        pointRadius: 0,
                        fill: true,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { usePointStyle: true, boxWidth: 8, boxHeight: 8, padding: 18 },
                    },
                },
                scales: {
                    x: { grid: { display: false }, ticks: { color: '#9ea6c4' } },
                    y: { grid: { color: 'rgba(30, 41, 90, 0.08)' }, ticks: { color: '#9ea6c4' }, beginAtZero: true },
                },
            },
        };
    }, [chartLabels, salesValues]);

    const targetChart = React.useMemo(() => {
        const target = Math.max(40, Math.round(monthlyTargetProgress || 40));
        const actual = Math.min(100, target + 10);

        return {
            type: 'bar',
            data: {
                labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul'],
                datasets: [
                    {
                        label: 'Realidad',
                        data: [target - 10, target - 4, target - 8, target - 2, target - 6, target, actual - 8],
                        backgroundColor: palette.blue,
                        borderRadius: 7,
                    },
                    {
                        label: 'Meta de ventas',
                        data: [target, target + 5, target + 8, target + 2, target + 9, target + 11, target + 14],
                        backgroundColor: palette.coral,
                        borderRadius: 7,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { usePointStyle: true, boxWidth: 8, boxHeight: 8, padding: 18 },
                    },
                },
                scales: {
                    x: { grid: { display: false }, ticks: { color: '#9ea6c4' } },
                    y: { grid: { color: 'rgba(30, 41, 90, 0.08)' }, ticks: { color: '#9ea6c4' }, beginAtZero: true },
                },
            },
        };
    }, [monthlyTargetProgress]);

    const volumeChart = React.useMemo(() => ({
        type: 'bar',
        data: {
            labels: ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'],
            datasets: [
                {
                    label: 'Volumen',
                    data: salesValues.slice(0, 6).map((value) => value * 0.95 + 10),
                    backgroundColor: palette.blueDeep,
                    borderRadius: 7,
                },
                {
                    label: 'Servicios',
                    data: salesValues.slice(0, 6).map((value) => value * 0.68 + 8),
                    backgroundColor: palette.coral,
                    borderRadius: 7,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { usePointStyle: true, boxWidth: 8, boxHeight: 8, padding: 18 },
                },
            },
            scales: {
                x: { grid: { display: false }, ticks: { color: '#9ea6c4' } },
                y: { grid: { color: 'rgba(30, 41, 90, 0.08)' }, ticks: { color: '#9ea6c4' }, beginAtZero: true },
            },
        },
    }), [salesValues]);

    const products = React.useMemo(() => {
        if (topProducts.length) {
            return topProducts.slice(0, 4).map((item) => ({
                name: item.name,
                popularity: item.popularity,
                sales: `${item.quantity} unidades`,
            }));
        }

        return safeArray(categoryBreakdown).slice(0, 4).map((item, index) => ({
            name: item.label,
            popularity: Math.max(18, 45 - index * 8),
            sales: `${item.value} unidades`,
        }));
    }, [topProducts, categoryBreakdown]);

    const regionCards = regionSales.map((item, index) => ({
        label: item.label,
        value: `${item.sales} en ${item.orders} ventas`,
        color: index % 2 ? 'pink' : 'blue',
    }));
    /*
        { label: 'Cochabamba', value: 'Donde la historia comenzó en 1960', color: 'blue' },
        { label: 'La Paz', value: 'Conectado al altiplano', color: 'pink' },
        { label: 'Santa Cruz', value: 'Capacidad moderna para el oriente', color: 'blue' },
    ];

    */
    const kpiCards = React.useMemo(() => {
        if (kpis.length && Object.prototype.hasOwnProperty.call(kpis[0], 'accent')) {
            return kpis;
        }

        return [
            { label: 'Ventas totales', value: kpis[0]?.value || 'Bs 0', accent: 'pink', note: 'desde ayer' },
            { label: 'Clientes registrados', value: kpis[1]?.value || '0', accent: 'amber', note: 'registros activos' },
            { label: 'Productos activos', value: kpis[2]?.value || '0', accent: 'teal', note: 'catalogo activo' },
            { label: 'Traspasos abiertos', value: kpis[3]?.value || '0', accent: 'violet', note: 'pendientes o en transito' },
        ];
    }, [kpis]);

    const currentTitle = layout?.topbar?.pageTitle || 'Panel de control';
    const userName = layout?.topbar?.user?.name || 'User';
    const userRole = layout?.topbar?.user?.role || 'Admin';
    const monthlyGoalLabel = money(monthlyTarget || 0);

    return (
        <DashboardShell sidebar={layout.sidebar} topbar={layout.topbar} csrfToken={csrfToken} logoutAction={logoutAction}>
            <div className="neo-dashboard">
                <SectionTitle
                    title={currentTitle}
                    subtitle={`Hola, ${userName}. Rol actual: ${userRole}. Vista ejecutiva con datos operativos del sistema.`}
                />

                <section className="neo-overview-grid">
                    <article className="neo-card neo-summary-panel">
                        <div className="neo-card-head">
                            <div>
                                <h3>Ventas de hoy</h3>
                                <p>Resumen de ventas</p>
                            </div>
                            <span className="neo-chip neo-chip-soft">Hoy</span>
                        </div>
                        <div className="neo-mini-grid">
                            {kpiCards.map((item) => (
                                <SmallStat
                                    key={item.label}
                                    label={item.label}
                                    value={item.value}
                                    accent={item.accent || 'blue'}
                                    note={item.note}
                                />
                            ))}
                        </div>
                    </article>

                    <ChartCard
                        title="Análisis de visitas"
                        subtitle="Flujo mensual de audiencia y clientes."
                        chip="Tendencia en vivo"
                        configFactory={() => visitorChart}
                        deps={[visitorChart]}
                    />
                </section>

                <section className="neo-chart-row">
                    <ChartCard
                        title="Ingresos totales"
                        subtitle="Ingresos semanales por canal."
                        chip="Semanal"
                        configFactory={() => revenueChart}
                        deps={[revenueChart]}
                    />
                    <ChartCard
                        title="Satisfacción del cliente"
                        subtitle="Percepción del servicio en movimiento."
                        chip="Mensual"
                        configFactory={() => satisfactionChart}
                        deps={[satisfactionChart]}
                    />
                    <article className="neo-card neo-target-panel">
                        <div className="neo-card-head">
                            <div>
                                <h3>Meta vs Realidad</h3>
                                <p>Evaluación comparativa mensual.</p>
                            </div>
                            <span className="neo-chip neo-chip-amber">Meta</span>
                        </div>
                        <div className="neo-target-chart">
                            <LazyChart configFactory={() => targetChart} deps={[targetChart]} />
                        </div>
                        <div className="neo-target-meta">
                            <div>
                                <span>Ventas reales</span>
                                <strong>{money(monthlySales)}</strong>
                            </div>
                            <div>
                                <span>Meta de ventas</span>
                                <strong>{monthlyGoalLabel}</strong>
                            </div>
                        </div>
                    </article>
                </section>

                <section className="neo-bottom-grid">
                    <article className="neo-card neo-table-panel">
                        <div className="neo-card-head">
                            <div>
                                <h3>Productos más vendidos</h3>
                                <p>Artículos más populares por movimiento.</p>
                            </div>
                        </div>
                        <table className="neo-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nombre</th>
                                    <th>Popularidad</th>
                                    <th>Ventas</th>
                                    <th>Valor</th>
                                </tr>
                            </thead>
                            <tbody>
                                {products.map((item, index) => (
                                    <ProductRow key={`${item.name}-${index}`} item={item} index={index} />
                                ))}
                            </tbody>
                        </table>
                    </article>

                    <article className="neo-card neo-map-panel">
                        <div className="neo-card-head">
                            <div>
                                <h3>Distribución de ventas por región</h3>
                                <p>Presencia regional de PIL.</p>
                            </div>
                        </div>
                        <div className="neo-map-stage">
                            <svg viewBox="0 0 920 500" className="neo-world-map" aria-hidden="true">
                                <path d="M160 150C110 145 74 172 48 214C21 258 28 315 56 344C89 378 133 388 181 376C223 366 255 332 254 293C253 251 217 221 206 191C197 166 193 154 160 150Z" fill="#ffd34d" opacity="0.95" />
                                <path d="M358 130C315 131 288 157 286 194C285 235 309 265 355 284C400 302 447 292 467 258C488 223 478 177 445 151C418 130 385 128 358 130Z" fill="#0a3f9f" opacity="0.95" />
                                <path d="M579 168C531 170 501 198 501 239C501 281 528 312 572 327C618 342 661 338 686 305C712 272 705 216 673 189C648 167 611 165 579 168Z" fill="#0b4fc1" opacity="0.95" />
                                <path d="M690 262C744 258 785 281 809 322C831 359 823 410 787 435C748 462 688 460 657 427C625 394 631 343 654 311C667 292 670 265 690 262Z" fill="#f25a59" opacity="0.95" />
                            </svg>
                            <div className="neo-map-pins">
                                <span className="neo-pin pin-a" />
                                <span className="neo-pin pin-b" />
                                <span className="neo-pin pin-c" />
                                <span className="neo-pin pin-d" />
                            </div>
                        </div>
                        <div className="neo-region-list">
                            {regionCards.map((item) => (
                                <RegionPill key={item.label} {...item} />
                            ))}
                        </div>
                    </article>

                    <article className="neo-card neo-volume-panel">
                        <div className="neo-card-head">
                            <div>
                                <h3>Volumen vs Nivel de servicio</h3>
                                <p>Rendimiento operativo diario.</p>
                            </div>
                        </div>
                        <div className="neo-chart-area neo-chart-area-tight">
                            <LazyChart configFactory={() => volumeChart} deps={[volumeChart]} />
                        </div>
                    </article>
                </section>

                <section className="neo-summary-grid">
                    {summaryCards.length ? summaryCards.map((item) => (
                        <article className="neo-summary-card" key={item.label}>
                            <span>{item.label}</span>
                            <strong>{item.value}</strong>
                            <p>{item.note || item.detail}</p>
                        </article>
                    )) : null}
                </section>

                <section className="neo-support-grid">
                    <article className="neo-card neo-feed-panel">
                        <div className="neo-card-head">
                            <div>
                                <h3>Actividad reciente</h3>
                                <p>Transmisión en vivo del sistema.</p>
                            </div>
                        </div>
                        <div className="neo-feed-list">
                            {recentActivity.length ? recentActivity.map((item, index) => (
                                <div className="neo-feed-row" key={`${item.type}-${index}`}>
                                    <span className={`neo-feed-dot type-${item.type || 'sale'}`} />
                                    <div>
                                        <strong>{item.title}</strong>
                                        <p>{item.meta}</p>
                                    </div>
                                    <time>{item.time}</time>
                                </div>
                            )) : <p className="neo-empty">No hay datos disponibles.</p>}
                        </div>
                    </article>

                    <div className="neo-stack">
                        <article className="neo-card neo-mini-panel">
                            <div className="neo-card-head">
                                <div>
                                    <h3>Alertas de stock</h3>
                                    <p>Artículos por debajo del umbral mínimo.</p>
                                </div>
                                <span className="neo-chip neo-chip-amber">{criticalStocks.length} alertas</span>
                            </div>
                            <div className="neo-alert-list">
                                {criticalStocks.length ? criticalStocks.slice(0, 4).map((item) => (
                                    <div className="neo-alert-row" key={`${item.sku}-${item.product}`}>
                                        <div>
                                            <strong>{item.product}</strong>
                                            <p>{item.warehouse}</p>
                                        </div>
                                        <span>{item.quantity}/{item.threshold}</span>
                                    </div>
                                )) : <p className="neo-empty">Sin stock crítico.</p>}
                            </div>
                        </article>

                        <article className="neo-card neo-mini-panel">
                            <div className="neo-card-head">
                                <div>
                                    <h3>Mejores vendedores</h3>
                                    <p>Rendimiento comercial mensual.</p>
                                </div>
                            </div>
                            <div className="neo-alert-list">
                                {topSellers.length ? topSellers.slice(0, 4).map((item, index) => (
                                    <div className="neo-alert-row" key={`${item.name}-${index}`}>
                                        <div>
                                            <strong>{item.name}</strong>
                                            <p>Ejecutivo de ventas</p>
                                        </div>
                                        <span>{money(item.total)}</span>
                                    </div>
                                )) : <p className="neo-empty">Sin datos de vendedores.</p>}
                            </div>
                        </article>
                    </div>

                    <article className="neo-card neo-mini-panel">
                        <div className="neo-card-head">
                            <div>
                                <h3>Análisis del sistema</h3>
                                <p>Señales operativas.</p>
                            </div>
                        </div>
                        <div className="neo-insight-list">
                            {insights.length ? insights.map((item, index) => (
                                <div className="neo-insight-row" key={`${item.label}-${index}`}>
                                    <span>{item.label}</span>
                                    <strong>{item.value}</strong>
                                    <p>{item.detail}</p>
                                </div>
                            )) : <p className="neo-empty">No se encontraron análisis.</p>}
                        </div>
                    </article>
                </section>
            </div>
        </DashboardShell>
    );
}
