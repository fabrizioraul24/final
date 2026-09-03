import React, { useState } from 'react';
import { ArrowUpRight, Globe, MapPin } from 'lucide-react';
import { COUNTRY_IMPRESSIONS } from '../data/mockData';
import { useTheme } from '../context/ThemeContext';
import { useUser } from '../context/UserContext';

export default function ImpressionsMapCard() {
  const { isDark } = useTheme();
  const { metrics } = useUser();
  const [selectedCountry, setSelectedCountry] = useState(null);

  return (
    <div className={`p-6 rounded-3xl border transition-all duration-300 flex flex-col lg:flex-row gap-6 justify-between ${
      isDark 
        ? 'bg-fitonist-card-dark border-fitonist-border-dark text-slate-100 shadow-xl' 
        : 'bg-white border-fitonist-border-light text-slate-900 shadow-sm'
    }`}>
      {/* Left Column: Title & Animated Map */}
      <div className="flex-1 flex flex-col justify-between">
        <div className="flex items-center justify-between mb-2">
          <h3 className="text-lg font-bold tracking-tight">Impressions</h3>

          {/* Metric Pill: 231,841 Impressions worldwide */}
          <div className="flex items-center gap-3">
            <div className="text-right">
              <span className="text-xl font-extrabold tracking-tight">
                {metrics.impressions.toLocaleString()}
              </span>
              <span className="text-xs text-slate-400 font-medium block">
                Impressions worldwide
              </span>
            </div>
            <button className={`p-2 rounded-full border transition-all ${
              isDark 
                ? 'bg-[#1e212b] border-fitonist-border-dark text-slate-400 hover:text-white' 
                : 'bg-slate-100 border-slate-200 text-slate-600 hover:text-slate-900'
            }`}>
              <ArrowUpRight size={16} />
            </button>
          </div>
        </div>

        {/* SVG World Map Graphics with glowing purple node clusters matching exact design */}
        <div className="relative h-44 w-full flex items-center justify-center my-2 overflow-hidden rounded-2xl bg-slate-900/30 p-2 border border-slate-800/40">
          <svg viewBox="0 0 800 400" className="w-full h-full opacity-60">
            {/* World Continent Paths (Simplified Stylized SVG) */}
            <g fill={isDark ? "#334155" : "#cbd5e1"}>
              {/* North America */}
              <path d="M 120,80 Q 200,60 250,110 Q 220,180 140,160 Q 80,120 120,80 Z" />
              {/* South America */}
              <path d="M 220,200 Q 260,220 250,300 Q 200,320 190,240 Z" />
              {/* Europe */}
              <path d="M 380,80 Q 480,70 470,140 Q 400,160 380,80 Z" />
              {/* Africa */}
              <path d="M 390,160 Q 490,170 470,280 Q 400,270 390,160 Z" />
              {/* Asia */}
              <path d="M 490,80 Q 680,60 690,180 Q 560,200 490,80 Z" />
              {/* Australia */}
              <path d="M 640,250 Q 720,240 710,310 Q 630,310 640,250 Z" />
            </g>
          </svg>

          {/* Pulsing Glowing Purple Heatmap Nodes */}
          {/* USA Node */}
          <div className="absolute top-1/3 left-[24%] flex items-center justify-center">
            <span className="w-4 h-4 bg-fitonist-purple rounded-full animate-ping opacity-75"></span>
            <span className="absolute w-2.5 h-2.5 bg-fitonist-purpleLight rounded-full shadow-glow-purple"></span>
          </div>

          {/* Europe / Germany / Spain Node */}
          <div className="absolute top-1/3 left-[52%] flex items-center justify-center">
            <span className="w-4 h-4 bg-fitonist-purple rounded-full animate-ping opacity-75"></span>
            <span className="absolute w-2.5 h-2.5 bg-fitonist-purpleLight rounded-full shadow-glow-purple"></span>
          </div>

          {/* Australia Node */}
          <div className="absolute bottom-[28%] right-[18%] flex items-center justify-center">
            <span className="w-4 h-4 bg-fitonist-purple rounded-full animate-ping opacity-75"></span>
            <span className="absolute w-2.5 h-2.5 bg-fitonist-purpleLight rounded-full shadow-glow-purple"></span>
          </div>
        </div>
      </div>

      {/* Right Column: Country Breakdown List */}
      <div className="w-full lg:w-64 flex flex-col justify-center space-y-2.5">
        {COUNTRY_IMPRESSIONS.map((item) => {
          const isSelected = selectedCountry === item.code;
          return (
            <div
              key={item.code}
              onClick={() => setSelectedCountry(item.code === selectedCountry ? null : item.code)}
              className={`p-3 rounded-2xl border transition-all duration-200 cursor-pointer flex items-center justify-between ${
                isSelected 
                  ? 'border-fitonist-purple bg-fitonist-purple/10 shadow-glow-purple' 
                  : isDark 
                    ? 'bg-[#1e212b]/60 border-fitonist-border-dark hover:bg-[#1e212b]' 
                    : 'bg-slate-50 border-slate-200 hover:bg-slate-100'
              }`}
            >
              <div className="flex items-center gap-3">
                <span className="text-xl">{item.flag}</span>
                <span className="text-xs font-bold">{item.country}</span>
              </div>

              <span className="text-xs font-extrabold tracking-tight">
                {item.count}
              </span>
            </div>
          );
        })}
      </div>
    </div>
  );
}
