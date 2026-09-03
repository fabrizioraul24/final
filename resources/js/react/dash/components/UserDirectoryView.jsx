import React, { useState, useMemo } from 'react';
import { 
  Users, 
  UserCheck, 
  UserX, 
  ShieldCheck, 
  UserPlus, 
  Download, 
  Search, 
  ChevronLeft, 
  ChevronRight, 
  Edit2, 
  Trash2, 
  Filter,
  CheckCircle2,
  Sparkles
} from 'lucide-react';
import { useUser } from '../context/UserContext';
import { useTheme } from '../context/ThemeContext';

export default function UserDirectoryView() {
  const { users, openFormForCreate, openFormForEdit, deleteUser } = useUser();
  const { isDark } = useTheme();

  // State for active metric card filter, search, role/status selects, and pagination
  const [activeFilterCard, setActiveFilterCard] = useState('ALL'); // 'ALL', 'ACTIVE', 'INACTIVE', 'STAFF'
  const [search, setSearch] = useState('');
  const [roleFilter, setRoleFilter] = useState('ALL');
  const [currentPage, setCurrentPage] = useState(1);
  const itemsPerPage = 5;
  const isActiveStatus = (status) => ['Active', 'Activo'].includes(status);
  const isInactiveStatus = (status) => ['Inactive', 'Inactivo'].includes(status);
  const isStaffRole = (role) => ['Admin', 'Administrador', 'Trainer', 'Vendedor'].includes(role);
  const roleLabel = (role) => ({
    Admin: 'Administrador',
    Trainer: 'Vendedor',
    Member: 'Cliente',
  }[role] || role);

  // Counts for the 4 metric cards
  const totalUsers = users.length;
  const activeUsers = users.filter(u => isActiveStatus(u.status)).length;
  const inactiveUsers = users.filter(u => isInactiveStatus(u.status)).length;
  const staffUsers = users.filter(u => isStaffRole(u.role)).length;

  // Filtered Users computation
  const filteredUsers = useMemo(() => {
    return users.filter(u => {
      // 1. Metric card filter
      if (activeFilterCard === 'ACTIVE' && !isActiveStatus(u.status)) return false;
      if (activeFilterCard === 'INACTIVE' && !isInactiveStatus(u.status)) return false;
      if (activeFilterCard === 'STAFF' && !isStaffRole(u.role)) return false;

      // 2. Search query filter
      const matchesSearch = u.name.toLowerCase().includes(search.toLowerCase()) || 
                            u.email.toLowerCase().includes(search.toLowerCase());
      if (!matchesSearch) return false;

      // 3. Role Select filter
      if (roleFilter !== 'ALL' && roleLabel(u.role) !== roleFilter) return false;

      return true;
    });
  }, [users, activeFilterCard, search, roleFilter]);

  // Pagination computation
  const totalPages = Math.ceil(filteredUsers.length / itemsPerPage) || 1;
  const paginatedUsers = useMemo(() => {
    const start = (currentPage - 1) * itemsPerPage;
    return filteredUsers.slice(start, start + itemsPerPage);
  }, [filteredUsers, currentPage, itemsPerPage]);

  const handleCardClick = (cardKey) => {
    setActiveFilterCard(cardKey);
    setCurrentPage(1); // Reset to page 1 on filter change
  };

  // Export Users Report to CSV
  const handleExportCSV = () => {
    const headers = ['ID,Nombre,Email,Rol,Plan,Estado,FechaIngreso\n'];
    const rows = filteredUsers.map(u => `${u.id},"${u.name}",${u.email},${u.role},${u.plan},${u.status},${u.joinedDate}`);
    const csvContent = 'data:text/csv;charset=utf-8,' + headers.concat(rows).join('\n');
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement('a');
    link.setAttribute('href', encodedUri);
    link.setAttribute('download', `reporte_usuarios_${new Date().toISOString().split('T')[0]}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  };

  return (
    <div className="w-full max-w-[1500px] mx-auto space-y-6 animate-in fade-in duration-300">
      {/* Page Header */}
      <div className={`p-6 rounded-3xl border flex flex-col md:flex-row items-start md:items-center justify-between gap-4 transition-all ${
        isDark 
          ? 'bg-gradient-to-r from-indigo-900/40 via-fitonist-card-dark to-purple-900/30 border-fitonist-border-dark' 
          : 'bg-gradient-to-r from-indigo-50 via-white to-purple-50 border-indigo-200 shadow-sm'
      }`}>
        <div className="flex items-center gap-4">
          <div className="w-14 h-14 rounded-2xl bg-indigo-600 text-white flex items-center justify-center shadow-lg flex-shrink-0">
            <Users size={28} />
          </div>
          <div>
            <h1 className="text-2xl font-black tracking-tight">Directorio e Métricas de Usuarios</h1>
            <p className="text-sm text-slate-400 font-medium">
              Filtra, busca, gestiona y exporta el reporte completo de la plataforma.
            </p>
          </div>
        </div>

        <div className="flex items-center gap-3">
          <button
            onClick={handleExportCSV}
            className={`px-5 py-2.5 rounded-full text-xs font-bold border transition-all flex items-center gap-2 ${
              isDark 
                ? 'border-slate-700 bg-slate-800 text-slate-200 hover:bg-slate-700' 
                : 'border-indigo-200 bg-white text-indigo-700 hover:bg-indigo-50 shadow-sm'
            }`}
          >
            <Download size={15} />
            <span>Descargar Reporte CSV</span>
          </button>

          <button
            onClick={openFormForCreate}
            className="px-6 py-2.5 rounded-full text-xs font-extrabold text-white bg-indigo-600 hover:bg-indigo-700 shadow-md flex items-center gap-2 transform hover:scale-[1.02] transition-all"
          >
            <UserPlus size={16} />
            <span>+ Crear Usuario</span>
          </button>
        </div>
      </div>

      {/* 4 Interactive Metric Filter Cards */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        {/* Card 1: Total Users */}
        <div
          onClick={() => handleCardClick('ALL')}
          className={`p-5 rounded-3xl border cursor-pointer transition-all duration-200 flex items-center justify-between group ${
            activeFilterCard === 'ALL'
              ? 'ring-4 ring-indigo-500 border-indigo-500 bg-indigo-500/10 shadow-lg scale-[1.02]'
              : isDark ? 'bg-fitonist-card-dark border-fitonist-border-dark hover:border-indigo-500/40' : 'bg-white border-indigo-100 shadow-sm hover:border-indigo-300'
          }`}
        >
          <div className="flex flex-col">
            <span className="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Usuarios</span>
            <span className="text-3xl font-black mt-1 tracking-tight">{totalUsers}</span>
            <span className="text-[11px] text-indigo-400 font-bold mt-1">Haz clic para ver todos</span>
          </div>
          <div className="p-3 rounded-2xl bg-indigo-500/20 text-indigo-400">
            <Users size={24} />
          </div>
        </div>

        {/* Card 2: Active Users */}
        <div
          onClick={() => handleCardClick('ACTIVE')}
          className={`p-5 rounded-3xl border cursor-pointer transition-all duration-200 flex items-center justify-between group ${
            activeFilterCard === 'ACTIVE'
              ? 'ring-4 ring-emerald-500 border-emerald-500 bg-emerald-500/10 shadow-lg scale-[1.02]'
              : isDark ? 'bg-fitonist-card-dark border-fitonist-border-dark hover:border-emerald-500/40' : 'bg-white border-indigo-100 shadow-sm hover:border-emerald-300'
          }`}
        >
          <div className="flex flex-col">
            <span className="text-xs font-bold text-slate-400 uppercase tracking-wider">Usuarios Activos</span>
            <span className="text-3xl font-black mt-1 tracking-tight text-emerald-500">{activeUsers}</span>
            <span className="text-[11px] text-emerald-400 font-bold mt-1">Filtrar por activos</span>
          </div>
          <div className="p-3 rounded-2xl bg-emerald-500/20 text-emerald-400">
            <UserCheck size={24} />
          </div>
        </div>

        {/* Card 3: Inactive Users */}
        <div
          onClick={() => handleCardClick('INACTIVE')}
          className={`p-5 rounded-3xl border cursor-pointer transition-all duration-200 flex items-center justify-between group ${
            activeFilterCard === 'INACTIVE'
              ? 'ring-4 ring-rose-500 border-rose-500 bg-rose-500/10 shadow-lg scale-[1.02]'
              : isDark ? 'bg-fitonist-card-dark border-fitonist-border-dark hover:border-rose-500/40' : 'bg-white border-indigo-100 shadow-sm hover:border-rose-300'
          }`}
        >
          <div className="flex flex-col">
            <span className="text-xs font-bold text-slate-400 uppercase tracking-wider">Usuarios Inactivos</span>
            <span className="text-3xl font-black mt-1 tracking-tight text-rose-500">{inactiveUsers}</span>
            <span className="text-[11px] text-rose-400 font-bold mt-1">Filtrar por inactivos</span>
          </div>
          <div className="p-3 rounded-2xl bg-rose-500/20 text-rose-400">
            <UserX size={24} />
          </div>
        </div>

        {/* Card 4: Coaches & Staff */}
        <div
          onClick={() => handleCardClick('STAFF')}
          className={`p-5 rounded-3xl border cursor-pointer transition-all duration-200 flex items-center justify-between group ${
            activeFilterCard === 'STAFF'
              ? 'ring-4 ring-amber-500 border-amber-500 bg-amber-500/10 shadow-lg scale-[1.02]'
              : isDark ? 'bg-fitonist-card-dark border-fitonist-border-dark hover:border-amber-500/40' : 'bg-white border-indigo-100 shadow-sm hover:border-amber-300'
          }`}
        >
          <div className="flex flex-col">
            <span className="text-xs font-bold text-slate-400 uppercase tracking-wider">Equipo autorizado</span>
            <span className="text-3xl font-black mt-1 tracking-tight text-amber-500">{staffUsers}</span>
            <span className="text-[11px] text-amber-400 font-bold mt-1">Filtrar staff</span>
          </div>
          <div className="p-3 rounded-2xl bg-amber-500/20 text-amber-400">
            <ShieldCheck size={24} />
          </div>
        </div>
      </div>

      {/* Controls Bar: Search & Select Filters */}
      <div className={`p-5 rounded-3xl border flex flex-col md:flex-row items-center justify-between gap-4 ${
        isDark ? 'bg-fitonist-card-dark border-fitonist-border-dark' : 'bg-white border-indigo-100 shadow-sm'
      }`}>
        {/* Search Input */}
        <div className={`relative flex items-center w-full md:w-80 rounded-full border ${
          isDark ? 'bg-[#1e212b] border-slate-700 text-white' : 'bg-indigo-50/60 border-indigo-200 text-slate-900'
        }`}>
          <Search size={16} className="absolute left-3.5 text-slate-400" />
          <input
            type="text"
            placeholder="Buscar usuario o correo..."
            value={search}
            onChange={(e) => { setSearch(e.target.value); setCurrentPage(1); }}
            className="w-full pl-10 pr-4 py-2 bg-transparent text-xs font-medium focus:outline-none placeholder:text-slate-400"
          />
        </div>

        {/* Role Select & Clear Filters */}
        <div className="flex items-center gap-3 w-full md:w-auto">
          <div className="flex items-center gap-2">
            <Filter size={15} className="text-slate-400" />
            <select
              value={roleFilter}
              onChange={(e) => { setRoleFilter(e.target.value); setCurrentPage(1); }}
              className={`px-3 py-2 rounded-full border text-xs font-bold focus:outline-none ${
                isDark ? 'bg-[#1e212b] border-slate-700 text-white' : 'bg-indigo-50/60 border-indigo-200 text-slate-900'
              }`}
            >
              <option value="ALL">Todos los roles</option>
              <option value="Administrador">Administradores</option>
              <option value="Vendedor">Vendedores</option>
              <option value="Cliente">Clientes</option>
            </select>
          </div>

          {(activeFilterCard !== 'ALL' || search || roleFilter !== 'ALL') && (
            <button
              onClick={() => { setActiveFilterCard('ALL'); setSearch(''); setRoleFilter('ALL'); setCurrentPage(1); }}
              className="px-3 py-1.5 rounded-full text-xs font-bold text-rose-400 border border-rose-500/30 hover:bg-rose-500/10 transition-all"
            >
              Limpiar Filtros
            </button>
          )}
        </div>
      </div>

      {/* Main Data Table */}
      <div className={`rounded-3xl border overflow-hidden transition-all ${
        isDark ? 'bg-fitonist-card-dark border-fitonist-border-dark' : 'bg-white border-indigo-100 shadow-md'
      }`}>
        <div className="overflow-x-auto">
          <table className="w-full text-left border-collapse">
            <thead>
              <tr className={`border-b text-[11px] font-extrabold uppercase tracking-wider ${
                isDark ? 'border-slate-800 text-slate-400 bg-slate-900/40' : 'border-indigo-100 text-slate-500 bg-indigo-50/50'
              }`}>
                <th className="py-4 px-6">Usuario</th>
                <th className="py-4 px-4">Rol</th>
                <th className="py-4 px-4">Plan</th>
                <th className="py-4 px-4">Estado</th>
                <th className="py-4 px-4">Fecha Ingreso</th>
                <th className="py-4 px-6 text-right">Acciones</th>
              </tr>
            </thead>

            <tbody className="divide-y divide-inherit">
              {paginatedUsers.length > 0 ? (
                paginatedUsers.map((user) => (
                  <tr key={user.id} className="hover:bg-indigo-500/5 transition-colors group">
                    <td className="py-3.5 px-6">
                      <div className="flex items-center gap-3">
                        <img
                          src={user.avatar}
                          alt={user.name}
                          className="w-10 h-10 rounded-full object-cover ring-2 ring-indigo-500/30"
                        />
                        <div>
                          <p className="text-xs font-bold">{user.name}</p>
                          <p className="text-[11px] text-slate-400">{user.email}</p>
                        </div>
                      </div>
                    </td>

                    <td className="py-3.5 px-4">
                      <span className={`px-2.5 py-1 rounded-lg text-[10px] font-extrabold border ${
                        roleLabel(user.role) === 'Administrador'
                          ? 'bg-purple-500/20 text-purple-400 border-purple-500/30' 
                          : roleLabel(user.role) === 'Vendedor'
                            ? 'bg-amber-500/20 text-amber-400 border-amber-500/30'
                            : 'bg-indigo-500/20 text-indigo-400 border-indigo-500/30'
                      }`}>
                        {roleLabel(user.role)}
                      </span>
                    </td>

                    <td className="py-3.5 px-4 text-xs font-semibold text-slate-300">
                      {user.plan}
                    </td>

                    <td className="py-3.5 px-4">
                      <span className={`inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold ${
                        isActiveStatus(user.status) ? 'bg-emerald-500/20 text-emerald-400' : 'bg-rose-500/20 text-rose-400'
                      }`}>
                        <span className={`w-1.5 h-1.5 rounded-full ${isActiveStatus(user.status) ? 'bg-emerald-400' : 'bg-rose-400'}`}></span>
                        {isActiveStatus(user.status) ? 'Activo' : 'Inactivo'}
                      </span>
                    </td>

                    <td className="py-3.5 px-4 text-xs text-slate-400">
                      {user.joinedDate || '2024-01-15'}
                    </td>

                    <td className="py-3.5 px-6 text-right">
                      <div className="flex items-center justify-end gap-2">
                        <button
                          onClick={() => openFormForEdit(user)}
                          className={`p-2 rounded-xl border transition-all ${
                            isDark ? 'border-slate-700 hover:bg-slate-800 text-slate-300' : 'border-slate-200 hover:bg-indigo-50 text-slate-700'
                          }`}
                          title="Editar"
                        >
                          <Edit2 size={14} />
                        </button>

                        <button
                          onClick={() => deleteUser(user.id)}
                          className="p-2 rounded-xl border border-rose-500/30 text-rose-400 hover:bg-rose-500/20 transition-all"
                          title="Eliminar"
                        >
                          <Trash2 size={14} />
                        </button>
                      </div>
                    </td>
                  </tr>
                ))
              ) : (
                <tr>
                  <td colSpan="6" className="py-8 text-center text-xs text-slate-400">
                    No se encontraron usuarios coincidentes con los filtros seleccionados.
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>

        {/* Pagination Footer */}
        <div className={`p-4 border-t border-inherit flex items-center justify-between ${
          isDark ? 'bg-slate-900/20' : 'bg-indigo-50/30'
        }`}>
          <span className="text-xs text-slate-400 font-medium">
            Mostrando {filteredUsers.length > 0 ? (currentPage - 1) * itemsPerPage + 1 : 0} - {Math.min(currentPage * itemsPerPage, filteredUsers.length)} de {filteredUsers.length} usuarios
          </span>

          <div className="flex items-center gap-2">
            <button
              onClick={() => setCurrentPage(prev => Math.max(prev - 1, 1))}
              disabled={currentPage === 1}
              className={`p-2 rounded-xl border transition-all ${
                currentPage === 1 
                  ? 'opacity-40 cursor-not-allowed border-slate-700' 
                  : isDark ? 'border-slate-700 hover:bg-slate-800 text-white' : 'border-indigo-200 hover:bg-indigo-50 text-indigo-700'
              }`}
            >
              <ChevronLeft size={16} />
            </button>

            {Array.from({ length: totalPages }, (_, i) => i + 1).map((pageNum) => (
              <button
                key={pageNum}
                onClick={() => setCurrentPage(pageNum)}
                className={`w-8 h-8 rounded-xl text-xs font-bold transition-all ${
                  currentPage === pageNum
                    ? 'bg-indigo-600 text-white shadow-md'
                    : isDark ? 'text-slate-400 hover:bg-slate-800' : 'text-slate-600 hover:bg-indigo-50'
                }`}
              >
                {pageNum}
              </button>
            ))}

            <button
              onClick={() => setCurrentPage(prev => Math.min(prev + 1, totalPages))}
              disabled={currentPage === totalPages}
              className={`p-2 rounded-xl border transition-all ${
                currentPage === totalPages 
                  ? 'opacity-40 cursor-not-allowed border-slate-700' 
                  : isDark ? 'border-slate-700 hover:bg-slate-800 text-white' : 'border-indigo-200 hover:bg-indigo-50 text-indigo-700'
              }`}
            >
              <ChevronRight size={16} />
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}
