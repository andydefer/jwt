import { useEffect } from 'react';
import { useAuthStore } from '../stores/authStore';
import type { User } from '../types';

interface UseAuthReturn {
  isAuthenticated: boolean;
  user: User | null;
  isLoading: boolean;
  error: string | null;
  login: (email: string, password: string, deviceId?: string) => Promise<void>;
  register: (name: string, email: string, password: string, deviceId?: string) => Promise<void>;
  logout: () => void;
}

export const useAuth = (): UseAuthReturn => {
  const {
    token,
    user,
    isLoading,
    error,
    login,
    logout,
    register,
    initialize,
    isInitialized,
  } = useAuthStore();

  useEffect(() => {
    if (!isInitialized) {
      initialize().catch(() => {
        // On ignore les erreurs d'auto-login
      });
    }
  }, [initialize, isInitialized]);

  return {
    isAuthenticated: !!token,
    user,
    isLoading,
    error,
    login,
    register,
    logout,
  };
};
