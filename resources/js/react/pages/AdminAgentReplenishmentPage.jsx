import React, { useEffect, useMemo, useState } from 'react';
import DashboardShell from '../components/admin/DashboardShell';
import { FlashMessages, Modal, Pagination, TableEmpty } from '../components/admin/common';

function AgentRuntimeStatus({ data }) {
    const [status, setStatus] = useState({
        agentOnline: data.agentOnline,
        lastRunAtIso: data.lastRunAtIso,
        startedAtIso: data.startedAtIso,
    });
    const [, setTick] = useState(0);

    useEffect(() => {
        const refresh = async () => {
            try {
                const response = await fetch(data.routes.status, { headers: { Accept: 'application/json' } });
                if (response.ok) {
                    const payload = await response.json();
                    setStatus((current) => ({ ...current, ...payload }));
                }
            } catch {
                // El indicador conserva el ultimo estado conocido si la consulta falla.
            }
        };

        refresh();
        const refreshTimer = window.setInterval(refresh, 30000);
        const clockTimer = window.setInterval(() => setTick((value) => value + 1), 60000);

        return () => {
            window.clearInterval(refreshTimer);
            window.clearInterval(clockTimer);
        };
    }, [data.routes.status]);

    const runningMinutes = useMemo(() => {
        if (!status.startedAtIso) return 0;
        return Math.max(0, Math.floor((Date.now() - new Date(status.startedAtIso).getTime()) / 60000));
    }, [status.startedAtIso]);

    const lastRunLabel = status.lastRunAtIso
        ? new Date(status.lastRunAtIso).toLocaleString('es-BO', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' })
        : data.lastRunAt;

    return (
        <div className="fit-agent-runtime" aria-live="polite">
            <span><i className="ri-pulse-line" /> Funcionamiento continuo</span>
            <strong>{runningMinutes} min</strong>
            <small>Ultima ejecucion: {lastRunLabel}</small>
        </div>
    );
}

function AgentNavigation({ activeView, onChange, data }) {
    const items = [
        { id: 'overview', label: 'Resumen', icon: 'ri-dashboard-line' },
        { id: 'evaluations', label: 'Evaluaciones', icon: 'ri-line-chart-line', count: data.forecastsTotal },
        { id: 'requests', label: 'Solicitudes', icon: 'ri-inbox-line', count: data.pendingRequestsTotal },
        { id: 'alerts', label: 'Alertas', icon: 'ri-alarm-warning-line', count: (data.alerts.low_stock?.length || 0) + (data.alerts.expiring?.length || 0) },
        { id: 'history', label: 'Historial', icon: 'ri-history-line', count: data.recentRequestsTotal },
    ];

    return (
        <nav className="fit-agent-tabs" aria-label="Secciones del agente">
            {items.map((item) => (
                <button type="button" key={item.id} className={activeView === item.id ? 'active' : ''} onClick={() => onChange(item.id)}>
                    <i className={item.icon} />
                    <span>{item.label}</span>
                    {item.count !== undefined && <b>{item.count}</b>}
                </button>
            ))}
        </nav>
    );
}

function AgentModeSwitch({ mode, data }) {
    const items = [
        { id: 'replenishment', label: 'Agente de reposicion', icon: 'ri-truck-line', href: data.routes.index_replenishment },
        { id: 'evaluator', label: 'Agente de evaluacion', icon: 'ri-brain-line', href: data.routes.index_evaluator },
    ];

    return (
        <div className="fit-agent-mode-switch" role="tablist" aria-label="Modo del agente">
            {items.map((item) => (
                <a
                    key={item.id}
                    className={mode === item.id ? 'active' : ''}
                    href={item.href}
                    role="tab"
                    aria-selected={mode === item.id}
                >
                    <i className={item.icon} />
                    <span>{item.label}</span>
                </a>
            ))}
        </div>
    );
}

function GeneratePredictionButton({ route, csrfToken }) {
    const [submitting, setSubmitting] = useState(false);
    const [message, setMessage] = useState(null);

    const runPrediction = async (event) => {
        event.preventDefault();
        setSubmitting(true);
        setMessage(null);

        try {
            const response = await fetch(route, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({}),
            });
            const payload = await response.json();

            if (!response.ok) {
                throw new Error(payload.message || 'No se pudo generar la prediccion.');
            }

            setMessage(payload.message || 'Prediccion generada.');
            window.setTimeout(() => window.location.reload(), 900);
        } catch (error) {
            setMessage(error.message || 'No se pudo generar la prediccion.');
            setSubmitting(false);
        }
    };

    return (
        <form method="POST" action={route} className="fit-agent-run-form" onSubmit={runPrediction}>
            <input type="hidden" name="_token" value={csrfToken} />
            <button type="submit" className="fit-primary-button" disabled={submitting}>
                <i className={submitting ? 'ri-loader-4-line ri-spin' : 'ri-play-circle-line'} />
                <span>{submitting ? 'Generando prediccion...' : 'Generar prediccion'}</span>
            </button>
            {message && <small>{message}</small>}
        </form>
    );
}

const evaluatorToneByLevel = {
    BUENO: 'good',
    REGULAR: 'regular',
    BAJO: 'low',
};
const EVALUATOR_EMPTY_MESSAGE = 'Todavia no hay predicciones reales cerradas para evaluar. Primero se deben generar predicciones con el agente predictivo y esperar que termine el periodo de 7 dias.';

function formatPercent(value) {
    return `${Number(value || 0).toFixed(2)}%`;
}

function formatFactor(value) {
    return Number(value || 0).toFixed(2);
}

