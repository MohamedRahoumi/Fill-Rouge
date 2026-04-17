import { defineStore } from 'pinia';
import api from '../services/api';

export const useParentStore = defineStore('parent', {
  state: () => ({
    dashboard: {
        parent: null,
        joueurs: [],
        paiements: [],
        notifications: []
    },
    currentJoueur: null,
    loading: false,
    error: null,
  }),
  actions: {
    async fetchDashboard() {
        this.loading = true;
        try {
            const response = await api.get('/parent/dashboard');
            this.dashboard = response.data;
        } catch (err) {
            this.error = "Erreur dashboard parent";
        } finally {
            this.loading = false;
        }
    },
    async fetchJoueur(id) {
        const response = await api.get(`/parent/joueurs/${id}`);
        this.currentJoueur = response.data;
    },
    async createPaymentIntent(data) {
        const response = await api.post('/parent/paiement/intent', data);
        return response.data.client_secret;
    },
    async confirmPayment(data) {
        await api.post('/parent/paiement/confirm', data);
        await this.fetchDashboard();
    },
    async payDirect(data) {
        // For testing purpose only as defined in Backend
                const response = await api.post('/parent/paiement', data);
        await this.fetchDashboard();
                return response.data;
    }
  }
});
