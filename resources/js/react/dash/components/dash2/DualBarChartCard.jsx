import React from 'react';
import { ResponsiveContainer, BarChart, Bar, XAxis, YAxis, Tooltip } from 'recharts';
import { useTheme } from '../../context/ThemeContext';

export default function DualBarChartCard({ data: dashboardData }) {
  const { isDark } = useTheme();

  const roleLabels = dashboardData?.roleMix?.labels || [];
  const roleValues = dashboardData?.roleMix?.data || [];
  const categoryValues = dashboardData?.categoryMix?.data || [];
  const data = roleLabels.length
    ? roleLabels.map((label, index) => ({
        day: String(label || 'Rol').slice(0, 8),
        coral: Number(roleValues[index] || 0),
        peach: Number(categoryValues[index] || 0),
      }))
    : [
        { day: 'Sun', coral: 40, peach: 65 },
        { day: 'Mon', coral: 70, peach: 45 },
        { day: 'Tue', coral: 35, peach: 80 },
        { day: 'Wed', coral: 25, peach: 50 },
        { day: 'Thu', coral: 90, peach: 60 },
        { day: 'Fri', coral: 55, peach: 75 },
        { day: 'Sat', coral: 80, peach: 90 },
      ];

  return (
    <div className={`p-6 rounded-3xl border transition-all duration-300 flex flex-col justify-between ${
      isDark 
        ? 'bg-[#151722] border-fitonist-border-dark text-slate-100 shadow-xl' 
        : 'bg-white border-indigo-100/80 text-slate-800 shadow-sm'
    }`}>
      <h3 className="text-sm font-bold tracking-tight mb-2">Usuarios por rol</h3>

      <div className="h-36 w-full">
        <ResponsiveContainer width="100%" height="100%">
          <BarChart data={data} margin={{ top: 5, right: 5, left: -25, bottom: 0 }} barGap={3}>
            <XAxis dataKey="day" axisLine={false} tickLine={false} tick={{ fill: '#94a3b8', fontSize: 9 }} />
            <YAxis hide />
            <Tooltip />
            <Bar dataKey="coral" fill="#f97316" radius={[4, 4, 0, 0]} />
            <Bar dataKey="peach" fill="#fdba74" radius={[4, 4, 0, 0]} />
          </BarChart>
        </ResponsiveContainer>
      </div>
    </div>
  );
}
