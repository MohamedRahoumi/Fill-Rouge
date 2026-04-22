<script setup>
import { ref, onMounted } from 'vue';
import { useAdminStore } from '../../stores/admin';
import AppLayout from '../../components/layout/AppLayout.vue';
import BaseButton from '../../components/common/BaseButton.vue';
import BaseModal from '../../components/common/BaseModal.vue';
import BaseInput from '../../components/common/BaseInput.vue';

const adminStore = useAdminStore();
const activeSection = ref('categories'); // categories, groupes
const showCatModal = ref(false);
const showGrpModal = ref(false);

const catForm = ref({ nom: '', description: '' });
const grpForm = ref({ nom: '', categorie_id: '', coach_id: '' });

onMounted(() => {
  adminStore.fetchDashboard();
});

const handleAddCat = async () => {
    await adminStore.addCategorie(catForm.value);
    showCatModal.value = false;
    catForm.value = { nom: '', description: '' };
};

const handleAddGrp = async () => {
    await adminStore.addGroupe(grpForm.value);
    showGrpModal.value = false;
    grpForm.value = { nom: '', categorie_id: '', coach_id: '' };
};
</script>

<template>
  <AppLayout :pageTitle="activeSection === 'categories' ? 'Gestion des Catégories' : 'Gestion des Groupes'">
    <div class="space-y-8">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex flex-wrap bg-white p-1 rounded-2xl border border-slate-200 shadow-sm w-fit">
          <button @click="activeSection = 'categories'" :class="activeSection === 'categories' ? 'bg-brand-500 text-white' : 'text-slate-500'" class="px-6 py-2 rounded-xl text-sm font-bold transition-all">Catégories</button>
          <button @click="activeSection = 'groupes'" :class="activeSection === 'groupes' ? 'bg-brand-500 text-white' : 'text-slate-500'" class="px-6 py-2 rounded-xl text-sm font-bold transition-all">Groupes</button>
        </div>
        <BaseButton v-if="activeSection === 'categories'" @click="showCatModal = true">Nouvelle Catégorie</BaseButton>
        <BaseButton v-else @click="showGrpModal = true">Nouveau Groupe</BaseButton>
      </div>

      <!-- Categories Grid -->
      <div v-if="activeSection === 'categories'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="cat in adminStore.categories" :key="cat.id" class="bg-white p-6 rounded-[32px] border border-slate-200 shadow-sm hover:shadow-md transition-all">
          <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center mb-4">
            <i class="fas fa-layer-group text-xl"></i>
          </div>
          <h3 class="text-lg font-bold text-slate-900">{{ cat.nom }}</h3>
          <p class="text-sm text-slate-500 mt-2 line-clamp-2 h-10">{{ cat.description || 'Aucune description' }}</p>
          <div class="mt-6 pt-6 border-t border-slate-50 flex items-center justify-between">
            <div class="flex gap-4">
               <div><span class="block text-[10px] text-slate-400 font-bold uppercase tracking-wider">Groupes</span><span class="font-bold text-slate-900">{{ cat.groupes_count }}</span></div>
               <div><span class="block text-[10px] text-slate-400 font-bold uppercase tracking-wider">Joueurs</span><span class="font-bold text-slate-900">{{ cat.joueurs_count }}</span></div>
            </div>
            <button class="text-slate-400 hover:text-brand-500"><i class="fas fa-arrow-right"></i></button>
          </div>
        </div>
      </div>

      <!-- Groupes Grid -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="grp in adminStore.groupes" :key="grp.id" class="bg-white p-6 rounded-[32px] border border-slate-200 shadow-sm hover:shadow-md transition-all">
          <div class="flex justify-between items-start mb-4">
            <div class="w-12 h-12 rounded-2xl bg-brand-50 text-brand-600 flex items-center justify-center">
              <i class="fas fa-users text-xl"></i>
            </div>
            <span class="px-3 py-1 bg-slate-100 text-slate-600 rounded-full text-[10px] font-bold uppercase">{{ grp.categorie?.nom }}</span>
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
            <div class="flex gap-2">
                <button class="w-8 h-8 rounded-lg hover:bg-slate-50 text-slate-400 transition-colors"><i class="fas fa-users-gear text-xs"></i></button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Category Modal -->
    <BaseModal :show="showCatModal" @close="showCatModal = false" title="Nouvelle Catégorie">
        <form @submit.prevent="handleAddCat" class="space-y-4">
            <BaseInput v-model="catForm.nom" label="Nom" placeholder="U13, U15..." required />
            <BaseInput v-model="catForm.description" label="Description" placeholder="Détails de la catégorie" />
            <BaseButton type="submit" class="w-full">Créer</BaseButton>
        </form>
    </BaseModal>

    <!-- Groupe Modal -->
    <BaseModal :show="showGrpModal" @close="showGrpModal = false" title="Nouveau Groupe">
        <form @submit.prevent="handleAddGrp" class="space-y-4">
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
                    <option v-for="coach in adminStore.coachs" :key="coach.id" :value="coach.id">{{ coach.prenom }} {{ coach.nom }}</option>
                </select>
            </div>
            <BaseButton type="submit" class="w-full">Créer</BaseButton>
        </form>
    </BaseModal>
  </AppLayout>
</template>
