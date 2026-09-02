import React from 'react';
import { 
  LayoutGrid, 
  UserPlus, 
  Users, 
  Settings, 
  Sun,
  Moon,
  ShieldCheck
} from 'lucide-react';
import { useUser } from '../context/UserContext';
import { useTheme } from '../context/ThemeContext';

export default function Sidebar() {
  const { currentDashboard, setCurrentDashboard, users, openFormForCreate } = useUser();
  const { isDark, toggleTheme } = useTheme();

  const navButtons = [
    { id: 'dash2', label: 'Dashboard Consola', icon: LayoutGrid, desc: 'Panel Principal' },
    { id: 'user_list', label: 'Directorio de Usuarios', icon: Users, desc: `${users.length} Miembros`, badge: users.length },
  ];

  return (
    <aside className={`fixed left-0 top-0 bottom-0 z-40 w-64 flex flex-col justify-between p-6 border-r transition-all duration-300 ${
      isDark 
        ? 'bg-[#151722] border-fitonist-border-dark text-slate-200' 
        : 'bg-[#4f46e5] text-white shadow-2xl border-indigo-600'
    }`}>
      {/* Top Brand & Nav */}
      <div className="space-y-6">
        {/* Brand Header */}
        <div className="flex items-center gap-3.5 px-1 py-1">
          <div className="w-10 h-10 rounded-2xl bg-white/20 backdrop-blur-md border border-white/30 flex items-center justify-center text-white font-black text-xl shadow-md flex-shrink-0">
            F
          </div>
          <div className="flex flex-col">
            <span className="font-black text-xl tracking-tight font-sans">
              fitonist
            </span>
            <span className="text-[10px] font-extrabold tracking-widest uppercase opacity-80">
              Indigo Console
            </span>
          </div>
        </div>

        {/* Primary Action Button */}
        <button
          onClick={openFormForCreate}
          className={`w-full flex items-center justify-center gap-2 py-3.5 px-4 rounded-2xl font-extrabold text-xs transition-all shadow-md transform hover:scale-[1.02] ${
            isDark 
              ? 'bg-indigo-600 hover:bg-indigo-500 text-white shadow-lg' 
              : 'bg-white text-[#4f46e5] hover:bg-indigo-50 shadow-lg'
          }`}
        >
          <UserPlus size={17} />
          <span>+ Crear Usuario</span>
        </button>

        {/* Navigation Items */}
        <div className="space-y-2">
          <span className="text-[10px] font-extrabold uppercase tracking-wider px-3 text-indigo-200 dark:text-slate-500">
            Menú de Navegación
          </span>

          <nav className="space-y-1.5 mt-1">
            {navButtons.map((item) => {
              const Icon = item.icon;
              const isActive = currentDashboard === item.id;

              return (
                <button
                  key={item.id}
                  onClick={() => setCurrentDashboard(item.id)}
                  className={`w-full flex items-center justify-between p-3.5 rounded-2xl transition-all duration-200 ${
                    isActive 
                      ? isDark 
                        ? 'bg-indigo-600 text-white font-bold shadow-lg' 
                        : 'bg-white text-[#4f46e5] font-black shadow-md'
                      : isDark 
                        ? 'text-slate-400 hover:text-white hover:bg-slate-800/70' 
                        : 'text-indigo-100 hover:text-white hover:bg-indigo-600/60'
                  }`}
                >
                  <div className="flex items-center gap-3.5">
                    <Icon size={20} />
                    <div className="flex flex-col text-left">
                      <span className="text-xs font-bold leading-tight">{item.label}</span>
                      <span className="text-[10px] opacity-75 font-medium">{item.desc}</span>
                    </div>
                  </div>

                  {item.badge !== undefined && (
                    <span className={`px-2.5 py-0.5 rounded-full text-[10px] font-extrabold ${
                      isActive 
                        ? isDark ? 'bg-slate-900 text-white' : 'bg-indigo-600 text-white' 
                        : isDark 
                          ? 'bg-indigo-500/20 text-indigo-400' 
                          : 'bg-indigo-700/60 text-white'
                    }`}>
                      {item.badge}
                    </span>
                  )}
                </button>
              );
            })}
          </nav>
        </div>
      </div>

      {/* Bottom Settings & Theme Switcher */}
      <div className="space-y-3 border-t border-inherit pt-4">
        <button
          onClick={toggleTheme}
          className={`w-full flex items-center justify-between p-3.5 rounded-2xl text-xs font-bold transition-all ${
            isDark 
              ? 'bg-slate-800/80 text-amber-400 hover:bg-slate-800' 
              : 'bg-indigo-600/70 text-amber-300 hover:bg-indigo-600'
          }`}
        >
          <div className="flex items-center gap-3">
            {isDark ? <Sun size={18} /> : <Moon size={18} />}
            <span>{isDark ? 'Modo Claro' : 'Modo Oscuro'}</span>
          </div>
          <span className="text-[10px] px-2 py-0.5 rounded-md bg-black/20 font-mono">
            {isDark ? 'LIGHT' : 'DARK'}
          </span>
        </button>

        <div className="flex items-center justify-between px-2 text-[10px] font-semibold opacity-75">
          <span className="flex items-center gap-1">
            <ShieldCheck size={13} className="text-emerald-400" /> Sistema Activo
          </span>
          <span>v2.4 Indigo</span>
        </div>
      </div>
    </aside>
  );
}
