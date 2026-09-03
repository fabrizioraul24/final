import React, { createContext, useContext, useState, useEffect } from 'react';
import { INITIAL_USERS } from '../data/mockData';
import confetti from 'canvas-confetti';

const UserContext = createContext();

export const UserProvider = ({ children, initialMetrics, currentUser }) => {
  const [users, setUsers] = useState(() => {
    const saved = localStorage.getItem('fitonist_users');
    return saved ? JSON.parse(saved) : INITIAL_USERS;
  });

  const [currentDashboard, setCurrentDashboard] = useState(() => {
    const saved = localStorage.getItem('fitonist_active_dash');
    return (saved === 'user_list' || saved === 'dash2') ? saved : 'dash2';
  });

  const [isFormOpen, setIsFormOpen] = useState(false);
  const [isUsersListOpen, setIsUsersListOpen] = useState(false);
  const [editingUser, setEditingUser] = useState(null);
  const [searchQuery, setSearchQuery] = useState('');
  const loggedUser = {
    name: currentUser?.name || 'Usuario Pil',
    email: currentUser?.email || currentUser?.role || '',
    role: currentUser?.role || '',
    avatar: currentUser?.avatar || currentUser?.avatarUrl || null,
  };

  // Dynamic Dashboard Stats synced with User state
  const [metrics, setMetrics] = useState(() => ({
    revenue: 79675,
    revenueGrowth: '+2.4%',
    dailySubs: 312,
    dailySubsGrowth: '+4.7%',
    weeklyInstalls: 4365,
    impressions: 231841,
    activeGrowth: 1974,
    churnCount: 287,
    radialProgress: 75,
    ...(initialMetrics || {}),
  }));

  useEffect(() => {
    localStorage.setItem('fitonist_users', JSON.stringify(users));
  }, [users]);

  useEffect(() => {
    localStorage.setItem('fitonist_active_dash', currentDashboard);
  }, [currentDashboard]);

  const addUser = (newUser) => {
    const created = {
      ...newUser,
      id: `usr_${Date.now()}`,
      joinedDate: new Date().toISOString().split('T')[0],
      avatar: newUser.avatar || `https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&q=80&w=200`
    };

    setUsers(prev => [created, ...prev]);
    
    // Update live metrics!
    setMetrics(prev => ({
      ...prev,
      dailySubs: prev.dailySubs + 1,
      revenue: prev.revenue + 49,
      activeGrowth: prev.activeGrowth + 1,
    }));

    // Trigger celebration confetti animation
    confetti({
      particleCount: 90,
      spread: 80,
      origin: { y: 0.6 },
      colors: ['#0b4fc1', '#7ea6ff', '#f25a59', '#22c55e']
    });
  };

  const updateUser = (updated) => {
    setUsers(prev => prev.map(u => u.id === updated.id ? updated : u));
    setEditingUser(null);
  };

  const deleteUser = (id) => {
    setUsers(prev => prev.filter(u => u.id !== id));
  };

  const openFormForCreate = () => {
    setEditingUser(null);
    setIsFormOpen(true);
  };

  const openFormForEdit = (user) => {
    setEditingUser(user);
    setIsFormOpen(true);
  };

  return (
    <UserContext.Provider value={{
      users,
      metrics,
      setMetrics,
      currentDashboard,
      setCurrentDashboard,
      isFormOpen,
      setIsFormOpen,
      isUsersListOpen,
      setIsUsersListOpen,
      editingUser,
      setEditingUser,
      searchQuery,
      setSearchQuery,
      currentUser: loggedUser,
      addUser,
      updateUser,
      deleteUser,
      openFormForCreate,
      openFormForEdit,
    }}>
      {children}
    </UserContext.Provider>
  );
};

export const useUser = () => {
  const context = useContext(UserContext);
  if (!context) {
    throw new Error('useUser must be used within a UserProvider');
  }
  return context;
};
