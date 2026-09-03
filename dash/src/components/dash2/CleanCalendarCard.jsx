import React, { useState } from 'react';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import { useTheme } from '../../context/ThemeContext';

export default function CleanCalendarCard() {
  const { isDark } = useTheme();
  const [selectedDay, setSelectedDay] = useState(14);

  const days = Array.from({ length: 31 }, (_, i) => i + 1);
  const weekDays = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];
  const circledDays = [14, 25, 28];

  return (
    <div className={`h-full min-h-[270px] p-6 rounded-3xl border transition-all duration-300 flex flex-col justify-between ${
      isDark 
        ? 'bg-[#151722] border-fitonist-border-dark text-slate-100 shadow-xl' 
        : 'bg-white border-indigo-100/80 text-slate-800 shadow-sm'
    }`}>
      {/* Month Header */}
      <div className="flex items-center justify-center gap-4 mb-2">
        <button className="text-slate-400 hover:text-indigo-500 transition-colors">
          <ChevronLeft size={16} />
        </button>
        <h3 className="text-sm font-bold tracking-wide">January</h3>
        <button className="text-slate-400 hover:text-indigo-500 transition-colors">
          <ChevronRight size={16} />
        </button>
      </div>

      {/* Week Header */}
      <div className="grid grid-cols-7 gap-1 text-center mb-1">
        {weekDays.map((d) => (
          <span key={d} className="text-[10px] font-bold text-slate-400 uppercase">
            {d}
          </span>
        ))}
      </div>

      {/* Calendar Grid */}
      <div className="grid grid-cols-7 gap-1 text-center flex-1 items-center">
        {days.map((day) => {
          const isCircled = circledDays.includes(day);
          const isSelected = selectedDay === day;

          return (
            <button
              key={day}
              onClick={() => setSelectedDay(day)}
              className={`h-6 w-6 mx-auto rounded-full text-xs font-semibold flex items-center justify-center transition-all ${
                isSelected
                  ? 'bg-indigo-600 text-white shadow-md'
                  : isCircled
                    ? 'border-2 border-indigo-400 font-bold text-indigo-500'
                    : isDark ? 'text-slate-400 hover:text-white' : 'text-slate-600 hover:text-indigo-600'
              }`}
            >
              {day}
            </button>
          );
        })}
      </div>
    </div>
  );
}
