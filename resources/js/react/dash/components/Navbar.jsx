import React, { useState } from 'react';
import {
  Search, 
  Sun, 
  Moon, 
  LogOut,
  Bell, 
  ChevronDown
} from 'lucide-react';
import { useUser } from '../context/UserContext';
import { useTheme } from '../context/ThemeContext';
import NotificationsPopover from './NotificationsPopover';

export default function Navbar({ csrfToken, logoutAction }) {
  const { 
    searchQuery, 
    setSearchQuery, 
    currentUser,
  } = useUser();
  const { isDark, toggleTheme } = useTheme();
  const [showProfileMenu, setShowProfileMenu] = useState(false);
  const [showNotifications, setShowNotifications] = useState(false);
  const initials = currentUser.name
    ?.split(' ')
    .filter(Boolean)
    .slice(0, 2)
    .map((part) => part[0])
    .join('')
    .toUpperCase() || 'US';

  return (
    <header className="w-full flex items-center justify-between py-4 px-8 gap-4">
      <div className={`hidden sm:flex items-center gap-2 px-4 py-2 rounded-full border text-sm font-black ${
        isDark
          ? 'bg-[#151722] border-fitonist-border-dark text-white'
          : 'bg-white border-[#0b4fc1]/20 text-[#0b4fc1] shadow-sm'
      }`}>
        <span>Dashboard Pil La Paz</span>
      </div>

      {/* Right Controls */}
      <div className="flex items-center gap-3">
        {/* Global Search Bar */}
        <div className={`relative hidden sm:flex items-center w-64 rounded-full transition-all border ${
          isDark 
            ? 'bg-[#151722] border-fitonist-border-dark text-slate-200 focus-within:border-indigo-500' 
            : 'bg-white border-indigo-200 text-slate-800 focus-within:border-indigo-500 shadow-sm'
        }`}>
          <Search size={16} className="absolute left-3.5 text-slate-400" />
          <input
            type="text"
            placeholder="Buscar..."
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
            className="w-full pl-9 pr-4 py-2 bg-transparent text-xs font-medium focus:outline-none placeholder:text-slate-400"
          />
        </div>

        {/* Theme Switcher */}
        <button
          onClick={toggleTheme}
          className={`p-2.5 rounded-full border transition-all ${
            isDark 
              ? 'bg-[#151722] border-fitonist-border-dark text-amber-400 hover:bg-slate-800' 
              : 'bg-white border-indigo-200 text-slate-700 hover:bg-indigo-50 shadow-sm'
          }`}
          title={isDark ? "Modo Claro" : "Modo Oscuro"}
        >
          {isDark ? <Sun size={18} /> : <Moon size={18} />}
        </button>

        {/* Notifications Icon */}
        <div className="relative">
          <button
            onClick={() => setShowNotifications(!showNotifications)}
            className={`p-2.5 rounded-full border relative transition-all ${
              isDark 
                ? 'bg-[#151722] border-fitonist-border-dark text-slate-300 hover:bg-slate-800' 
                : 'bg-white border-indigo-200 text-slate-700 hover:bg-indigo-50 shadow-sm'
            }`}
          >
            <Bell size={18} />
            <span className="absolute top-1 right-1 w-2.5 h-2.5 bg-indigo-600 rounded-full ring-2 ring-white animate-pulse"></span>
          </button>
          {showNotifications && (
            <NotificationsPopover onClose={() => setShowNotifications(false)} />
          )}
        </div>

        {/* User Profile Pill */}
        <div className="relative">
          <button
            onClick={() => setShowProfileMenu(!showProfileMenu)}
            className={`flex items-center gap-3 p-1.5 pr-3 rounded-full border transition-all ${
              isDark 
                ? 'bg-[#151722] border-fitonist-border-dark hover:border-indigo-500/50' 
                : 'bg-white border-indigo-200 hover:border-indigo-400 shadow-sm'
            }`}
          >
            {currentUser.avatar ? (
              <img
                src={currentUser.avatar}
                alt={currentUser.name}
                className="w-8 h-8 rounded-full object-cover ring-2 ring-[#0b4fc1]/40"
              />
            ) : (
              <span className="w-8 h-8 rounded-full flex items-center justify-center text-[11px] leading-none font-black text-white bg-gradient-to-br from-[#0b4fc1] to-[#7ea6ff] ring-2 ring-[#0b4fc1]/40">
                {initials}
              </span>
            )}
            <span className={`text-xs font-extrabold hidden md:inline ${isDark ? 'text-slate-100' : 'text-slate-800'}`}>
              {currentUser.name}
            </span>
            <ChevronDown size={14} className="text-slate-400" />
          </button>

          {showProfileMenu && (
            <div className={`absolute right-0 mt-2 w-56 py-2 rounded-2xl border shadow-2xl z-50 animate-in fade-in zoom-in-95 duration-150 ${
              isDark 
                ? 'bg-[#151722] border-fitonist-border-dark text-slate-200' 
                : 'bg-white border-slate-200 text-slate-800'
            }`}>
              <div className="px-4 py-2 border-b border-inherit">
                <p className="text-xs font-bold">{currentUser.name}</p>
                <p className="text-[11px] text-slate-400">{currentUser.email || currentUser.role}</p>
              </div>
              {logoutAction && (
                <form method="POST" action={logoutAction}>
                  <input type="hidden" name="_token" value={csrfToken || ''} />
                  <button
                    type="submit"
                    className="w-full px-4 py-2 text-xs flex items-center gap-2 hover:bg-[#f25a59]/10 hover:text-[#f25a59] font-medium"
                  >
                    <LogOut size={14} /> Cerrar sesion
                  </button>
                </form>
              )}
              <button 
                onClick={toggleTheme}
                className="w-full px-4 py-2 text-xs flex items-center gap-2 hover:bg-[#0b4fc1]/10 hover:text-[#0b4fc1] font-medium"
              >
                {isDark ? <Sun size={14} /> : <Moon size={14} />} {isDark ? 'Modo Claro' : 'Modo Oscuro'}
              </button>
            </div>
          )}
        </div>
      </div>
    </header>
  );
}
