<script setup>
import { ref, onMounted } from 'vue';
import { useAdminStore } from '../../stores/admin';
import AppLayout from '../../components/layout/AppLayout.vue';
import BaseButton from '../../components/common/BaseButton.vue';
import BaseModal from '../../components/common/BaseModal.vue';
import BaseInput from '../../components/common/BaseInput.vue';

const adminStore = useAdminStore();
const showModal = ref(false);
const form = ref({ nom: '', prenom: '', date_naissance: '', parent_id: '', categorie_id: '' });

onMounted(async () => {
    await adminStore.fetchDashboard();
});

const handleAdd = async () => {
    await adminStore.addJoueur(form.value);
    showModal.value = false;
    form.value = { nom: '', prenom: '', date_naissance: '', parent_id: '', categorie_id: '' };
};
</script>

<template>
  <AppLayout pageTitle="Gestion des Joueurs">
    <div class="space-y-6">
      <div class="flex justify-end">
        <BaseButton @click="showModal = true">Nouveau Joueur</BaseButton>
      </div>

      <div class="bg-white rounded-[40px] border border-slate-200 shadow-sm overflow-hidden animate-fade-in">
        <div class="overflow-x-auto">
          <table class="w-full text-left">
            <thead>
              <tr class="bg-slate-50 border-b border-slate-100">
                <th class="px-8 py-5 text-[11px] font-bold text-slate-500 uppercase tracking-widest">Joueur</th>
                <th class="px-8 py-5 text-[11px] font-bold text-slate-500 uppercase tracking-widest">Parent</th>
                <th class="px-8 py-5 text-[11px] font-bold text-slate-500 uppercase tracking-widest">Catégorie</th>
                <th class="px-8 py-5 text-[11px] font-bold text-slate-500 uppercase tracking-widest">Groupe</th>
                <th class="px-8 py-5 text-[11px] font-bold text-slate-500 uppercase tracking-widest text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="joueur in adminStore.joueurs" :key="joueur.id" class="hover:bg-slate-50 transition-colors">
                <td class="px-8 py-5">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-brand-50 text-brand-600 flex items-center justify-center font-bold">{{ joueur.prenom.charAt(0) }}</div>
                    <div>
                      <div class="text-sm font-bold text-slate-900">{{ joueur.prenom }} {{ joueur.nom }}</div>
                      <div class="text-[10px] text-slate-400 italic">Né le {{ new Date(joueur.date_naissance).toLocaleDateString() }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-8 py-5 text-sm text-slate-600">{{ joueur.parent?.prenom }} {{ joueur.parent?.nom }}</td>
                <td class="px-8 py-5">
                  <span class="px-3 py-1 bg-indigo-50 text-indigo-600 rounded-full text-[10px] font-bold uppercase">{{ joueur.categorie?.nom || 'Non classé' }}</span>
                </td>
                <td class="px-8 py-5 text-sm text-slate-900 font-bold">{{ joueur.groupe?.nom || '—' }}</td>
                <td class="px-8 py-5 text-right">
                  <button class="text-slate-400 hover:text-brand-500 transition-colors"><i class="fas fa-ellipsis-v"></i></button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Add Modal -->
    <BaseModal :show="showModal" @close="showModal = false" title="Inscrire un Joueur">
        <form @submit.prevent="handleAdd" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <BaseInput v-model="form.prenom" label="Prénom" required />
                <BaseInput v-model="form.nom" label="Nom" required />
            </div>
            <BaseInput v-model="form.date_naissance" label="Date de naissance" type="date" required />
            <div class="space-y-1.5">
                <label class="block text-sm font-semibold text-slate-700">Responsable (Parent)</label>
                <select v-model="form.parent_id" class="w-full bg-slate-50 border-[1.5px] border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-brand-500" required>
                    <option v-for="p in adminStore.parents" :key="p.id" :value="p.id">{{ p.prenom }} {{ p.nom }}</option>
                </select>
            </div>
            <div class="space-y-1.5">
                <label class="block text-sm font-semibold text-slate-700">Catégorie</label>
                <select v-model="form.categorie_id" class="w-full bg-slate-50 border-[1.5px] border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-brand-500">
                    <option v-for="c in adminStore.categories" :key="c.id" :value="c.id">{{ c.nom }}</option>
                </select>
            </div>
            <BaseButton type="submit" class="w-full">Enregistrer le joueur</BaseButton>
        </form>
    </BaseModal>
  </AppLayout>
</template>
