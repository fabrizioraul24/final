import React, { useState } from 'react';
import { Clock, PieChart } from 'lucide-react';
import { useTheme } from '../context/ThemeContext';

export default function AgeRangeCard() {
  const { isDark } = useTheme();
  const [activeGroup, setActiveGroup] = useState(null);

  const demographics = [
    {
      id: 'group_1',
      percentage: '46%',
      label: '18-30 years',
      color: 'bg-fitonist-purple text-white shadow-glow-purple',
      size: 'w-28 h-28',
      position: 'top-2 left-2 z-20',
      value: 46
    },
    {
      id: 'group_2',
      percentage: '32%',
      label: '31-45 years',
      color: 'bg-amber-300 text-slate-950 shadow-md',
      size: 'w-24 h-24',
      position: 'bottom-2 right-4 z-30',
      value: 32
    },
    {
      id: 'group_3',
      percentage: '18%',
      label: '46-60 years',
      color: 'bg-cyan-400 text-slate-950 shadow-md',
      size: 'w-16 h-16',
      position: 'top-0 right-10 z-10',
      value: 18
    },
    {
      id: 'group_4',
      percentage: '4%',
      label: '> 60 years',
      color: 'bg-emerald-400 text-slate-950 shadow-md',
      size: 'w-10 h-10',
      position: 'right-0 top-1/2 -translate-y-1/2 z-10',
      value: 4
    }
  ];

  return (
    <div className={`p-6 rounded-3xl border transition-all duration-300 flex flex-col justify-between ${
      isDark 
        ? 'bg-fitonist-card-dark border-fitonist-border-dark text-slate-100 shadow-xl' 
        : 'bg-white border-fitonist-border-light text-slate-900 shadow-sm'
    }`}>
      {/* Header */}
      <div className="flex items-center justify-between mb-2">
        <h3 className="text-lg font-bold tracking-tight">Age range</h3>

        <button className={`p-2 rounded-full border transition-all ${
          isDark 
            ? 'bg-[#1e212b] border-fitonist-border-dark text-slate-400 hover:text-white' 
            : 'bg-slate-100 border-slate-200 text-slate-600 hover:text-slate-900'
        }`}>
          <Clock size={16} />
        </button>
      </div>

      {/* Interactive Overlapping Bubble Demographics Chart */}
      <div className="relative h-48 w-full flex items-center justify-center">
        {demographics.map((item) => {
          const isSelected = activeGroup === item.id;

          return (
            <div
              key={item.id}
              onMouseEnter={() => setActiveGroup(item.id)}
              onMouseLeave={() => setActiveGroup(null)}
              className={`absolute rounded-full flex flex-col items-center justify-center cursor-pointer transition-all duration-300 transform hover:scale-110 ${item.size} ${item.color} ${item.position} ${
                isSelected ? 'ring-4 ring-white/50 scale-110 z-40' : ''
              }`}
            >
              <span className="font-extrabold text-sm leading-none">{item.percentage}</span>
              <span className="text-[9px] font-bold opacity-80 mt-0.5 whitespace-nowrap">{item.label}</span>
            </div>
          );
        })}
      </div>

      {/* Selected Group Description */}
      <div className={`p-3 rounded-2xl border text-center transition-all ${
        isDark ? 'bg-[#1e212b]/60 border-fitonist-border-dark' : 'bg-slate-100 border-slate-200'
      }`}>
        <p className="text-xs text-slate-400 font-medium">
          {activeGroup 
            ? `Grupo seleccionado: ${demographics.find(d => d.id === activeGroup)?.label} (${demographics.find(d => d.id === activeGroup)?.percentage} de usuarios totales)`
            : 'Pasa el cursor sobre los círculos para ver detalles de la audiencia'}
        </p>
      </div>
    </div>
  );
}
