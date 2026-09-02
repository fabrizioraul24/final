import React, { useState } from 'react';
import { TrendingUp } from 'lucide-react';
import { ResponsiveContainer, AreaChart, Area, XAxis, YAxis, Tooltip } from 'recharts';
import { useUser } from '../../context/UserContext';
import { useTheme } from '../../context/ThemeContext';

export default function RevenueDash2Card({ data }) {
  const [timeframe, setTimeframe] = useState('Month');
  const { metrics } = useUser();
  const { isDark } = useTheme();
  const labels = data?.salesSeries?.labels || [];
  const values = data?.salesSeries?.data || [];
  const liveSeries = labels.map((day, index) => ({
    day,
    value: Number(values[index] || 0),
    line2: Number(values[index - 1] || 0),
  }));

  // Dynamic X-axis ticks: 4, 8, 12, 16, 20, 24, 28, 31
  const monthData = [
    { day: '4', value: 3900, line2: 2400 },
    { day: '8', value: 2400, line2: 1900 },
    { day: '12', value: 4800, line2: 3700 },
    { day: '16', value: 3200, line2: 2600 },
    { day: '20', value: 5100, line2: 4300 },
    { day: '24', value: 4900, line2: 3900 },
    { day: '28', value: 4100, line2: 3200 },
    { day: '31', value: 5400, line2: 4100 },
  ];

  const todayData = [
    { day: '4', value: 1200, line2: 900 },
    { day: '8', value: 2100, line2: 1400 },
    { day: '12', value: 3800, line2: 2600 },
    { day: '16', value: 6500, line2: 4800 },
    { day: '20', value: 5200, line2: 3900 },
    { day: '24', value: 7100, line2: 5400 },
    { day: '28', value: 4300, line2: 3200 },
    { day: '31', value: 6000, line2: 4500 },
  ];

  const weekData = [
    { day: '4', value: 12400, line2: 8900 },
    { day: '8', value: 14500, line2: 10200 },
    { day: '12', value: 18200, line2: 13400 },
    { day: '16', value: 16800, line2: 12100 },
    { day: '20', value: 24500, line2: 17800 },
    { day: '24', value: 28900, line2: 21000 },
    { day: '28', value: 22100, line2: 16500 },
    { day: '31', value: 26000, line2: 19000 },
  ];

  const rangeData = [
    { day: '4', value: 65000, line2: 48000 },
    { day: '8', value: 72000, line2: 53000 },
    { day: '12', value: 79675, line2: 59000 },
    { day: '16', value: 84000, line2: 63000 },
    { day: '20', value: 88000, line2: 66000 },
    { day: '24', value: 92000, line2: 70000 },
    { day: '28', value: 95000, line2: 72000 },
    { day: '31', value: 99000, line2: 75000 },
  ];

  const getData = () => {
    if (liveSeries.length) {
      return liveSeries;
    }

    switch (timeframe) {
      case 'Today': return todayData;
      case 'Week': return weekData;
      case 'Range': return rangeData;
      default: return monthData;
    }
  };

  return (
    <div className={`h-full min-h-[350px] p-6 rounded-3xl border transition-all duration-300 flex flex-col justify-between ${
      isDark 
        ? 'bg-[#151722] border-fitonist-border-dark text-slate-100 shadow-xl' 
        : 'bg-white border-indigo-100/80 text-slate-800 shadow-sm'
    }`}>
      {/* Top Header & Timeframe Pills */}
      <div className="flex items-center justify-between mb-2">
        <h3 className="text-lg font-bold tracking-tight">Revenue</h3>

        <div className={`flex items-center p-1 rounded-full border ${
          isDark ? 'bg-slate-800 border-slate-700' : 'bg-indigo-50/70 border-indigo-100'
        }`}>
          {['Today', 'Week', 'Month', 'Range'].map((tab) => {
            const isActive = timeframe === tab;
            return (
              <button
                key={tab}
                onClick={() => setTimeframe(tab)}
                className={`px-3 py-1 rounded-full text-xs font-bold transition-all duration-200 ${
                  isActive
                    ? 'bg-indigo-600 text-white shadow-sm'
                    : isDark ? 'text-slate-400 hover:text-white' : 'text-slate-600 hover:text-indigo-600'
                }`}
              >
                {tab}
              </button>
            );
          })}
        </div>
      </div>

      {/* Metrics Row */}
      <div className="flex items-center gap-6 mb-2">
        {/* Metric 1 */}
        <div className="flex flex-col">
          <div className="flex items-center gap-2">
            <span className="text-2xl font-extrabold tracking-tight">
              {Number(metrics.revenue || 0).toLocaleString()}
            </span>
            <span className="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-500 border border-emerald-500/30">
              <TrendingUp size={11} />
              {metrics.revenueGrowth}
            </span>
          </div>
          <span className="text-xs text-slate-400 font-medium mt-0.5">This month</span>
        </div>

        {/* Metric 2 */}
        <div className="flex flex-col">
          <div className="flex items-center gap-2">
            <span className="text-2xl font-extrabold tracking-tight">
              {metrics.dailySubs}
            </span>
            <span className="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-500 border border-emerald-500/30">
              <TrendingUp size={11} />
              {metrics.dailySubsGrowth}
            </span>
          </div>
          <span className="text-xs text-slate-400 font-medium mt-0.5">Daily subscriptions</span>
        </div>
      </div>

      {/* Chart with X-Axis Ticks */}
      <div className="h-44 w-full relative my-auto">
        <ResponsiveContainer width="100%" height="100%">
          <AreaChart data={getData()} margin={{ top: 5, right: 10, left: -25, bottom: 0 }}>
            <defs>
              <linearGradient id="dash2Indigo" x1="0" y1="0" x2="0" y2="1">
                <stop offset="5%" stopColor="#4f46e5" stopOpacity={0.35}/>
                <stop offset="95%" stopColor="#4f46e5" stopOpacity={0.0}/>
              </linearGradient>
              <linearGradient id="dash2Coral" x1="0" y1="0" x2="0" y2="1">
                <stop offset="5%" stopColor="#f97316" stopOpacity={0.25}/>
                <stop offset="95%" stopColor="#f97316" stopOpacity={0.0}/>
              </linearGradient>
            </defs>
            <XAxis 
              dataKey="day" 
              axisLine={false} 
              tickLine={false} 
              tick={{ fill: '#94a3b8', fontSize: 11, fontWeight: 700 }}
            />
            <YAxis hide domain={['auto', 'auto']} />
            <Tooltip />
            <Area 
              type="natural" 
              dataKey="value" 
              stroke="#4f46e5" 
              strokeWidth={3} 
              fillOpacity={1} 
              fill="url(#dash2Indigo)" 
            />
            <Area 
              type="natural" 
              dataKey="line2" 
              stroke="#f97316" 
              strokeWidth={2.5} 
              strokeDasharray="3 3"
              fillOpacity={1} 
              fill="url(#dash2Coral)" 
            />
          </AreaChart>
        </ResponsiveContainer>
      </div>
    </div>
  );
}