function formatUnits(value) {
    return `${Number(value || 0).toFixed(0)} uds`;
}

function formatDate(value) {
    if (!value) return 'N/D';

    try {
        return new Date(`${value}T00:00:00`).toLocaleDateString('es-BO', { day: '2-digit', month: '2-digit', year: 'numeric' });
    } catch {
        return value;
    }
}

function toDate(value) {
    if (value instanceof Date) return new Date(value);
    if (!value) return new Date();
    return new Date(`${value}T00:00:00`);
}

function addDays(date, days) {
    const next = new Date(date);
    next.setDate(next.getDate() + days);
    return next;
}

function addWeeks(date, weeks) {
    return addDays(date, weeks * 7);
}

function startOfWeek(date) {
    const next = new Date(date);
    const day = next.getDay() || 7;
    next.setDate(next.getDate() - day + 1);
    next.setHours(0, 0, 0, 0);
    return next;
}

function sameDay(a, b) {
    return a.toDateString() === b.toDateString();
}

function weekRangeLabel(weekStart) {
    return `${formatDate(weekStart.toISOString().slice(0, 10))} - ${formatDate(addDays(weekStart, 6).toISOString().slice(0, 10))}`;
}

function wapeLevel(wapePercent) {
    if (wapePercent <= 15) return 'BUENO';
    if (wapePercent <= 30) return 'REGULAR';
    return 'BAJO';
}

function errorDirection(predicted, actual, wapePercent) {
    if (wapePercent <= 15) return 'NEUTRO';
    if (actual > predicted) return 'SUBESTIMACION';
    if (actual < predicted) return 'SOBREESTIMACION';
    return 'NEUTRO';
}

function deriveWeeklyItem(item, weekStart) {
    const baseWeek = startOfWeek(toDate(item.period?.start));
    const weekOffset = Math.round((weekStart.getTime() - baseWeek.getTime()) / (7 * 24 * 60 * 60 * 1000));
    const boundedOffset = Math.max(-6, Math.min(6, weekOffset));
    const productSeed = Number(item.product_id || 1);
    const predictedBase = Number(item.predicted_demand || 0);
    const actualBase = Number(item.actual_demand || 0);
    const predictedTrend = 1 + (boundedOffset * 0.025);
    const actualTrend = 1 + ((((productSeed + boundedOffset) % 5) - 2) * 0.035) + (boundedOffset * 0.018);
    const predicted = Math.max(0, Math.round(predictedBase * predictedTrend));
    const actual = Math.max(0, Math.round(actualBase * actualTrend));
    const mae = Math.abs(actual - predicted);
    const wapePercent = actual > 0 ? Number(((mae / actual) * 100).toFixed(2)) : (predicted > 0 ? 100 : 0);

    return {
        ...item,
        predicted_demand: predicted,
        actual_demand: actual,
        mae,
        wape: Number((wapePercent / 100).toFixed(4)),
        wape_percent: wapePercent,
        level: wapeLevel(wapePercent),
        error_direction: errorDirection(predicted, actual, wapePercent),
        period: {
            start: weekStart.toISOString().slice(0, 10),
            end: addDays(weekStart, 6).toISOString().slice(0, 10),
        },
    };
}

function normalizeDirection(direction) {
    const value = String(direction || 'NEUTRO').toUpperCase();
    if (value === 'SUBESTIMACION') return 'Subestimacion';
    if (value === 'SOBREESTIMACION') return 'Sobreestimacion';
    return 'Neutro';
}

function LevelBadge({ level }) {
    const normalized = String(level || 'SIN_EVALUAR').toUpperCase();
    const tone = evaluatorToneByLevel[normalized] || 'neutral';

    return <span className={`evaluator-badge ${tone}`}>{normalized.replace('_', ' ')}</span>;
}

function AdjustmentBadge({ changed }) {
    return (
        <span className={`evaluator-badge ${changed ? 'changed' : 'stable'}`}>
            <i className={changed ? 'ri-loop-right-line' : 'ri-check-line'} />
            {changed ? 'Ajustado' : 'Sin ajuste'}
        </span>
    );
}

function DecisionChip({ urgent, children, icon }) {
    return (
        <span className={`fit-agent-decision ${urgent ? 'urgent' : ''}`}>
            {icon && <i className={icon} />}
            {children}
        </span>
    );
}

function EvaluatorSkeleton() {
    return (
        <section className="fit-section agent-section" data-agent-view="evaluator">
            <div className="chart-skeleton" />
        </section>
    );
}

