import React from 'react';
import StatsTrendCard from './StatsTrendCard';
import CleanCalendarCard from './CleanCalendarCard';
import MultiProgressCard from './MultiProgressCard';
import WaveLineChartCard from './WaveLineChartCard';
import SplineAreaCard from './SplineAreaCard';
import RadialProgressCard from './RadialProgressCard';
import HorizontalBarsCard from './HorizontalBarsCard';
import DualBarChartCard from './DualBarChartCard';
import RevenueDash2Card from './RevenueDash2Card';
import { useTheme } from '../../context/ThemeContext';
import { Sparkles } from 'lucide-react';

export default function Dash2View() {
  const { isDark } = useTheme();

  return (
    <div className="w-full max-w-[1600px] mx-auto space-y-6">
      {/* Header Banner */}
      <div className={`p-4 rounded-2xl border flex items-center justify-between transition-all ${
        isDark 
          ? 'bg-gradient-to-r from-indigo-900/30 via-slate-900 to-fitonist-card-dark border-fitonist-border-dark' 
          : 'bg-gradient-to-r from-indigo-100/70 via-white to-purple-50 border-indigo-200 shadow-sm'
      }`}>
        <div className="flex items-center gap-3">
          <div className="p-2 rounded-xl bg-indigo-600 text-white shadow-md">
            <Sparkles size={18} />
          </div>
          <div>
            <h2 className="text-sm font-extrabold tracking-tight">
              Dashboard 2 - Consola Simétrica Soft Indigo
            </h2>
            <p className="text-xs text-slate-400 font-medium">
              Distribución simétrica de tarjetas con alineación vertical perfecta.
            </p>
          </div>
        </div>

        <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-indigo-500/20 text-indigo-400 border border-indigo-500/30">
          <span className="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></span>
          Dashboard 2 Activo
        </span>
      </div>

      {/* Main Grid Layout with Symmetric Row Alignments */}
      <div className="space-y-6">
        {/* Row 1: Symmetrical Top Cards (Metricas Generales, January, Progreso General, Commodo) */}
        <div className="grid grid-cols-1 md:grid-cols-12 gap-6 items-stretch">
          <div className="md:col-span-3 h-full">
            <StatsTrendCard />
          </div>
          <div className="md:col-span-3 h-full">
            <CleanCalendarCard />
          </div>
          <div className="md:col-span-3 h-full">
            <MultiProgressCard />
          </div>
          <div className="md:col-span-3 h-full">
            <WaveLineChartCard />
          </div>
        </div>

        {/* Row 2: Symmetrical Middle Cards (Dolor sit amet, Radial Progress, Revenue) */}
        <div className="grid grid-cols-1 md:grid-cols-12 gap-6 items-stretch">
          <div className="md:col-span-5 h-full">
            <SplineAreaCard />
          </div>
          <div className="md:col-span-3 h-full">
            <RadialProgressCard />
          </div>
          <div className="md:col-span-4 h-full">
            <RevenueDash2Card />
          </div>
        </div>

        {/* Row 3: Symmetrical Bottom Cards */}
        <div className="grid grid-cols-1 md:grid-cols-12 gap-6 items-stretch">
          <div className="md:col-span-4 h-full">
            <HorizontalBarsCard />
          </div>
          <div className="md:col-span-8 h-full">
            <DualBarChartCard />
          </div>
        </div>
      </div>
    </div>
  );
}
