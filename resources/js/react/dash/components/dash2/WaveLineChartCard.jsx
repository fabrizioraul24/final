import React from 'react';
import { Search } from 'lucide-react';
import { ResponsiveContainer, AreaChart, Area, XAxis, YAxis, Tooltip } from 'recharts';
import { useTheme } from '../../context/ThemeContext';

export default function WaveLineChartCard({ data: dashboardData }) {
  const { isDark } = useTheme();

  const salesLabels = dashboardData?.salesSeries?.labels || [];
  const salesValues = dashboardData?.salesSeries?.data || [];
  const data = salesLabels.length
    ? salesLabels.map((day, index) => ({
        day,
        purple: Number(salesValues[index] || 0),
        orange: Math.max(0, Number(salesValues[index - 1] || 0)),
      }))
    : [
        { day: 'Dom', purple: 40, orange: 20 },
        { day: 'Lun', purple: 70, orange: 35 },
        { day: 'Mar', purple: 50, orange: 25 },
        { day: 'Mie', purple: 85, orange: 60 },
        { day: 'Jue', purple: 60, orange: 40 },
        { day: 'Vie', purple: 95, orange: 75 },
        { day: 'Sab', purple: 75, orange: 50 },
      ];

  return (
    <div className={`h-full min-h-[270px] p-6 rounded-3xl border transition-all duration-300 flex flex-col justify-between ${
      isDark 
        ? 'bg-[#151722] border-fitonist-border-dark text-slate-100 shadow-xl' 
        : 'bg-white border-indigo-100/80 text-slate-800 shadow-sm'
    }`}>
      {/* Top Search Input inside Card */}
      <div className="flex items-center justify-between mb-2">
        <div className={`relative flex items-center w-full rounded-full border ${
          isDark ? 'bg-slate-800 border-slate-700 text-slate-200' : 'bg-indigo-50/50 border-indigo-100 text-slate-800'
        }`}>
          <input
            type="text"
            placeholder="Buscar..."
            className="w-full pl-4 pr-10 py-1.5 bg-transparent text-xs font-medium focus:outline-none"
          />
          <div className="p-1.5 mr-1 rounded-full bg-indigo-600 text-white">
            <Search size={13} />
          </div>
        </div>
      </div>

      <div className="mb-1">
        <h3 className="text-sm font-bold tracking-tight">Ventas</h3>
        <p className="text-[10px] text-slate-400">Movimiento semanal registrado</p>
      </div>

      {/* Dual Wave Area Chart */}
      <div className="h-28 w-full mt-auto">
        <ResponsiveContainer width="100%" height="100%">
          <AreaChart data={data} margin={{ top: 5, right: 5, left: -25, bottom: 0 }}>
            <defs>
              <linearGradient id="purpleGrad" x1="0" y1="0" x2="0" y2="1">
                <stop offset="5%" stopColor="#0b4fc1" stopOpacity={0.4}/>
                <stop offset="95%" stopColor="#0b4fc1" stopOpacity={0.0}/>
              </linearGradient>
              <linearGradient id="orangeGrad" x1="0" y1="0" x2="0" y2="1">
                <stop offset="5%" stopColor="#f25a59" stopOpacity={0.4}/>
                <stop offset="95%" stopColor="#f25a59" stopOpacity={0.0}/>
              </linearGradient>
            </defs>
            <XAxis dataKey="day" axisLine={false} tickLine={false} tick={{ fill: '#94a3b8', fontSize: 9 }} />
            <YAxis hide />
            <Tooltip />
            <Area type="monotone" dataKey="purple" stroke="#0b4fc1" strokeWidth={2} fillOpacity={1} fill="url(#purpleGrad)" />
            <Area type="monotone" dataKey="orange" stroke="#f25a59" strokeWidth={2} fillOpacity={1} fill="url(#orangeGrad)" />
          </AreaChart>
        </ResponsiveContainer>
      </div>
    </div>
  );
}
