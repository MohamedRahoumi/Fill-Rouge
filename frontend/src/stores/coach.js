import { defineStore } from 'pinia';
import api from '../services/api';

export const useCoachStore = defineStore('coach', {
  state: () => ({
    dashboard: {
        coach: null,
        groupes: [],
        prochainesSeances: []
    },
    currentGroupe: null,
    currentSeance: null,
    loading: false,
    error: null,
  }),
  actions: {
    async fetchDashboard() {
        this.loading = true;
        try {
            const response = await api.get('/coach/dashboard');
            this.dashboard = response.data;
        } catch (err) {
            this.error = "Erreur dashboard coach";
        } finally {
            this.loading = false;
        }
    },
    async fetchGroupe(id) {
        this.loading = true;
        try {
            const response = await api.get(`/coach/groupes/${id}`);
            this.currentGroupe = response.data;
        } finally {
            this.loading = false;
        }
    },
    async addSeance(groupeId, data) {
        await api.post(`/coach/groupes/${groupeId}/seances`, data);
        await this.fetchGroupe(groupeId);
        await this.fetchDashboard();
    },
    async assignJoueur(groupeId, joueurId) {
        await api.post(`/coach/groupes/${groupeId}/assign-joueur`, { joueur_id: joueurId });
        await this.fetchGroupe(groupeId);
    },
    async fetchAppel(seanceId) {
        const response = await api.get(`/coach/seances/${seanceId}/appel`);
        this.currentSeance = response.data;
    },
    async submitAppel(seanceId, data) {
        await api.post(`/coach/seances/${seanceId}/appel`, data);
    },
    async submitEvaluation(seanceId, joueurId, data) {
        await api.post(`/coach/seances/${seanceId}/joueurs/${joueurId}/evaluation`, data);
    }
  }
});
