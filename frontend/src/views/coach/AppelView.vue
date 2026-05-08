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
const seanceId = route.params.id;

const presences = ref({}); // { joueurId: bool }
const motifs = ref({}); // { joueurId: string }
const saving = ref(false);

const showEvalModal = ref(false);
const selectedPlayer = ref(null);
const evalForm = ref({ note: 5, commentaire: '' });

onMounted(async () => {
    await coachStore.fetchAppel(seanceId);
    // Initialize presences
    coachStore.currentSeance.joueurs.forEach(j => {
        const p = coachStore.currentSeance.presences[j.id];
        presences.value[j.id] = p ? p.est_present : true;
        motifs.value[j.id] = p ? p.motif_absence : '';
    });
});

const submitAppel = async () => {
    saving.value = true;
    try {
        const presencesList = Object.keys(presences.value).filter(id => presences.value[id]).map(Number);
        await coachStore.submitAppel(seanceId, {
            presences: presencesList,
            motifs: motifs.value
        });
        alert('Appel enregistré avec succès !');
        router.push('/coach/dashboard');
    } finally {
        saving.value = false;
    }
};

const openEvalModal = (player) => {
    selectedPlayer.value = player;
    const existing = coachStore.currentSeance.evaluationsSeance[player.id];
    evalForm.value = existing ? { note: existing.note, commentaire: existing.commentaire } : { note: 5, commentaire: '' };
    showEvalModal.value = true;
};

const submitEval = async () => {
    await coachStore.submitEvaluation(seanceId, selectedPlayer.value.id, evalForm.value);
    showEvalModal.value = false;
    await coachStore.fetchAppel(seanceId);
};
</script>

<template>
  <AppLayout pageTitle="Feuille de présence">
    <div v-if="!coachStore.currentSeance" class="flex justify-center h-64"><div class="spinner"></div></div>
    <div v-else class="max-w-5xl mx-auto space-y-8 animate-fade-in">
        
        <!-- Session Header -->
        <div class="bg-white p-6 sm:p-8 rounded-[32px] sm:rounded-[40px] border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4 sm:gap-6">
                <div class="w-16 h-16 rounded-3xl bg-slate-100 flex flex-col items-center justify-center text-slate-800">
                    <span class="text-[10px] font-bold uppercase">{{ new Date(coachStore.currentSeance.seance.date_seance).toLocaleDateString('fr-FR', { month: 'short' }) }}</span>
                    <span class="text-2xl font-black">{{ new Date(coachStore.currentSeance.seance.date_seance).getDate() }}</span>
                </div>
                <div>
                    <h2 class="text-2xl font-black text-slate-900">{{ coachStore.currentSeance.seance.titre }}</h2>
                    <p class="text-sm text-slate-500 font-medium">Groupe : <span class="text-brand-600">{{ coachStore.currentSeance.seance.groupe?.nom }}</span></p>
                </div>
            </div>
            <div class="flex flex-wrap gap-3 w-full md:w-auto">
                <BaseButton variant="ghost" @click="router.back()">Annuler</BaseButton>
                <BaseButton @click="submitAppel" :loading="saving">Enregistrer l'appel</BaseButton>
            </div>
        </div>

        <!-- Players List -->
        <div class="bg-white rounded-[40px] border border-slate-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100">
                            <th class="px-8 py-5 text-[11px] font-bold text-slate-500 uppercase tracking-widest">Joueur</th>
                            <th class="px-8 py-5 text-[11px] font-bold text-slate-500 uppercase tracking-widest">Présence</th>
                            <th class="px-8 py-5 text-[11px] font-bold text-slate-500 uppercase tracking-widest">Observation / Motif</th>
                            <th class="px-8 py-5 text-[11px] font-bold text-slate-500 uppercase tracking-widest text-right">Évaluation</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="joueur in coachStore.currentSeance.joueurs" :key="joueur.id" class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-8 py-5">
                                <div class="flex items-center gap-3">
                                    <img :src="joueur.photo_url" class="w-10 h-10 rounded-full object-cover border-2 border-slate-100" :alt="joueur.prenom">
                                    <span class="text-sm font-bold text-slate-900">{{ joueur.prenom }} {{ joueur.nom }}</span>
                                </div>
                            </td>
                            <td class="px-8 py-5">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" v-model="presences[joueur.id]" class="sr-only peer">
                                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-500"></div>
                                    <span class="ms-3 text-xs font-bold" :class="presences[joueur.id] ? 'text-brand-600' : 'text-red-500'">
                                        {{ presences[joueur.id] ? 'PRÉSENT' : 'ABSENT' }}
                                    </span>
                                </label>
                            </td>
                            <td class="px-8 py-5">
                                <input 
                                    v-if="!presences[joueur.id]"
                                    v-model="motifs[joueur.id]"
                                    type="text" 
                                    placeholder="Motif d'absence..." 
                                    class="w-full bg-red-50/50 border border-red-100 rounded-lg px-3 py-1.5 text-xs outline-none focus:border-red-200"
                                >
                                <span v-else class="text-xs text-slate-400 italic">—</span>
                            </td>
                            <td class="px-8 py-5 text-right">
                                <template v-if="presences[joueur.id]">
                                    <button 
                                        @click="openEvalModal(joueur)"
                                        class="px-4 py-1.5 rounded-lg text-[10px] font-black uppercase transition-all"
                                        :class="coachStore.currentSeance.evaluationsSeance[joueur.id] ? 'bg-brand-50 text-brand-600 border border-brand-100' : 'bg-gold-50 text-gold-600 border border-gold-100'"
                                    >
                                        {{ coachStore.currentSeance.evaluationsSeance[joueur.id] ? 'Modifier Éval' : 'Évaluer' }}
                                    </button>
                                </template>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Eval Modal -->
        <BaseModal :show="showEvalModal" @close="showEvalModal = false" :title="`Évaluation : ${selectedPlayer?.prenom}`">
            <form @submit.prevent="submitEval" class="space-y-6">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-3 text-center">Note de la séance ({{ evalForm.note }}/10)</label>
                    <div class="flex justify-between items-center bg-slate-50 p-4 rounded-2xl">
                        <button type="button" @click="evalForm.note = Math.max(1, evalForm.note - 1)" class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-600"><i class="fas fa-minus"></i></button>
                        <div class="text-3xl font-black text-brand-600">{{ evalForm.note }}</div>
                        <button type="button" @click="evalForm.note = Math.min(10, evalForm.note + 1)" class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-600"><i class="fas fa-plus"></i></button>
                    </div>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-sm font-semibold text-slate-700">Commentaires techniques</label>
                    <textarea 
                        v-model="evalForm.commentaire"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl p-4 text-sm outline-none focus:border-brand-500 h-32"
                        placeholder="Points forts, points à améliorer..."
                        required
                    ></textarea>
                </div>
                <BaseButton type="submit" class="w-full">Enregistrer l'évaluation</BaseButton>
            </form>
        </BaseModal>
    </div>
  </AppLayout>
</template>
