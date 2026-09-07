import React, { useMemo, useState } from 'react';
import { Area, AreaChart, CartesianGrid, ResponsiveContainer, Tooltip, XAxis, YAxis } from 'recharts';
import { Modal } from './common';

function insightNumber(value, suffix = '') {
    const number = Number(value || 0);
    return `${number.toLocaleString('es-BO', { maximumFractionDigits: 0 })}${suffix}`;
}

function insightMoney(value) {
    return `Bs ${Number(value || 0).toLocaleString('es-BO', { maximumFractionDigits: 0 })}`;
}

function insightDeltaLabel(delta, lowerIsBetter = false) {
    const direction = delta?.direction || 'flat';
    const percent = Number(delta?.percent || 0);
    const sign = percent > 0 ? '+' : '';
    const good = lowerIsBetter ? direction !== 'up' : direction !== 'down';

    return {
        text: `${sign}${percent.toFixed(1)}%`,
        className: `${direction} ${good ? 'good' : 'bad'}`,
        icon: direction === 'up' ? 'ri-arrow-up-line' : direction === 'down' ? 'ri-arrow-down-line' : 'ri-subtract-line',
    };
}

function InsightSparkline({ series, metric, color = '#0b4fc1' }) {
    const values = series.map((item) => Number(item[metric] || 0));
    const max = Math.max(...values, 1);
    const width = 420;
    const height = 120;
    const step = values.length > 1 ? width / (values.length - 1) : width;
    const points = values.map((value, index) => {
        const x = index * step;
        const y = height - ((value / max) * (height - 18)) - 9;
        return `${x.toFixed(2)},${y.toFixed(2)}`;
    }).join(' ');

    return (
        <svg className="agent-insight-sparkline" viewBox={`0 0 ${width} ${height}`} preserveAspectRatio="none" aria-hidden="true">
            <polyline points={points} fill="none" stroke={color} strokeWidth="6" strokeLinecap="round" strokeLinejoin="round" />
            <polygon points={`0,${height} ${points} ${width},${height}`} fill={color} opacity="0.08" />
        </svg>
    );
}

function WapeAxisChart({ series }) {
    const weekdays = ['Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab', 'Dom'];
    const data = weekdays.map((day, index) => {
        const source = series[index] || {};

        return {
            day,
            wape: Number(source.wape ?? 0),
            actual: Number(source.actual ?? 0),
            predicted: Number(source.predicted ?? 0),
        };
    });
    const maxValue = Math.max(...data.map((item) => item.wape), 10);

    return (
        <div className="agent-wape-chart-wrap">
            <div className="agent-wape-recharts">
                <ResponsiveContainer width="100%" height="100%">
                    <AreaChart data={data} margin={{ top: 12, right: 12, left: -18, bottom: 0 }}>
                        <defs>
                            <linearGradient id="agentWapeGradient" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="5%" stopColor="#e11d48" stopOpacity={0.34} />
                                <stop offset="95%" stopColor="#e11d48" stopOpacity={0.02} />
                            </linearGradient>
                        </defs>
                        <CartesianGrid strokeDasharray="4 6" stroke="#e2e8f0" vertical={false} />
                        <XAxis dataKey="day" axisLine={false} tickLine={false} tick={{ fill: '#475569', fontSize: 11, fontWeight: 800 }} />
                        <YAxis
                            axisLine={false}
                            tickLine={false}
                            tick={{ fill: '#64748b', fontSize: 10, fontWeight: 700 }}
                            domain={[0, Math.ceil(maxValue + 5)]}
                            tickFormatter={(value) => `${value}%`}
                        />
                        <Tooltip
                            formatter={(value, name, props) => {
                                if (name === 'wape') return [`${Number(value || 0).toFixed(2)}%`, 'Error WAPE'];
                                return [value, props?.name || name];
                            }}
                            labelFormatter={(label) => `Dia: ${label}`}
                            contentStyle={{ borderRadius: 12, borderColor: '#e2e8f0', fontSize: 12 }}
                        />
                        <Area type="natural" dataKey="wape" stroke="#e11d48" strokeWidth={3} fill="url(#agentWapeGradient)" dot={{ r: 4, fill: '#fff', stroke: '#e11d48', strokeWidth: 2 }} activeDot={{ r: 6 }} />
                    </AreaChart>
                </ResponsiveContainer>
            </div>
            <div className="agent-axis-help">
                <span><strong>X</strong> muestra los dias de la semana.</span>
                <span><strong>Y</strong> muestra el error WAPE en porcentaje; mientras mas bajo, mejor fue la prediccion.</span>
            </div>
        </div>
    );
}

