import React, { useState } from 'react';
import { ChevronDown } from 'lucide-react';
import { ResponsiveContainer, PieChart, Pie, Cell, Tooltip } from 'recharts';
import { useTheme } from '../../context/ThemeContext';

export default function DonutChartCard() {
  const { isDark } = useTheme();
  const [filter, setFilter] = useState('Mes');

  const data = [
    { name: 'Ventas (70%)', value: 70, color: '#f25a59' },
    { name: 'Stock (30%)', value: 30, color: '#0b4fc1' },
  ];

  return (
    <div className={`p-6 rounded-3xl border transition-all duration-300 flex flex-col justify-between ${
      isDark 
        ? 'bg-[#151722] border-fitonist-border-dark text-slate-100 shadow-xl' 
        : 'bg-white border-indigo-100/80 text-slate-800 shadow-sm'
    }`}>
      {/* Header & Filter */}
      <div className="flex items-center justify-between mb-2">
        <h3 className="text-sm font-bold tracking-tight">Distribucion</h3>

        <div className="relative">
          <select
            value={filter}
            onChange={(e) => setFilter(e.target.value)}
            className={`appearance-none px-3 py-1 pr-7 rounded-full border text-xs font-bold focus:outline-none ${
              isDark ? 'bg-slate-800 border-slate-700 text-slate-300' : 'bg-indigo-50/60 border-indigo-200 text-indigo-700'
            }`}
          >
            <option value="Mes">Mes</option>
            <option value="Anio">Anio</option>
          </select>
          <ChevronDown size={14} className="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" />
        </div>
      </div>

      {/* Donut Pie Chart with 70% / 30% center text */}
      <div className="relative h-36 w-full flex items-center justify-center">
        <ResponsiveContainer width="100%" height="100%">
          <PieChart>
            <Pie
              data={data}
              innerRadius={38}
              outerRadius={55}
              paddingAngle={4}
              dataKey="value"
            >
              {data.map((entry, index) => (
                <Cell key={`cell-${index}`} fill={entry.color} />
              ))}
            </Pie>
            <Tooltip />
          </PieChart>
        </ResponsiveContainer>

        <div className="absolute inset-0 flex flex-col items-center justify-center text-center pointer-events-none">
          <span className="text-xs font-black text-indigo-600 dark:text-indigo-400">30%</span>
          <span className="text-[9px] text-slate-400 font-semibold">Stock</span>
          <span className="text-sm font-black text-orange-500 mt-0.5">70%</span>
        </div>
      </div>
    </div>
  );
}
