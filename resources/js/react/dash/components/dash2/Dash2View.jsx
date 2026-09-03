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

export default function Dash2View({ data }) {
  return (
    <div className="w-full max-w-[1600px] mx-auto space-y-6">
      <div className="space-y-6">
        <div className="grid grid-cols-1 md:grid-cols-12 gap-6 items-stretch">
          <div className="md:col-span-3 h-full">
            <StatsTrendCard />
          </div>
          <div className="md:col-span-3 h-full">
            <CleanCalendarCard />
          </div>
          <div className="md:col-span-3 h-full">
            <MultiProgressCard data={data} />
          </div>
          <div className="md:col-span-3 h-full">
            <WaveLineChartCard data={data} />
          </div>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-12 gap-6 items-stretch">
          <div className="md:col-span-5 h-full">
            <SplineAreaCard data={data} />
          </div>
          <div className="md:col-span-3 h-full">
            <RadialProgressCard />
          </div>
          <div className="md:col-span-4 h-full">
            <RevenueDash2Card data={data} />
          </div>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-12 gap-6 items-stretch">
          <div className="md:col-span-4 h-full">
            <HorizontalBarsCard data={data} />
          </div>
          <div className="md:col-span-8 h-full">
            <DualBarChartCard data={data} />
          </div>
        </div>
      </div>
    </div>
  );
}
