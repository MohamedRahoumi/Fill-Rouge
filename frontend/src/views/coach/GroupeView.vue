<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useCoachStore } from '../../stores/coach';
import AppLayout from '../../components/layout/AppLayout.vue';
import BaseButton from '../../components/common/BaseButton.vue';
import BaseModal from '../../components/common/BaseModal.vue';
import BaseInput from '../../components/common/BaseInput.vue';

const route = useRoute();
const router = useRouter();
const coachStore = useCoachStore();
const groupeId = route.params.id;

const showSeanceModal = ref(false);
const showAssignModal = ref(false);

const seanceForm = ref({
    titre: '',
    date_seance: new Date().toISOString().split('T')[0],
    heure_debut: '18:00',
    heure_fin: '19:30'
});

const selectedJoueurId = ref('');
const saving = ref(false);

onMounted(async () => {
    await coachStore.fetchGroupe(groupeId);
});

const handleAddSeance = async () => {
    saving.value = true;
    try {
        await coachStore.addSeance(groupeId, seanceForm.value);
        showSeanceModal.value = false;
        seanceForm.value = {
            titre: '',
            date_seance: new Date().toISOString().split('T')[0],
            heure_debut: '18:00',
            heure_fin: '19:30'
        };
    } finally {
        saving.value = false;
    }
};

const handleAssignJoueur = async () => {
    if (!selectedJoueurId.value) return;
    saving.value = true;
    try {
        await coachStore.assignJoueur(groupeId, selectedJoueurId.value);
        showAssignModal.value = false;
        selectedJoueurId.value = '';
    } finally {
        saving.value = false;
    }
};
</script>

