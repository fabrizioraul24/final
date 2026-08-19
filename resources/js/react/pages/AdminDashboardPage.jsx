import React from 'react';
import DashboardShell from '../components/admin/DashboardShell';
import LazyChart from '../components/admin/LazyChart';

const colors = {
    primary: '#6366f1',    // Indigo
    primaryLight: '#818cf8',
    accent: '#06b6d4',     // Cyan
    success: '#10b981',    // Emerald
    red: '#ef4444',        // Rose
    yellow: '#f59e0b',     // Amber
    green: '#10b981',
};

function ChartCard({ title, description, chip, configFactory, deps }) {
    return (
        <article className="card exec-chart-card premium-chart-card">
            <div className="exec-chart-head">
                <div>
                    <h4>{title}</h4>
                    <p>{description}</p>
                </div>
                <span className="chip premium-chip">{chip}</span>
            </div>
            <LazyChart configFactory={configFactory} deps={deps} />
        </article>
    );
}

export default function AdminDashboardPage(props) {
    const {
        layout,
        csrfToken,
        logoutAction
    } = props;

    // React state for live-sync data
    const [kpisState, setKpisState] = React.useState(props.kpis || []);
    const [salesSeriesState, setSalesSeriesState] = React.useState(props.salesSeries || { labels: [], data: [] });
    const [categoryMixState, setCategoryMixState] = React.useState(props.categoryMix || { labels: [], data: [] });
    const [transferStatusesState, setTransferStatusesState] = React.useState(props.transferStatuses || { labels: [], data: [] });
    const [roleMixState, setRoleMixState] = React.useState(props.roleMix || { labels: [], data: [] });
    const [summaryCardsState, setSummaryCardsState] = React.useState(props.summaryCards || []);
    const [insightsState, setInsightsState] = React.useState(props.insights || []);
    const [recentActivityState, setRecentActivityState] = React.useState(props.recentActivity || []);
    const [categoryBreakdownState, setCategoryBreakdownState] = React.useState(props.categoryBreakdown || []);
    const [roleBreakdownState, setRoleBreakdownState] = React.useState(props.roleBreakdown || []);

    // New metrics state
    const [monthlySalesState, setMonthlySalesState] = React.useState(props.monthlySales || 0);
    const [monthlyTargetProgressState, setMonthlyTargetProgressState] = React.useState(props.monthlyTargetProgress || 0);
    const [topSellersState, setTopSellersState] = React.useState(props.topSellers || []);
    const [criticalStocksState, setCriticalStocksState] = React.useState(props.criticalStocks || []);

    const [syncTime, setSyncTime] = React.useState("");
    const [isSyncing, setIsSyncing] = React.useState(false);

    // Live digital clock state
    const [currentTime, setCurrentTime] = React.useState(new Date());

    // Update clock every second
    React.useEffect(() => {
        const timer = setInterval(() => setCurrentTime(new Date()), 1000);
        return () => clearInterval(timer);
    }, []);

    // Dynamic Greeting based on current hour
    const getGreeting = () => {
        const hour = currentTime.getHours();
        if (hour < 6) return '¡Buenas noches, trasnochador! 🌙';
        if (hour < 12) return '¡Buenos días! ☀️';
        if (hour < 18) return '¡Buenas tardes! 🌤️';
        return '¡Buenas noches! 🌙';
    };

    // Formatted date string
    const getFormattedDate = () => {
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        return currentTime.toLocaleDateString('es-ES', options);
    };

    // Live refresh loader
    const fetchLiveStats = () => {
        setIsSyncing(true);
        fetch('/dashboard/admin/live-stats')
            .then(res => {
                if (!res.ok) throw new Error("API error");
                return res.json();
            })
            .then(data => {
                setKpisState([
                    {
                        label: 'Ventas del dia',
                        value: 'Bs ' + data.kpis.sales_today.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}),
                        icon: 'ri-line-chart-line',
                        chip: 'Actualizado hoy',
                        chipClass: 'chip-success',
                    },
                    {
                        label: 'Clientes registrados',
                        value: String(data.kpis.customers),
                        icon: 'ri-community-line',
                        chip: 'Empresas + tiendas',
                        chipClass: 'chip-muted',
                    },
                    {
                        label: 'Productos activos',
                        value: String(data.kpis.products_active),
                        icon: 'ri-shopping-bag-3-line',
                        chip: 'Catalogo disponible',
                        chipClass: 'chip-muted',
                    },
                    {
                        label: 'Traspasos abiertos',
                        value: String(data.kpis.transfers_active),
                        icon: 'ri-shuffle-line',
                        chip: 'Pendientes o en transito',
                    }
                ]);
                setSalesSeriesState(data.salesSeries);
                setCategoryMixState(data.categoryMix);
                setTransferStatusesState(data.transferStatuses);
                setRoleMixState(data.roleMix);
                setSummaryCardsState([
                    {
                        label: 'Total semanal',
                        value: 'Bs ' + data.weeklySalesTotal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}),
                        detail: data.weeklySalesCount + ' ventas registradas',
                    },
                    {
                        label: 'Ticket promedio',
                        value: 'Bs ' + data.averageTicket.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}),
                        detail: 'Promedio por venta',
                    },
                    {
                        label: 'Mejor dia',
                        value: data.bestSalesIndex !== false ? data.salesSeries.labels[data.bestSalesIndex] : 'Sin datos',
                        detail: 'Pico: Bs ' + data.bestSalesValue.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}),
                    },
                    {
                        label: 'Vs ayer',
                        value: (data.salesDelta >= 0 ? '+' : '') + data.salesDelta.toFixed(1) + '%',
                        detail: 'Hoy: Bs ' + data.kpis.sales_today.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}),
                    },
                ]);
                setRecentActivityState(data.recentActivity);
                setCategoryBreakdownState(data.categoryBreakdown);
                setRoleBreakdownState(data.roleBreakdown);

                // New stats
                setMonthlySalesState(data.monthlySales);
                setMonthlyTargetProgressState(data.monthlyTargetProgress);
                setTopSellersState(data.topSellers);
                setCriticalStocksState(data.criticalStocks);

                const now = new Date();
                setSyncTime(`En vivo · Refrescado a las ${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}:${String(now.getSeconds()).padStart(2, '0')}`);
            })
            .catch(err => console.error("Error fetching admin live stats:", err))
            .finally(() => setIsSyncing(false));
    };

    // React polling hook
    React.useEffect(() => {
        const now = new Date();
        setSyncTime(`En vivo · Refrescado a las ${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}:${String(now.getSeconds()).padStart(2, '0')}`);

        const interval = setInterval(fetchLiveStats, 15000);
        return () => clearInterval(interval);
    }, []);

    const salesChartConfig = () => ({
        type: 'line',
        data: {
            labels: salesSeriesState.labels,
            datasets: [{
                label: 'Ventas (Bs)',
                data: salesSeriesState.data,
                borderColor: '#06b6d4',
                backgroundColor: 'rgba(6,182,212,0.06)',
                tension: 0.38,
                fill: true,
                pointRadius: 5,
                pointHoverRadius: 7,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#06b6d4',
                pointBorderWidth: 2,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { 
                legend: { display: false },
                tooltip: {
                    padding: 12,
                    backgroundColor: 'rgba(16, 22, 56, 0.95)',
                    titleColor: '#fff',
                    bodyColor: '#34d399',
                    borderColor: 'rgba(255, 255, 255, 0.1)',
                    borderWidth: 1,
                    cornerRadius: 8,
                    displayColors: false
                }
            },
            scales: {
                x: { grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { color: 'rgba(255,255,255,0.6)', font: { size: 11 } } },
                y: { grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { color: 'rgba(255,255,255,0.6)', font: { size: 11 } }, beginAtZero: true },
            },
        },
    });

    const categoryChartConfig = () => ({
        type: 'bar',
        data: {
            labels: categoryMixState.labels,
            datasets: [{
                label: 'Productos',
                data: categoryMixState.data,
                backgroundColor: [
                    'rgba(99, 102, 241, 0.85)',
                    'rgba(6, 182, 212, 0.85)',
                    'rgba(16, 185, 129, 0.85)',
                    'rgba(245, 158, 11, 0.85)',
                    'rgba(239, 68, 68, 0.85)',
                    'rgba(139, 92, 246, 0.85)'
                ],
                hoverBackgroundColor: [
                    '#6366f1',
                    '#06b6d4',
                    '#10b981',
                    '#f59e0b',
                    '#ef4444',
                    '#8b5cf6'
                ],
                borderRadius: 6,
                borderWidth: 0,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { 
                legend: { display: false },
                tooltip: {
                    padding: 12,
                    backgroundColor: 'rgba(16, 22, 56, 0.95)',
                    titleColor: '#fff',
                    cornerRadius: 8,
                    displayColors: false
                }
            },
            scales: {
                x: { ticks: { color: 'rgba(255,255,255,0.6)', font: { size: 10 } }, grid: { display: false } },
                y: { ticks: { color: 'rgba(255,255,255,0.6)', font: { size: 11 } }, grid: { color: 'rgba(255,255,255,0.04)' }, beginAtZero: true },
            },
        },
    });

    const transferChartConfig = () => ({
        type: 'doughnut',
        data: {
            labels: transferStatusesState.labels,
            datasets: [{
                data: transferStatusesState.data,
                backgroundColor: [
                    'rgba(99, 102, 241, 0.85)',
                    'rgba(6, 182, 212, 0.85)',
                    'rgba(245, 158, 11, 0.85)',
                    'rgba(239, 68, 68, 0.85)',
                    'rgba(16, 185, 129, 0.85)',
                ],
                borderWidth: 2,
                borderColor: '#111827'
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '72%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { color: 'rgba(255,255,255,0.7)', padding: 14, boxWidth: 8, font: { size: 11, weight: '500' } },
                },
                tooltip: {
                    padding: 10,
                    backgroundColor: 'rgba(16, 22, 56, 0.95)',
                    titleColor: '#fff',
                    cornerRadius: 8
                }
            },
        },
    });

    const roleChartConfig = () => ({
        type: 'bar',
        data: {
            labels: roleMixState.labels,
            datasets: [{
                label: 'Usuarios',
                data: roleMixState.data,
                backgroundColor: 'rgba(129, 140, 248, 0.8)',
                hoverBackgroundColor: '#818cf8',
                borderRadius: 6,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { 
                legend: { display: false },
                tooltip: {
                    padding: 12,
                    backgroundColor: 'rgba(16, 22, 56, 0.95)',
                    titleColor: '#fff',
                    cornerRadius: 8,
                    displayColors: false
                }
            },
            scales: {
                x: { ticks: { color: 'rgba(255,255,255,0.6)', font: { size: 11 } }, grid: { display: false } },
                y: { ticks: { color: 'rgba(255,255,255,0.6)', font: { size: 11 } }, grid: { color: 'rgba(255,255,255,0.04)' }, beginAtZero: true },
            },
        },
    });

    // Helper to get vs yesterday delta
    const getSalesDelta = () => {
        const vsAyer = summaryCardsState.find(c => c.label === 'Vs ayer');
        return vsAyer ? vsAyer.value : null;
    };

    const salesDeltaVal = getSalesDelta();

    return (
        <DashboardShell
            sidebar={layout.sidebar}
            topbar={layout.topbar}
            csrfToken={csrfToken}
            logoutAction={logoutAction}
        >
            {/* Scoped CSS Injector for Premium UX/UI styling */}
            <style dangerouslySetInnerHTML={{ __html: `
                .exec-dashboard {
                    display: flex;
                    flex-direction: column;
                    gap: 1.6rem;
                    padding-bottom: 3rem;
                }

                /* Welcome Banner Component */
                .welcome-hero {
                    background: linear-gradient(135deg, rgba(20, 28, 68, 0.85) 0%, rgba(30, 48, 108, 0.6) 50%, rgba(48, 29, 90, 0.5) 100%);
                    border: 1px solid rgba(255, 255, 255, 0.08);
                    border-radius: 1.75rem;
                    padding: 1.75rem 2.25rem;
                    position: relative;
                    overflow: hidden;
                    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3), inset 0 1px 0 rgba(255, 255, 255, 0.1);
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    gap: 2rem;
                    backdrop-filter: blur(12px);
                    animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
                }

                .welcome-hero::after {
                    content: '';
                    position: absolute;
                    inset: 0;
                    background: radial-gradient(circle at 90% 10%, rgba(99, 102, 241, 0.12) 0%, transparent 60%);
                    pointer-events: none;
                }

                @keyframes fadeInUp {
                    from { opacity: 0; transform: translateY(18px); }
                    to { opacity: 1; transform: translateY(0); }
                }

                .welcome-text h2 {
                    margin: 0;
                    font-size: clamp(1.4rem, 2.5vw, 1.95rem);
                    font-weight: 800;
                    background: linear-gradient(120deg, #ffffff 40%, #e2e8f0 70%, #a5b4fc 100%);
                    -webkit-background-clip: text;
                    -webkit-text-fill-color: transparent;
                    letter-spacing: -0.02em;
                }

                .welcome-text p {
                    margin: 0.5rem 0 0;
                    font-size: 0.95rem;
                    color: rgba(255, 255, 255, 0.78);
                    line-height: 1.5;
                    max-width: 720px;
                }

                .welcome-meta {
                    text-align: right;
                    display: flex;
                    flex-direction: column;
                    gap: 0.15rem;
                    flex-shrink: 0;
                }

                .welcome-time {
                    font-size: 1.85rem;
                    font-weight: 800;
                    color: #fff;
                    font-variant-numeric: tabular-nums;
                    letter-spacing: -0.02em;
                    text-shadow: 0 0 15px rgba(255, 255, 255, 0.15);
                }

                .welcome-date {
                    font-size: 0.85rem;
                    color: rgba(255, 255, 255, 0.55);
                    font-weight: 500;
                    text-transform: capitalize;
                }

                /* Shortcuts Panel Component */
                .shortcuts-section {
                    animation: fadeInUp 0.7s cubic-bezier(0.16, 1, 0.3, 1) 0.05s both;
                }

                .section-header-bar {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    margin-bottom: 1rem;
                }

                .section-header-bar h3 {
                    margin: 0;
                    font-size: 1.1rem;
                    font-weight: 700;
                    letter-spacing: -0.01em;
                    color: rgba(255, 255, 255, 0.9);
                    display: flex;
                    align-items: center;
                    gap: 0.6rem;
                }

                .section-header-bar h3 i {
                    color: #818cf8;
                    font-size: 1.25rem;
                }

                .shortcuts-grid {
                    display: grid;
                    grid-template-columns: repeat(6, 1fr);
                    gap: 1rem;
                }

                @media (max-width: 1440px) {
                    .shortcuts-grid { grid-template-columns: repeat(3, 1fr); }
                }
                @media (max-width: 840px) {
                    .shortcuts-grid { grid-template-columns: repeat(2, 1fr); }
                }
                @media (max-width: 480px) {
                    .shortcuts-grid { grid-template-columns: 1fr; }
                }

                .shortcut-card {
                    background: rgba(255, 255, 255, 0.02);
                    border: 1px solid rgba(255, 255, 255, 0.07);
                    border-radius: 1.35rem;
                    padding: 1.25rem 1.15rem;
                    display: flex;
                    flex-direction: column;
                    justify-content: space-between;
                    transition: all 0.35s cubic-bezier(0.2, 0.8, 0.2, 1);
                    position: relative;
                    overflow: hidden;
                    cursor: pointer;
                    text-decoration: none;
                    color: inherit;
                    min-height: 154px;
                    box-shadow: 0 10px 20px rgba(0,0,0,0.12);
                }

                .shortcut-card:hover {
                    transform: translateY(-5px);
                    background: rgba(255, 255, 255, 0.045);
                    border-color: rgba(129, 140, 248, 0.35);
                    box-shadow: 0 20px 35px rgba(0, 0, 0, 0.35), 0 0 15px rgba(129, 140, 248, 0.1);
                }

                .shortcut-icon-container {
                    width: 42px;
                    height: 42px;
                    border-radius: 0.95rem;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 1.25rem;
                    margin-bottom: 0.85rem;
                    color: #fff;
                    transition: transform 0.3s ease;
                }

                .shortcut-card:hover .shortcut-icon-container {
                    transform: scale(1.1);
                }

                .shortcut-info h4 {
                    margin: 0;
                    font-size: 0.88rem;
                    font-weight: 700;
                    color: #ffffff;
                    margin-bottom: 0.25rem;
                }

                .shortcut-info p {
                    margin: 0;
                    font-size: 0.76rem;
                    color: rgba(255, 255, 255, 0.52);
                    line-height: 1.35;
                }

                .shortcut-action {
                    font-size: 0.76rem;
                    font-weight: 700;
                    color: #818cf8;
                    display: flex;
                    align-items: center;
                    gap: 0.2rem;
                    margin-top: 0.75rem;
                    transition: all 0.2s ease;
                }

                .shortcut-card:hover .shortcut-action {
                    color: #fff;
                    transform: translateX(3px);
                }

                /* Sync indicator overrides */
                .premium-sync-bar {
                    background: rgba(16, 22, 56, 0.5) !important;
                    border: 1px solid rgba(255, 255, 255, 0.05) !important;
                    border-radius: 1.2rem !important;
                    backdrop-filter: blur(8px);
                }

                /* Target and Stats Row */
                .target-stats-split {
                    display: grid;
                    grid-template-columns: 1.4fr 1fr;
                    gap: 1.5rem;
                    animation: fadeInUp 0.7s cubic-bezier(0.16, 1, 0.3, 1) 0.1s both;
                }

                @media (max-width: 1100px) {
                    .target-stats-split { grid-template-columns: 1fr; }
                }

                .premium-target-card {
                    background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(6, 182, 212, 0.08) 100%) !important;
                    border: 1px solid rgba(6, 182, 212, 0.22) !important;
                    border-radius: 1.5rem !important;
                    padding: 1.5rem !important;
                    box-shadow: 0 20px 35px rgba(0, 0, 0, 0.2), inset 0 1px 0 rgba(255, 255, 255, 0.05) !important;
                    display: flex;
                    flex-direction: column;
                    justify-content: space-between;
                }

                .weekly-strip-card {
                    background: rgba(255, 255, 255, 0.02) !important;
                    border: 1px solid rgba(255, 255, 255, 0.06) !important;
                    border-radius: 1.5rem !important;
                    padding: 1.5rem !important;
                    display: flex;
                    flex-direction: column;
                    justify-content: space-between;
                    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.18) !important;
                }

                .weekly-grid {
                    display: grid;
                    grid-template-columns: repeat(2, 1fr);
                    gap: 0.85rem;
                }

                .weekly-item {
                    background: rgba(255, 255, 255, 0.015);
                    border: 1px solid rgba(255, 255, 255, 0.04);
                    border-radius: 1.1rem;
                    padding: 0.85rem 1rem;
                    display: flex;
                    flex-direction: column;
                    justify-content: center;
                }

                .weekly-item span {
                    font-size: 0.72rem;
                    color: rgba(255, 255, 255, 0.45);
                    text-transform: uppercase;
                    letter-spacing: 0.05em;
                    margin-bottom: 0.2rem;
                }

                .weekly-item strong {
                    font-size: 1.12rem;
                    font-weight: 800;
                    color: #ffffff;
                }

                .weekly-item p {
                    margin: 0.15rem 0 0;
                    font-size: 0.75rem;
                    color: rgba(255, 255, 255, 0.55);
                }

                /* Premium KPI Cards */
                .premium-kpi-card {
                    background: rgba(255, 255, 255, 0.02) !important;
                    border: 1px solid rgba(255, 255, 255, 0.06) !important;
                    border-radius: 1.5rem !important;
                    padding: 1.4rem 1.6rem !important;
                    display: flex;
                    flex-direction: column;
                    justify-content: space-between;
                    min-height: 154px !important;
                    transition: all 0.35s cubic-bezier(0.2, 0.8, 0.2, 1) !important;
                    box-shadow: 0 12px 28px rgba(0, 0, 0, 0.15) !important;
                }

                .premium-kpi-card:hover {
                    transform: translateY(-4px);
                    background: rgba(255, 255, 255, 0.035) !important;
                    border-color: var(--kpi-border-hover);
                    box-shadow: 0 20px 35px rgba(0, 0, 0, 0.25), 0 0 15px var(--kpi-glow) !important;
                }

                .kpi-top-row {
                    display: flex;
                    justify-content: space-between;
                    align-items: flex-start;
                }

                .kpi-title {
                    margin: 0;
                    font-size: 0.88rem;
                    font-weight: 500;
                    color: rgba(255, 255, 255, 0.55);
                }

                .kpi-icon-container {
                    width: 38px;
                    height: 38px;
                    border-radius: 0.85rem;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 1.2rem;
                    color: #fff;
                    transition: all 0.3s ease;
                }

                .premium-kpi-card:hover .kpi-icon-container {
                    transform: scale(1.1) rotate(6deg);
                }

                .kpi-main-val {
                    font-size: clamp(1.8rem, 3.2vw, 2.2rem);
                    font-weight: 800;
                    color: #fff;
                    letter-spacing: -0.03em;
                    line-height: 1.1;
                    margin-top: 0.65rem;
                }

                .kpi-bottom-row {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    margin-top: 0.85rem;
                }

                .kpi-chip-text {
                    font-size: 0.74rem;
                    color: rgba(255, 255, 255, 0.48);
                    display: flex;
                    align-items: center;
                    gap: 0.25rem;
                }

                .kpi-trend-badge {
                    font-size: 0.74rem;
                    font-weight: 700;
                    padding: 0.2rem 0.55rem;
                    border-radius: 999px;
                    display: inline-flex;
                    align-items: center;
                    gap: 0.15rem;
                }

                .kpi-trend-up {
                    background: rgba(16, 185, 129, 0.15);
                    color: #34d399;
                    border: 1px solid rgba(16, 185, 129, 0.2);
                }

                .kpi-trend-down {
                    background: rgba(239, 68, 68, 0.15);
                    color: #fca5a5;
                    border: 1px solid rgba(239, 68, 68, 0.2);
                }

                /* Column section cards */
                .premium-list-card {
                    background: rgba(255, 255, 255, 0.02) !important;
                    border: 1px solid rgba(255, 255, 255, 0.06) !important;
                    border-radius: 1.5rem !important;
                    padding: 1.5rem !important;
                    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2) !important;
                }

                /* Activity Stream UI */
                .activity-stream {
                    display: flex;
                    flex-direction: column;
                    gap: 0.75rem;
                }

                .activity-item {
                    display: grid;
                    grid-template-columns: auto 1fr auto;
                    gap: 1rem;
                    align-items: center;
                    padding: 0.8rem 1rem;
                    background: rgba(255, 255, 255, 0.012);
                    border: 1px solid rgba(255, 255, 255, 0.04);
                    border-radius: 1.1rem;
                    transition: all 0.25s ease;
                }

                .activity-item:hover {
                    background: rgba(255, 255, 255, 0.025);
                    border-color: rgba(255, 255, 255, 0.08);
                    transform: translateX(3px);
                }

                .activity-icon-container {
                    width: 38px;
                    height: 38px;
                    border-radius: 0.85rem;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 1.1rem;
                    color: #fff;
                }

                .activity-icon--sale {
                    background: linear-gradient(135deg, rgba(16, 185, 129, 0.2), rgba(16, 185, 129, 0.05));
                    border: 1px solid rgba(16, 185, 129, 0.3);
                    color: #34d399;
                }

                .activity-icon--transfer {
                    background: linear-gradient(135deg, rgba(245, 158, 11, 0.2), rgba(245, 158, 11, 0.05));
                    border: 1px solid rgba(245, 158, 11, 0.3);
                    color: #f59e0b;
                }

                .activity-details strong {
                    font-size: 0.85rem;
                    color: #ffffff;
                    display: block;
                    margin-bottom: 0.15rem;
                }

                .activity-details span {
                    font-size: 0.76rem;
                    color: rgba(255, 255, 255, 0.5);
                }

                .activity-time {
                    font-size: 0.74rem;
                    color: rgba(255, 255, 255, 0.42);
                    font-weight: 500;
                }

                /* Critical Stock warning panel */
                .stock-alert-item {
                    background: rgba(239, 68, 68, 0.04) !important;
                    border: 1px solid rgba(239, 68, 68, 0.18) !important;
                    border-radius: 1.1rem;
                    padding: 0.8rem 1.1rem;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    transition: all 0.25s ease;
                }

                .stock-alert-item:hover {
                    background: rgba(239, 68, 68, 0.06) !important;
                    border-color: rgba(239, 68, 68, 0.3) !important;
                }

                .stock-alert-bar-track {
                    height: 5px;
                    background: rgba(239, 68, 68, 0.12);
                    border-radius: 999px;
                    width: 100px;
                    overflow: hidden;
                    margin-top: 0.3rem;
                }

                .stock-alert-bar-fill {
                    height: 100%;
                    border-radius: 999px;
                    background: #ef4444;
                    box-shadow: 0 0 8px rgba(239, 68, 68, 0.8);
                }

                /* Seller ranking item */
                .rank-item {
                    display: grid;
                    grid-template-columns: auto auto 1fr auto;
                    gap: 0.85rem;
                    align-items: center;
                    padding: 0.75rem 1rem;
                    background: rgba(255, 255, 255, 0.01);
                    border: 1px solid rgba(255, 255, 255, 0.04);
                    border-radius: 1.1rem;
                    transition: all 0.25s ease;
                }

                .rank-item:hover {
                    background: rgba(255, 255, 255, 0.025);
                    border-color: rgba(255, 255, 255, 0.08);
                    transform: translateX(3px);
                }

                .rank-badge {
                    width: 26px;
                    height: 26px;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 0.75rem;
                    font-weight: 800;
                    color: #fff;
                }

                .rank-badge-1 { background: linear-gradient(135deg, #f59e0b, #b45309); box-shadow: 0 0 10px rgba(245, 158, 11, 0.4); }
                .rank-badge-2 { background: linear-gradient(135deg, #94a3b8, #64748b); }
                .rank-badge-3 { background: linear-gradient(135deg, #b45309, #78350f); }
                .rank-badge-other { background: rgba(255, 255, 255, 0.08); color: rgba(255, 255, 255, 0.6); }

                .seller-avatar-mini {
                    width: 32px;
                    height: 32px;
                    border-radius: 50%;
                    background: rgba(129, 140, 248, 0.12);
                    border: 1px solid rgba(129, 140, 248, 0.25);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-weight: 600;
                    color: #a5b4fc;
                    font-size: 0.85rem;
                }

                /* Breakdown bars list */
                .premium-breakdown-row {
                    display: flex;
                    flex-direction: column;
                    gap: 0.35rem;
                    padding: 0.5rem 0;
                }

                .breakdown-meta {
                    display: flex;
                    justify-content: space-between;
                    font-size: 0.82rem;
                    font-weight: 600;
                }

                .breakdown-label {
                    color: rgba(255, 255, 255, 0.85);
                }

                .breakdown-percent {
                    color: #818cf8;
                }

                .breakdown-bar-track {
                    height: 8px;
                    background: rgba(255, 255, 255, 0.06);
                    border-radius: 999px;
                    overflow: hidden;
                    border: 1px solid rgba(255, 255, 255, 0.02);
                }

                .breakdown-bar-fill {
                    height: 100%;
                    border-radius: 999px;
                    transition: width 0.8s cubic-bezier(0.16, 1, 0.3, 1);
                }

                .breakdown-fill--category {
                    background: linear-gradient(90deg, #6366f1, #06b6d4);
                    box-shadow: 0 0 10px rgba(6, 182, 212, 0.4);
                }

                .breakdown-fill--role {
                    background: linear-gradient(90deg, #818cf8, #a78bfa);
                }

                .breakdown-count {
                    font-size: 0.74rem;
                    color: rgba(255, 255, 255, 0.45);
                    margin-top: 0.15rem;
                }

                /* Interactive charts custom styling */
                .premium-chart-card {
                    background: rgba(255, 255, 255, 0.02) !important;
                    border: 1px solid rgba(255, 255, 255, 0.06) !important;
                    border-radius: 1.5rem !important;
                    padding: 1.5rem !important;
                    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2) !important;
                }
            ` }} />

            <div className="exec-dashboard">
                {/* Live Sync Status Bar */}
                <div className="sync-indicator-bar premium-sync-bar" style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', padding: '0.7rem 1.25rem' }}>
                    <div className="sync-status" style={{ display: 'flex', alignItems: 'center', gap: '0.65rem', fontSize: '0.84rem', fontWeight: 600 }}>
                        <div className="sync-dot-container" style={{ position: 'relative', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                            <div className="sync-dot" style={{ width: '7px', height: '7px', backgroundColor: '#10b981', borderRadius: '50%' }}></div>
                            <div className="sync-pulse" style={{ position: 'absolute', width: '17px', height: '17px', border: '2px solid #10b981', borderRadius: '50%', opacity: 0, animation: 'pulseGlow 2s infinite' }}></div>
                            <style dangerouslySetInnerHTML={{ __html: `
                                @keyframes pulseGlow {
                                    0% { transform: scale(0.5); opacity: 0.8; }
                                    100% { transform: scale(1.6); opacity: 0; }
                                }
                            `}} />
                        </div>
                        <span style={{ color: 'rgba(255,255,255,0.85)' }}>Panel Ejecutivo Sincronizado</span>
                        <span className="sync-time" style={{ color: 'rgba(255, 255, 255, 0.45)', fontWeight: 400, marginLeft: '0.2rem' }}>{syncTime}</span>
                    </div>
                    <button 
                        className="btn-sync" 
                        onClick={fetchLiveStats} 
                        style={{ display: 'flex', alignItems: 'center', gap: '0.4rem', background: 'rgba(255, 255, 255, 0.06)', border: '1px solid rgba(255, 255, 255, 0.1)', color: '#ffffff', padding: '0.35rem 0.85rem', borderRadius: '999px', fontSize: '0.78rem', cursor: 'pointer', fontWeight: 700, transition: 'all 0.2s ease' }}
                        onMouseOver={(e) => { e.currentTarget.style.background = 'rgba(255, 255, 255, 0.12)'; e.currentTarget.style.borderColor = 'rgba(255, 255, 255, 0.2)'; }}
                        onMouseOut={(e) => { e.currentTarget.style.background = 'rgba(255, 255, 255, 0.06)'; e.currentTarget.style.borderColor = 'rgba(255, 255, 255, 0.1)'; }}
                    >
                        <i className={`ri-refresh-line ${isSyncing ? 'ri-spin' : ''}`} style={{ fontSize: '0.9rem' }}></i>
                        Refrescar Datos
                    </button>
                </div>

                {/* Welcome Hero Banner with Clock */}
                <div className="welcome-hero">
                    <div className="welcome-text">
                        <h2>{getGreeting()}</h2>
                        <p>
                            El radar ejecutivo de Pil Andina se encuentra operativo. Actualmente, se registran <strong>{kpisState.find(k => k.label === 'Productos activos')?.value || 0}</strong> productos activos en catálogo y las operaciones están estables.
                        </p>
                    </div>
                    <div className="welcome-meta">
                        <span className="welcome-time">
                            {currentTime.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false })}
                        </span>
                        <span className="welcome-date">{getFormattedDate()}</span>
                    </div>
                </div>

                {/* Quick Access Grid (Accesos Directos) */}
                <section className="shortcuts-section">
                    <div className="section-header-bar">
                        <h3>
                            <i className="ri-flashlight-line"></i>
                            Accesos Directos Operativos
                        </h3>
                    </div>
                    <div className="shortcuts-grid">
                        <a href="/admin/agente-reposicion" className="shortcut-card">
                            <div className="shortcut-icon-container" style={{ background: 'linear-gradient(135deg, rgba(139, 92, 246, 0.22), rgba(139, 92, 246, 0.05))', border: '1px solid rgba(139, 92, 246, 0.35)' }}>
                                <i className="ri-robot-2-line" style={{ color: '#a78bfa' }}></i>
                            </div>
                            <div className="shortcut-info">
                                <h4>Agente Inteligente IA</h4>
                                <p>Revisión automática de stock, sugerencias de reabastecimiento y órdenes.</p>
                            </div>
                            <span className="shortcut-action">Optimizar Stock <i className="ri-arrow-right-s-line"></i></span>
                        </a>

                        <a href="/dashboard/traspasos" className="shortcut-card">
                            <div className="shortcut-icon-container" style={{ background: 'linear-gradient(135deg, rgba(245, 158, 11, 0.22), rgba(245, 158, 11, 0.05))', border: '1px solid rgba(245, 158, 11, 0.35)' }}>
                                <i className="ri-shuffle-line" style={{ color: '#fbbf24' }}></i>
                            </div>
                            <div className="shortcut-info">
                                <h4>Flujo de Traspasos</h4>
                                <p>Supervisa, despacha y autoriza traspasos de mercadería entre almacenes.</p>
                            </div>
                            <span className="shortcut-action">Operar Envíos <i className="ri-arrow-right-s-line"></i></span>
                        </a>

                        <a href="/dashboard/ventas" className="shortcut-card">
                            <div className="shortcut-icon-container" style={{ background: 'linear-gradient(135deg, rgba(16, 185, 129, 0.22), rgba(16, 185, 129, 0.05))', border: '1px solid rgba(16, 185, 129, 0.35)' }}>
                                <i className="ri-currency-line" style={{ color: '#34d399' }}></i>
                            </div>
                            <div className="shortcut-info">
                                <h4>Registro de Ventas</h4>
                                <p>Control de cobros, facturación de clientes y registros de caja diaria.</p>
                            </div>
                            <span className="shortcut-action">Ver Ventas <i className="ri-arrow-right-s-line"></i></span>
                        </a>

                        <a href="/dashboard/productos" className="shortcut-card">
                            <div className="shortcut-icon-container" style={{ background: 'linear-gradient(135deg, rgba(6, 182, 212, 0.22), rgba(6, 182, 212, 0.05))', border: '1px solid rgba(6, 182, 212, 0.35)' }}>
                                <i className="ri-shopping-bag-line" style={{ color: '#22d3ee' }}></i>
                            </div>
                            <div className="shortcut-info">
                                <h4>Catálogo General</h4>
                                <p>Administración de códigos SKU, familias de productos y descripciones.</p>
                            </div>
                            <span className="shortcut-action">Gestionar Items <i className="ri-arrow-right-s-line"></i></span>
                        </a>

                        <a href="/dashboard/usuarios" className="shortcut-card">
                            <div className="shortcut-icon-container" style={{ background: 'linear-gradient(135deg, rgba(99, 102, 241, 0.22), rgba(99, 102, 241, 0.05))', border: '1px solid rgba(99, 102, 241, 0.35)' }}>
                                <i className="ri-group-line" style={{ color: '#818cf8' }}></i>
                            </div>
                            <div className="shortcut-info">
                                <h4>Control de Personal</h4>
                                <p>Alta de usuarios, edición de permisos y asignación de roles operativos.</p>
                            </div>
                            <span className="shortcut-action">Ver Usuarios <i className="ri-arrow-right-s-line"></i></span>
                        </a>

                        <a href="/dashboard/backups" className="shortcut-card">
                            <div className="shortcut-icon-container" style={{ background: 'linear-gradient(135deg, rgba(239, 68, 68, 0.22), rgba(239, 68, 68, 0.05))', border: '1px solid rgba(239, 68, 68, 0.35)' }}>
                                <i className="ri-shield-keyhole-line" style={{ color: '#fca5a5' }}></i>
                            </div>
                            <div className="shortcut-info">
                                <h4>Copias de Seguridad</h4>
                                <p>Resguardo seguro de base de datos, descargas y configuración de cron.</p>
                            </div>
                            <span className="shortcut-action">Administrar Backups <i className="ri-arrow-right-s-line"></i></span>
                        </a>
                    </div>
                </section>

                {/* Meta de Ventas & Rendimiento Semanal Split Row */}
                <div className="target-stats-split">
                    {/* Corporate Sales Target Widget */}
                    <article className="card premium-target-card">
                        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', gap: '1.5rem' }}>
                            <div>
                                <h3 style={{ margin: 0, fontSize: '1.12rem', color: '#fff', display: 'flex', alignItems: 'center', gap: '0.45rem', fontWeight: 700 }}>
                                    <i className="ri-flag-2-line" style={{ color: '#06b6d4' }}></i>
                                    Objetivo Comercial Mensual
                                </h3>
                                <p style={{ margin: '0.35rem 0 0', fontSize: '0.82rem', color: 'rgba(255, 255, 255, 0.58)', lineHeight: 1.4 }}>
                                    Avance de ventas consolidadas acumuladas en el mes actual versus la meta institucional de la empresa.
                                </p>
                            </div>
                            <div style={{ background: 'rgba(6, 182, 212, 0.08)', border: '1px solid rgba(6, 182, 212, 0.25)', color: '#22d3ee', padding: '0.4rem 0.85rem', borderRadius: '0.75rem', fontSize: '1.05rem', fontWeight: 800, textAlign: 'right', whiteSpace: 'nowrap', boxShadow: '0 4px 12px rgba(6,182,212,0.15)' }}>
                                Bs {monthlySalesState.toLocaleString('es-BO', {minimumFractionDigits: 2, maximumFractionDigits: 2})}
                            </div>
                        </div>
                        <div style={{ marginTop: '1.25rem' }}>
                            <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: '0.8rem', marginBottom: '0.45rem', fontWeight: 700 }}>
                                <span style={{ color: 'rgba(255,255,255,0.75)' }}>Progreso de Meta</span>
                                <span style={{ color: '#06b6d4', textShadow: '0 0 10px rgba(6,182,212,0.3)' }}>{monthlyTargetProgressState}%</span>
                            </div>
                            <div style={{ height: '9px', background: 'rgba(255, 255, 255, 0.05)', borderRadius: '999px', overflow: 'hidden', border: '1px solid rgba(255, 255, 255, 0.03)' }}>
                                <div style={{ height: '100%', borderRadius: '999px', background: 'linear-gradient(90deg, #6366f1, #06b6d4)', boxShadow: '0 0 12px rgba(6, 182, 212, 0.5)', width: `${monthlyTargetProgressState}%`, transition: 'width 0.8s cubic-bezier(0.16, 1, 0.3, 1)' }}></div>
                            </div>
                            <div style={{ marginTop: '0.65rem', fontSize: '0.78rem', color: 'rgba(255, 255, 255, 0.45)', display: 'flex', justifyContent: 'space-between' }}>
                                <span>Restante para Meta: <strong>Bs {Math.max(0, 150000 - monthlySalesState).toLocaleString('es-BO', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</strong></span>
                                <span>Meta: <strong>Bs 150,000.00</strong></span>
                            </div>
                        </div>
                    </article>

                    {/* Performance Summary Strip */}
                    <article className="card weekly-strip-card">
                        <div>
                            <h3 style={{ margin: 0, fontSize: '1.12rem', color: '#fff', display: 'flex', alignItems: 'center', gap: '0.45rem', fontWeight: 700 }}>
                                <i className="ri-medal-line" style={{ color: '#fbbf24' }}></i>
                                Rendimiento Comercial Semanal
                            </h3>
                            <p style={{ margin: '0.35rem 0 0', fontSize: '0.82rem', color: 'rgba(255, 255, 255, 0.58)', lineHeight: 1.4 }}>
                                Métricas consolidadas del pulso de ventas correspondientes a los últimos 7 días.
                            </p>
                        </div>
                        <div className="weekly-grid" style={{ marginTop: '1.1rem' }}>
                            <div className="weekly-item">
                                <span>Monto Semanal</span>
                                <strong>{summaryCardsState.find(c => c.label === 'Total semanal')?.value || 'Bs 0.00'}</strong>
                                <p>{summaryCardsState.find(c => c.label === 'Total semanal')?.detail || ''}</p>
                            </div>
                            <div className="weekly-item">
                                <span>Ticket Promedio</span>
                                <strong>{summaryCardsState.find(c => c.label === 'Ticket promedio')?.value || 'Bs 0.00'}</strong>
                                <p>{summaryCardsState.find(c => c.label === 'Ticket promedio')?.detail || ''}</p>
                            </div>
                        </div>
                    </article>
                </div>

                {/* KPI Cards Grid */}
                <div className="exec-stats-grid">
                    {kpisState.map((item) => {
                        // Custom styles & icons for specific KPIs
                        let kpiColor = 'rgba(99, 102, 241, 0.2)';
                        let kpiColorShadow = 'rgba(99, 102, 241, 0.3)';
                        let kpiColorHover = '#6366f1';
                        let glowColor = 'rgba(99, 102, 241, 0.15)';
                        
                        if (item.label === 'Ventas del dia') {
                            kpiColor = 'rgba(16, 185, 129, 0.15)';
                            kpiColorShadow = 'rgba(16, 185, 129, 0.25)';
                            kpiColorHover = '#10b981';
                            glowColor = 'rgba(16, 185, 129, 0.1)';
                        } else if (item.label === 'Clientes registrados') {
                            kpiColor = 'rgba(6, 182, 212, 0.15)';
                            kpiColorShadow = 'rgba(6, 182, 212, 0.25)';
                            kpiColorHover = '#06b6d4';
                            glowColor = 'rgba(6, 182, 212, 0.1)';
                        } else if (item.label === 'Traspasos abiertos') {
                            kpiColor = 'rgba(245, 158, 11, 0.15)';
                            kpiColorShadow = 'rgba(245, 158, 11, 0.25)';
                            kpiColorHover = '#f59e0b';
                            glowColor = 'rgba(245, 158, 11, 0.1)';
                        }

                        const hasDelta = item.label === 'Ventas del dia' && salesDeltaVal;

                        return (
                            <article 
                                key={item.label} 
                                className="card premium-kpi-card"
                                style={{ 
                                    '--kpi-border-hover': kpiColorHover, 
                                    '--kpi-glow': glowColor, 
                                    '--kpi-color-glow': kpiColorHover, 
                                    '--kpi-color-shadow': kpiColorShadow 
                                }}
                            >
                                <div className="kpi-top-row">
                                    <h3 className="kpi-title">{item.label}</h3>
                                    <div className="kpi-icon-container" style={{ background: kpiColor, border: `1px solid ${kpiColorShadow}` }}>
                                        <i className={item.icon} style={{ color: kpiColorHover }}></i>
                                    </div>
                                </div>
                                
                                <div className="kpi-main-val">{item.value}</div>
                                
                                <div className="kpi-bottom-row">
                                    <span className="kpi-chip-text">
                                        <i className="ri-time-line"></i>
                                        {item.chip}
                                    </span>
                                    {hasDelta && (
                                        <span className={`kpi-trend-badge ${salesDeltaVal.startsWith('-') ? 'kpi-trend-down' : 'kpi-trend-up'}`}>
                                            <i className={salesDeltaVal.startsWith('-') ? 'ri-arrow-down-line' : 'ri-arrow-up-line'}></i>
                                            {salesDeltaVal}
                                        </span>
                                    )}
                                </div>
                            </article>
                        );
                    })}
                </div>

                {/* Core Columns Section (Actividad, Alertas, Vendedores, Distribución) */}
                <div className="admin-insight-layout">
                    {/* Left Column: Recent Activity & Stock Alerts */}
                    <div style={{ display: 'flex', flexDirection: 'column', gap: '1.5rem' }}>
                        {/* Actividad Reciente */}
                        <article className="card premium-list-card" style={{ flex: 1 }}>
                            <div className="exec-chart-head" style={{ marginBottom: '1.25rem' }}>
                                <div>
                                    <h4 style={{ display: 'flex', alignItems: 'center', gap: '0.45rem', margin: 0 }}>
                                        <i className="ri-pulse-line" style={{ color: '#10b981' }}></i>
                                        Operaciones y Actividad Reciente
                                    </h4>
                                    <p style={{ margin: '0.2rem 0 0' }}>Flujo de ventas y transferencias de almacén en tiempo real.</p>
                                </div>
                                <span className="chip premium-chip" style={{ background: 'rgba(16, 185, 129, 0.08)', borderColor: 'rgba(16, 185, 129, 0.2)', color: '#34d399' }}>Monitoreo Activo</span>
                            </div>
                            <div className="activity-stream">
                                {recentActivityState.length > 0 ? (
                                    recentActivityState.map((item, index) => (
                                        <div key={`${item.type}-${index}`} className="activity-item">
                                            <div className={`activity-icon-container activity-icon--${item.type}`}>
                                                <i className={item.icon} />
                                            </div>
                                            <div className="activity-details">
                                                <strong>{item.title}</strong>
                                                <span>{item.meta}</span>
                                            </div>
                                            <span className="activity-time">{item.time}</span>
                                        </div>
                                    ))
                                ) : (
                                    <div style={{ padding: '2rem', color: 'rgba(255,255,255,0.4)', textAlign: 'center', fontSize: '0.9rem' }}>
                                        No hay operaciones registradas hoy.
                                    </div>
                                )}
                            </div>
                        </article>

                        {/* Critical Stock Alerts */}
                        <article className="card premium-list-card">
                            <div className="exec-chart-head" style={{ marginBottom: '1.2rem' }}>
                                <div>
                                    <h4 style={{ color: '#fca5a5', display: 'flex', alignItems: 'center', gap: '0.45rem', margin: 0 }}>
                                        <i className="ri-error-warning-line" style={{ fontSize: '1.2rem' }}></i>
                                        Alertas de Stock de Seguridad
                                    </h4>
                                    <p style={{ margin: '0.2rem 0 0' }}>Lotes cuya cantidad se encuentra por debajo del umbral mínimo de reserva.</p>
                                </div>
                            </div>
                            <div style={{ display: 'flex', flexDirection: 'column', gap: '0.75rem' }}>
                                {criticalStocksState.length > 0 ? (
                                    criticalStocksState.map((item, index) => {
                                        const percentageLeft = Math.max(0, Math.min(100, Math.round((item.quantity / item.threshold) * 100)));
                                        return (
                                            <div key={index} className="stock-alert-item">
                                                <div style={{ minWidth: 0, flex: 1, paddingRight: '1rem' }}>
                                                    <strong style={{ fontSize: '0.85rem', display: 'block', color: '#fff', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
                                                        {item.product}
                                                    </strong>
                                                    <span style={{ fontSize: '0.74rem', color: 'rgba(255,255,255,0.45)', display: 'block', marginTop: '0.15rem' }}>
                                                        SKU: {item.sku} · {item.warehouse}
                                                    </span>
                                                    <div style={{ display: 'flex', alignItems: 'center', gap: '0.5rem', marginTop: '0.35rem' }}>
                                                        <div className="stock-alert-bar-track" style={{ flex: 1, margin: 0 }}>
                                                            <div className="stock-alert-bar-fill" style={{ width: `${percentageLeft}%` }}></div>
                                                        </div>
                                                        <span style={{ fontSize: '0.7rem', color: '#fca5a5', fontWeight: 700 }}>{percentageLeft}% rest.</span>
                                                    </div>
                                                </div>
                                                <div style={{ textAlign: 'right', flexShrink: 0 }}>
                                                    <strong style={{ color: '#ef4444', fontSize: '1rem', display: 'block' }}>{item.quantity} uds</strong>
                                                    <span style={{ fontSize: '0.74rem', color: 'rgba(255,255,255,0.4)' }}>Mín: {item.threshold}</span>
                                                </div>
                                            </div>
                                        );
                                    })
                                ) : (
                                    <div style={{ padding: '1.5rem', color: 'rgba(255,255,255,0.35)', textAlign: 'center', fontSize: '0.85rem', background: 'rgba(255,255,255,0.01)', borderRadius: '1rem', border: '1px dashed rgba(255,255,255,0.06)' }}>
                                        <i className="ri-checkbox-circle-line" style={{ fontSize: '1.3rem', color: '#34d399', display: 'block', marginBottom: '0.35rem' }}></i>
                                        Todo en orden. No hay lotes con desabastecimiento crítico.
                                    </div>
                                )}
                            </div>
                            {criticalStocksState.length > 0 && (
                                <div style={{ marginTop: '1.1rem', display: 'flex', justifyContent: 'flex-end' }}>
                                    <a 
                                        href="/admin/agente-reposicion" 
                                        style={{ display: 'inline-flex', alignItems: 'center', gap: '0.35rem', background: 'linear-gradient(135deg, #6366f1, #4f46e5)', color: '#fff', border: 'none', borderRadius: '0.9rem', padding: '0.5rem 1.1rem', fontSize: '0.8rem', fontWeight: 700, cursor: 'pointer', textDecoration: 'none', transition: 'all 0.2s ease', boxShadow: '0 8px 16px rgba(99,102,241,0.25)' }}
                                        onMouseOver={(e) => e.currentTarget.style.transform = 'translateY(-2px)'}
                                        onMouseOut={(e) => e.currentTarget.style.transform = 'none'}
                                    >
                                        <i className="ri-robot-line"></i>
                                        Reponer Stock con Agente IA
                                    </a>
                                </div>
                            )}
                        </article>
                    </div>

                    {/* Right Column: Seller Rankings & Catalog/Role breakdown */}
                    <div style={{ display: 'flex', flexDirection: 'column', gap: '1.5rem' }}>
                        {/* Rendimiento de Vendedores */}
                        <article className="card premium-list-card">
                            <div className="exec-chart-head" style={{ marginBottom: '1.25rem' }}>
                                <div>
                                    <h4 style={{ display: 'flex', alignItems: 'center', gap: '0.45rem', margin: 0 }}>
                                        <i className="ri-line-chart-line" style={{ color: '#fbbf24' }}></i>
                                        Rendimiento de Vendedores
                                    </h4>
                                    <p style={{ margin: '0.2rem 0 0' }}>Ingresos generados por asesor comercial en el mes en curso.</p>
                                </div>
                            </div>
                            <div style={{ display: 'flex', flexDirection: 'column', gap: '0.7rem' }}>
                                {topSellersState.length > 0 ? (
                                    topSellersState.map((item, index) => {
                                        const initials = item.name.split(' ').map(n => n[0]).join('').slice(0, 2).toUpperCase();
                                        return (
                                            <div key={index} className="rank-item">
                                                <div className={`rank-badge rank-badge-${index < 3 ? index + 1 : 'other'}`}>
                                                    {index < 3 ? ['🥇', '🥈', '🥉'][index] : index + 1}
                                                </div>
                                                <div className="seller-avatar-mini">{initials}</div>
                                                <div style={{ minWidth: 0 }}>
                                                    <strong style={{ fontSize: '0.85rem', color: '#fff', whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis', display: 'block' }}>
                                                        {item.name}
                                                    </strong>
                                                    <span style={{ fontSize: '0.74rem', color: 'rgba(255,255,255,0.45)' }}>
                                                        Asesor Comercial
                                                    </span>
                                                </div>
                                                <div style={{ textAlign: 'right', fontWeight: 800, color: '#34d399', fontSize: '0.88rem' }}>
                                                    Bs {item.total.toLocaleString('es-BO', {minimumFractionDigits: 2, maximumFractionDigits: 2})}
                                                </div>
                                            </div>
                                        );
                                    })
                                ) : (
                                    <div style={{ padding: '2rem', color: 'rgba(255,255,255,0.4)', textAlign: 'center', fontSize: '0.85rem' }}>
                                        No hay registros de ventas en este periodo.
                                    </div>
                                )}
                            </div>
                        </article>

                        {/* Breakdown Bars (Categorías & Roles) */}
                        <article className="card premium-list-card" style={{ flex: 1, display: 'flex', flexDirection: 'column', gap: '1.4rem' }}>
                            {/* Category breakdown */}
                            <div>
                                <div className="exec-chart-head" style={{ marginBottom: '0.9rem' }}>
                                    <div>
                                        <h4 style={{ margin: 0, fontSize: '0.95rem' }}>Participación por Categoría</h4>
                                        <p style={{ margin: '0.15rem 0 0', fontSize: '0.76rem' }}>Distribución de productos en catálogo activo.</p>
                                    </div>
                                </div>
                                <div style={{ display: 'flex', flexDirection: 'column', gap: '0.8rem' }}>
                                    {categoryBreakdownState.map((item) => (
                                        <div key={item.label} className="premium-breakdown-row">
                                            <div className="breakdown-meta">
                                                <span className="breakdown-label">{item.label}</span>
                                                <span className="breakdown-percent">{item.share}%</span>
                                            </div>
                                            <div className="breakdown-bar-track">
                                                <div className="breakdown-bar-fill breakdown-fill--category" style={{ width: `${Math.max(item.share, 4)}%` }}></div>
                                            </div>
                                            <div className="breakdown-count">{item.value} productos registrados</div>
                                        </div>
                                    ))}
                                </div>
                            </div>

                            <hr style={{ border: 0, height: '1px', background: 'rgba(255, 255, 255, 0.06)', margin: 0 }} />

                            {/* Role breakdown */}
                            <div>
                                <div className="exec-chart-head" style={{ marginBottom: '0.9rem' }}>
                                    <div>
                                        <h4 style={{ margin: 0, fontSize: '0.95rem' }}>Estructura del Personal</h4>
                                        <p style={{ margin: '0.15rem 0 0', fontSize: '0.76rem' }}>Distribución de cuentas por rol en el sistema.</p>
                                    </div>
                                </div>
                                <div style={{ display: 'flex', flexDirection: 'column', gap: '0.8rem' }}>
                                    {roleBreakdownState.map((item) => (
                                        <div key={item.label} className="premium-breakdown-row">
                                            <div className="breakdown-meta">
                                                <span className="breakdown-label">{item.label}</span>
                                                <span className="breakdown-percent">{item.share}%</span>
                                            </div>
                                            <div className="breakdown-bar-track">
                                                <div className="breakdown-bar-fill breakdown-fill--role" style={{ width: `${Math.max(item.share, 4)}%` }}></div>
                                            </div>
                                            <div className="breakdown-count">{item.value} usuarios asignados</div>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        </article>
                    </div>
                </div>

                {/* Analytical Charts Grid (2x2) */}
                <div className="exec-chart-grid">
                    <ChartCard title="Historial de Ventas Semanales" description="Facturación diaria del pulso comercial en los últimos 7 días." chip="Tendencia Diaria" configFactory={salesChartConfig} deps={[salesSeriesState]} />
                    <ChartCard title="Inventario por Categoría" description="Principales familias de productos que sostienen el stock actual." chip="Distribución de Stock" configFactory={categoryChartConfig} deps={[categoryMixState]} />
                    <ChartCard title="Estado de Operaciones de Traspaso" description="Clasificación de transferencias internas según estado logístico." chip="Resumen Operativo" configFactory={transferChartConfig} deps={[transferStatusesState]} />
                    <ChartCard title="Usuarios Registrados por Rol" description="Cuentas activas en la intranet según perfil corporativo." chip="Estructura de Cuentas" configFactory={roleChartConfig} deps={[roleMixState]} />
                </div>
            </div>
        </DashboardShell>
    );
}
