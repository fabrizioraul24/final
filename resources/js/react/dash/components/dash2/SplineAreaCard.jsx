import React, { useState } from 'react';
import { ResponsiveContainer, AreaChart, Area, XAxis, YAxis, Tooltip } from 'recharts';
import { useTheme } from '../../context/ThemeContext';

export default function SplineAreaCard({ data: dashboardData }) {
  const { isDark } = useTheme();
  const [filter, setFilter] = useState('Hoy');
  const tabs = [
    { value: 'Hoy', label: 'Hoy' },
    { value: 'Semana', label: 'Semana' },
    { value: 'Mes', label: 'Mes' },
    { value: 'Rango', label: 'Año' },
  ];

  const selectedPeriod = dashboardData?.revenuePeriods?.[filter] || dashboardData?.revenuePeriods?.Semana || null;
  const labels = selectedPeriod?.series?.labels || dashboardData?.salesSeries?.labels || [];
  const values = selectedPeriod?.series?.data || dashboardData?.salesSeries?.data || [];
  const data = labels.length
    ? labels.map((name, index) => ({
        name,
        lorem: Number(values[index] || 0),
        ipsum: Number(values[index - 1] || 0),
      }))
    : [
        { name: '1', lorem: 30, ipsum: 15 },
        { name: '2', lorem: 45, ipsum: 25 },
        { name: '3', lorem: 25, ipsum: 35 },
        { name: '4', lorem: 60, ipsum: 30 },
        { name: '5', lorem: 40, ipsum: 75 },
        { name: '6', lorem: 70, ipsum: 50 },
        { name: '7', lorem: 55, ipsum: 20 },
      ];

  return (
    <div className={`h-full min-h-[350px] p-6 rounded-3xl border transition-all duration-300 flex flex-col justify-between ${
      isDark 
        ? 'bg-[#151722] border-fitonist-border-dark text-slate-100 shadow-xl' 
        : 'bg-white border-indigo-100/80 text-slate-800 shadow-sm'
    }`}>
      {/* Header & Filter */}
      <div className="flex items-center justify-between mb-2">
        <div>
          <h3 className="text-sm font-bold tracking-tight">Ventas</h3>
          <p className="text-[10px] text-slate-400">{selectedPeriod?.label || 'Esta semana'}</p>
        </div>

        <div className={`flex items-center p-1 rounded-full border ${
          isDark ? 'bg-slate-800 border-slate-700' : 'bg-indigo-50/70 border-indigo-100'
        }`}>
          {tabs.map((tab) => {
            const isActive = filter === tab.value;
            return (
              <button
                key={tab.value}
                type="button"
                onClick={() => setFilter(tab.value)}
                className={`px-3 py-1 rounded-full text-xs font-bold transition-all duration-200 ${
                  isActive
                    ? 'bg-indigo-600 text-white shadow-sm'
                    : isDark ? 'text-slate-400 hover:text-white' : 'text-slate-600 hover:text-indigo-600'
                }`}
              >
                {tab.label}
              </button>
            );
          })}
        </div>
      </div>

      {/* Large Spline Curve Area Chart */}
      <div className="h-48 w-full my-auto">
        <ResponsiveContainer width="100%" height="100%">
          <AreaChart data={data} margin={{ top: 10, right: 10, left: -25, bottom: 0 }}>
            <defs>
              <linearGradient id="splinePurple" x1="0" y1="0" x2="0" y2="1">
                <stop offset="5%" stopColor="#0b4fc1" stopOpacity={0.3}/>
                <stop offset="95%" stopColor="#0b4fc1" stopOpacity={0.0}/>
              </linearGradient>
              <linearGradient id="splineOrange" x1="0" y1="0" x2="0" y2="1">
                <stop offset="5%" stopColor="#f25a59" stopOpacity={0.3}/>
                <stop offset="95%" stopColor="#f25a59" stopOpacity={0.0}/>
              </linearGradient>
            </defs>
            <XAxis dataKey="name" axisLine={false} tickLine={false} tick={{ fill: '#94a3b8', fontSize: 10 }} />
            <YAxis hide />
            <Tooltip formatter={(value, name) => [`Bs ${Number(value || 0).toLocaleString()}`, name === 'lorem' ? 'Actual' : 'Anterior']} />
            <Area type="natural" dataKey="lorem" stroke="#0b4fc1" strokeWidth={3} fillOpacity={1} fill="url(#splinePurple)" />
            <Area type="natural" dataKey="ipsum" stroke="#f25a59" strokeWidth={3} fillOpacity={1} fill="url(#splineOrange)" />
          </AreaChart>
        </ResponsiveContainer>
      </div>

      {/* Legend Row */}
      <div className="flex items-center justify-center gap-6 mt-2 text-xs font-semibold text-slate-400">
        <div className="flex items-center gap-2">
          <span className="w-3 h-0.5 bg-indigo-600 rounded-full"></span>
          <span>Actual</span>
        </div>
        <div className="flex items-center gap-2">
          <span className="w-3 h-0.5 bg-orange-500 rounded-full"></span>
          <span>Anterior</span>
        </div>
      </div>
    </div>
  );
}
