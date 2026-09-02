import React, { useState } from 'react';
import { ChevronDown } from 'lucide-react';
import { ResponsiveContainer, AreaChart, Area, XAxis, YAxis, Tooltip } from 'recharts';
import { useTheme } from '../../context/ThemeContext';

export default function SplineAreaCard({ data: dashboardData }) {
  const { isDark } = useTheme();
  const [filter, setFilter] = useState('All time');

  const labels = dashboardData?.salesSeries?.labels || [];
  const values = dashboardData?.salesSeries?.data || [];
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
        <h3 className="text-sm font-bold tracking-tight">Ventas semanales</h3>

        <div className="relative">
          <select
            value={filter}
            onChange={(e) => setFilter(e.target.value)}
            className={`appearance-none px-3 py-1 pr-7 rounded-full border text-xs font-bold focus:outline-none ${
              isDark ? 'bg-slate-800 border-slate-700 text-slate-300' : 'bg-indigo-50/60 border-indigo-200 text-indigo-700'
            }`}
          >
            <option value="All time">All time</option>
            <option value="This year">This year</option>
          </select>
          <ChevronDown size={14} className="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" />
        </div>
      </div>

      {/* Large Spline Curve Area Chart */}
      <div className="h-48 w-full my-auto">
        <ResponsiveContainer width="100%" height="100%">
          <AreaChart data={data} margin={{ top: 10, right: 10, left: -25, bottom: 0 }}>
            <defs>
              <linearGradient id="splinePurple" x1="0" y1="0" x2="0" y2="1">
                <stop offset="5%" stopColor="#4f46e5" stopOpacity={0.3}/>
                <stop offset="95%" stopColor="#4f46e5" stopOpacity={0.0}/>
              </linearGradient>
              <linearGradient id="splineOrange" x1="0" y1="0" x2="0" y2="1">
                <stop offset="5%" stopColor="#f97316" stopOpacity={0.3}/>
                <stop offset="95%" stopColor="#f97316" stopOpacity={0.0}/>
              </linearGradient>
            </defs>
            <XAxis dataKey="name" axisLine={false} tickLine={false} tick={{ fill: '#94a3b8', fontSize: 10 }} />
            <YAxis hide />
            <Tooltip />
            <Area type="natural" dataKey="lorem" stroke="#4f46e5" strokeWidth={3} fillOpacity={1} fill="url(#splinePurple)" />
            <Area type="natural" dataKey="ipsum" stroke="#f97316" strokeWidth={3} fillOpacity={1} fill="url(#splineOrange)" />
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
