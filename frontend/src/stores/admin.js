import { defineStore } from 'pinia';
import api from '../services/api';

export const useAdminStore = defineStore('admin', {
  state: () => ({
    stats: {},
    coachs: [],
    parents: [],
    joueurs: [],
    categories: [],
    groupes: [],
    paiements: [],
    loading: false,
    error: null,
  }),
  actions: {
    async fetchDashboard() {
      this.loading = true;
      try {
        const response = await api.get('/admin/dashboard');
        this.stats = response.data.stats;
        this.coachs = response.data.allCoachs;
        this.parents = response.data.allParents;
        this.joueurs = response.data.allJoueurs;
        this.categories = response.data.allCategories;
        this.groupes = response.data.allGroupes;
      } catch (err) {
        this.error = "Erreur lors du chargement du dashboard";
      } finally {
        this.loading = false;
      }
    },

    // Coachs
    async addCoach(data) {
      const response = await api.post('/admin/coachs', data);
      await this.fetchDashboard();
      return response.data;
    },
    async updateCoach(id, data) {
      const response = await api.put(`/admin/coachs/${id}`, data);
      await this.fetchDashboard();
      return response.data;
    },
    async deleteCoach(id) {
      await api.delete(`/admin/coachs/${id}`);
      await this.fetchDashboard();
    },

    // Parents
    async addParent(data) {
      const response = await api.post('/admin/parents', data);
      await this.fetchDashboard();
      return response.data;
    },
    async updateParent(id, data) {
      const response = await api.put(`/admin/parents/${id}`, data);
      await this.fetchDashboard();
      return response.data;
    },
    async deleteParent(id) {
      await api.delete(`/admin/parents/${id}`);
      await this.fetchDashboard();
    },

    // Joueurs
    async fetchJoueurs() {
        const response = await api.get('/admin/joueurs');
        this.joueurs = response.data.data || response.data;
    },
    async addJoueur(data) {
      await api.post('/admin/joueurs', data);
      await this.fetchDashboard();
    },
    async updateJoueur(id, data) {
      await api.put(`/admin/joueurs/${id}`, data);
      await this.fetchDashboard();
    },
    async deleteJoueur(id) {
      await api.delete(`/admin/joueurs/${id}`);
      await this.fetchDashboard();
    },

    // Paiements
    async fetchPaiements() {
      const response = await api.get('/admin/paiements');
      this.paiements = response.data.data || response.data;
    },
    async validatePaiement(id) {
      await api.post(`/admin/paiements/${id}/valider`);
      await this.fetchPaiements();
    },

    // Structure
    async addCategorie(data) {
        await api.post('/admin/categories', data);
        await this.fetchDashboard();
    },
    async updateCategorie(id, data) {
        await api.put(`/admin/categories/${id}`, data);
        await this.fetchDashboard();
    },
    async deleteCategorie(id) {
        await api.delete(`/admin/categories/${id}`);
        await this.fetchDashboard();
    },
    async addGroupe(data) {
        await api.post('/admin/groupes', data);
        await this.fetchDashboard();
    },
    async updateGroupe(id, data) {
        await api.put(`/admin/groupes/${id}`, data);
        await this.fetchDashboard();
    },
    async deleteGroupe(id) {
        await api.delete(`/admin/groupes/${id}`);
        await this.fetchDashboard();
    },
    async sendNotification(data) {
        const response = await api.post('/admin/notifications', data);
        await this.fetchDashboard();
        return response.data;
    }
  }
});
