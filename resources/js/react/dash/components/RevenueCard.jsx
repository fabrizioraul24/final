import React, { useState } from 'react';
import { TrendingUp, ArrowUpRight } from 'lucide-react';
import { ResponsiveContainer, AreaChart, Area, XAxis, YAxis, Tooltip, ReferenceLine } from 'recharts';
import { REVENUE_TIMEFRAME_DATA } from '../data/mockData';
import { useUser } from '../context/UserContext';
import { useTheme } from '../context/ThemeContext';

export default function RevenueCard() {
  const [timeframe, setTimeframe] = useState('Month');
  const tabs = [
    { key: 'Today', label: 'Hoy' },
    { key: 'Week', label: 'Semana' },
    { key: 'Month', label: 'Mes' },
    { key: 'Range', label: 'Rango' },
  ];
  const { metrics } = useUser();
  const { isDark } = useTheme();

  const data = REVENUE_TIMEFRAME_DATA[timeframe] || REVENUE_TIMEFRAME_DATA.Month;

  // Custom sleek tooltip matching image ($2,894 and 287 badge)
  const CustomTooltip = ({ active, payload }) => {
    if (active && payload && payload.length) {
      const val1 = payload[0].value;
      const val2 = payload[1]?.value || 287;
      return (
        <div className="flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-900/90 text-white text-xs font-bold border border-slate-700 shadow-xl backdrop-blur-md">
          <span className="flex items-center gap-1 text-fitonist-purple">
            <span className="w-2 h-2 rounded-full bg-fitonist-purple"></span>
            ${val1.toLocaleString()}
          </span>
          <span className="text-slate-500">•</span>
          <span className="flex items-center gap-1 text-amber-400">
            <span className="w-2 h-2 rounded-full bg-amber-400"></span>
            {val2}
          </span>
        </div>
      );
    }
    return null;
  };

  return (
    <div className={`p-6 rounded-3xl border transition-all duration-300 flex flex-col justify-between ${
      isDark 
        ? 'bg-fitonist-card-dark border-fitonist-border-dark text-slate-100 shadow-xl' 
        : 'bg-white border-fitonist-border-light text-slate-900 shadow-sm'
    }`}>
      {/* Top Header & Filters */}
      <div className="flex items-center justify-between mb-6">
        <h3 className="text-lg font-bold tracking-tight">Ingresos</h3>
        
        <div className={`flex items-center p-1 rounded-full border ${
          isDark ? 'bg-[#1e212b] border-fitonist-border-dark' : 'bg-slate-100 border-slate-200'
        }`}>
          {tabs.map((tab) => {
            const isActive = timeframe === tab.key;
            return (
              <button
                key={tab.key}
                onClick={() => setTimeframe(tab.key)}
                className={`px-3.5 py-1 rounded-full text-xs font-bold transition-all duration-200 ${
                  isActive
                    ? 'bg-white text-slate-950 shadow-sm'
                    : isDark ? 'text-slate-400 hover:text-white' : 'text-slate-600 hover:text-slate-900'
                }`}
              >
                {tab.label}
              </button>
            );
          })}
        </div>
      </div>

      {/* Metrics Row */}
      <div className="flex items-center gap-8 mb-4">
        {/* Metric 1 */}
        <div className="flex flex-col">
          <div className="flex items-center gap-2">
            <span className="text-3xl font-extrabold tracking-tight">
              {metrics.revenue.toLocaleString()}
            </span>
            <span className="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
              <TrendingUp size={12} />
              {metrics.revenueGrowth}
            </span>
          </div>
          <span className="text-xs text-slate-400 font-medium mt-0.5">Este mes</span>
        </div>

        {/* Metric 2 */}
        <div className="flex flex-col">
          <div className="flex items-center gap-2">
            <span className="text-3xl font-extrabold tracking-tight">
              {metrics.dailySubs}
            </span>
            <span className="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
              <TrendingUp size={12} />
              {metrics.dailySubsGrowth}
            </span>
          </div>
          <span className="text-xs text-slate-400 font-medium mt-0.5">Ventas del dia</span>
        </div>
      </div>

      {/* Chart Container */}
      <div className="h-44 w-full relative mt-2">
        <ResponsiveContainer width="100%" height="100%">
          <AreaChart data={data} margin={{ top: 10, right: 10, left: -25, bottom: 0 }}>
            <defs>
              <linearGradient id="purpleGlow" x1="0" y1="0" x2="0" y2="1">
                <stop offset="5%" stopColor="#0b4fc1" stopOpacity={0.35}/>
                <stop offset="95%" stopColor="#0b4fc1" stopOpacity={0.0}/>
              </linearGradient>
              <linearGradient id="goldGlow" x1="0" y1="0" x2="0" y2="1">
                <stop offset="5%" stopColor="#f25a59" stopOpacity={0.25}/>
                <stop offset="95%" stopColor="#f25a59" stopOpacity={0.0}/>
              </linearGradient>
            </defs>
            <XAxis 
              dataKey="day" 
              axisLine={false} 
              tickLine={false} 
              tick={{ fill: '#94a3b8', fontSize: 11, fontWeight: 600 }}
            />
            <YAxis hide domain={['auto', 'auto']} />
            <Tooltip content={<CustomTooltip />} />
            <Area 
              type="natural" 
              dataKey="value" 
              stroke="#0b4fc1" 
              strokeWidth={3} 
              fillOpacity={1} 
              fill="url(#purpleGlow)" 
            />
            <Area 
              type="natural" 
              dataKey="line2" 
              stroke="#f25a59" 
              strokeWidth={2.5} 
              strokeDasharray="4 4"
              fillOpacity={1} 
              fill="url(#goldGlow)" 
            />
          </AreaChart>
        </ResponsiveContainer>
      </div>
    </div>
  );
}
