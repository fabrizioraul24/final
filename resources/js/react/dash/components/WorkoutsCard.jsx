import React, { useState } from 'react';
import { ChevronDown, Dumbbell, Check } from 'lucide-react';
import { WORKOUT_DATA } from '../data/mockData';
import { useTheme } from '../context/ThemeContext';

export default function WorkoutsCard() {
  const [genderFilter, setGenderFilter] = useState('Masculino');
  const [completedWorkouts, setCompletedWorkouts] = useState({});
  const { isDark } = useTheme();

  const filteredWorkouts = WORKOUT_DATA.filter(w => {
    if (genderFilter === 'Todos') return true;
    return w.gender === genderFilter;
  });

  const toggleComplete = (id) => {
    setCompletedWorkouts(prev => ({
      ...prev,
      [id]: !prev[id]
    }));
  };

  const getTagColorClass = (color) => {
    switch(color) {
      case 'yellow': return 'bg-amber-500/20 text-amber-400 border-amber-500/30';
      case 'green': return 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30';
      case 'purple': return 'bg-purple-500/20 text-purple-400 border-purple-500/30';
      case 'pink': return 'bg-pink-500/20 text-pink-400 border-pink-500/30';
      case 'cyan': return 'bg-cyan-500/20 text-cyan-400 border-cyan-500/30';
      default: return 'bg-slate-500/20 text-slate-400 border-slate-500/30';
    }
  };

  return (
    <div className={`p-6 rounded-3xl border transition-all duration-300 flex flex-col justify-between ${
      isDark 
        ? 'bg-fitonist-card-dark border-fitonist-border-dark text-slate-100 shadow-xl' 
        : 'bg-white border-fitonist-border-light text-slate-900 shadow-sm'
    }`}>
      {/* Header & Filter */}
      <div className="flex items-center justify-between mb-4">
        <h3 className="text-lg font-bold tracking-tight">Productos destacados</h3>

        <div className="relative">
          <select
            value={genderFilter}
            onChange={(e) => setGenderFilter(e.target.value)}
            className={`appearance-none px-3.5 py-1.5 pr-8 rounded-full border text-xs font-bold focus:outline-none cursor-pointer ${
              isDark 
                ? 'bg-[#1e212b] border-fitonist-border-dark text-slate-300' 
                : 'bg-slate-100 border-slate-200 text-slate-700'
            }`}
          >
            <option value="Masculino">Masculino</option>
            <option value="Femenino">Femenino</option>
            <option value="Todos">Todas las categorias</option>
          </select>
          <ChevronDown size={14} className="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" />
        </div>
      </div>

      {/* Workout Items List */}
      <div className="space-y-3">
        {filteredWorkouts.map((item) => {
          const isDone = completedWorkouts[item.id];
          
          return (
            <div
              key={item.id}
              onClick={() => toggleComplete(item.id)}
              className={`p-3 rounded-2xl border transition-all duration-200 cursor-pointer flex items-center justify-between group ${
                isDone 
                  ? 'opacity-60 border-emerald-500/40 bg-emerald-500/5' 
                  : isDark 
                    ? 'bg-[#1e212b]/70 border-fitonist-border-dark hover:border-fitonist-purple/50 hover:bg-[#1e212b]' 
                    : 'bg-slate-50 border-slate-200 hover:border-slate-300 hover:bg-slate-100'
              }`}
            >
              {/* Thumbnail & Info */}
              <div className="flex items-center gap-3">
                <div className="relative">
                  <img
                    src={item.image}
                    alt={item.title}
                    className="w-12 h-12 rounded-xl object-cover"
                  />
                  {isDone && (
                    <div className="absolute inset-0 bg-emerald-500/80 rounded-xl flex items-center justify-center text-white">
                      <Check size={18} />
                    </div>
                  )}
                </div>

                <div className="flex flex-col">
                  <span className={`text-xs font-extrabold transition-colors ${
                    isDone ? 'line-through text-slate-400' : isDark ? 'text-slate-100' : 'text-slate-900'
                  }`}>
                    {item.title}
                  </span>
                  
                  <div className="flex items-center gap-1.5 mt-1">
                    <span className={`px-2 py-0.5 rounded-md text-[10px] font-bold border ${getTagColorClass(item.tagColor)}`}>
                      {item.category}
                    </span>
                    <span className="text-[10px] text-slate-400 font-medium">
                      {item.reps}
                    </span>
                  </div>
                </div>
              </div>

              {/* Count Badge (e.g. 39k, 31k, 27k) */}
              <div className={`px-3 py-1.5 rounded-xl text-xs font-black border ${
                isDark ? 'bg-slate-900/60 border-fitonist-border-dark text-slate-300' : 'bg-white border-slate-200 text-slate-700 shadow-sm'
              }`}>
                {item.formattedCount}
              </div>
            </div>
          );
        })}
      </div>
    </div>
  );
}
