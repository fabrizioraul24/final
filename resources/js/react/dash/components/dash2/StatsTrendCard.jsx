import React, { useState } from 'react';
import { ArrowUp, ArrowDown, ChevronDown } from 'lucide-react';
import { useUser } from '../../context/UserContext';
import { useTheme } from '../../context/ThemeContext';

export default function StatsTrendCard() {
  const { metrics } = useUser();
  const { isDark } = useTheme();
  const [timeframe, setTimeframe] = useState('Semana');

  return (
    <div className={`h-full min-h-[270px] p-6 rounded-3xl border transition-all duration-300 flex flex-col justify-between ${
      isDark 
        ? 'bg-[#151722] border-fitonist-border-dark text-slate-100 shadow-xl' 
        : 'bg-white border-indigo-100/80 text-slate-800 shadow-sm'
    }`}>
      {/* Title & Filter Dropdown */}
      <div className="flex items-center justify-between mb-2">
        <h3 className="text-sm font-bold text-slate-400">Metricas Generales</h3>
        
        <div className="relative">
          <select
            value={timeframe}
            onChange={(e) => setTimeframe(e.target.value)}
            className={`appearance-none px-3 py-1 pr-7 rounded-full border text-xs font-bold focus:outline-none ${
              isDark ? 'bg-slate-800 border-slate-700 text-slate-300' : 'bg-indigo-50/60 border-indigo-200 text-indigo-700'
            }`}
          >
            <option value="Semana">Semana</option>
            <option value="Mes">Mes</option>
            <option value="Anio">Anio</option>
          </select>
          <ChevronDown size={14} className="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" />
        </div>
      </div>

      {/* Up Trend Stat */}
      <div className="flex flex-col">
        <div className="flex items-center gap-2">
          <ArrowUp size={22} className="text-indigo-500 stroke-[3]" />
          <span className="text-3xl font-extrabold tracking-tight">
            {metrics.activeGrowth}
          </span>
        </div>
        <p className="text-[11px] text-slate-400 mt-0.5 leading-snug">
          Usuarios activos registrados en el sistema
        </p>
      </div>

      <div className="border-t border-inherit my-1"></div>

      {/* Down Trend Stat */}
      <div className="flex flex-col">
        <div className="flex items-center gap-2">
          <ArrowDown size={22} className="text-rose-400 stroke-[3]" />
          <span className="text-3xl font-extrabold tracking-tight text-slate-700 dark:text-slate-300">
            {metrics.churnCount}
          </span>
        </div>
        <p className="text-[11px] text-slate-400 mt-0.5 leading-snug">
          Usuarios inactivos o suspendidos del sistema
        </p>
      </div>
    </div>
  );
}