export default function AgentInsightsDashboard({ data }) {
    const [insights, setInsights] = useState(data.insights);
    const [granularity, setGranularity] = useState(data.insights?.granularity || 'week');
    const [productId, setProductId] = useState('');
    const [periodModalOpen, setPeriodModalOpen] = useState(false);
    const [loading, setLoading] = useState(false);

    const current = insights?.current || {};
    const previous = insights?.previous || {};
    const deltas = insights?.deltas || {};
    const series = insights?.series || [];
    const recentSeries = series.slice(-12);
    const maxSales = Math.max(...recentSeries.map((item) => Number(item.sales_qty || 0)), 1);
    const maxTransfers = Math.max(...recentSeries.map((item) => Number(item.transfer_requested || 0)), 1);

    const loadInsights = async (nextGranularity = granularity, nextProductId = productId) => {
        if (!data.routes.insights) return;
        setLoading(true);

        try {
            const url = new URL(data.routes.insights, window.location.origin);
            url.searchParams.set('granularity', nextGranularity);
            if (nextProductId) url.searchParams.set('product_id', nextProductId);

            const response = await fetch(url.toString(), {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            });
            const payload = await response.json();
            if (response.ok) {
                setInsights(payload.data);
            }
        } finally {
            setLoading(false);
        }
    };

    const reportUrl = useMemo(() => {
        const url = new URL(data.routes.visual_report, window.location.origin);
        url.searchParams.set('granularity', granularity);
        if (productId) url.searchParams.set('product_id', productId);
        return `${url.pathname}${url.search}`;
    }, [data.routes.visual_report, granularity, productId]);

    const cards = [
        { label: 'Ventas', value: insightNumber(current.sales_qty, ' uds'), delta: insightDeltaLabel(deltas.sales_qty), icon: 'ri-shopping-bag-3-line', tone: 'blue' },
        { label: 'Facturacion', value: insightMoney(current.sales_amount), delta: insightDeltaLabel(deltas.sales_amount), icon: 'ri-money-dollar-circle-line', tone: 'green' },
        { label: 'Traspasos', value: insightNumber(current.transfer_requested, ' uds'), delta: insightDeltaLabel(deltas.transfer_requested), icon: 'ri-truck-line', tone: 'cyan' },
        { label: 'WAPE', value: current.avg_wape_percent === null ? 'N/D' : `${Number(current.avg_wape_percent).toFixed(1)}%`, delta: insightDeltaLabel(deltas.avg_wape_percent, true), icon: 'ri-percent-line', tone: 'rose' },
    ];
    const granularityLabel = granularity === 'month' ? 'Comparar por mes' : 'Comparar por semana';

    return (
        <section className={`agent-insights-dashboard ${loading ? 'is-loading' : ''}`}>
            <div className="agent-insights-head">
                <div>
                    <span>Dashboard comparativo</span>
                    <h2>Rendimiento visual del agente</h2>
                    <p>{insights?.product_name || 'General'}: {previous?.label || 'periodo anterior'} vs {current?.label || 'periodo actual'}.</p>
                </div>
                <div className="agent-insights-controls">
                    <button type="button" className="fit-outline-button compact agent-period-button" onClick={() => setPeriodModalOpen(true)}>
                        <i className="ri-calendar-event-line" />
                        <span>{granularityLabel}</span>
                    </button>
                    <select value={productId} onChange={(event) => {
                        setProductId(event.target.value);
                        loadInsights(granularity, event.target.value);
                    }}>
                        <option value="">Todos los productos</option>
                        {(insights?.products || []).map((product) => <option value={product.id} key={product.id}>{product.name}</option>)}
                    </select>
                    <a className="fit-outline-button compact agent-report-button" href={reportUrl}>
                        <i className="ri-file-chart-line" />
                        <span>Reporte visual</span>
                    </a>
                </div>
            </div>

            <div className="agent-insight-card-grid">
                {cards.map((card) => (
                    <article className={`agent-insight-card ${card.tone}`} key={card.label}>
                        <div>
                            <span>{card.label}</span>
                            <strong>{card.value}</strong>
                        </div>
                        <i className={card.icon} />
                        <em className={card.delta.className}><i className={card.delta.icon} /> {card.delta.text}</em>
                    </article>
                ))}
            </div>

            <div className="agent-insight-visual-grid">
                <article className="agent-insight-panel wide">
                    <div className="agent-insight-panel-head">
                        <div><strong>Ventas vs traspasos</strong><span>Ultimos periodos visibles</span></div>
                        <span className="metric-chip">Actual: {current?.label || 'N/D'}</span>
                    </div>
                    <div className="agent-combo-chart">
                        {recentSeries.map((item) => (
                            <div className="agent-combo-column" key={item.key}>
                                <div className="agent-combo-bars">
                                    <span className="sales" style={{ height: `${Math.max(5, (Number(item.sales_qty || 0) / maxSales) * 100)}%` }} />
                                    <span className="transfers" style={{ height: `${Math.max(5, (Number(item.transfer_requested || 0) / maxTransfers) * 100)}%` }} />
                                </div>
                                <small>{item.label}</small>
                            </div>
                        ))}
                    </div>
                    <div className="agent-chart-legend" aria-label="Leyenda de colores">
                        <span><i className="legend-dot sales" /> Azul: ventas reales de cada semana</span>
                        <span><i className="legend-dot transfers" /> Amarillo: traspasos solicitados de cada semana</span>
                    </div>
                </article>

                <article className="agent-insight-panel">
                    <div className="agent-insight-panel-head">
                        <div><strong>Error WAPE</strong><span>Grafico X/Y</span></div>
                    </div>
                    <WapeAxisChart series={insights?.daily_wape || []} />
                </article>

                <article className="agent-insight-panel">
                    <div className="agent-insight-panel-head">
                        <div><strong>Productos fuertes</strong><span>Mayor venta del periodo</span></div>
                    </div>
                    <div className="agent-top-products">
                        {(insights?.top_products || []).map((product, index) => (
                            <div className="agent-top-product" key={product.id}>
                                <b>{index + 1}</b>
                                <span>{product.name}</span>
                                <strong>{insightNumber(product.qty, ' uds')}</strong>
                            </div>
                        ))}
                    </div>
                </article>
            </div>

            <Modal open={periodModalOpen} title="Periodo de comparacion" onClose={() => setPeriodModalOpen(false)} contentClassName="agent-period-modal">
                <div className="modal-body">
                    <div className="agent-period-calendar">
                        <button type="button" className={`agent-period-card ${granularity === 'week' ? 'active' : ''}`} onClick={() => {
                            setGranularity('week');
                            loadInsights('week', productId);
                            setPeriodModalOpen(false);
                        }}>
                            <span><i className="ri-calendar-week-line" /></span>
                            <strong>Comparar por semana</strong>
                            <small>Usa semanas cerradas del anio para ver cambios rapidos entre S41, S42 y siguientes.</small>
                        </button>
                        <button type="button" className={`agent-period-card ${granularity === 'month' ? 'active' : ''}`} onClick={() => {
                            setGranularity('month');
                            loadInsights('month', productId);
                            setPeriodModalOpen(false);
                        }}>
                            <span><i className="ri-calendar-2-line" /></span>
                            <strong>Comparar por mes</strong>
                            <small>Agrupa ventas, traspasos y WAPE por mes para una lectura mas general.</small>
                        </button>
                    </div>
                    <div className="evaluator-calendar agent-period-preview">
                        <div className="evaluator-calendar-head">
                            <button type="button" className="fit-action-button" disabled title="Anterior">
                                <i className="ri-arrow-left-s-line" />
                            </button>
                            <div>
                                <strong>Vista 2025</strong>
                                <span>{granularity === 'month' ? 'Seleccion mensual activa' : 'Seleccion semanal activa'}</span>
                            </div>
                            <button type="button" className="fit-action-button" disabled title="Siguiente">
                                <i className="ri-arrow-right-s-line" />
                            </button>
                        </div>
                        <div className="agent-period-grid">
                            {['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'].map((month) => (
                                <span className={granularity === 'month' ? 'active' : ''} key={month}>{month}</span>
                            ))}
                        </div>
                    </div>
                </div>
            </Modal>
        </section>
    );
}
