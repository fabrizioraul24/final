import React from 'react';
import DashboardShell from '../components/admin/DashboardShell';
import { FlashMessages } from '../components/admin/common';
import AgentInsightsDashboard from '../components/admin/AgentInsightsDashboard';
import { AgentModeSwitch } from './AdminAgentReplenishmentPage';

export default function AdminAgentInsightsPage({ layout, data, flash, csrfToken, logoutAction }) {
    return (
        <DashboardShell sidebar={layout.sidebar} topbar={layout.topbar} csrfToken={csrfToken} logoutAction={logoutAction}>
            <div className="fit-users-page fit-agent-page agent-insights-page">
                <FlashMessages flash={flash} />

                <section className="fit-users-header">
                    <div className="fit-users-header-left">
                        <div className="fit-header-icon"><i className="ri-bar-chart-grouped-line" /></div>
                        <div>
                            <h1>Graficos del agente</h1>
                            <p>Comparaciones visuales de ventas, traspasos, errores y cambios entre semanas o meses.</p>
                            <AgentModeSwitch mode={data.agentMode || 'insights'} data={data} />
                        </div>
                    </div>

                    <div className="fit-users-header-actions fit-agent-header-actions">
                        <span className="fit-section-badge indigo"><i className="ri-line-chart-line" /> Comparativa visual</span>
                        <span className="fit-section-badge green"><i className="ri-file-chart-line" /> Reporte PDF</span>
                    </div>
                </section>

                <AgentInsightsDashboard data={data} />
            </div>
        </DashboardShell>
    );
}
