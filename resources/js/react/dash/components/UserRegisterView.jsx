import React, { useState } from 'react';
import { 
  UserPlus, 
  CheckCircle2, 
  Sparkles, 
  Users, 
  ShieldCheck, 
  ArrowRight,
  TrendingUp,
  Award
} from 'lucide-react';
import { useUser } from '../context/UserContext';
import { useTheme } from '../context/ThemeContext';

export default function UserRegisterView() {
  const { addUser, users, setCurrentDashboard } = useUser();
  const { isDark } = useTheme();

  const [formData, setFormData] = useState({
    name: '',
    email: '',
    role: 'Cliente',
    gender: 'Femenino',
    plan: 'Pro Fitness',
    status: 'Activo',
    avatar: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80&w=200'
  });

  const [registeredSuccess, setRegisteredSuccess] = useState(false);

  const avatarOptions = [
    'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80&w=200',
    'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&q=80&w=200',
    'https://images.unsplash.com/photo-1517841905240-472988babdf9?auto=format&fit=crop&q=80&w=200',
    'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=200',
    'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&q=80&w=200',
  ];

  const handleSubmit = (e) => {
    e.preventDefault();
    if (!formData.name || !formData.email) return;

    addUser(formData);
    setRegisteredSuccess(true);
    setTimeout(() => setRegisteredSuccess(false), 4000);
    setFormData({
      name: '',
      email: '',
      role: 'Cliente',
      gender: 'Femenino',
      plan: 'Pro Fitness',
      status: 'Activo',
      avatar: avatarOptions[0]
    });
  };

  return (
    <div className="w-full max-w-[1400px] mx-auto space-y-6">
      {/* Header Banner */}
      <div className={`p-6 rounded-3xl border flex flex-col md:flex-row items-start md:items-center justify-between gap-4 transition-all ${
        isDark 
          ? 'bg-gradient-to-r from-indigo-900/40 via-fitonist-card-dark to-purple-900/30 border-fitonist-border-dark' 
          : 'bg-gradient-to-r from-indigo-50 via-white to-purple-50 border-indigo-200 shadow-sm'
      }`}>
        <div className="flex items-center gap-4">
          <div className="w-14 h-14 rounded-2xl bg-gradient-to-tr from-indigo-600 to-fitonist-purple text-white flex items-center justify-center shadow-glow-purple flex-shrink-0">
            <UserPlus size={28} />
          </div>
          <div>
            <h1 className="text-2xl font-black tracking-tight">Registro Oficial de Usuarios</h1>
            <p className="text-sm text-slate-400 font-medium">
              Anade un nuevo perfil para actualizar metricas en vivo del panel.
            </p>
          </div>
        </div>

        <div className="flex items-center gap-3">
          <button
            onClick={() => setCurrentDashboard('user_list')}
            className={`px-5 py-2.5 rounded-full text-xs font-bold border transition-all flex items-center gap-2 ${
              isDark ? 'border-slate-700 bg-slate-800 text-white hover:bg-slate-700' : 'border-indigo-200 bg-white text-indigo-700 hover:bg-indigo-50 shadow-sm'
            }`}
          >
            <Users size={16} />
            <span>Ver Lista ({users.length})</span>
          </button>
        </div>
      </div>

      {/* Main Registration Grid */}
      <div className="grid grid-cols-1 lg:grid-cols-12 gap-6">
        {/* Left Column: Form (8 cols) */}
        <div className={`lg:col-span-8 p-8 rounded-3xl border transition-all ${
          isDark 
            ? 'bg-fitonist-card-dark border-fitonist-border-dark text-slate-100 shadow-xl' 
            : 'bg-white border-indigo-100 text-slate-900 shadow-md'
        }`}>
          {registeredSuccess && (
            <div className="mb-6 p-4 rounded-2xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-400 text-xs font-bold flex items-center gap-3 animate-in fade-in">
              <CheckCircle2 size={20} />
              <span>Usuario creado exitosamente. Metricas del panel actualizadas.</span>
            </div>
          )}

          <form onSubmit={handleSubmit} className="space-y-6">
            {/* Avatar Gallery */}
            <div>
              <label className="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-3">
                Selecciona la foto de perfil
              </label>
              <div className="flex items-center gap-4">
                {avatarOptions.map((url, idx) => (
                  <img
                    key={idx}
                    src={url}
                    alt={`Avatar ${idx}`}
                    onClick={() => setFormData({ ...formData, avatar: url })}
                    className={`w-14 h-14 rounded-2xl object-cover cursor-pointer transition-all ${
                      formData.avatar === url 
                        ? 'ring-4 ring-indigo-500 scale-110 shadow-lg' 
                        : 'opacity-50 hover:opacity-100'
                    }`}
                  />
                ))}
              </div>
            </div>

            {/* Inputs Grid */}
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-6">
              <div>
                <label className="text-xs font-bold text-slate-400 block mb-2">Nombre Completo *</label>
                <input
                  type="text"
                  required
                  placeholder="Ej. Mateo Rossi"
                  value={formData.name}
                  onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                  className={`w-full px-4 py-3 rounded-2xl border text-sm font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500 ${
                    isDark ? 'bg-[#1e212b] border-fitonist-border-dark text-white' : 'bg-slate-50 border-slate-200 text-slate-900'
                  }`}
                />
              </div>

              <div>
                <label className="text-xs font-bold text-slate-400 block mb-2">Correo Electrónico *</label>
                <input
                  type="email"
                  required
                  placeholder="mateo@fitonist.net"
                  value={formData.email}
                  onChange={(e) => setFormData({ ...formData, email: e.target.value })}
                  className={`w-full px-4 py-3 rounded-2xl border text-sm font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500 ${
                    isDark ? 'bg-[#1e212b] border-fitonist-border-dark text-white' : 'bg-slate-50 border-slate-200 text-slate-900'
                  }`}
                />
              </div>

              <div>
                <label className="text-xs font-bold text-slate-400 block mb-2">Rol de Usuario</label>
                <select
                  value={formData.role}
                  onChange={(e) => setFormData({ ...formData, role: e.target.value })}
                  className={`w-full px-4 py-3 rounded-2xl border text-xs font-bold focus:outline-none ${
                    isDark ? 'bg-[#1e212b] border-fitonist-border-dark text-white' : 'bg-slate-50 border-slate-200 text-slate-900'
                  }`}
                >
                  <option value="Administrador">Administrador principal</option>
                  <option value="Vendedor">Vendedor</option>
                  <option value="Cliente">Cliente activo</option>
                </select>
              </div>

              <div>
                <label className="text-xs font-bold text-slate-400 block mb-2">Género</label>
                <select
                  value={formData.gender}
                  onChange={(e) => setFormData({ ...formData, gender: e.target.value })}
                  className={`w-full px-4 py-3 rounded-2xl border text-xs font-bold focus:outline-none ${
                    isDark ? 'bg-[#1e212b] border-fitonist-border-dark text-white' : 'bg-slate-50 border-slate-200 text-slate-900'
                  }`}
                >
                  <option value="Femenino">Femenino</option>
                  <option value="Masculino">Masculino</option>
                </select>
              </div>

              <div>
                <label className="text-xs font-bold text-slate-400 block mb-2">Plan de Membresía</label>
                <select
                  value={formData.plan}
                  onChange={(e) => setFormData({ ...formData, plan: e.target.value })}
                  className={`w-full px-4 py-3 rounded-2xl border text-xs font-bold focus:outline-none ${
                    isDark ? 'bg-[#1e212b] border-fitonist-border-dark text-white' : 'bg-slate-50 border-slate-200 text-slate-900'
                  }`}
                >
                  <option value="Pro Fitness">Pro Fitness ($49/mes)</option>
                  <option value="Elite Coach">Elite Coach ($99/mes)</option>
                  <option value="Pase mensual">Pase Mensual ($29/mes)</option>
                  <option value="Annual Flex">Anual Flex ($399/año)</option>
                </select>
              </div>

              <div>
                <label className="text-xs font-bold text-slate-400 block mb-2">Estado Inicial</label>
                <select
                  value={formData.status}
                  onChange={(e) => setFormData({ ...formData, status: e.target.value })}
                  className={`w-full px-4 py-3 rounded-2xl border text-xs font-bold focus:outline-none ${
                    isDark ? 'bg-[#1e212b] border-fitonist-border-dark text-white' : 'bg-slate-50 border-slate-200 text-slate-900'
                  }`}
                >
                  <option value="Activo">Activo inmediatamente</option>
                  <option value="Inactivo">Inactivo pendiente</option>
                </select>
              </div>
            </div>

            {/* Submit Action Button */}
            <div className="pt-4 flex items-center justify-end">
              <button
                type="submit"
                className="w-full sm:w-auto px-8 py-3.5 rounded-2xl text-sm font-extrabold text-white bg-gradient-to-r from-indigo-600 to-fitonist-purple hover:opacity-95 shadow-lg flex items-center justify-center gap-2 transform hover:scale-[1.02] transition-all"
              >
                <CheckCircle2 size={18} />
                <span>Registrar usuario y actualizar panel</span>
              </button>
            </div>
          </form>
        </div>

        {/* Right Column: Live Preview Card & Recent Users (4 cols) */}
        <div className="lg:col-span-4 space-y-6">
          {/* Live Preview Badge */}
          <div className={`p-6 rounded-3xl border ${
            isDark ? 'bg-[#151722] border-fitonist-border-dark' : 'bg-white border-indigo-100 shadow-md'
          }`}>
            <h3 className="text-xs font-extrabold uppercase tracking-wider text-slate-400 mb-4 flex items-center gap-2">
              <Sparkles size={14} className="text-amber-400" /> Previsualización en Vivo
            </h3>

            <div className="flex items-center gap-4 p-4 rounded-2xl bg-indigo-500/10 border border-indigo-500/20">
              <img
                src={formData.avatar}
                alt="Preview"
                className="w-14 h-14 rounded-full object-cover ring-2 ring-indigo-500/50"
              />
              <div className="flex-1 min-w-0">
                <h4 className="font-extrabold text-sm truncate">{formData.name || 'Nombre del usuario'}</h4>
                <p className="text-xs text-slate-400 truncate">{formData.email || 'correo@ejemplo.com'}</p>
                <div className="flex items-center gap-2 mt-2">
                  <span className="px-2 py-0.5 rounded-md text-[10px] font-bold bg-indigo-500/20 text-indigo-400">
                    {formData.role}
                  </span>
                  <span className="px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-500/20 text-emerald-400">
                    {formData.plan}
                  </span>
                </div>
              </div>
            </div>
          </div>

          {/* Quick Metrics Sync Info */}
          <div className={`p-6 rounded-3xl border ${
            isDark ? 'bg-[#151722] border-fitonist-border-dark' : 'bg-white border-indigo-100 shadow-md'
          }`}>
            <h3 className="text-sm font-extrabold mb-3 flex items-center gap-2">
              <TrendingUp size={16} className="text-emerald-400" /> Sincronización Automática
            </h3>
            <p className="text-xs text-slate-400 leading-relaxed">
              Cada usuario agregado actualizara automaticamente los indicadores del panel.
            </p>
          </div>
        </div>
      </div>
    </div>
  );
}
