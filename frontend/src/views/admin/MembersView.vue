<script setup>
import { ref, onMounted, computed } from 'vue';
import { useAdminStore } from '../../stores/admin';
import AppLayout from '../../components/layout/AppLayout.vue';
import BaseButton from '../../components/common/BaseButton.vue';
import BaseModal from '../../components/common/BaseModal.vue';
import BaseInput from '../../components/common/BaseInput.vue';

const adminStore = useAdminStore();
const activeTab = ref('coachs'); // coachs, parents
const showModal = ref(false);
const isEditing = ref(false);
const loading = ref(false);

const form = ref({
  id: null,
  nom: '',
  prenom: '',
  email: '',
  password: '',
  password_confirmation: ''
});

onMounted(() => {
  adminStore.fetchDashboard();
});

const members = computed(() => {
  return activeTab.value === 'coachs' ? adminStore.coachs : adminStore.parents;
});

const openCreateModal = () => {
  isEditing.value = false;
  form.value = { id: null, nom: '', prenom: '', email: '', password: '', password_confirmation: '' };
  showModal.value = true;
};

const openEditModal = (member) => {
  isEditing.value = true;
  form.value = { ...member, password: '', password_confirmation: '' };
  showModal.value = true;
};

const handleSubmit = async () => {
  loading.value = true;
  try {
    if (isEditing.value) {
      if (activeTab.value === 'coachs') await adminStore.updateCoach(form.value.id, form.value);
      else await adminStore.updateParent(form.value.id, form.value);
    } else {
      if (activeTab.value === 'coachs') await adminStore.addCoach(form.value);
      else await adminStore.addParent(form.value);
    }
    showModal.value = false;
  } catch (error) {
    console.error(error);
  } finally {
    loading.value = false;
  }
};

const handleDelete = async (id) => {
  if (confirm('Êtes-vous sûr de vouloir supprimer ce membre ?')) {
    if (activeTab.value === 'coachs') await adminStore.deleteCoach(id);
    else await adminStore.deleteParent(id);
  }
};
</script>

<template>
  <AppLayout :pageTitle="activeTab === 'coachs' ? 'Gestion des Coachs' : 'Gestion des Parents'">
    <div class="space-y-6">
      <!-- Tabs & Actions -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex bg-white p-1 rounded-2xl border border-slate-200 w-fit shadow-sm">
          <button 
            @click="activeTab = 'coachs'"
            class="px-6 py-2 rounded-xl text-sm font-bold transition-all"
            :class="activeTab === 'coachs' ? 'bg-brand-500 text-white shadow-md' : 'text-slate-500 hover:text-slate-700'"
          >
            Coachs
          </button>
          <button 
            @click="activeTab = 'parents'"
            class="px-6 py-2 rounded-xl text-sm font-bold transition-all"
            :class="activeTab === 'parents' ? 'bg-brand-500 text-white shadow-md' : 'text-slate-500 hover:text-slate-700'"
          >
            Parents
          </button>
        </div>
        
        <BaseButton @click="openCreateModal">
          <i class="fas fa-plus"></i>
          Ajouter un {{ activeTab === 'coachs' ? 'Coach' : 'Parent' }}
        </BaseButton>
      </div>

      <!-- Members Table -->
      <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden animate-fade-in">
        <div class="overflow-x-auto">
          <table class="w-full text-left">
            <thead>
              <tr class="bg-slate-50 border-b border-slate-100">
                <th class="px-6 py-4 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Membre</th>
                <th class="px-6 py-4 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Email</th>
                <th v-if="activeTab === 'coachs'" class="px-6 py-4 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Groupes</th>
                <th v-else class="px-6 py-4 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Joueurs</th>
                <th class="px-6 py-4 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="member in members" :key="member.id" class="hover:bg-slate-50 transition-colors">
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-brand-50 text-brand-600 flex items-center justify-center font-bold">
                      {{ member.prenom.charAt(0) }}{{ member.nom.charAt(0) }}
                    </div>
                    <div>
                      <div class="text-sm font-bold text-slate-900">{{ member.prenom }} {{ member.nom }}</div>
                      <div class="text-[11px] text-slate-400">Inscrit le {{ new Date(member.created_at).toLocaleDateString() }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 text-sm text-slate-600">{{ member.email }}</td>
                <td class="px-6 py-4">
                  <span class="px-2.5 py-1 bg-slate-100 text-slate-600 rounded-lg text-[11px] font-bold">
                    {{ activeTab === 'coachs' ? (member.groupes_geres_count || 0) : (member.joueurs_count || 0) }}
                  </span>
                </td>
                <td class="px-6 py-4 text-right">
                  <div class="flex items-center justify-end gap-2">
                    <button @click="openEditModal(member)" class="w-8 h-8 rounded-lg hover:bg-white hover:shadow-sm border border-transparent hover:border-slate-200 text-slate-400 hover:text-brand-500 transition-all flex items-center justify-center">
                      <i class="fas fa-edit text-xs"></i>
                    </button>
                    <button @click="handleDelete(member.id)" class="w-8 h-8 rounded-lg hover:bg-white hover:shadow-sm border border-transparent hover:border-slate-200 text-slate-400 hover:text-red-500 transition-all flex items-center justify-center">
                      <i class="fas fa-trash text-xs"></i>
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="members.length === 0">
                <td colspan="5" class="px-6 py-20 text-center">
                  <div class="flex flex-col items-center gap-3 text-slate-400">
                    <i class="fas fa-users text-4xl opacity-20"></i>
                    <p class="text-sm italic">Aucun membre trouvé.</p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <BaseModal 
      :show="showModal" 
      @close="showModal = false"
      :title="isEditing ? 'Modifier le membre' : 'Ajouter un nouveau membre'"
      :subtitle="activeTab === 'coachs' ? 'Espace Administration / Coachs' : 'Espace Administration / Parents'"
    >
      <form @submit.prevent="handleSubmit" class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
          <BaseInput v-model="form.prenom" label="Prénom" placeholder="Jean" required />
          <BaseInput v-model="form.nom" label="Nom" placeholder="Dupont" required />
        </div>
        <BaseInput v-model="form.email" label="Adresse email" type="email" placeholder="email@exemple.com" required />
        
        <template v-if="!isEditing">
          <div class="grid grid-cols-2 gap-4">
            <BaseInput v-model="form.password" label="Mot de passe" type="password" required />
            <BaseInput v-model="form.password_confirmation" label="Confirmation" type="password" required />
          </div>
        </template>

        <div class="flex justify-end gap-3 mt-6">
          <BaseButton variant="ghost" @click="showModal = false">Annuler</BaseButton>
          <BaseButton type="submit" :loading="loading">
            {{ isEditing ? 'Mettre à jour' : 'Créer le membre' }}
          </BaseButton>
        </div>
      </form>
    </BaseModal>
  </AppLayout>
</template>
