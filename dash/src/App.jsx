import React from 'react';
import Sidebar from './components/Sidebar';
import Navbar from './components/Navbar';
import Dash2View from './components/dash2/Dash2View';
import UserDirectoryView from './components/UserDirectoryView';
import UserFormModal from './components/UserFormModal';
import { useTheme } from './context/ThemeContext';
import { useUser } from './context/UserContext';

export default function App() {
  const { isDark } = useTheme();
  const { currentDashboard } = useUser();

  return (
    <div className={`min-h-screen transition-colors duration-300 font-sans flex ${
      isDark ? 'bg-[#0e0f14] text-slate-100' : 'bg-[#f4f6fc] text-slate-900'
    }`}>
      {/* Fixed Non-Collapsible Sidebar */}
      <Sidebar />

      {/* Main Content Area */}
      <div className="pl-64 flex-1 flex flex-col min-h-screen">
        {/* Top Navbar */}
        <Navbar />

        {/* Main View Area */}
        <main className="flex-1 p-8">
          {currentDashboard === 'dash2' ? (
            <div className="animate-in fade-in duration-300">
              <Dash2View />
            </div>
          ) : (
            <div className="animate-in fade-in duration-300">
              <UserDirectoryView />
            </div>
          )}
        </main>
      </div>

      {/* Quick User Creation Modal */}
      <UserFormModal />
    </div>
  );
}
