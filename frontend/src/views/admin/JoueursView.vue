<script setup>
import { ref, onMounted } from 'vue';
import { useAdminStore } from '../../stores/admin';
import AppLayout from '../../components/layout/AppLayout.vue';
import BaseButton from '../../components/common/BaseButton.vue';
import BaseModal from '../../components/common/BaseModal.vue';
import BaseInput from '../../components/common/BaseInput.vue';

const adminStore = useAdminStore();
const showModal = ref(false);
const isEditing = ref(false);
const loading = ref(false);
const form = ref({ id: null, nom: '', prenom: '', date_naissance: '', parent_id: '', categorie_id: '', photo: '' });

onMounted(async () => {
    await adminStore.fetchDashboard();
});

const openCreateModal = () => {
    isEditing.value = false;
    form.value = { id: null, nom: '', prenom: '', date_naissance: '', parent_id: '', categorie_id: '', photo: '' };
    showModal.value = true;
};

const openEditModal = (joueur) => {
    isEditing.value = true;
    form.value = { 
        id: joueur.id, 
        nom: joueur.nom, 
        prenom: joueur.prenom, 
        date_naissance: joueur.date_naissance, 
        parent_id: joueur.parent_id, 
        categorie_id: joueur.categorie_id,
        photo: joueur.photo
    };
    showModal.value = true;
};

const handleSubmit = async () => {
    loading.value = true;
    try {
        if (isEditing.value) {
            await adminStore.updateJoueur(form.value.id, form.value);
        } else {
            await adminStore.addJoueur(form.value);
        }
        showModal.value = false;
    } catch (error) {
        console.error(error);
    } finally {
        loading.value = false;
    }
};

const handleDelete = async (id) => {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce joueur ?')) {
        await adminStore.deleteJoueur(id);
    }
};
</script>

<template>
  <AppLayout pageTitle="Gestion des Joueurs">
    <div class="space-y-6">
      <div class="flex justify-end">
        <BaseButton @click="openCreateModal">
          <i class="fas fa-plus mr-2"></i>
          Nouveau Joueur
        </BaseButton>
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
                    <img :src="joueur.photo_url" class="w-10 h-10 rounded-full object-cover border-2 border-brand-50" :alt="joueur.prenom">
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
                  <div class="flex items-center justify-end gap-2">
                    <button @click="openEditModal(joueur)" class="w-8 h-8 rounded-lg hover:bg-white hover:shadow-sm border border-transparent hover:border-slate-200 text-slate-400 hover:text-brand-500 transition-all flex items-center justify-center">
                      <i class="fas fa-edit text-xs"></i>
                    </button>
                    <button @click="handleDelete(joueur.id)" class="w-8 h-8 rounded-lg hover:bg-white hover:shadow-sm border border-transparent hover:border-slate-200 text-slate-400 hover:text-red-500 transition-all flex items-center justify-center">
                      <i class="fas fa-trash text-xs"></i>
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="adminStore.joueurs.length === 0">
                <td colspan="5" class="px-8 py-10 text-center text-slate-400 italic">
                  Aucun joueur trouvé.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Modal Form -->
    <BaseModal 
      :show="showModal" 
      @close="showModal = false" 
      :title="isEditing ? 'Modifier le Joueur' : 'Inscrire un Joueur'"
    >
        <form @submit.prevent="handleSubmit" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <BaseInput v-model="form.prenom" label="Prénom" placeholder="Prénom" required />
                <BaseInput v-model="form.nom" label="Nom" placeholder="Nom" required />
            </div>
            <BaseInput v-model="form.date_naissance" label="Date de naissance" type="date" required />
            <BaseInput v-model="form.photo" label="Photo (URL)" placeholder="https://..." />
            
            <div class="space-y-1.5">
                <label class="block text-sm font-semibold text-slate-700">Responsable (Parent)</label>
                <select v-model="form.parent_id" class="w-full bg-slate-50 border-[1.5px] border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-brand-500 transition-all" required>
                    <option value="" disabled>Sélectionner un parent</option>
                    <option v-for="p in adminStore.parents" :key="p.id" :value="p.id">{{ p.prenom }} {{ p.nom }}</option>
                </select>
            </div>

            <div class="space-y-1.5">
                <label class="block text-sm font-semibold text-slate-700">Catégorie</label>
                <select v-model="form.categorie_id" class="w-full bg-slate-50 border-[1.5px] border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-brand-500 transition-all">
                    <option :value="null">Non classé</option>
                    <option v-for="c in adminStore.categories" :key="c.id" :value="c.id">{{ c.nom }}</option>
                </select>
            </div>

            <div class="flex justify-end gap-3 mt-6">
              <BaseButton variant="ghost" @click="showModal = false">Annuler</BaseButton>
              <BaseButton type="submit" :loading="loading">
                  {{ isEditing ? 'Mettre à jour' : 'Enregistrer le joueur' }}
              </BaseButton>
            </div>
        </form>
    </BaseModal>
  </AppLayout>
</template>
