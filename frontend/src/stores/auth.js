import { defineStore } from 'pinia';
import api from '../api';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: localStorage.getItem('tirtapay_token') || null,
    user: JSON.parse(localStorage.getItem('tirtapay_user') || 'null'),
    loading: false,
    error: null,
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
    role: (state) => state.user?.role || null,
    isSuperAdmin: (state) => state.user?.role === 'super_admin',
    isAdmin: (state) => ['super_admin', 'admin'].includes(state.user?.role),
    isPelanggan: (state) => state.user?.role === 'pelanggan',
    customer: (state) => state.user?.customer || null,
  },

  actions: {
    async login(phone, password) {
      this.loading = true;
      this.error = null;
      try {
        const response = await api.post('/auth/login', { phone, password });
        this.token = response.data.token;
        this.user = response.data.user;

        localStorage.setItem('tirtapay_token', this.token);
        localStorage.setItem('tirtapay_user', JSON.stringify(this.user));

        return response.data;
      } catch (err) {
        this.error = err.response?.data?.message || 'Login gagal, periksa nomor HP dan password.';
        throw err;
      } finally {
        this.loading = false;
      }
    },

    async register(name, phone, password, address = '') {
      this.loading = true;
      this.error = null;
      try {
        const response = await api.post('/auth/register', { name, phone, password, address });
        this.token = response.data.token;
        this.user = response.data.user;

        localStorage.setItem('tirtapay_token', this.token);
        localStorage.setItem('tirtapay_user', JSON.stringify(this.user));

        return response.data;
      } catch (err) {
        this.error = err.response?.data?.message || 'Registrasi gagal.';
        throw err;
      } finally {
        this.loading = false;
      }
    },

    async fetchMe() {
      if (!this.token) return null;
      try {
        const response = await api.get('/auth/me');
        this.user = response.data.user;
        localStorage.setItem('tirtapay_user', JSON.stringify(this.user));
        return this.user;
      } catch (err) {
        this.logout();
        return null;
      }
    },

    async logout() {
      try {
        if (this.token) {
          await api.post('/auth/logout');
        }
      } catch (e) {
        // ignore
      } finally {
        this.token = null;
        this.user = null;
        localStorage.removeItem('tirtapay_token');
        localStorage.removeItem('tirtapay_user');
      }
    },
  },
});
