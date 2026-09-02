import React from 'react';
import { useTheme } from '../../context/ThemeContext';

export default function MultiProgressCard({ data }) {
  const { isDark } = useTheme();
  const kpis = data?.kpis || {};
  const progressFrom = (value, total) => {
    if (!total) {
      return 0;
    }

    return Math.min(100, Math.round((Number(value || 0) / total) * 100));
  };
  const total = Math.max(
    Number(kpis.customers || 0),
    Number(kpis.products_active || 0),
    Number(kpis.transfers_active || 0),
    1,
  );

  const progressItems = [
    { label: 'Clientes', percent: progressFrom(kpis.customers, total), color: 'bg-indigo-400' },
    { label: 'Productos', percent: progressFrom(kpis.products_active, total), color: 'bg-rose-400' },
    { label: 'Traspasos', percent: progressFrom(kpis.transfers_active, total), color: 'bg-indigo-600' },
  ];

  return (
    <div className={`h-full min-h-[270px] p-6 rounded-3xl border transition-all duration-300 flex flex-col justify-between ${
      isDark 
        ? 'bg-[#151722] border-fitonist-border-dark text-slate-100 shadow-xl' 
        : 'bg-white border-indigo-100/80 text-slate-800 shadow-sm'
    }`}>
      <h3 className="text-sm font-bold text-slate-400 mb-2">Progreso General</h3>

      <div className="space-y-4 my-auto">
        {progressItems.map((item, idx) => (
          <div key={idx} className="space-y-1">
            <div className="flex justify-between text-xs font-semibold text-slate-400">
              <span>{item.label}</span>
              <span>{item.percent}%</span>
            </div>
            <div className={`w-full h-3 rounded-full overflow-hidden ${isDark ? 'bg-slate-800' : 'bg-indigo-50'}`}>
              <div
                className={`h-full rounded-full transition-all duration-500 ${item.color}`}
                style={{ width: `${item.percent}%` }}
              ></div>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}