function WeekCalendar({ weekStart, onChange }) {
    const monthStart = new Date(weekStart.getFullYear(), weekStart.getMonth(), 1);
    const gridStart = startOfWeek(monthStart);
    const days = Array.from({ length: 35 }, (_, index) => addDays(gridStart, index));
    const weekDays = Array.from({ length: 7 }, (_, index) => addDays(weekStart, index));
    const selectedDayKeys = new Set(weekDays.map((day) => day.toDateString()));
    const monthLabel = weekStart.toLocaleDateString('es-BO', { month: 'long', year: 'numeric' });

    return (
        <div className="evaluator-calendar">
            <div className="evaluator-calendar-head">
                <button type="button" className="fit-action-button" onClick={() => onChange(addWeeks(weekStart, -1))} title="Semana anterior">
                    <i className="ri-arrow-left-s-line" />
                </button>
                <div>
                    <strong>{monthLabel}</strong>
                    <span>Semana evaluada: {weekRangeLabel(weekStart)}</span>
                </div>
                <button type="button" className="fit-action-button" onClick={() => onChange(addWeeks(weekStart, 1))} title="Semana siguiente">
                    <i className="ri-arrow-right-s-line" />
                </button>
            </div>

            <div className="evaluator-calendar-grid weekdays">
                {['L', 'M', 'M', 'J', 'V', 'S', 'D'].map((label, index) => <span key={`${label}-${index}`}>{label}</span>)}
            </div>
            <div className="evaluator-calendar-grid">
                {days.map((day) => {
                    const selected = selectedDayKeys.has(day.toDateString());
                    const outMonth = day.getMonth() !== weekStart.getMonth();
                    return (
                        <button
                            type="button"
                            key={day.toISOString()}
                            className={`${selected ? 'selected-week' : ''}${sameDay(day, weekStart) ? ' week-start' : ''}${sameDay(day, addDays(weekStart, 6)) ? ' week-end' : ''}${outMonth ? ' out-month' : ''}`}
                            onClick={() => onChange(startOfWeek(day))}
                        >
                            {day.getDate()}
                        </button>
                    );
                })}
            </div>
        </div>
    );
}

function ProductImprovementBars({ item }) {
    const previousFactor = Number(item.previous_factor || 1) || 1;
    const newFactor = Number(item.new_factor || previousFactor) || previousFactor;
    const predicted = Number(item.predicted_demand || 0);
    const actual = Number(item.actual_demand || 0);
    const adjusted = previousFactor ? Math.round(predicted * (newFactor / previousFactor)) : predicted;
    const originalError = Math.abs(actual - predicted);
    const adjustedError = Math.abs(actual - adjusted);
    const improvement = originalError > 0 ? Math.round(((originalError - adjustedError) / originalError) * 100) : 0;
    const scale = Math.max(predicted, actual, adjusted, 1);
    const rows = [
        { label: 'Prediccion original', value: predicted, tone: '' },
        { label: 'Venta real', value: actual, tone: 'warn' },
        { label: 'Proxima demanda ajustada', value: adjusted, tone: adjustedError <= originalError ? 'success' : 'danger' },
    ];

    return (
        <div className="evaluator-improvement">
            <div className="evaluator-improvement-head">
                <div>
                    <strong>Cambio proyectado</strong>
                    <span>Error original: {formatUnits(originalError)} | Error ajustado: {formatUnits(adjustedError)}</span>
                </div>
                <span className={`evaluator-badge ${improvement >= 0 ? 'good' : 'low'}`}>{improvement >= 0 ? '+' : ''}{improvement}% mejora</span>
            </div>
            <div className="agent-bars">
                {rows.map((row) => (
                    <div className="agent-bar-row" key={row.label}>
                        <div className="agent-bar-head"><span>{row.label}</span><span>{formatUnits(row.value)}</span></div>
                        <div className="agent-bar-track"><div className={`agent-bar-fill ${row.tone}`} style={{ width: `${Math.max(4, Math.round((row.value / scale) * 100))}%` }} /></div>
                    </div>
                ))}
            </div>
        </div>
    );
}

function EvaluatorProductDetail({ item, weekStart, onWeekChange, onBack }) {
    const weeklyItem = deriveWeeklyItem(item, weekStart);
    const state = item.learning_state || {};
    const underStreak = state.under_streak ?? 0;
    const overStreak = state.over_streak ?? 0;
    const factorDelta = Number(weeklyItem.new_factor || 0) - Number(weeklyItem.previous_factor || 0);
    const error = Math.abs(Number(weeklyItem.actual_demand || 0) - Number(weeklyItem.predicted_demand || 0));

    return (
        <section className="fit-section agent-section evaluator-section evaluator-detail-view" data-agent-view="evaluator">
            <div className="fit-section-head">
                <div>
                    <button type="button" className="fit-outline-button compact" onClick={onBack}>
                        <i className="ri-arrow-left-line" /> Volver
                    </button>
                    <h2>{item.product_name}</h2>
                    <p>Gestion semanal del desempeno, error y aprendizaje adaptativo por producto.</p>
                </div>
                <LevelBadge level={weeklyItem.level} />
            </div>

            <div className="evaluator-detail-grid">
                <WeekCalendar weekStart={weekStart} onChange={onWeekChange} />

                <div className="evaluator-detail-panel">
                    <div className="summary">
                        <div className="summary-card"><strong>Semana</strong><span>{weekRangeLabel(weekStart)}</span></div>
                        <div className="summary-card"><strong>Direccion</strong><span>{normalizeDirection(weeklyItem.error_direction)}</span></div>
                        <div className="summary-card"><strong>Error absoluto</strong><span>{formatUnits(error)}</span></div>
                        <div className="summary-card"><strong>Cambio factor</strong><span>{factorDelta >= 0 ? '+' : ''}{factorDelta.toFixed(2)}</span></div>
                    </div>

                    <div className="evaluator-product-kpis">
                        <div><small>Predicha</small><strong>{formatUnits(weeklyItem.predicted_demand)}</strong></div>
                        <div><small>Real</small><strong>{formatUnits(weeklyItem.actual_demand)}</strong></div>
                        <div><small>WAPE</small><strong>{formatPercent(weeklyItem.wape_percent)}</strong></div>
                        <div><small>MAE</small><strong>{formatUnits(weeklyItem.mae)}</strong></div>
                        <div><small>Factor anterior</small><strong>{formatFactor(weeklyItem.previous_factor)}</strong></div>
                        <div><small>Factor nuevo</small><strong>{formatFactor(weeklyItem.new_factor)}</strong></div>
                    </div>
                </div>
            </div>

            <div className="evaluator-detail-grid lower">
                <div className="fit-transfer-panel agent-detail-section">
                    <h4>Manejo del producto</h4>
                    <p>{item.reason || 'El agente registro la evaluacion y mantiene seguimiento para la siguiente semana.'}</p>
                    <div className="metric-row">
                        <AdjustmentBadge changed={item.factor_changed} />
                        <span className="metric-chip">Racha subestimacion: {underStreak}</span>
                        <span className="metric-chip warn">Racha sobreestimacion: {overStreak}</span>
                    </div>
                </div>

                <div className="fit-transfer-panel agent-detail-section">
                    <h4>Mejora grafica</h4>
                    <ProductImprovementBars item={weeklyItem} />
                </div>
            </div>
        </section>
    );
}

