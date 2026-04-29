<script setup>
import { ref, onMounted, computed } from 'vue';
import { useAdminStore } from '../../stores/admin';
import AppLayout from '../../components/layout/AppLayout.vue';
import BaseButton from '../../components/common/BaseButton.vue';
import BaseModal from '../../components/common/BaseModal.vue';
import BaseInput from '../../components/common/BaseInput.vue';

const adminStore = useAdminStore();
const activeSection = ref('categories'); // categories, groupes
const showCatModal = ref(false);
const showGrpModal = ref(false);
const isEditingCat = ref(false);
const isEditingGrp = ref(false);
const loading = ref(false);
const expandedCategories = ref({});
const expandedGroupes = ref({});

const catForm = ref({ id: null, nom: '', description: '' });
const grpForm = ref({ id: null, nom: '', categorie_id: '', coach_id: '' });

const groupesByCategorie = computed(() => {
  const map = {};

  for (const groupe of adminStore.groupes) {
    const categoryId = groupe.categorie_id;
    if (!categoryId) continue;

    if (!map[categoryId]) {
      map[categoryId] = [];
    }

    map[categoryId].push(groupe);
  }

  return map;
});

const joueursByGroupe = computed(() => {
  const map = {};

  for (const joueur of adminStore.joueurs) {
    const groupeId = joueur.groupe_id;
    if (!groupeId) continue;

    if (!map[groupeId]) {
      map[groupeId] = [];
    }

    map[groupeId].push(joueur);
  }

  return map;
});

onMounted(() => {
  adminStore.fetchDashboard();
});

const openCreateCatModal = () => {
    isEditingCat.value = false;
    catForm.value = { id: null, nom: '', description: '' };
    showCatModal.value = true;
};

const openEditCatModal = (cat) => {
    isEditingCat.value = true;
    catForm.value = { id: cat.id, nom: cat.nom, description: cat.description };
    showCatModal.value = true;
};

const handleSubmitCat = async () => {
    loading.value = true;
    try {
        if (isEditingCat.value) {
            await adminStore.updateCategorie(catForm.value.id, catForm.value);
        } else {
            await adminStore.addCategorie(catForm.value);
        }
        showCatModal.value = false;
    } catch (error) {
        console.error(error);
    } finally {
        loading.value = false;
    }
};

const handleDeleteCat = async (id) => {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette catégorie ? Tous les groupes associés risquent d\'être impactés.')) {
        await adminStore.deleteCategorie(id);
    }
};

const openCreateGrpModal = () => {
    isEditingGrp.value = false;
    grpForm.value = { id: null, nom: '', categorie_id: '', coach_id: '' };
    showGrpModal.value = true;
};

const openEditGrpModal = (grp) => {
    isEditingGrp.value = true;
    grpForm.value = { id: grp.id, nom: grp.nom, categorie_id: grp.categorie_id, coach_id: grp.coach_id };
    showGrpModal.value = true;
};

const handleSubmitGrp = async () => {
    loading.value = true;
    try {
        if (isEditingGrp.value) {
            await adminStore.updateGroupe(grpForm.value.id, grpForm.value);
        } else {
            await adminStore.addGroupe(grpForm.value);
        }
        showGrpModal.value = false;
    } catch (error) {
        console.error(error);
    } finally {
        loading.value = false;
    }
};

const handleDeleteGrp = async (id) => {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce groupe ?')) {
        await adminStore.deleteGroupe(id);
    }
};

const toggleCategoryGroups = (categoryId) => {
  expandedCategories.value[categoryId] = !expandedCategories.value[categoryId];
};

const toggleGroupePlayers = (groupeId) => {
  expandedGroupes.value[groupeId] = !expandedGroupes.value[groupeId];
};
</script>