<template>
  <AppLayout :pageTitle="coachStore.currentGroupe?.groupe.nom || 'Détails du groupe'">
    <div v-if="coachStore.loading" class="flex justify-center py-12"><div class="spinner"></div></div>
    <div v-else-if="coachStore.currentGroupe" class="max-w-6xl mx-auto space-y-8 animate-fade-in">
        
        <!-- Header & Quick Stats -->
        <div class="bg-white p-8 rounded-[40px] border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-6">
                <div class="w-16 h-16 rounded-3xl bg-brand-50 text-brand-600 flex items-center justify-center shadow-inner">
                    <i class="fas fa-users text-2xl"></i>
                </div>
                <div>
                    <h2 class="text-3xl font-black text-slate-900 tracking-tight">{{ coachStore.currentGroupe.groupe.nom }}</h2>
                    <p class="text-sm text-slate-500 font-semibold uppercase tracking-wider">Catégorie : <span class="text-brand-600">{{ coachStore.currentGroupe.groupe.categorie?.nom }}</span></p>
                </div>
            </div>
            <div class="flex gap-4">
                <div class="px-6 py-3 bg-slate-50 rounded-2xl border border-slate-100">
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest">Joueurs</span>
                    <span class="text-xl font-black text-slate-900">{{ coachStore.currentGroupe.groupe.joueurs.length }}</span>
                </div>
                <div class="px-6 py-3 bg-slate-50 rounded-2xl border border-slate-100">
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest">Séances</span>
                    <span class="text-xl font-black text-slate-900">{{ coachStore.currentGroupe.groupe.seances.length }}</span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Effectif Section -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-[40px] border border-slate-200 shadow-sm overflow-hidden">
                    <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between bg-slate-50/30">
                        <h3 class="text-lg font-black text-slate-800">Effectif du groupe</h3>
                        <BaseButton variant="ghost" size="sm" @click="showAssignModal = true">
                            <i class="fas fa-plus-circle mr-2"></i> Ajouter un joueur
                        </BaseButton>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-slate-50/50">
                                <tr class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">
                                    <th class="px-8 py-4">Joueur</th>
                                    <th class="px-8 py-4">Âge</th>
                                    <th class="px-8 py-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="player in coachStore.currentGroupe.groupe.joueurs" :key="player.id" class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-8 py-5">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center font-bold text-slate-600">
                                                {{ player.prenom.charAt(0) }}{{ player.nom.charAt(0) }}
                                            </div>
                                            <span class="text-sm font-bold text-slate-900">{{ player.prenom }} {{ player.nom }}</span>
                                        </div>
                                    </td>
                                    <td class="px-8 py-5 text-sm text-slate-600 font-medium">{{ player.age }} ans</td>
                                    <td class="px-8 py-5 text-right">
                                        <button class="w-8 h-8 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-brand-600 transition-colors"><i class="fas fa-chart-line text-xs"></i></button>
                                    </td>
                                </tr>
                                <tr v-if="coachStore.currentGroupe.groupe.joueurs.length === 0">
                                    <td colspan="3" class="px-8 py-12 text-center text-slate-400 italic text-sm">Aucun joueur dans ce groupe.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- History Section -->
                <div class="bg-white rounded-[40px] border border-slate-200 shadow-sm overflow-hidden">
                    <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between bg-slate-50/30">
                        <h3 class="text-lg font-black text-slate-800">Planning & Historique</h3>
                        <BaseButton @click="showSeanceModal = true" size="sm">Planifier une séance</BaseButton>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-slate-50/50">
                                <tr class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">
                                    <th class="px-8 py-4">Séance</th>
                                    <th class="px-8 py-4">Date & Heure</th>
                                    <th class="px-8 py-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="seance in [...coachStore.currentGroupe.groupe.seances].reverse()" :key="seance.id" class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-8 py-5 font-bold text-slate-900 text-sm">{{ seance.titre }}</td>
                                    <td class="px-8 py-5">
                                        <div class="text-xs font-bold text-slate-700">{{ new Date(seance.date_seance).toLocaleDateString() }}</div>
                                        <div class="text-[10px] text-slate-400">{{ seance.heure_debut }} - {{ seance.heure_fin }}</div>
                                    </td>
                                    <td class="px-8 py-5 text-right">
                                        <router-link :to="`/coach/seances/${seance.id}/appel`" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gold-50 text-gold-600 border border-gold-100 text-[10px] font-black uppercase hover:bg-gold-100 transition-colors">
                                            <i class="fas fa-clipboard-list"></i> Faire l'appel
                                        </router-link>
                                    </td>
                                </tr>
                                <tr v-if="coachStore.currentGroupe.groupe.seances.length === 0">
                                    <td colspan="3" class="px-8 py-12 text-center text-slate-400 italic text-sm">Aucune séance planifiée.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Stats & Sidebar -->
            <div class="space-y-6">
                <div class="bg-gradient-to-br from-slate-900 to-slate-800 p-8 rounded-[40px] text-white shadow-xl">
                    <h4 class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-6">Résumé pédagogique</h4>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-4 bg-white/5 rounded-2xl border border-white/10">
                            <span class="text-sm font-medium text-slate-300">Taux de présence</span>
                            <span class="text-lg font-black text-brand-400">85%</span>
                        </div>
                        <div class="flex items-center justify-between p-4 bg-white/5 rounded-2xl border border-white/10">
                            <span class="text-sm font-medium text-slate-300">Moyenne technique</span>
                            <span class="text-lg font-black text-gold-400">7.2/10</span>
                        </div>
                    </div>
                    <p class="text-[11px] text-slate-500 mt-6 leading-relaxed italic">Ces statistiques sont calculées sur la base des évaluations des 5 dernières séances.</p>
                </div>
            </div>
        </div>

        <!-- Seance Modal -->
        <BaseModal :show="showSeanceModal" @close="showSeanceModal = false" title="Planifier une nouvelle séance">
            <form @submit.prevent="handleAddSeance" class="space-y-6 p-2">
                <BaseInput v-model="seanceForm.titre" label="Titre de la séance" placeholder="Ex: Entraînement technique, Match amical..." required />
                <div class="grid grid-cols-2 gap-4">
                    <BaseInput v-model="seanceForm.date_seance" type="date" label="Date" required />
                    <div class="grid grid-cols-2 gap-2">
                        <BaseInput v-model="seanceForm.heure_debut" type="time" label="Début" required />
                        <BaseInput v-model="seanceForm.heure_fin" type="time" label="Fin" required />
                    </div>
                </div>
                <BaseButton type="submit" class="w-full" :loading="saving">Créer la séance</BaseButton>
            </form>
        </BaseModal>

        <!-- Assign Modal -->
        <BaseModal :show="showAssignModal" @close="showAssignModal = false" title="Assigner un joueur au groupe">
            <div class="space-y-6 p-2">
                <div class="space-y-2">
                    <label class="block text-sm font-bold text-slate-700">Joueurs disponibles (Catégorie {{ coachStore.currentGroupe.groupe.categorie?.nom }})</label>
                    <select v-model="selectedJoueurId" class="w-full bg-slate-50 border-[1.5px] border-slate-200 rounded-2xl px-4 py-3 text-sm outline-none focus:border-brand-500 transition-all font-medium">
                        <option value="">Choisir un joueur...</option>
                        <option v-for="player in coachStore.currentGroupe.joueursCategorie" :key="player.id" :value="player.id" :disabled="player.groupe_id === Number(groupeId)">
                            {{ player.prenom }} {{ player.nom }} {{ player.groupe_id === Number(groupeId) ? '(Déjà dans ce groupe)' : '' }}
                        </option>
                    </select>
                </div>
                <BaseButton @click="handleAssignJoueur" class="w-full" :loading="saving" :disabled="!selectedJoueurId">Confirmer l'assignation</BaseButton>
            </div>
        </BaseModal>

    </div>
  </AppLayout>
</template>
