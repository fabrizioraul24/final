import React from 'react';
import { useTheme } from '../../context/ThemeContext';

export default function MultiProgressCard() {
  const { isDark } = useTheme();

  const progressItems = [
    { label: 'Consectetuer', percent: 65, color: 'bg-indigo-400' },
    { label: 'Adipiscing', percent: 85, color: 'bg-rose-400' },
    { label: 'Sit amet', percent: 45, color: 'bg-indigo-600' },
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
