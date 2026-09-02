import React, { useState } from 'react';
import { 
  Search, 
  Sun, 
  Moon, 
  UserPlus, 
  Bell, 
  ChevronDown, 
  LayoutGrid,
  Users
} from 'lucide-react';
import { useUser } from '../context/UserContext';
import { useTheme } from '../context/ThemeContext';
import NotificationsPopover from './NotificationsPopover';
import { preloadPage } from '../../pageRegistry';

export default function Navbar() {
  const { 
    searchQuery, 
    setSearchQuery, 
    users 
  } = useUser();
  const { isDark, toggleTheme } = useTheme();
  const [showProfileMenu, setShowProfileMenu] = useState(false);
  const [showNotifications, setShowNotifications] = useState(false);
  const currentPath = window.location.pathname;

  const currentUser = users[0] || {
    name: 'Olivia Brooks',
    email: 'admin@fitonist.net',
    avatar: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80&w=200'
  };

  return (
    <header className="w-full flex items-center justify-between py-4 px-8 gap-4">
      {/* Navigation Pills */}
      <div className="flex items-center gap-2">
        <div className={`flex items-center p-1.5 rounded-full border shadow-inner ${
          isDark 
            ? 'bg-[#151720]/90 border-fitonist-border-dark' 
            : 'bg-indigo-100/60 border-indigo-200'
        }`}>
          <a
            href="/dashboard/admin"
            onMouseEnter={() => preloadPage('adminDashboard')}
            onFocus={() => preloadPage('adminDashboard')}
            onTouchStart={() => preloadPage('adminDashboard')}
            className={`flex items-center gap-2 px-5 py-2 rounded-full text-xs font-extrabold transition-all duration-300 ${
              currentPath === '/dashboard/admin'
                ? 'bg-indigo-600 text-white shadow-md scale-[1.02]'
                : isDark ? 'text-slate-400 hover:text-slate-200' : 'text-slate-700 hover:text-slate-900'
            }`}
          >
            <LayoutGrid size={15} />
            <span>Dashboard Consola</span>
          </a>

          <a
            href="/dashboard/usuarios"
            onMouseEnter={() => preloadPage('adminUsers')}
            onFocus={() => preloadPage('adminUsers')}
            onTouchStart={() => preloadPage('adminUsers')}
            className={`flex items-center gap-2 px-5 py-2 rounded-full text-xs font-extrabold transition-all duration-300 ${
              currentPath === '/dashboard/usuarios'
                ? 'bg-indigo-600 text-white shadow-md scale-[1.02]'
                : isDark ? 'text-slate-400 hover:text-slate-200' : 'text-slate-700 hover:text-slate-900'
            }`}
          >
            <Users size={15} />
            <span>Directorio de Usuarios</span>
          </a>
        </div>
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

        {/* Quick User Creation Modal Button */}
        <a
          href="/dashboard/usuarios"
          onMouseEnter={() => preloadPage('adminUsers')}
          onFocus={() => preloadPage('adminUsers')}
          onTouchStart={() => preloadPage('adminUsers')}
          className="hidden md:flex items-center gap-2 px-4 py-2 rounded-full text-xs font-extrabold text-white bg-indigo-600 hover:bg-indigo-700 shadow-md transition-all"
        >
          <UserPlus size={15} />
          <span>+ Crear Usuario</span>
        </a>

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
            <img
              src={currentUser.avatar}
              alt={currentUser.name}
              className="w-8 h-8 rounded-full object-cover ring-2 ring-indigo-500/40"
            />
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
                <p className="text-[11px] text-slate-400">{currentUser.email}</p>
              </div>
              <a 
                href="/dashboard/usuarios"
                onMouseEnter={() => preloadPage('adminUsers')}
                onFocus={() => preloadPage('adminUsers')}
                onTouchStart={() => preloadPage('adminUsers')}
                className="w-full px-4 py-2 text-xs flex items-center gap-2 hover:bg-indigo-500/10 hover:text-indigo-600 font-medium"
              >
                <UserPlus size={14} /> Crear Nuevo Usuario (Modal)
              </a>
              <button 
                onClick={toggleTheme}
                className="w-full px-4 py-2 text-xs flex items-center gap-2 hover:bg-indigo-500/10 hover:text-indigo-600 font-medium"
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