<template>
  <AppLayout :pageTitle="activeSection === 'categories' ? 'Gestion des Catégories' : 'Gestion des Groupes'">
    <div class="space-y-8">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex flex-wrap bg-white p-1 rounded-2xl border border-slate-200 shadow-sm w-fit">
          <button @click="activeSection = 'categories'" :class="activeSection === 'categories' ? 'bg-brand-500 text-white shadow-md' : 'text-slate-500'" class="px-6 py-2 rounded-xl text-sm font-bold transition-all">Catégories</button>
          <button @click="activeSection = 'groupes'" :class="activeSection === 'groupes' ? 'bg-brand-500 text-white shadow-md' : 'text-slate-500'" class="px-6 py-2 rounded-xl text-sm font-bold transition-all">Groupes</button>
        </div>
        <BaseButton v-if="activeSection === 'categories'" @click="openCreateCatModal">
          <i class="fas fa-plus mr-2"></i>
          Nouvelle Catégorie
        </BaseButton>
        <BaseButton v-else @click="openCreateGrpModal">
          <i class="fas fa-plus mr-2"></i>
          Nouveau Groupe
        </BaseButton>
      </div>

      <!-- Categories Grid -->
      <div v-if="activeSection === 'categories'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="cat in adminStore.categories" :key="cat.id" class="bg-white p-6 rounded-[32px] border border-slate-200 shadow-sm hover:shadow-md transition-all relative group">
          <div class="flex justify-between items-start mb-4">
            <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
              <i class="fas fa-layer-group text-xl"></i>
            </div>
            <div class="flex gap-1">
              <button @click="openEditCatModal(cat)" class="w-8 h-8 rounded-lg hover:bg-slate-50 text-slate-400 hover:text-brand-500 transition-all flex items-center justify-center">
                <i class="fas fa-edit text-xs"></i>
              </button>
              <button @click="handleDeleteCat(cat.id)" class="w-8 h-8 rounded-lg hover:bg-slate-50 text-slate-400 hover:text-red-500 transition-all flex items-center justify-center">
                <i class="fas fa-trash text-xs"></i>
              </button>
            </div>
          </div>
          <h3 class="text-lg font-bold text-slate-900">{{ cat.nom }}</h3>
          <p class="text-sm text-slate-500 mt-2 line-clamp-2 h-10">{{ cat.description || 'Aucune description' }}</p>
          <div class="mt-6 pt-6 border-t border-slate-50 flex items-center justify-between">
            <div class="flex gap-4">
               <div><span class="block text-[10px] text-slate-400 font-bold uppercase tracking-wider">Groupes</span><span class="font-bold text-slate-900">{{ cat.groupes_count }}</span></div>
               <div><span class="block text-[10px] text-slate-400 font-bold uppercase tracking-wider">Joueurs</span><span class="font-bold text-slate-900">{{ cat.joueurs_count }}</span></div>
            </div>
            <button
              @click="toggleCategoryGroups(cat.id)"
              class="text-xs font-bold px-3 py-1.5 rounded-lg border border-slate-200 text-slate-600 hover:text-brand-600 hover:border-brand-200 transition-colors"
            >
              {{ expandedCategories[cat.id] ? 'Masquer' : 'Voir groupes' }}
            </button>
          </div>
          <div v-if="expandedCategories[cat.id]" class="mt-4 rounded-2xl border border-slate-100 bg-slate-50 p-3 animate-fade-in">
            <div v-if="(groupesByCategorie[cat.id] || []).length === 0" class="text-xs text-slate-400 italic">
              Aucun groupe.
            </div>
            <ul v-else class="space-y-2">
              <li v-for="group in groupesByCategorie[cat.id]" :key="group.id" class="flex items-center justify-between rounded-xl bg-white px-3 py-2 border border-slate-100">
                <span class="text-sm font-semibold text-slate-800">{{ group.nom }}</span>
                <span class="text-[11px] font-bold text-slate-500">{{ group.joueurs_count || 0 }}j</span>
              </li>
            </ul>
          </div>
        </div>
      </div>

      <!-- Groupes Grid -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="grp in adminStore.groupes" :key="grp.id" class="bg-white p-6 rounded-[32px] border border-slate-200 shadow-sm hover:shadow-md transition-all relative">
          <div class="flex justify-between items-start mb-4">
            <div class="w-12 h-12 rounded-2xl bg-brand-50 text-brand-600 flex items-center justify-center">
              <i class="fas fa-users text-xl"></i>
            </div>
            <div class="flex flex-col items-end gap-2">
              <span class="px-3 py-1 bg-slate-100 text-slate-600 rounded-full text-[10px] font-bold uppercase">{{ grp.categorie?.nom }}</span>
              <div class="flex gap-1">
                <button @click="openEditGrpModal(grp)" class="w-8 h-8 rounded-lg hover:bg-slate-50 text-slate-400 hover:text-brand-500 transition-all flex items-center justify-center">
                  <i class="fas fa-edit text-xs"></i>
                </button>
                <button @click="handleDeleteGrp(grp.id)" class="w-8 h-8 rounded-lg hover:bg-slate-50 text-slate-400 hover:text-red-500 transition-all flex items-center justify-center">
                  <i class="fas fa-trash text-xs"></i>
                </button>
              </div>
            </div>
          </div>
          <h3 class="text-lg font-bold text-slate-900">{{ grp.nom }}</h3>
          <div class="mt-4 flex items-center gap-3">
             <div class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center text-slate-500"><i class="fas fa-user-tie text-xs"></i></div>
             <div class="text-xs">
                <p class="text-slate-400 font-medium">Coach assigné</p>
                <p class="text-slate-900 font-bold">{{ grp.coach ? grp.coach.prenom + ' ' + grp.coach.nom : 'Non assigné' }}</p>
             </div>
          </div>
          <div class="mt-6 pt-6 border-t border-slate-50 flex items-center justify-between">
            <span class="text-xs font-bold text-slate-500 uppercase">{{ grp.joueurs_count }} Joueurs</span>
            <button
              @click="toggleGroupePlayers(grp.id)"
              class="px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-bold text-slate-600 hover:text-brand-600 hover:border-brand-200 transition-colors"
            >
              {{ expandedGroupes[grp.id] ? 'Masquer' : 'Voir joueurs' }}
            </button>
          </div>
          <div v-if="expandedGroupes[grp.id]" class="mt-4 rounded-2xl border border-slate-100 bg-slate-50 p-3 animate-fade-in">
            <div v-if="(joueursByGroupe[grp.id] || []).length === 0" class="text-xs text-slate-400 italic">
              Aucun joueur.
            </div>
            <ul v-else class="space-y-2 max-h-52 overflow-auto pr-1">
              <li v-for="joueur in joueursByGroupe[grp.id]" :key="joueur.id" class="flex items-center justify-between rounded-xl bg-white px-3 py-2 border border-slate-100">
                <span class="text-sm font-semibold text-slate-800">{{ joueur.prenom }} {{ joueur.nom }}</span>
                <span class="text-[11px] font-bold text-slate-500 uppercase">{{ joueur.categorie?.nom || 'Sans cat.' }}</span>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <!-- Category Modal -->
    <BaseModal 
      :show="showCatModal" 
      @close="showCatModal = false" 
      :title="isEditingCat ? 'Modifier la Catégorie' : 'Nouvelle Catégorie'"
    >
        <form @submit.prevent="handleSubmitCat" class="space-y-4">
            <BaseInput v-model="catForm.nom" label="Nom" placeholder="U13, U15..." required />
            <BaseInput v-model="catForm.description" label="Description" placeholder="Détails de la catégorie" />
            <div class="flex justify-end gap-3 mt-6">
              <BaseButton variant="ghost" @click="showCatModal = false">Annuler</BaseButton>
              <BaseButton type="submit" :loading="loading">
                {{ isEditingCat ? 'Mettre à jour' : 'Créer' }}
              </BaseButton>
            </div>
        </form>
    </BaseModal>

    <!-- Groupe Modal -->
    <BaseModal 
      :show="showGrpModal" 
      @close="showGrpModal = false" 
      :title="isEditingGrp ? 'Modifier le Groupe' : 'Nouveau Groupe'"
    >
        <form @submit.prevent="handleSubmitGrp" class="space-y-4">
            <BaseInput v-model="grpForm.nom" label="Nom du groupe" placeholder="Elite, Initiation..." required />
            <div class="space-y-1.5">
                <label class="block text-sm font-semibold text-slate-700">Catégorie</label>
                <select v-model="grpForm.categorie_id" class="w-full bg-slate-50 border-[1.5px] border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-brand-500" required>
                    <option v-for="cat in adminStore.categories" :key="cat.id" :value="cat.id">{{ cat.nom }}</option>
                </select>
            </div>
            <div class="space-y-1.5">
                <label class="block text-sm font-semibold text-slate-700">Coach</label>
                <select v-model="grpForm.coach_id" class="w-full bg-slate-50 border-[1.5px] border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-brand-500">
                    <option :value="null">Non assigné</option>
                    <option v-for="coach in adminStore.coachs" :key="coach.id" :value="coach.id">{{ coach.prenom }} {{ coach.nom }}</option>
                </select>
            </div>
            <div class="flex justify-end gap-3 mt-6">
              <BaseButton variant="ghost" @click="showGrpModal = false">Annuler</BaseButton>
              <BaseButton type="submit" :loading="loading">
                {{ isEditingGrp ? 'Mettre à jour' : 'Créer' }}
              </BaseButton>
            </div>
        </form>
    </BaseModal>
  </AppLayout>
</template>
