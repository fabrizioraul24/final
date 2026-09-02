import React from 'react';
import { useUser } from '../../context/UserContext';
import { useTheme } from '../../context/ThemeContext';

export default function RadialProgressCard() {
  const { metrics } = useUser();
  const { isDark } = useTheme();

  const percentage = metrics.radialProgress || 75;
  const circumference = 2 * Math.PI * 48;
  const strokeDashoffset = circumference - (percentage / 100) * circumference;

  return (
    <div className={`h-full min-h-[350px] p-6 rounded-3xl border transition-all duration-300 flex flex-col items-center justify-between text-center ${
      isDark 
        ? 'bg-[#151722] border-fitonist-border-dark text-slate-100 shadow-xl' 
        : 'bg-white border-indigo-100/80 text-slate-800 shadow-sm'
    }`}>
      <div className="w-full text-left mb-1">
        <h3 className="text-sm font-bold tracking-tight">Meta mensual</h3>
        <p className="text-[10px] text-slate-400">Avance de ventas del mes</p>
      </div>

      {/* SVG Radial Gauge */}
      <div className="relative w-32 h-32 flex items-center justify-center my-auto">
        <svg className="w-full h-full transform -rotate-90" viewBox="0 0 120 120">
          <circle
            cx="60"
            cy="60"
            r="48"
            stroke="currentColor"
            strokeWidth="10"
            className={isDark ? 'text-slate-800' : 'text-indigo-50'}
            fill="transparent"
          />
          <circle
            cx="60"
            cy="60"
            r="48"
            stroke="currentColor"
            strokeWidth="10"
            strokeDasharray={circumference}
            strokeDashoffset={strokeDashoffset}
            strokeLinecap="round"
            className="text-indigo-600 transition-all duration-1000 ease-out"
            fill="transparent"
          />
        </svg>

        <span className="absolute text-2xl font-black tracking-tight font-sans">
          {percentage}%
        </span>
      </div>

      <p className="text-[11px] text-slate-400 max-w-[200px] mb-3">
        Progreso calculado con ventas registradas en la base de datos
      </p>

      {/* Action Button "Suscipit" matching mockup */}
      <button className="w-full py-2.5 px-6 rounded-full bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold text-xs shadow-md transition-all">
        Ventas
      </button>
    </div>
  );
}
