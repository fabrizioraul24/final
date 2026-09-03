import React from 'react';
import {
  Archive,
  Boxes,
  Building2,
  FileText,
  History,
  LayoutGrid,
  Package,
  RefreshCw,
  Shuffle,
  ShoppingCart,
  Tags,
  Users,
  WalletCards,
} from 'lucide-react';
import { useTheme } from '../context/ThemeContext';
import { preloadPage } from '../../pageRegistry';

const pageByHref = {
  '/dashboard/admin': 'adminDashboard',
  '/dashboard/usuarios': 'adminUsers',
  '/dashboard/clientes': 'adminCompanies',
  '/dashboard/productos': 'adminProducts',
  '/dashboard/lotes': 'adminLots',
  '/dashboard/categorias': 'adminCategories',
  '/dashboard/traspasos': 'adminTransfers',
  '/dashboard/ventas': 'adminSales',
  '/dashboard/cotizaciones': 'adminQuotations',
  '/dashboard/logs': 'adminLogs',
  '/dashboard/backups': 'adminBackups',
  '/admin/agente-reposicion': 'adminAgentReplenishment',
};

export default function Sidebar() {
  const { isDark } = useTheme();
  const currentPath = window.location.pathname;

  const navButtons = [
    { href: '/dashboard/admin', label: 'Panel de control', icon: LayoutGrid, desc: 'Panel principal' },
    { href: '/dashboard/usuarios', label: 'Usuarios', icon: Users, desc: 'Gestion de usuarios' },
    { href: '/dashboard/clientes', label: 'Clientes', icon: Building2, desc: 'Empresas y clientes' },
    { href: '/dashboard/productos', label: 'Productos', icon: Package, desc: 'Catalogo PIL' },
    { href: '/dashboard/lotes', label: 'Lotes', icon: Archive, desc: 'Inventario FEFO' },
    { href: '/dashboard/categorias', label: 'Categorias', icon: Tags, desc: 'Familias de producto' },
    { href: '/dashboard/traspasos', label: 'Traspasos', icon: Shuffle, desc: 'Movimientos internos' },
    { href: '/dashboard/ventas', label: 'Ventas', icon: ShoppingCart, desc: 'Registro comercial' },
    { href: '/dashboard/cotizaciones', label: 'Cotizaciones', icon: FileText, desc: 'Proformas y PDF' },
    { href: '/dashboard/logs', label: 'Bitacora', icon: History, desc: 'Bitacora del sistema' },
    { href: '/dashboard/backups', label: 'Respaldos', icon: Boxes, desc: 'Respaldo de datos' },
    { href: '/admin/agente-reposicion', label: 'Agente IA', icon: RefreshCw, desc: 'Reposicion automatica' },
    { href: '/dashboard/pago', label: 'Pagos', icon: WalletCards, desc: 'Recibos y cobros' },
  ];

  return (
    <aside className={`fixed left-0 top-0 bottom-0 z-40 w-64 flex flex-col justify-between p-6 border-r overflow-hidden transition-all duration-300 ${
      isDark
        ? 'bg-[#151722] border-fitonist-border-dark text-slate-200'
        : 'bg-[#0b4fc1] text-white shadow-2xl border-indigo-600'
    }`}>
      <div className="space-y-6 min-h-0 flex flex-col">
        <div className="flex items-center gap-3.5 px-1 py-1">
          <div className="w-10 h-10 rounded-2xl bg-white/20 backdrop-blur-md border border-white/30 flex items-center justify-center text-white font-black text-xl shadow-md flex-shrink-0">
            PIL
          </div>
          <div className="flex flex-col min-w-0">
            <span className="font-black text-xl tracking-tight font-sans leading-tight">
              PIL Bolivia
            </span>
            <span className="text-[10px] font-extrabold tracking-widest uppercase opacity-80">
              Panel Admin
            </span>
          </div>
        </div>

        <div className="space-y-2 min-h-0 flex-1 flex flex-col">
          <span className="text-[10px] font-extrabold uppercase tracking-wider px-3 text-indigo-200 dark:text-slate-500">
            Menu
          </span>

          <nav className="dash-sidebar-scroll space-y-1.5 mt-1 overflow-y-auto pr-1">
            {navButtons.map((item) => {
              const Icon = item.icon;
              const isActive = currentPath === item.href;

              return (
                <a
                  key={item.href}
                  href={item.href}
                  onMouseEnter={() => preloadPage(pageByHref[item.href])}
                  onFocus={() => preloadPage(pageByHref[item.href])}
                  onTouchStart={() => preloadPage(pageByHref[item.href])}
                  className={`w-full flex items-center justify-between p-3.5 rounded-2xl transition-all duration-200 no-underline ${
                    isActive
                      ? isDark
                        ? 'bg-indigo-600 text-white font-bold shadow-lg'
                        : 'bg-white text-[#0b4fc1] font-black shadow-md'
                      : isDark
                        ? 'text-slate-400 hover:text-white hover:bg-slate-800/70'
                        : 'text-indigo-100 hover:text-white hover:bg-indigo-600/60'
                  }`}
                >
                  <div className="flex items-center gap-3.5 min-w-0">
                    <Icon size={20} className="flex-shrink-0" />
                    <div className="flex flex-col text-left min-w-0">
                      <span className="text-xs font-bold leading-tight truncate">{item.label}</span>
                      <span className="text-[10px] opacity-75 font-medium truncate">{item.desc}</span>
                    </div>
                  </div>

                  {isActive && (
                    <span className={`px-2.5 py-0.5 rounded-full text-[10px] font-extrabold ${
                      isDark ? 'bg-slate-900 text-white' : 'bg-indigo-600 text-white'
                    }`}>
                      Actual
                    </span>
                  )}
                </a>
              );
            })}
          </nav>
        </div>
      </div>

      <div />
    </aside>
  );
}
