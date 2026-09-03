import React from 'react';
import { Bell, CheckCircle2, UserPlus, TrendingUp } from 'lucide-react';
import { useTheme } from '../context/ThemeContext';

export default function NotificationsPopover({ onClose }) {
  const { isDark } = useTheme();

  const notifications = [
    {
      id: 1,
      title: 'Nuevo Usuario Registrado',
      desc: 'Sophia Chen se ha unido al plan Pro Fitness',
      time: 'Hace 5 min',
      icon: UserPlus,
      color: 'text-fitonist-purple bg-fitonist-purple/20'
    },
    {
      id: 2,
      title: 'Objetivo de Ingresos Alcanzado',
      desc: 'Se superó el hito mensual de $75,000 USD',
      time: 'Hace 1 hora',
      icon: TrendingUp,
      color: 'text-emerald-400 bg-emerald-500/20'
    },
    {
      id: 3,
      title: 'Sincronización Completada',
      desc: 'Métricas de la App Store y Play Store actualizadas',
      time: 'Hace 3 horas',
      icon: CheckCircle2,
      color: 'text-sky-400 bg-sky-500/20'
    }
  ];

  return (
    <div className={`absolute right-0 mt-2 w-80 py-3 rounded-2xl border shadow-2xl z-50 animate-in fade-in zoom-in-95 duration-150 ${
      isDark 
        ? 'bg-fitonist-card-dark border-fitonist-border-dark text-slate-200' 
        : 'bg-white border-slate-200 text-slate-800'
    }`}>
      <div className="flex items-center justify-between px-4 pb-2 border-b border-inherit">
        <div className="flex items-center gap-2 font-extrabold text-xs tracking-wide">
          <Bell size={15} className="text-fitonist-purple" />
          <span>Notificaciones en Vivo</span>
        </div>
        <span className="text-[10px] font-bold px-2 py-0.5 rounded-full bg-fitonist-purple text-white">
          3 Nuevas
        </span>
      </div>

      <div className="divide-y divide-inherit max-h-72 overflow-y-auto">
        {notifications.map((item) => {
          const Icon = item.icon;
          return (
            <div key={item.id} className="p-3 hover:bg-slate-800/30 transition-colors flex items-start gap-3">
              <div className={`p-2 rounded-xl flex-shrink-0 ${item.color}`}>
                <Icon size={16} />
              </div>
              <div className="flex-1 min-w-0">
                <p className="text-xs font-bold truncate">{item.title}</p>
                <p className="text-[11px] text-slate-400 leading-tight mt-0.5">{item.desc}</p>
                <span className="text-[9px] text-slate-500 mt-1 block">{item.time}</span>
              </div>
            </div>
          );
        })}
      </div>

      <div className="pt-2 px-4 border-t border-inherit text-center">
        <button 
          onClick={onClose}
          className="text-[11px] font-bold text-fitonist-purple hover:underline"
        >
          Cerrar Notificaciones
        </button>
      </div>
    </div>
  );
}
