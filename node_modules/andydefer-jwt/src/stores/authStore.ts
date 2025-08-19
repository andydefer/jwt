import { router } from '@inertiajs/react';
import axios from 'axios';
import { create } from 'zustand';
import { persist } from 'zustand/middleware';
import { AuthState } from '../types';
export const useAuthStore = create<AuthState>()(
  persist(
    (set, get) => ({
      token: null,
      user: null,
      isLoading: false,
      error: null,
      isInitialized: false,

      initialize: async () => {
        if (get().isInitialized || get().isLoading) return;
        set({ isLoading: true });
        try {
          const storedToken = get().token;
          if (storedToken) {
            await get().fetchUser();
          }
        } catch {
          set({ token: null, user: null });
        } finally {
          set({ isLoading: false, isInitialized: true });
        }
      },

      login: async (email, password, deviceId) => {
        set({ isLoading: true, error: null });
        try {
          const response = await axios.post('/jwt/login', {
            email,
            password,
            device_id: deviceId || crypto.randomUUID(),
          });

          const token = response.data.token || response.data.data?.token;
          if (!token) throw new Error('Token not returned');

          set({ token, isLoading: false, isInitialized: true });
          await get().fetchUser();
        } catch (error: any) {
          set({ error: error.response?.data?.message || 'Login failed', isLoading: false });
          throw error;
        }
      },

      register: async (name, email, password, deviceId) => {
        set({ isLoading: true, error: null });
        try {
          const response = await axios.post('/jwt/register', {
            name,
            email,
            password,
            password_confirmation: password,
            device_id: deviceId || crypto.randomUUID(),
          });

          const token = response.data.token || response.data.data?.token;
          const user = response.data.user || response.data.data?.user;

          if (!token || !user) throw new Error('Token or user not returned');

          set({ token, user, isLoading: false, isInitialized: true });
        } catch (error: any) {
          set({ error: error.response?.data?.message || 'Registration failed', isLoading: false });
          throw error;
        }
      },

      logout: async () => {
        const token = get().token;
        try {
          if (token) {
            await axios.post('/jwt/logout', {}, {
              headers: { Authorization: `Bearer ${token}` },
            });
          }
        } finally {
          set({ token: null, user: null, isInitialized: true });
          router.visit('/login');
        }
      },

      fetchUser: async () => {
        const token = get().token;
        if (!token) return;
        try {
          const response = await axios.get('/jwt/user', {
            headers: { Authorization: `Bearer ${token}` },
          });
          set({ user: response.data.user || response.data.data });
        } catch (error: any) {
          if (error.response?.status === 401) {
            set({ token: null, user: null });
          }
          throw error;
        }
      },

      refreshToken: async () => {
        const token = get().token;
        if (!token) return;

        set({ isLoading: true });
        try {
          const response = await axios.post('/jwt/refresh', {}, {
            headers: { Authorization: `Bearer ${token}` },
          });
          set({ token: response.data.token || response.data.data?.token });
        } catch (error) {
          console.error('Token refresh failed', error);
        } finally {
          set({ isLoading: false });
        }
      },
    }),
    {
      name: 'jwt-auth-storage',
      partialize: (state) => ({ token: state.token, user: state.user }),
    },
  ),
);
