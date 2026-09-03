import React, { useState, useEffect } from 'react';
import { X, UserPlus, CheckCircle2, Shield, UserCheck, Sparkles, Image as ImageIcon } from 'lucide-react';
import { useUser } from '../context/UserContext';
import { useTheme } from '../context/ThemeContext';

export default function UserFormModal() {
  const { isFormOpen, setIsFormOpen, editingUser, addUser, updateUser } = useUser();
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

  const avatarOptions = [
    'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80&w=200',
    'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&q=80&w=200',
    'https://images.unsplash.com/photo-1517841905240-472988babdf9?auto=format&fit=crop&q=80&w=200',
    'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=200',
    'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&q=80&w=200',
  ];

  useEffect(() => {
    if (editingUser) {
      setFormData(editingUser);
    } else {
      setFormData({
        name: '',
        email: '',
        role: 'Cliente',
        gender: 'Femenino',
        plan: 'Pro Fitness',
        status: 'Activo',
        avatar: avatarOptions[0]
      });
    }
  }, [editingUser, isFormOpen]);

  if (!isFormOpen) return null;

  const handleSubmit = (e) => {
    e.preventDefault();
    if (!formData.name || !formData.email) return;

    if (editingUser) {
      updateUser(formData);
    } else {
      addUser(formData);
    }
    setIsFormOpen(false);
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-in fade-in duration-200">
      <div className={`w-full max-w-lg rounded-3xl border shadow-2xl overflow-hidden transition-all duration-300 transform animate-in zoom-in-95 ${
        isDark 
          ? 'bg-fitonist-card-dark border-fitonist-border-dark text-slate-100' 
          : 'bg-white border-slate-200 text-slate-900'
      }`}>
        {/* Header */}
        <div className="flex items-center justify-between px-6 py-5 border-b border-inherit bg-gradient-to-r from-fitonist-purple/10 to-transparent">
          <div className="flex items-center gap-3">
            <div className="p-2.5 rounded-2xl bg-fitonist-purple text-white shadow-glow-purple">
              <UserPlus size={20} />
            </div>
            <div>
              <h3 className="font-extrabold text-lg tracking-tight">
                {editingUser ? 'Editar Usuario' : 'Formulario de Usuario'}
              </h3>
              <p className="text-xs text-slate-400 font-medium">
                {editingUser ? 'Actualiza los datos del perfil' : 'Registra un nuevo usuario en la plataforma'}
              </p>
            </div>
          </div>

          <button
            onClick={() => setIsFormOpen(false)}
            className={`p-2 rounded-full border transition-all ${
              isDark ? 'border-slate-700 hover:bg-slate-800 text-slate-400' : 'border-slate-200 hover:bg-slate-100 text-slate-600'
            }`}
          >
            <X size={18} />
          </button>
        </div>

        {/* Form Content */}
        <form onSubmit={handleSubmit} className="p-6 space-y-4 max-h-[80vh] overflow-y-auto">
          {/* Avatar Selector */}
          <div>
            <label className="text-xs font-bold text-slate-400 block mb-2">Seleccionar Avatar</label>
            <div className="flex items-center gap-3">
              {avatarOptions.map((url, idx) => (
                <img
                  key={idx}
                  src={url}
                  alt={`Avatar ${idx}`}
                  onClick={() => setFormData({ ...formData, avatar: url })}
                  className={`w-11 h-11 rounded-full object-cover cursor-pointer transition-all ${
                    formData.avatar === url ? 'ring-4 ring-fitonist-purple scale-110 shadow-glow-purple' : 'opacity-60 hover:opacity-100'
                  }`}
                />
              ))}
            </div>
          </div>

          {/* Full Name */}
          <div>
            <label className="text-xs font-bold text-slate-400 block mb-1">Nombre Completo *</label>
            <input
              type="text"
              required
              placeholder="Ej. Carlos Mendoza"
              value={formData.name}
              onChange={(e) => setFormData({ ...formData, name: e.target.value })}
              className={`w-full px-4 py-2.5 rounded-xl border text-sm font-medium focus:outline-none focus:ring-2 focus:ring-fitonist-purple ${
                isDark ? 'bg-[#1e212b] border-fitonist-border-dark text-white' : 'bg-slate-50 border-slate-200 text-slate-900'
              }`}
            />
          </div>

          {/* Email */}
          <div>
            <label className="text-xs font-bold text-slate-400 block mb-1">Correo Electrónico *</label>
            <input
              type="email"
              required
              placeholder="carlos@fitonist.net"
              value={formData.email}
              onChange={(e) => setFormData({ ...formData, email: e.target.value })}
              className={`w-full px-4 py-2.5 rounded-xl border text-sm font-medium focus:outline-none focus:ring-2 focus:ring-fitonist-purple ${
                isDark ? 'bg-[#1e212b] border-fitonist-border-dark text-white' : 'bg-slate-50 border-slate-200 text-slate-900'
              }`}
            />
          </div>

          {/* Role & Gender Row */}
          <div className="grid grid-cols-2 gap-4">
            <div>
              <label className="text-xs font-bold text-slate-400 block mb-1">Rol</label>
              <select
                value={formData.role}
                onChange={(e) => setFormData({ ...formData, role: e.target.value })}
                className={`w-full px-3.5 py-2.5 rounded-xl border text-xs font-bold focus:outline-none ${
                  isDark ? 'bg-[#1e212b] border-fitonist-border-dark text-white' : 'bg-slate-50 border-slate-200 text-slate-900'
                }`}
              >
                <option value="Administrador">Administrador</option>
                <option value="Vendedor">Vendedor</option>
                <option value="Cliente">Cliente</option>
              </select>
            </div>

            <div>
              <label className="text-xs font-bold text-slate-400 block mb-1">Género</label>
              <select
                value={formData.gender}
                onChange={(e) => setFormData({ ...formData, gender: e.target.value })}
                className={`w-full px-3.5 py-2.5 rounded-xl border text-xs font-bold focus:outline-none ${
                  isDark ? 'bg-[#1e212b] border-fitonist-border-dark text-white' : 'bg-slate-50 border-slate-200 text-slate-900'
                }`}
              >
                <option value="Femenino">Femenino</option>
                <option value="Masculino">Masculino</option>
              </select>
            </div>
          </div>

          {/* Plan & Status Row */}
          <div className="grid grid-cols-2 gap-4">
            <div>
              <label className="text-xs font-bold text-slate-400 block mb-1">Plan de Suscripción</label>
              <select
                value={formData.plan}
                onChange={(e) => setFormData({ ...formData, plan: e.target.value })}
                className={`w-full px-3.5 py-2.5 rounded-xl border text-xs font-bold focus:outline-none ${
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
              <label className="text-xs font-bold text-slate-400 block mb-1">Estado</label>
              <select
                value={formData.status}
                onChange={(e) => setFormData({ ...formData, status: e.target.value })}
                className={`w-full px-3.5 py-2.5 rounded-xl border text-xs font-bold focus:outline-none ${
                  isDark ? 'bg-[#1e212b] border-fitonist-border-dark text-white' : 'bg-slate-50 border-slate-200 text-slate-900'
                }`}
              >
                <option value="Activo">Activo</option>
                <option value="Inactivo">Inactivo</option>
              </select>
            </div>
          </div>

          {/* Footer Submit Buttons */}
          <div className="pt-4 flex items-center justify-end gap-3 border-t border-inherit">
            <button
              type="button"
              onClick={() => setIsFormOpen(false)}
              className={`px-5 py-2.5 rounded-xl text-xs font-bold border transition-all ${
                isDark ? 'border-slate-700 hover:bg-slate-800 text-slate-300' : 'border-slate-200 hover:bg-slate-100 text-slate-700'
              }`}
            >
              Cancelar
            </button>

            <button
              type="submit"
              className="px-6 py-2.5 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-fitonist-purple to-indigo-600 hover:opacity-95 shadow-glow-purple flex items-center gap-2"
            >
              <CheckCircle2 size={16} />
              <span>{editingUser ? 'Guardar Cambios' : 'Registrar Usuario'}</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}
