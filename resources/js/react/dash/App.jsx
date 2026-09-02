import React, { useState } from 'react';
import Sidebar from '../components/admin/Sidebar';
import Navbar from './components/Navbar';
import Dash2View from './components/dash2/Dash2View';
import { useTheme } from './context/ThemeContext';

export default function App({ layout, ...dashboardData }) {
  const { isDark } = useTheme();
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const sidebar = layout?.sidebar || { logoUrl: null, items: [] };

  return (
    <div className={`dashboard-shell transition-colors duration-300 font-sans ${
      isDark ? 'bg-[#0e0f14] text-slate-100' : 'bg-[#f4f6fc] text-slate-900'
    }`}>
      <Sidebar
        logoUrl={sidebar.logoUrl}
        items={sidebar.items}
        isOpen={sidebarOpen}
        onClose={() => setSidebarOpen(false)}
      />

      <main className="main-area">
        <Navbar />

        <section className="content-scroll pt-2">
          <div className="animate-in fade-in duration-300">
            <Dash2View data={dashboardData} />
          </div>
        </section>
      </main>
    </div>
  );
}
