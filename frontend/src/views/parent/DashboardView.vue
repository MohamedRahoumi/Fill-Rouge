<script setup>
import { onMounted } from 'vue';
import { useParentStore } from '../../stores/parent';
import AppLayout from '../../components/layout/AppLayout.vue';

const parentStore = useParentStore();

onMounted(() => {
  parentStore.fetchDashboard();
});

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'MAD' }).format(amount);
};
</script>

<template>
  <AppLayout pageTitle="Espace Parent">
    <div v-if="parentStore.loading" class="flex justify-center h-64"><div class="spinner"></div></div>
    <div v-else class="space-y-8 animate-fade-in">
        
        <!-- Children Section -->
        <div>
            <h3 class="text-xl font-bold text-slate-800 mb-6 flex items-center gap-2">
                <i class="fas fa-child text-brand-500"></i>
                Mes Enfants
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <router-link 
                    v-for="joueur in parentStore.dashboard.joueurs" 
                    :key="joueur.id"
                    :to="`/parent/joueurs/${joueur.id}`"
                    class="bg-white p-8 rounded-[40px] border border-slate-200 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all group"
                >
                    <div class="flex items-center gap-4 mb-6">
                        <img :src="joueur.photo_url" class="w-16 h-16 rounded-3xl object-cover border-4 border-brand-50 shadow-sm" :alt="joueur.prenom">
                        <div>
                            <h4 class="text-xl font-bold text-slate-900">{{ joueur.prenom }} {{ joueur.nom }}</h4>
                            <span class="px-2.5 py-0.5 bg-slate-100 text-slate-500 rounded-full text-[10px] font-bold uppercase tracking-wider">{{ joueur.categorie?.nom || 'Sans catégorie' }}</span>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-slate-400">Groupe</span>
                            <span class="text-slate-900 font-bold">{{ joueur.groupe?.nom || 'Non assigné' }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-slate-400">Évaluations</span>
                            <span class="text-slate-900 font-bold">{{ joueur.evaluations_count ?? joueur.evaluations?.length ?? 0 }}</span>
                        </div>
                    </div>

                    <div class="mt-8 pt-6 border-t border-slate-50 flex items-center justify-between text-brand-600 font-bold text-sm">
                        Suivre les progrès
                        <i class="fas fa-arrow-right text-xs"></i>
                    </div>
                </router-link>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Recent Payments -->
            <div class="bg-white rounded-[40px] border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-8 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-xl font-bold text-slate-900">Paiements récents</h3>
                    <router-link to="/parent/paiement" class="text-sm font-bold text-brand-600">Nouveau paiement</router-link>
                </div>
                <div class="p-6">
                    <div v-if="parentStore.dashboard.paiements?.length === 0" class="text-center py-10 text-slate-400 italic">
                        Aucun paiement enregistré.
                    </div>
                    <div v-else class="space-y-4">
                        <div v-for="pay in parentStore.dashboard.paiements" :key="pay.id" class="flex items-center justify-between p-4 rounded-3xl bg-slate-50 hover:bg-slate-100 transition-colors">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-2xl bg-white flex items-center justify-center text-slate-400 shadow-sm">
                                    <i class="fas fa-credit-card"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-slate-900">{{ new Date(pay.mois_concerne).toLocaleDateString('fr-FR', { month: 'long', year: 'numeric' }) }}</p>
                                    <p class="text-[10px] text-slate-400 uppercase font-black">{{ pay.statut }}</p>
                                </div>
                            </div>
                            <span class="text-sm font-black text-slate-900">{{ formatCurrency(pay.montant) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notifications -->
            <div class="bg-white rounded-[40px] border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-8 border-b border-slate-100">
                    <h3 class="text-xl font-bold text-slate-900">Notifications</h3>
                </div>
                <div class="p-6">
                    <div v-if="parentStore.dashboard.notifications?.length === 0" class="text-center py-10 text-slate-400 italic">
                        Aucune nouvelle notification.
                    </div>
                    <div v-else class="space-y-4">
                        <div v-for="notif in parentStore.dashboard.notifications" :key="notif.id" class="p-4 rounded-3xl bg-brand-50/50 border border-brand-100">
                            <h5 class="text-sm font-bold text-slate-900">{{ notif.titre }}</h5>
                            <p class="text-xs text-slate-600 mt-1">{{ notif.message }}</p>
                            <p class="text-[10px] text-brand-600 font-bold mt-2 font-mono">{{ new Date(notif.created_at).toLocaleDateString() }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
  </AppLayout>
</template>
