<script setup>
import { onMounted } from 'vue';
import { useCoachStore } from '../../stores/coach';
import AppLayout from '../../components/layout/AppLayout.vue';

const coachStore = useCoachStore();

onMounted(() => {
    coachStore.fetchDashboard();
});
</script>

<template>
  <AppLayout pageTitle="Espace Coach">
    <div v-if="coachStore.loading" class="flex justify-center h-64"><div class="spinner"></div></div>
    <div v-else class="space-y-8 animate-fade-in">
        <!-- Welcome header -->
        <div class="bg-gradient-to-br from-brand-600 to-brand-700 rounded-[40px] p-8 text-white shadow-xl shadow-brand-500/20 relative overflow-hidden">
            <div class="relative z-10">
                <h2 class="text-3xl font-extrabold mb-2">Bonjour, {{ coachStore.dashboard.coach?.prenom }} !</h2>
                <p class="text-brand-100 max-w-md">Prêt pour les séances d'aujourd'hui ? Vous gérez actuellement {{ coachStore.dashboard.groupes?.length }} groupes.</p>
            </div>
            <i class="fas fa-futbol absolute -right-10 -bottom-10 text-[200px] text-white/10 rotate-12"></i>
        </div>

        <!-- Groups Grid -->
        <div>
            <h3 class="text-lg font-bold text-slate-900 mb-6 flex items-center gap-2">
                <i class="fas fa-users text-brand-500"></i>
                Mes Groupes
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <router-link 
                    v-for="grp in coachStore.dashboard.groupes" 
                    :key="grp.id"
                    :to="`/coach/groupes/${grp.id}`"
                    class="bg-white p-6 rounded-[32px] border border-slate-200 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all group"
                >
                    <div class="flex justify-between items-start mb-4">
                        <div class="w-12 h-12 rounded-2xl bg-brand-50 text-brand-600 flex items-center justify-center group-hover:bg-brand-500 group-hover:text-white transition-colors">
                            <i class="fas fa-users text-xl"></i>
                        </div>
                        <span class="px-3 py-1 bg-slate-100 text-slate-600 rounded-full text-[10px] font-bold uppercase tracking-wider">{{ grp.categorie?.nom }}</span>
                    </div>
                    <h4 class="text-lg font-bold text-slate-900 mb-1">{{ grp.nom }}</h4>
                    <p class="text-xs text-slate-500">{{ grp.joueurs?.length }} joueurs inscrits</p>
                    <div class="mt-6 pt-6 border-t border-slate-50 flex items-center justify-between text-brand-600 font-bold text-sm">
                        Voir les détails
                        <i class="fas fa-arrow-right text-xs"></i>
                    </div>
                </router-link>
            </div>
        </div>

        <!-- Next Sessions -->
        <div class="bg-white rounded-[40px] border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-8 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-xl font-bold text-slate-900">Prochaines séances</h3>
                <BaseButton variant="ghost" class="!text-xs">Calendrier complet</BaseButton>
            </div>
            <div class="p-8">
                <div v-if="coachStore.dashboard.prochainesSeances?.length === 0" class="text-center py-10 text-slate-400 italic">
                    Aucune séance planifiée prochainement.
                </div>
                <div v-else class="space-y-4">
                    <div v-for="seance in coachStore.dashboard.prochainesSeances" :key="seance.id" class="flex items-center gap-6 p-4 rounded-3xl hover:bg-slate-50 transition-colors border border-transparent hover:border-slate-100">
                        <div class="w-16 h-16 rounded-2xl bg-slate-100 flex flex-col items-center justify-center">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">{{ new Date(seance.date_seance).toLocaleDateString('fr-FR', { month: 'short' }) }}</span>
                            <span class="text-xl font-black text-slate-800">{{ new Date(seance.date_seance).getDate() }}</span>
                        </div>
                        <div class="flex-1">
                            <h5 class="font-bold text-slate-900">{{ seance.titre }}</h5>
                            <div class="flex items-center gap-3 mt-1 text-xs text-slate-500">
                                <span class="flex items-center gap-1"><i class="far fa-clock"></i> {{ seance.heure_debut }} - {{ seance.heure_fin }}</span>
                                <span class="w-1 h-1 bg-slate-300 rounded-full"></span>
                                <span class="font-semibold text-brand-600">{{ seance.groupe?.nom }}</span>
                            </div>
                        </div>
                        <router-link :to="`/coach/seances/${seance.id}/appel`" class="px-5 py-2 bg-slate-900 text-white rounded-xl text-xs font-bold hover:bg-slate-800 transition-colors">
                            Faire l'appel
                        </router-link>
                    </div>
                </div>
            </div>
        </div>
    </div>
  </AppLayout>
</template>
