<script setup>
import { onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useParentStore } from '../../stores/parent';
import AppLayout from '../../components/layout/AppLayout.vue';

const route = useRoute();
const parentStore = useParentStore();
const joueurId = route.params.id;

onMounted(() => {
    parentStore.fetchJoueur(joueurId);
});
</script>

<template>
  <AppLayout pageTitle="Détails du Joueur">
    <div v-if="!parentStore.currentJoueur" class="flex justify-center h-64"><div class="spinner"></div></div>
    <div v-else class="space-y-8 animate-fade-in">
        
        <!-- Profile Header -->
        <div class="bg-white p-8 rounded-[40px] border border-slate-200 shadow-sm flex flex-col md:flex-row items-center gap-8">
            <div class="w-32 h-32 rounded-[48px] bg-brand-50 text-brand-600 flex items-center justify-center text-5xl font-black shadow-inner">
                {{ parentStore.currentJoueur.joueur.prenom.charAt(0) }}
            </div>
            <div class="text-center md:text-left flex-1">
                <h2 class="text-3xl font-black text-slate-900">{{ parentStore.currentJoueur.joueur.prenom }} {{ parentStore.currentJoueur.joueur.nom }}</h2>
                <div class="flex flex-wrap justify-center md:justify-start gap-4 mt-3">
                    <span class="px-3 py-1 bg-slate-100 text-slate-600 rounded-full text-xs font-bold uppercase tracking-wider">
                        <i class="fas fa-layer-group mr-1.5 opacity-50"></i>{{ parentStore.currentJoueur.joueur.categorie?.nom }}
                    </span>
                    <span class="px-3 py-1 bg-brand-50 text-brand-600 rounded-full text-xs font-bold uppercase tracking-wider">
                        <i class="fas fa-users mr-1.5 opacity-50"></i>{{ parentStore.currentJoueur.joueur.groupe?.nom }}
                    </span>
                </div>
            </div>
            <div class="flex gap-4">
               <div class="text-center px-6 py-3 bg-slate-50 rounded-3xl border border-slate-100">
                  <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Présences</p>
                  <p class="text-xl font-black text-slate-800">{{ parentStore.currentJoueur.joueur.presences?.filter(p => p.est_present).length || 0 }}</p>
               </div>
               <div class="text-center px-6 py-3 bg-slate-50 rounded-3xl border border-slate-100">
                  <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Évaluations</p>
                  <p class="text-xl font-black text-slate-800">{{ parentStore.currentJoueur.joueur.evaluations?.length || 0 }}</p>
               </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Evaluations Timeline -->
            <div class="bg-white rounded-[40px] border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-8 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-xl font-bold text-slate-900">Évaluations techniques</h3>
                    <i class="fas fa-chart-line text-brand-500"></i>
                </div>
                <div class="p-8 space-y-6">
                    <div v-if="parentStore.currentJoueur.joueur.evaluations?.length === 0" class="text-center py-10 text-slate-400 italic">
                        Aucune évaluation pour le moment.
                    </div>
                    <div v-else v-for="eval_ in parentStore.currentJoueur.joueur.evaluations" :key="eval_.id" class="relative pl-8 border-l-2 border-slate-100 pb-6 last:pb-0">
                        <div class="absolute -left-[9px] top-0 w-4 h-4 rounded-full bg-white border-2 border-brand-500"></div>
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-xs text-slate-400 font-bold uppercase">{{ new Date(eval_.date_evaluation).toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' }) }}</p>
                                <h5 class="text-sm font-bold text-slate-900 mt-1">Évaluation par Coach {{ eval_.coach?.nom }}</h5>
                            </div>
                            <div class="px-3 py-1 bg-brand-50 text-brand-600 rounded-lg font-black text-sm">
                                {{ eval_.note }}/10
                            </div>
                        </div>
                        <p class="text-sm text-slate-500 mt-3 bg-slate-50 p-4 rounded-2xl italic">" {{ eval_.commentaire }} "</p>
                    </div>
                </div>
            </div>

            <!-- Attendance History -->
            <div class="bg-white rounded-[40px] border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-8 border-b border-slate-100">
                    <h3 class="text-xl font-bold text-slate-900">Suivi des séances</h3>
                </div>
                <div class="p-8">
                    <div class="space-y-4">
                        <div v-for="pres in parentStore.currentJoueur.joueur.presences?.slice(0, 10)" :key="pres.id" class="flex items-center justify-between p-4 rounded-3xl border border-slate-100">
                            <div>
                                <p class="text-sm font-bold text-slate-800">{{ pres.seance?.titre || 'Séance d\'entraînement' }}</p>
                                <p class="text-[10px] text-slate-400 font-bold uppercase">{{ new Date(pres.seance?.date_seance).toLocaleDateString() }}</p>
                            </div>
                            <span 
                                class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider"
                                :class="pres.est_present ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700'"
                            >
                                {{ pres.est_present ? 'Présent' : 'Absent' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
  </AppLayout>
</template>
