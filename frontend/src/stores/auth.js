import { defineStore } from 'pinia';
import api from '../services/api';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: JSON.parse(localStorage.getItem('user')) || null,
    token: localStorage.getItem('auth_token') || null,
    loading: false,
    error: null,
  }),
  getters: {
    isAuthenticated: (state) => !!state.token,
    isAdmin: (state) => state.user?.role === 'superadmin',
    isCoach: (state) => state.user?.role === 'coach',
    isParent: (state) => state.user?.role === 'parent',
  },
  actions: {
    async login(email, password) {
      this.loading = true;
      this.error = null;
      try {
        const response = await api.post('/login', { email, password });
        this.user = response.data.user;
        this.token = response.data.access_token;
        localStorage.setItem('auth_token', this.token);
        localStorage.setItem('user', JSON.stringify(this.user));
        return true;
      } catch (err) {
        this.error = err.response?.data?.message || 'Erreur de connexion';
        return false;
      } finally {
        this.loading = false;
      }
    },
    async logout() {
      try {
        await api.post('/logout');
      } catch (err) {
        console.error('Logout error', err);
      } finally {
        this.user = null;
        this.token = null;
        localStorage.removeItem('auth_token');
        localStorage.removeItem('user');
        window.location.href = '/login';
      }
    },
    async fetchMe() {
      try {
        const response = await api.get('/me');
        this.user = response.data;
        localStorage.setItem('user', JSON.stringify(this.user));
      } catch (err) {
        this.logout();
      }
    }
  },
});
