import React from 'react';
import { useTheme } from '../../context/ThemeContext';

export default function HorizontalBarsCard() {
  const { isDark } = useTheme();

  const monthsData = [
    { month: 'OCT', segments: [{ w: '40%', c: 'bg-indigo-600' }, { w: '30%', c: 'bg-indigo-400' }, { w: '20%', c: 'bg-indigo-200' }] },
    { month: 'NOV', segments: [{ w: '50%', c: 'bg-indigo-600' }, { w: '25%', c: 'bg-indigo-300' }] },
    { month: 'DEC', segments: [{ w: '35%', c: 'bg-indigo-800' }, { w: '35%', c: 'bg-indigo-500' }, { w: '20%', c: 'bg-indigo-300' }] },
    { month: 'JAN', segments: [{ w: '60%', c: 'bg-indigo-600' }, { w: '25%', c: 'bg-indigo-400' }] },
  ];

  return (
    <div className={`p-6 rounded-3xl border transition-all duration-300 flex flex-col justify-between ${
      isDark 
        ? 'bg-[#151722] border-fitonist-border-dark text-slate-100 shadow-xl' 
        : 'bg-white border-indigo-100/80 text-slate-800 shadow-sm'
    }`}>
      <h3 className="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Tendencia Semestral</h3>

      <div className="space-y-3">
        {monthsData.map((row, idx) => (
          <div key={idx} className="flex items-center gap-3">
            <span className="text-[10px] font-bold text-slate-400 w-8">{row.month}</span>
            <div className={`flex-1 h-3.5 rounded-full overflow-hidden flex gap-0.5 ${isDark ? 'bg-slate-800' : 'bg-indigo-50'}`}>
              {row.segments.map((seg, sIdx) => (
                <div
                  key={sIdx}
                  className={`h-full ${seg.c}`}
                  style={{ width: seg.w }}
                ></div>
              ))}
            </div>
          </div>
        ))}
      </div>

      <div className="flex items-center justify-between text-[9px] font-bold text-slate-400 mt-3 pt-2 border-t border-inherit">
        <span>Lorem</span>
        <span>Ipsum</span>
        <span>Dolor</span>
        <span>Nonummy</span>
      </div>
    </div>
  );
}
