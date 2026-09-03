import React, { useState } from 'react';
import { ChevronDown, ArrowUpRight, BarChart2 } from 'lucide-react';
import { useTheme } from '../context/ThemeContext';

export default function CalendarCard() {
  const { isDark } = useTheme();
  const [selectedMonth, setSelectedMonth] = useState('July 2024');
  const [selectedRange, setSelectedRange] = useState({ start: 13, end: 21 });
  
  const daysInMonth = Array.from({ length: 31 }, (_, i) => i + 1);

  const handleDateClick = (day) => {
    if (day < selectedRange.start) {
      setSelectedRange({ start: day, end: selectedRange.end });
    } else {
      setSelectedRange({ start: selectedRange.start, end: day });
    }
  };

  // Calculated revenue based on selected days range
  const totalDaysSelected = selectedRange.end - selectedRange.start + 1;
  const calculatedRevenue = 18434 + (totalDaysSelected - 9) * 1250;

  return (
    <div className={`p-5 rounded-3xl border transition-all duration-300 flex flex-col justify-between ${
      isDark 
        ? 'bg-fitonist-card-dark border-fitonist-border-dark text-slate-100 shadow-xl' 
        : 'bg-white border-fitonist-border-light text-slate-900 shadow-sm'
    }`}>
      {/* Top Bar */}
      <div className="flex items-center justify-between mb-4">
        <div className={`flex items-center gap-2 px-3 py-1.5 rounded-full border text-xs font-bold ${
          isDark ? 'bg-[#1e212b] border-fitonist-border-dark text-slate-300' : 'bg-slate-100 border-slate-200 text-slate-700'
        }`}>
          <span>{selectedMonth}</span>
          <ChevronDown size={14} className="text-slate-400" />
        </div>

        <button className={`p-2 rounded-full border transition-all ${
          isDark 
            ? 'bg-[#1e212b] border-fitonist-border-dark text-slate-400 hover:text-white' 
            : 'bg-slate-100 border-slate-200 text-slate-600 hover:text-slate-900'
        }`}>
          <ArrowUpRight size={16} />
        </button>
      </div>

      {/* Calendar Grid */}
      <div className="grid grid-cols-7 gap-1 text-center mb-4">
        {daysInMonth.map((day) => {
          const isHighlighted = day >= selectedRange.start && day <= selectedRange.end;
          const isStart = day === selectedRange.start;
          const isEnd = day === selectedRange.end;

          return (
            <button
              key={day}
              onClick={() => handleDateClick(day)}
              className={`h-8 rounded-lg text-xs font-bold transition-all duration-200 flex items-center justify-center ${
                isHighlighted
                  ? 'bg-fitonist-purple text-white shadow-sm scale-[1.05]'
                  : isDark
                    ? 'text-slate-400 hover:bg-slate-800 hover:text-white'
                    : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'
              }`}
            >
              {day}
            </button>
          );
        })}
      </div>

      {/* Bottom Metric Pill ($18,434) */}
      <div className={`p-3.5 rounded-2xl border flex items-center justify-between ${
        isDark ? 'bg-[#1e212b]/80 border-fitonist-border-dark' : 'bg-slate-100 border-slate-200'
      }`}>
        <span className="text-xl font-extrabold tracking-tight">
          ${calculatedRevenue.toLocaleString()}
        </span>
        <div className="p-2 rounded-xl bg-fitonist-purple/20 text-fitonist-purple">
          <BarChart2 size={18} />
        </div>
      </div>
    </div>
  );
}