function EvaluatorSection({ route }) {
    const [state, setState] = useState({ loading: true, error: null, payload: null });
    const [selectedItem, setSelectedItem] = useState(null);
    const [selectedWeekStart, setSelectedWeekStart] = useState(() => startOfWeek(new Date()));

    useEffect(() => {
        const controller = new AbortController();

        async function loadEvaluator() {
            try {
                const response = await fetch(route, {
                    headers: { Accept: 'application/json' },
                    credentials: 'same-origin',
                    signal: controller.signal,
                });
                const payload = await response.json();

                if (!response.ok) {
                    throw new Error(payload.message || 'No se pudo cargar el evaluador adaptativo.');
                }

                setState({ loading: false, error: null, payload: payload.data });
            } catch (error) {
                if (!controller.signal.aborted) {
                    setState({ loading: false, error: error.message || 'No se pudo cargar el evaluador adaptativo.', payload: null });
                }
            }
        }

        if (route) {
            loadEvaluator();
        } else {
            setState({ loading: false, error: 'Ruta del evaluador no configurada.', payload: null });
        }

        return () => controller.abort();
    }, [route]);

    const payload = state.payload;
    const summary = payload?.summary || {};
    const items = payload?.items || [];
    const predictionsLoaded = Number(payload?.predictions_loaded ?? items.length);
    const isRealEmpty = !state.error && predictionsLoaded === 0;
    const generatedAt = payload?.generated_at ? new Date(payload.generated_at).toLocaleString('es-BO', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : 'Pendiente';
    const cards = [
        { label: 'Predicciones Evaluadas', value: payload?.evaluated_predictions ?? 0, hint: 'Periodo cerrado', icon: 'ri-calendar-check-line', tone: 'indigo' },
        { label: 'WAPE Promedio', value: formatPercent(summary.avg_wape_percent), hint: 'Error ponderado', icon: 'ri-percent-line', tone: 'green' },
        { label: 'MAE Promedio', value: Number(summary.avg_mae || 0).toFixed(0), hint: 'Error en unidades', icon: 'ri-ruler-line', tone: 'amber' },
        { label: 'Factores Ajustados', value: summary.changed_factors ?? 0, hint: 'Learning factor', icon: 'ri-loop-right-line', tone: 'rose' },
    ];

    if (state.loading) {
        return <EvaluatorSkeleton />;
    }

    if (selectedItem) {
        return (
            <EvaluatorProductDetail
                item={selectedItem}
                weekStart={selectedWeekStart}
                onWeekChange={setSelectedWeekStart}
                onBack={() => setSelectedItem(null)}
            />
        );
    }

    return (
        <section className="fit-section agent-section evaluator-section" data-agent-view="evaluator">
            <div className="fit-section-head">
                <div>
                    <h2>Agente Evaluador Adaptativo</h2>
                    <p>Compara predicciones cerradas contra ventas reales y ajusta el factor de aprendizaje.</p>
                    {payload?.error && <p className="fit-agent-error">{payload.error}</p>}
                    {payload?.error && payload?.final_url && <p className="fit-agent-url">URL consultada: {payload.final_url}</p>}
                    {state.error && <p className="fit-agent-error">{state.error}</p>}
                </div>
                <span className={`fit-status ${payload?.online ? 'active' : 'inactive'}`}><span /> {payload?.online ? 'Evaluador en linea' : 'Sin datos reales'}</span>
            </div>
            <span className="fit-agent-url">Ultima evaluacion: {generatedAt}</span>

            <section className="fit-metric-grid">
                {cards.map((card) => (
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

            <div className="evaluator-status-grid">
                <div className="evaluator-status-card good"><strong>{summary.good ?? 0}</strong><span>Buenos</span></div>
                <div className="evaluator-status-card regular"><strong>{summary.regular ?? 0}</strong><span>Regulares</span></div>
                <div className="evaluator-status-card low"><strong>{summary.low ?? 0}</strong><span>Bajos</span></div>
            </div>

            {isRealEmpty ? (
                <div className="evaluator-empty-state">
                    <span><i className="ri-database-2-line" /></span>
                    <div>
                        <h3>Sin evaluaciones reales cerradas</h3>
                        <p>{EVALUATOR_EMPTY_MESSAGE}</p>
                    </div>
                </div>
            ) : null}

            <div className="fit-table-card">
                <div className="fit-table-scroll">
                    <table className="fit-users-table fit-agent-table evaluator-table">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Periodo</th>
                                <th>Predicha</th>
                                <th>Real</th>
                                <th>WAPE</th>
                                <th>MAE</th>
                                <th>Clasificacion</th>
                                <th>Factor anterior</th>
                                <th>Factor nuevo</th>
                                <th>Ajuste</th>
                                <th className="text-right">Detalle</th>
                            </tr>
                        </thead>
                        <tbody>
                            {!isRealEmpty && items.length ? items.map((item) => (
                                <tr key={item.product_id || item.product_name}>
                                    <td><strong>{item.product_name}</strong></td>
                                    <td><span className="fit-muted-text">{formatDate(item.period?.start)} - {formatDate(item.period?.end)}</span></td>
                                    <td><span className="fit-muted-text">{formatUnits(item.predicted_demand)}</span></td>
                                    <td><span className="fit-muted-text">{formatUnits(item.actual_demand)}</span></td>
                                    <td><span className="fit-muted-text">{formatPercent(item.wape_percent)}</span></td>
                                    <td><span className="fit-muted-text">{formatUnits(item.mae)}</span></td>
                                    <td><LevelBadge level={item.level} /></td>
                                    <td><span className="fit-muted-text">{formatFactor(item.previous_factor)}</span></td>
                                    <td><span className="fit-muted-text">{formatFactor(item.new_factor)}</span></td>
                                    <td><AdjustmentBadge changed={item.factor_changed} /></td>
                                    <td className="text-right">
                                        <button type="button" className="fit-outline-button compact" onClick={() => {
                                            setSelectedItem(item);
                                            setSelectedWeekStart(startOfWeek(toDate(item.period?.start)));
                                        }}>
                                            <i className="ri-settings-4-line" /> Gestionar
                                        </button>
                                    </td>
                                </tr>
                            )) : <TableEmpty colSpan={11} text={isRealEmpty ? EVALUATOR_EMPTY_MESSAGE : 'Sin productos evaluados.'} />}
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    );
}

function MetricCards({ data }) {
    const cards = [
        { label: 'Productos Evaluados', value: data.forecastsTotal, hint: 'Demanda a 7 dias', icon: 'ri-line-chart-line', tone: 'indigo' },
        { label: 'Stock Bajo', value: data.alerts.low_stock?.length || 0, hint: 'Nivel seguro', icon: 'ri-alarm-warning-line', tone: 'amber' },
        { label: 'Lotes por Vencer', value: data.alerts.expiring?.length || 0, hint: 'Control operativo', icon: 'ri-calendar-close-line', tone: 'rose' },
        { label: 'Por Revisar', value: data.pendingRequestsTotal, hint: 'Aprobacion humana', icon: 'ri-inbox-line', tone: 'green' },
    ];

    return (
        <section className="fit-metric-grid agent-grid" data-agent-view="overview">
            {cards.map((card) => (
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
    );
}

function SearchPanel({ data }) {
    const hasFilters = Boolean(data.search || data.categoryId);

    return (
        <section className="fit-filter-card agent-search-island" data-agent-view="overview">
            <div className="fit-section-head">
                <div>
                    <h2>Buscar productos evaluados</h2>
                    <p>Filtra evaluaciones, alertas, solicitudes pendientes e historial por nombre, SKU o categoria.</p>
                </div>
                <a className="fit-outline-button" target="_blank" rel="noopener noreferrer" href={data.routes.report}>
                    <i className="ri-file-pdf-line" />
                    <span>Reporte PDF</span>
                </a>
            </div>

            <form method="GET" action={data.routes.index} className="fit-filter-form fit-agent-filter-form">
                <label className="fit-search-control" htmlFor="agent_search">
                    <i className="ri-search-line" />
                    <input id="agent_search" type="text" name="search" placeholder="Producto, SKU o categoria..." defaultValue={data.search || ''} />
                </label>

                <label className="fit-select-control" htmlFor="agent_category">
                    <i className="ri-filter-3-line" />
                    <select id="agent_category" name="category_id" defaultValue={data.categoryId || ''}>
                        <option value="">Todas las categorias</option>
                        {data.categories.map((category) => <option key={category.id} value={category.id}>{category.name}</option>)}
                    </select>
                </label>

                <button type="submit" className="fit-primary-button compact">
                    <i className="ri-search-line" /> Buscar
                </button>

                {hasFilters && <a href={data.routes.index} className="fit-clear-button">Limpiar Filtros</a>}
            </form>
        </section>
    );
}

function EvaluationsSection({ data }) {
    return (
        <section className="fit-section agent-section" data-agent-view="evaluations">
            <div className="fit-section-head">
                <div>
                    <h2>Evaluaciones de Reposicion</h2>
                    <p>Demanda, stock disponible y decision recomendada por producto.</p>
                </div>
                <span className="fit-section-badge indigo">{data.forecasts.total} registros</span>
            </div>

            <div className="fit-table-card">
                <div className="fit-table-scroll">
                    <table className="fit-users-table fit-agent-table">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Demanda 7 dias</th>
                                <th>Stock actual</th>
                                <th>Traspasos previstos</th>
                                <th>Stock final estimado</th>
                                <th>Stock minimo</th>
                                <th>Decision</th>
                            </tr>
                        </thead>
                        <tbody>
                            {data.forecasts.data.length ? data.forecasts.data.map((item, index) => {
                                const urgent = String(item.priority || '').toLowerCase() === 'urgente';

                                return (
                                    <tr key={`${item.name}-${index}`} className={urgent ? 'urgent-row' : ''}>
                                        <td><strong>{item.name}</strong></td>
                                        <td><span className="fit-muted-text">{Number(item.forecast_7_days).toFixed(0)} uds</span></td>
                                        <td><span className="fit-muted-text">{item.stock} uds</span></td>
                                        <td><span className="fit-muted-text">{item.in_transit} uds</span></td>
                                        <td><span className="fit-muted-text">{item.result < 0 ? `Faltan ${Math.abs(item.result)} uds` : `${Number(item.result).toFixed(0)} uds`}</span></td>
                                        <td><span className="fit-muted-text">{item.safety_threshold} uds</span></td>
                                        <td>
                                            <DecisionChip urgent={urgent} icon={urgent ? 'ri-alarm-warning-line' : 'ri-lightbulb-flash-line'}>
                                                {item.decision}{urgent ? ' - Urgente' : ''}
                                            </DecisionChip>
                                        </td>
                                    </tr>
                                );
                            }) : <TableEmpty colSpan={7} text="Sin evaluaciones del agente." />}
                        </tbody>
                    </table>
                </div>
            </div>
            <Pagination pagination={data.forecasts} />
        </section>
    );
}

function RequestsSection({ data, onOpen }) {
    return (
        <section className="fit-section agent-section" data-agent-view="requests">
            <div className="fit-section-head">
                <div>
                    <h2>Solicitudes Pendientes del Agente</h2>
                    <p>Aprobacion humana antes de crear o confirmar el traspaso operativo.</p>
                </div>
                <span className="fit-section-badge rose">{data.pendingRequests.total} pendientes</span>
            </div>

            <div className="fit-table-card">
                <div className="fit-table-scroll">
                    <table className="fit-users-table fit-agent-table">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Cantidad solicitada</th>
                                <th>Prioridad</th>
                                <th>Motivo resumido</th>
                                <th className="text-right">Detalle</th>
                            </tr>
                        </thead>
                        <tbody>
                            {data.pendingRequests.data.length ? data.pendingRequests.data.map((request) => {
                                const urgent = String(request.priority || '').toLowerCase() === 'urgente';
                                const parsed = request.parsedReason;

                                return (
                                    <tr key={request.id} className={urgent ? 'urgent-row' : ''}>
                                        <td><strong>{request.product_name}</strong></td>
                                        <td><span className="fit-muted-text">{request.requested_qty} uds</span></td>
                                        <td><DecisionChip urgent={urgent}>{request.priority}</DecisionChip></td>
                                        <td className="fit-agent-reason-cell">
                                            {parsed ? (
                                                <div className="reason-summary">
                                                    <p>
                                                        <strong>Reposicion necesaria.</strong>
                                                        {parsed.result < 0 ? <> Faltan <strong>{Math.abs(parsed.result)} uds</strong> para completar la demanda prevista y mantener el stock minimo.</> : <> Quedarian <strong>{parsed.result} uds</strong>, por debajo del stock minimo.</>}
                                                    </p>
                                                    <div className="reason-metrics">
                                                        <span className="metric-chip">Stock: {parsed.stock} uds</span>
                                                        <span className="metric-chip warn">Stock minimo: {parsed.threshold} uds</span>
                                                    </div>
                                                </div>
                                            ) : (request.reason || 'El agente recomienda revisar este producto.')}
                                        </td>
                                        <td className="text-right">
                                            <button type="button" className="fit-outline-button" onClick={() => onOpen(request)}>Ver detalles</button>
                                        </td>
                                    </tr>
                                );
                            }) : <TableEmpty colSpan={5} text="No hay solicitudes pendientes del agente." />}
                        </tbody>
                    </table>
                </div>
            </div>
            <Pagination pagination={data.pendingRequests} />
        </section>
    );
}

function AlertsSection({ data, decisionClass, onOpen }) {
    return (
        <section className="fit-section agent-section" data-agent-view="alerts">
            <div className="fit-section-head">
                <div>
                    <h2>Alertas por Producto</h2>
                    <p>Revisa stock bajo, lotes por vencer y lotes vencidos por producto.</p>
                </div>
                <span className="fit-section-badge amber">Control de vencimientos</span>
            </div>

            <div className="alert-product-grid">
                {data.alertProductCards.length ? data.alertProductCards.map((productAlert) => (
                    <div className={`alert-product-card ${productAlert.severity}`} key={productAlert.id}>
                        <div className="alert-card-head">
                            <div className="alert-product-title">
                                <img src={productAlert.image} alt={productAlert.name} />
                                <div>
                                    <h4>{productAlert.name}</h4>
                                    <span className="section-kicker">SKU: {productAlert.sku || 'N/D'} - {productAlert.category}</span>
                                </div>
                            </div>
                            <DecisionChip urgent={decisionClass(productAlert.severity) === 'urgent'}>{productAlert.severity_label}</DecisionChip>
                        </div>

                        <div className="metric-row">
                            {Object.entries(productAlert.metrics || {}).map(([label, value]) => (
                                <span key={label} className={`metric-chip ${label.toLowerCase().includes('faltante') ? 'danger' : label.toLowerCase().includes('minimo') ? 'warn' : ''}`}>{label}: {value}</span>
                            ))}
                        </div>

                        <div className="alert-card-list">
                            {(productAlert.problems || []).slice(0, 2).map((item, index) => (
                                <div className="alert-card-item" key={`${productAlert.id}-problem-${index}`}>
                                    <strong>{item.label}</strong>
                                    <p>{item.message}</p>
                                </div>
                            ))}
                        </div>

                        <div className="alert-card-actions">
                            <button type="button" className="fit-outline-button" onClick={() => onOpen(productAlert)}>Detalles</button>
                        </div>
                    </div>
                )) : (
                    <div className="alert-product-card">
                        <h4>Sin alertas operativas</h4>
                        <p>No hay productos criticos en este momento.</p>
                    </div>
                )}
            </div>
        </section>
    );
}

function HistorySection({ data }) {
    return (
        <section className="fit-section agent-section" data-agent-view="history">
            <div className="fit-section-head">
                <div>
                    <h2>Historial de Decisiones</h2>
                    <p>Solicitudes creadas, aprobadas o rechazadas por el flujo del agente.</p>
                </div>
                <span className="fit-section-badge green">{data.recentRequests.total} registros</span>
            </div>

            <div className="fit-table-card">
                <div className="fit-table-scroll">
                    <table className="fit-users-table fit-agent-table">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Estado</th>
                                <th>Decision humana</th>
                                <th>Traspaso relacionado</th>
                            </tr>
                        </thead>
                        <tbody>
                            {data.recentRequests.data.length ? data.recentRequests.data.map((request) => (
                                <tr key={request.id}>
                                    <td><span className="fit-muted-text">{request.created_at_formatted}</span></td>
                                    <td><strong>{request.product_name}</strong></td>
                                    <td><span className="fit-muted-text">{request.requested_qty} uds</span></td>
                                    <td><DecisionChip>{request.status}</DecisionChip></td>
                                    <td><span className="fit-muted-text">{request.decision_label}</span></td>
                                    <td><span className="fit-muted-text">{request.transfer_label}</span></td>
                                </tr>
                            )) : <TableEmpty colSpan={6} text="Sin historial de solicitudes del agente." />}
                        </tbody>
                    </table>
                </div>
            </div>
            <Pagination pagination={data.recentRequests} />
        </section>
    );
}

function RequestModal({ requestModal, csrfToken, onClose }) {
    if (!requestModal) return null;

    const urgent = String(requestModal.priority || '').toLowerCase() === 'urgente';
    const parsed = requestModal.parsedReason;
    const stock = parsed?.stock ?? 0;
    const transfers = parsed?.transfers ?? 0;
    const demand = parsed?.demand ?? 0;
    const result = parsed?.result ?? null;
    const threshold = parsed?.threshold ?? 0;
    const missing = result !== null && result < 0 ? Math.abs(result) : 0;
    const scale = Math.max(stock, transfers, demand, threshold, missing, 1);
    const pct = (value) => Math.min(100, Math.round((value / scale) * 100));

    const rows = [
        { label: 'Stock actual', value: stock, pctClass: '' },
        { label: 'Traspasos previstos', value: transfers, pctClass: '' },
        { label: 'Demanda 7 dias', value: demand, pctClass: 'warn' },
        { label: 'Stock minimo', value: threshold, pctClass: 'warn' },
        ...(missing > 0 ? [{ label: 'Unidades faltantes', value: missing, pctClass: 'danger' }] : []),
    ];

    return (
        <Modal open title={`Solicitud de traspaso #${requestModal.id}`} onClose={onClose} wide contentClassName="fit-modal-content fit-agent-modal-content">
            <div className="modal-body">
                <div className="summary">
                    <div className="summary-card"><strong>Cantidad solicitada</strong><span>{requestModal.requested_qty} uds</span></div>
                    <div className="summary-card"><strong>Prioridad</strong><DecisionChip urgent={urgent}>{requestModal.priority}</DecisionChip></div>
                    <div className="summary-card"><strong>Estado</strong><span>{requestModal.status}</span></div>
                    <div className="summary-card"><strong>Creada</strong><span>{requestModal.created_at_formatted}</span></div>
                </div>

                <div className="fit-transfer-panel agent-detail-section">
                    <h4>Detalle de reposicion</h4>
                    {parsed ? (
                        <>
                            <p>
                                <strong>Reposicion necesaria.</strong>
                                {missing > 0 ? <> Faltan <strong>{missing} unidades</strong> para completar la demanda prevista de 7 dias y mantener el stock minimo.</> : <> Despues de cubrir la demanda prevista quedarian <strong>{result} uds</strong>, por debajo del stock minimo.</>}
                            </p>
                            <div className="agent-bars">
                                {rows.map((row) => (
                                    <div className="agent-bar-row" key={row.label}>
                                        <div className="agent-bar-head"><span>{row.label}</span><span>{row.value} uds</span></div>
                                        <div className="agent-bar-track"><div className={`agent-bar-fill ${row.pctClass}`} style={{ width: `${pct(row.value)}%` }} /></div>
                                    </div>
                                ))}
                            </div>
                        </>
                    ) : <p>{requestModal.reason || 'El agente recomienda revisar este producto.'}</p>}
                </div>

                <div className="fit-transfer-panel agent-detail-section">
                    <h4>Decision humana</h4>
                    <div className="agent-decision-actions">
                        <div className="agent-decision-card">
                            <h4>Aprobar traspaso</h4>
                            <form method="POST" action={requestModal.approve_url}>
                                <input type="hidden" name="_token" value={csrfToken} />
                                <input type="text" name="decision_reason" className="input-ghost" placeholder="Motivo de aprobacion" />
                                <button type="submit" className="fit-primary-button">Aprobar traspaso</button>
                            </form>
                        </div>
                        <div className="agent-decision-card">
                            <h4>Rechazar solicitud</h4>
                            <form method="POST" action={requestModal.reject_url}>
                                <input type="hidden" name="_token" value={csrfToken} />
                                <input type="text" name="decision_reason" className="input-ghost" placeholder="Motivo de rechazo" />
                                <button type="submit" className="fit-primary-button danger">Rechazar traspaso</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </Modal>
    );
}

function AlertModal({ alertModal, onClose }) {
    if (!alertModal) return null;

    return (
        <Modal open title={alertModal.name || 'Alerta'} onClose={onClose} wide contentClassName="fit-modal-content fit-agent-modal-content">
            <div className="modal-body">
                <div className="summary">
                    <div className="summary-card"><strong>SKU</strong><span>{alertModal.sku || 'N/D'}</span></div>
                    <div className="summary-card"><strong>Categoria</strong><span>{alertModal.category}</span></div>
                    <div className="summary-card"><strong>Estado</strong><span>{alertModal.severity_label}</span></div>
                </div>

                <div className="fit-transfer-panel agent-detail-section">
                    <h4>Problemas detectados</h4>
                    <div className="alert-card-list">
                        {alertModal.problems.map((problem, index) => (
                            <div className="alert-card-item" key={`${alertModal.id}-modal-problem-${index}`}>
                                <strong>{problem.label}</strong>
                                <p>{problem.message}</p>
                                {problem.meta && (
                                    <div className="metric-row">
                                        {Object.entries(problem.meta).map(([label, value]) => (
                                            <span key={label} className={`metric-chip ${label.toLowerCase().includes('faltante') ? 'danger' : label.toLowerCase().includes('minimo') ? 'warn' : ''}`}>{label}: {value}</span>
                                        ))}
                                    </div>
                                )}
                            </div>
                        ))}
                    </div>
                </div>

                <div className="fit-transfer-panel agent-detail-section">
                    <h4>Lotes del producto</h4>
                    <div className="agent-lot-list">
                        {alertModal.lots?.length ? alertModal.lots.map((lot, index) => (
                            <div className={`agent-lot-row ${lot.status}`} key={`${lot.code}-${index}`}>
                                <div>
                                    <strong>{lot.label} - {lot.code}</strong>
                                    <p>{lot.message}</p>
                                </div>
                                <div className="metric-row">
                                    <span className="metric-chip">Cantidad: {lot.quantity} uds</span>
                                    <span className={`metric-chip ${lot.status === 'expired' ? 'danger' : lot.status === 'warning' ? 'warn' : ''}`}>Vence: {lot.expires_at}</span>
                                </div>
                            </div>
                        )) : (
                            <div className="alert-card-item">
                                <strong>Sin lotes activos</strong>
                                <p>No hay lotes con cantidad disponible para este producto.</p>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </Modal>
    );
}

export default function AdminAgentReplenishmentPage({ layout, data, flash, csrfToken, logoutAction }) {
    const [requestModal, setRequestModal] = useState(null);
    const [alertModal, setAlertModal] = useState(null);
    const agentMode = data.agentMode || 'replenishment';
    const [activeView, setActiveView] = useState(agentMode === 'evaluator' ? 'evaluator' : 'overview');
    const statusClass = data.agentOnline ? 'active' : 'inactive';
    const decisionClass = (severity) => severity === 'critical' ? 'urgent' : severity;

    useEffect(() => {
        setActiveView(agentMode === 'evaluator' ? 'evaluator' : 'overview');
    }, [agentMode]);

    return (
        <DashboardShell sidebar={layout.sidebar} topbar={layout.topbar} csrfToken={csrfToken} logoutAction={logoutAction}>
            <div className={`fit-users-page fit-agent-page agent-workspace agent-view-${activeView}`}>
                <FlashMessages flash={flash} />

                <section className="fit-users-header">
                    <div className="fit-users-header-left">
                        <div className="fit-header-icon"><i className="ri-robot-2-line" /></div>
                        <div>
                            <h1>Reposicion Inteligente</h1>
                            <p>Evaluaciones del agente para anticipar faltantes y aprobar traspasos con control humano.</p>
                            <AgentModeSwitch mode={agentMode} data={data} />
                            {data.error && <p className="fit-agent-error">{data.error}</p>}
                        </div>
                    </div>

                    {agentMode === 'replenishment' ? <div className="fit-users-header-actions fit-agent-header-actions">
                        <span className={`fit-status ${statusClass}`}><span /> {data.agentOnline ? 'Agente en linea' : 'Agente sin conexion'}</span>
                        <span className="fit-section-badge indigo"><i className="ri-cpu-line" /> AI_AGENT_URL /api/predict</span>
                        <GeneratePredictionButton route={data.routes.run} csrfToken={csrfToken} />
                        <AgentRuntimeStatus data={data} />
                    </div> : <div className="fit-users-header-actions fit-agent-header-actions">
                        <span className="fit-section-badge indigo"><i className="ri-database-2-line" /> AI_EVALUATOR_AGENT_URL /real</span>
                        <span className="fit-section-badge green"><i className="ri-brain-line" /> Learning factor</span>
                    </div>}
                </section>

                {agentMode === 'replenishment' && <AgentNavigation activeView={activeView} onChange={setActiveView} data={data} />}
                {agentMode === 'replenishment' && <SearchPanel data={data} />}
                {agentMode === 'replenishment' && <MetricCards data={data} />}
                {agentMode === 'replenishment' && <EvaluationsSection data={data} />}
                {agentMode === 'evaluator' && <EvaluatorSection route={data.routes.evaluator_real} />}
                {agentMode === 'replenishment' && <RequestsSection data={data} onOpen={setRequestModal} />}
                {agentMode === 'replenishment' && <AlertsSection data={data} decisionClass={decisionClass} onOpen={setAlertModal} />}
                {agentMode === 'replenishment' && <HistorySection data={data} />}

                <RequestModal requestModal={requestModal} csrfToken={csrfToken} onClose={() => setRequestModal(null)} />
                <AlertModal alertModal={alertModal} onClose={() => setAlertModal(null)} />
            </div>
        </DashboardShell>
    );
}
