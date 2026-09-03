import React from 'react';
import DashApp from '../dash/App.jsx';
import { ThemeProvider } from '../dash/context/ThemeContext.jsx';
import { UserProvider } from '../dash/context/UserContext.jsx';

function buildDashboardMetrics(props) {
    const kpis = props.kpis || {};
    const monthlySales = Number(props.monthlySales || 0);
    const salesToday = Number(kpis.sales_today || 0);
    const salesDelta = Number(props.summaryCards?.[3]?.value?.replace('%', '') || 0);

    return {
        revenue: monthlySales,
        revenueGrowth: `${salesDelta >= 0 ? '+' : ''}${salesDelta.toFixed(1)}%`,
        dailySubs: Number(kpis.sales_today_count || 0),
        dailySubsGrowth: `${salesDelta >= 0 ? '+' : ''}${salesDelta.toFixed(1)}%`,
        weeklyInstalls: Number(props.weeklySalesCount || 0),
        impressions: Number(kpis.products_active || 0),
        activeGrowth: Number(kpis.users_active || 0),
        churnCount: Number(kpis.users_inactive || 0),
        radialProgress: Number(props.monthlyTargetProgress || 0),
        salesToday,
    };
}

export default function AdminDashboardPage(props) {
    return (
        <ThemeProvider>
            <UserProvider initialMetrics={buildDashboardMetrics(props)} currentUser={props.layout?.topbar?.user}>
                <DashApp {...props} />
            </UserProvider>
        </ThemeProvider>
    );
}
