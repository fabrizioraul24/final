import React, { useState } from 'react';
import { X, UserPlus, Search, Edit2, Trash2, Shield, User, Sparkles } from 'lucide-react';
import { useUser } from '../context/UserContext';
import { useTheme } from '../context/ThemeContext';

export default function UsersListModal() {
  const { 
    users, 
    isUsersListOpen, 
    setIsUsersListOpen, 
    openFormForCreate, 
    openFormForEdit, 
    deleteUser 
  } = useUser();
  
  const { isDark } = useTheme();
  const [filterRole, setFilterRole] = useState('Todos');
  const [search, setSearch] = useState('');
  const getRoleLabel = (role) => ({ Admin: 'Administrador', Trainer: 'Vendedor', Member: 'Cliente' }[role] || role);

  if (!isUsersListOpen) return null;

  const filteredUsers = users.filter(u => {
    const matchesSearch = u.name.toLowerCase().includes(search.toLowerCase()) || u.email.toLowerCase().includes(search.toLowerCase());
    const roleLabel = getRoleLabel(u.role);
    const matchesRole = filterRole === 'Todos' || roleLabel === filterRole;
    return matchesSearch && matchesRole;
  });

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-in fade-in duration-200">
      <div className={`w-full max-w-4xl rounded-3xl border shadow-2xl overflow-hidden transition-all duration-300 transform animate-in zoom-in-95 flex flex-col max-h-[85vh] ${
        isDark 
          ? 'bg-fitonist-card-dark border-fitonist-border-dark text-slate-100' 
          : 'bg-white border-slate-200 text-slate-900'
      }`}>
        {/* Header */}
        <div className="flex items-center justify-between px-6 py-5 border-b border-inherit bg-gradient-to-r from-fitonist-purple/10 to-transparent">
          <div>
            <h3 className="font-extrabold text-xl tracking-tight flex items-center gap-2">
              <span>Gestion de usuarios PIL</span>
              <span className="text-xs px-2.5 py-0.5 rounded-full bg-fitonist-purple text-white font-bold">
                {users.length} Registrados
              </span>
            </h3>
            <p className="text-xs text-slate-400 font-medium mt-0.5">
              Administra roles, estados y datos dinamicos de los usuarios.
            </p>
          </div>

          <div className="flex items-center gap-3">
            <button
              onClick={() => { setIsUsersListOpen(false); openFormForCreate(); }}
              className="px-4 py-2 rounded-full text-xs font-bold text-white bg-fitonist-purple hover:opacity-90 transition-all shadow-glow-purple flex items-center gap-2"
            >
              <UserPlus size={15} />
              <span>Nuevo Usuario</span>
            </button>

            <button
              onClick={() => setIsUsersListOpen(false)}
              className={`p-2 rounded-full border transition-all ${
                isDark ? 'border-slate-700 hover:bg-slate-800 text-slate-400' : 'border-slate-200 hover:bg-slate-100 text-slate-600'
              }`}
            >
              <X size={18} />
            </button>
          </div>
        </div>

        {/* Filter Controls */}
        <div className="px-6 py-4 border-b border-inherit flex items-center justify-between gap-4">
          <div className={`flex items-center gap-2 px-3 py-1.5 rounded-full border flex-1 max-w-sm ${
            isDark ? 'bg-[#1e212b] border-fitonist-border-dark' : 'bg-slate-100 border-slate-200'
          }`}>
            <Search size={16} className="text-slate-400" />
            <input
              type="text"
              placeholder="Buscar por nombre o correo..."
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              className="bg-transparent text-xs font-medium focus:outline-none w-full placeholder:text-slate-500"
            />
          </div>

          {/* Role Pill Filters */}
          <div className="flex items-center gap-2">
            {['Todos', 'Administrador', 'Vendedor', 'Cliente'].map((role) => (
              <button
                key={role}
                onClick={() => setFilterRole(role)}
                className={`px-3 py-1 rounded-full text-xs font-bold transition-all ${
                  filterRole === role
                    ? 'bg-fitonist-purple text-white shadow-sm'
                    : isDark ? 'text-slate-400 hover:text-white' : 'text-slate-600 hover:text-slate-900'
                }`}
              >
                {role}
              </button>
            ))}
          </div>
        </div>

        {/* Users Table */}
        <div className="overflow-y-auto flex-1 p-6">
          <table className="w-full text-left border-collapse">
            <thead>
              <tr className={`border-b text-[11px] font-extrabold uppercase tracking-wider ${
                isDark ? 'border-fitonist-border-dark text-slate-400' : 'border-slate-200 text-slate-500'
              }`}>
                <th className="pb-3 px-2">Usuario</th>
                <th className="pb-3 px-2">Rol</th>
                <th className="pb-3 px-2">Plan</th>
                <th className="pb-3 px-2">Estado</th>
                <th className="pb-3 px-2 text-right">Acciones</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-inherit">
              {filteredUsers.map((user) => (
                <tr key={user.id} className="hover:bg-fitonist-purple/5 transition-colors group">
                  <td className="py-3 px-2">
                    <div className="flex items-center gap-3">
                      <img
                        src={user.avatar}
                        alt={user.name}
                        className="w-10 h-10 rounded-full object-cover ring-2 ring-fitonist-purple/20"
                      />
                      <div>
                        <p className="text-xs font-bold">{user.name}</p>
                        <p className="text-[11px] text-slate-400">{user.email}</p>
                      </div>
                    </div>
                  </td>

                  <td className="py-3 px-2">
                    <span className={`px-2.5 py-1 rounded-lg text-[10px] font-bold border ${
                      getRoleLabel(user.role) === 'Administrador'
                        ? 'bg-purple-500/20 text-purple-400 border-purple-500/30' 
                        : getRoleLabel(user.role) === 'Vendedor'
                          ? 'bg-amber-500/20 text-amber-400 border-amber-500/30'
                          : 'bg-sky-500/20 text-sky-400 border-sky-500/30'
                    }`}>
                      {getRoleLabel(user.role)}
                    </span>
                  </td>

                  <td className="py-3 px-2 text-xs font-semibold text-slate-300">
                    {user.plan}
                  </td>

                  <td className="py-3 px-2">
                    <span className={`inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold ${
                      ['Active', 'Activo'].includes(user.status) ? 'bg-emerald-500/20 text-emerald-400' : 'bg-rose-500/20 text-rose-400'
                    }`}>
                      <span className={`w-1.5 h-1.5 rounded-full ${['Active', 'Activo'].includes(user.status) ? 'bg-emerald-400' : 'bg-rose-400'}`}></span>
                      {['Active', 'Activo'].includes(user.status) ? 'Activo' : 'Inactivo'}
                    </span>
                  </td>

                  <td className="py-3 px-2 text-right">
                    <div className="flex items-center justify-end gap-2">
                      <button
                        onClick={() => { setIsUsersListOpen(false); openFormForEdit(user); }}
                        className={`p-1.5 rounded-lg border transition-all ${
                          isDark ? 'border-slate-700 hover:bg-slate-800 text-slate-300' : 'border-slate-200 hover:bg-slate-100 text-slate-700'
                        }`}
                        title="Editar Usuario"
                      >
                        <Edit2 size={14} />
                      </button>

                      <button
                        onClick={() => deleteUser(user.id)}
                        className="p-1.5 rounded-lg border border-rose-500/30 text-rose-400 hover:bg-rose-500/20 transition-all"
                        title="Eliminar Usuario"
                      >
                        <Trash2 size={14} />
                      </button>
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
}
