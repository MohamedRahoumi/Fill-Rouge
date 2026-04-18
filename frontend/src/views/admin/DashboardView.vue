<script setup>
import { ref, onMounted } from 'vue';
import api from '../../services/api';
import AppLayout from '../../components/layout/AppLayout.vue';

const stats = ref({
    coachs: 0,
    parents: 0,
    joueurs: 0,
    groupes: 0,
    categories: 0,
    paiements: 0
});
const recentPaiements = ref([]);
const loading = ref(true);

const fetchDashboardData = async () => {
    loading.value = true;
    try {
        const response = await api.get('/admin/dashboard');
        stats.value = response.data.stats;
        recentPaiements.value = response.data.recentPaiements;
    } catch (error) {
        console.error('Error fetching dashboard data', error);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchDashboardData();
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'MAD' }).format(amount);
};
</script>

<template>
  <AppLayout pageTitle="Tableau de bord">
    
    <div v-if="loading" class="flex items-center justify-center h-[60vh]">
        <div class="spinner"></div>
    </div>

    <div v-else class="space-y-8">
        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-6">
            <!-- Coachs -->
            <div class="stat-card bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-all">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                        <i class="fas fa-chalkboard-user text-xl"></i>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Coachs</p>
                        <h4 class="text-2xl font-extrabold text-slate-900">{{ stats.coachs }}</h4>
                    </div>
                </div>
            </div>

            <!-- Parents -->
            <div class="stat-card bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-all">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <i class="fas fa-users text-xl"></i>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Parents</p>
                        <h4 class="text-2xl font-extrabold text-slate-900">{{ stats.parents }}</h4>
                    </div>
                </div>
            </div>

            <!-- Joueurs -->
            <div class="stat-card bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-all">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center">
                        <i class="fas fa-futbol text-xl"></i>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Joueurs</p>
                        <h4 class="text-2xl font-extrabold text-slate-900">{{ stats.joueurs }}</h4>
                    </div>
                </div>
            </div>

            <!-- Groupes -->
            <div class="stat-card bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-all">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                        <i class="fas fa-users-viewfinder text-xl"></i>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Groupes</p>
                        <h4 class="text-2xl font-extrabold text-slate-900">{{ stats.groupes }}</h4>
                    </div>
                </div>
            </div>

            <!-- Catégories -->
            <div class="stat-card bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-all">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                        <i class="fas fa-layer-group text-xl"></i>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Catégories</p>
                        <h4 class="text-2xl font-extrabold text-slate-900">{{ stats.categories }}</h4>
                    </div>
                </div>
            </div>

            <!-- Paiements -->
            <div class="stat-card bg-brand-600 p-6 rounded-2xl border border-brand-700 shadow-lg shadow-brand-500/20">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-white/20 text-white flex items-center justify-center">
                        <i class="fas fa-credit-card text-xl"></i>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-white/70 uppercase tracking-wider">Revenus</p>
                        <h4 class="text-xl font-extrabold text-white">{{ formatCurrency(stats.paiements) }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Payments Table -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <div>
                    <h3 class="text-lg font-bold text-slate-900">Paiements récents</h3>
                    <p class="text-sm text-slate-500">Dernières transactions enregistrées</p>
                </div>
                <router-link to="/admin/paiements" class="text-sm font-semibold text-brand-600 hover:text-brand-700 underline underline-offset-4">Voir tout</router-link>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50">
                            <th class="px-6 py-4 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Parent</th>
                            <th class="px-6 py-4 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Montant</th>
                            <th class="px-6 py-4 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Statut</th>
                            <th class="px-6 py-4 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="paiement in recentPaiements" :key="paiement.id" class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-600 font-bold text-xs">
                                        {{ paiement.parent.prenom.charAt(0) }}{{ paiement.parent.nom.charAt(0) }}
                                    </div>
                                    <span class="text-sm font-semibold text-slate-700">{{ paiement.parent.prenom }} {{ paiement.parent.nom }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm font-bold text-slate-900">{{ formatCurrency(paiement.montant) }}</td>
                            <td class="px-6 py-4">
                                <span 
                                    class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider"
                                    :class="paiement.statut === 'paid' ? 'bg-emerald-100 text-emerald-700' : 'bg-orange-100 text-orange-700'"
                                >
                                    {{ paiement.statut === 'paid' ? 'Payé' : 'En attente' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-500">{{ new Date(paiement.created_at).toLocaleDateString() }}</td>
                        </tr>
                        <tr v-if="recentPaiements.length === 0">
                            <td colspan="4" class="px-6 py-10 text-center text-slate-500 italic">Aucun paiement trouvé.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
  </AppLayout>
</template>
