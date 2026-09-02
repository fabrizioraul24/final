import React, { useState } from 'react';
import { Apple, Play, Sparkles } from 'lucide-react';
import { INSTALLS_DATA } from '../data/mockData';
import { useTheme } from '../context/ThemeContext';

export default function InstallsCard() {
  const [timeframe, setTimeframe] = useState('Week');
  const { isDark } = useTheme();

  const currentData = INSTALLS_DATA[timeframe] || INSTALLS_DATA.Week;

  return (
    <div className={`p-6 rounded-3xl border transition-all duration-300 flex flex-col justify-between ${
      isDark 
        ? 'bg-fitonist-card-dark border-fitonist-border-dark text-slate-100 shadow-xl' 
        : 'bg-white border-fitonist-border-light text-slate-900 shadow-sm'
    }`}>
      {/* Header & Tabs */}
      <div className="flex items-center justify-between mb-4">
        <h3 className="text-lg font-bold tracking-tight">Installs</h3>

        <div className={`flex items-center p-1 rounded-full border ${
          isDark ? 'bg-[#1e212b] border-fitonist-border-dark' : 'bg-slate-100 border-slate-200'
        }`}>
          {['Today', 'Week', 'Month', 'Range'].map((tab) => {
            const isActive = timeframe === tab;
            return (
              <button
                key={tab}
                onClick={() => setTimeframe(tab)}
                className={`px-3 py-1 rounded-full text-xs font-bold transition-all duration-200 ${
                  isActive
                    ? 'bg-white text-slate-950 shadow-sm'
                    : isDark ? 'text-slate-400 hover:text-white' : 'text-slate-600 hover:text-slate-900'
                }`}
              >
                {tab}
              </button>
            );
          })}
        </div>
      </div>

      {/* Main Metric & Platform Badges */}
      <div className="flex items-center justify-between mb-6">
        <div>
          <span className="text-3xl font-extrabold tracking-tight">4,365</span>
          <span className="text-xs text-slate-400 font-medium block mt-0.5">This week</span>
        </div>

        {/* Platform breakdown badges (Apple & Google Play) */}
        <div className="flex items-center gap-2">
          {/* Apple Badge */}
          <div className={`flex items-center gap-2 px-3 py-1.5 rounded-2xl border ${
            isDark ? 'bg-[#1e212b] border-fitonist-border-dark' : 'bg-slate-100 border-slate-200'
          }`}>
            <div className="p-1 rounded-lg bg-fitonist-purple/20 text-fitonist-purple">
              <Apple size={14} />
            </div>
            <span className="text-xs font-extrabold">2,876</span>
          </div>

          {/* Play Store Badge */}
          <div className={`flex items-center gap-2 px-3 py-1.5 rounded-2xl border ${
            isDark ? 'bg-[#1e212b] border-fitonist-border-dark' : 'bg-slate-100 border-slate-200'
          }`}>
            <div className="p-1 rounded-lg bg-amber-400/20 text-amber-400">
              <Play size={14} className="fill-amber-400" />
            </div>
            <span className="text-xs font-extrabold">1,489</span>
          </div>
        </div>
      </div>

      {/* Custom Stacked Bar Chart with Striped Background & Highlight Badges */}
      <div className="h-44 flex items-end justify-between gap-2 pt-8">
        {currentData.map((item, index) => {
          const isHighlighted = item.highlighted;
          
          return (
            <div key={index} className="flex-1 flex flex-col items-center gap-2 h-full justify-end relative group">
              {/* Highlighted badges (e.g. 582 & 266 on Friday) */}
              {isHighlighted && item.appleBadge && (
                <div className="absolute -top-7 z-10 flex flex-col items-center gap-1 animate-bounce-slow">
                  <span className="px-2 py-0.5 rounded-lg bg-fitonist-purpleLight text-slate-950 font-black text-[10px] shadow-lg">
                    {item.appleBadge}
                  </span>
                </div>
              )}

              {/* Bar Stack Container */}
              <div className="w-full flex flex-col items-center h-32 justify-end">
                {isHighlighted ? (
                  /* Highlighted Active Bar Stack (Like Friday in image) */
                  <div className="w-full max-w-[42px] flex flex-col gap-1 h-full justify-end transition-transform duration-300 hover:scale-105">
                    {/* Top Segment (Purple) */}
                    <div className="w-full h-16 rounded-xl bg-fitonist-purpleLight flex items-center justify-center shadow-glow-purple">
                    </div>
                    {/* Bottom Segment (Yellow) */}
                    <div className="w-full h-12 rounded-xl bg-amber-300 flex items-center justify-center font-bold text-[10px] text-slate-900 shadow-sm">
                      {item.playBadge || 266}
                    </div>
                  </div>
                ) : (
                  /* Standard Dark Hatched/Striped Bar Stack */
                  <div className={`w-full max-w-[42px] rounded-2xl h-24 border transition-all duration-200 group-hover:border-fitonist-purple/60 ${
                    isDark 
                      ? 'bg-slate-800/40 border-fitonist-border-dark bg-striped-pattern' 
                      : 'bg-slate-200/60 border-slate-300 bg-striped-pattern-light'
                  }`}>
                  </div>
                )}
              </div>

              {/* Day Label */}
              <span className={`text-[11px] font-bold ${
                isHighlighted 
                  ? 'text-fitonist-purple' 
                  : 'text-slate-400'
              }`}>
                {item.day}
              </span>
            </div>
          );
        })}
      </div>
    </div>
  );
}
