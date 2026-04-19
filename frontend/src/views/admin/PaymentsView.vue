<script setup>
import { ref, onMounted } from 'vue';
import { useAdminStore } from '../../stores/admin';
import AppLayout from '../../components/layout/AppLayout.vue';

const adminStore = useAdminStore();
const loading = ref(true);

onMounted(async () => {
  await adminStore.fetchPaiements();
  loading.value = false;
});

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'MAD' }).format(amount);
};
</script>

<template>
  <AppLayout pageTitle="Gestion des Paiements">
    <div class="space-y-6">
      <div v-if="loading" class="flex items-center justify-center h-64"><div class="spinner"></div></div>
      <div v-else class="bg-white rounded-[32px] border border-slate-200 shadow-sm overflow-hidden animate-fade-in">
        <div class="overflow-x-auto">
          <table class="w-full text-left">
            <thead>
              <tr class="bg-slate-50 border-b border-slate-100">
                <th class="px-6 py-4 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Parent</th>
                <th class="px-6 py-4 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Montant</th>
                <th class="px-6 py-4 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Mois</th>
                <th class="px-6 py-4 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Statut</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="pay in adminStore.paiements" :key="pay.id" class="hover:bg-slate-50 transition-colors">
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center font-bold">
                        {{ pay.parent?.prenom.charAt(0) }}{{ pay.parent?.nom.charAt(0) }}
                    </div>
                    <div>
                        <div class="text-sm font-bold text-slate-900">{{ pay.parent?.prenom }} {{ pay.parent?.nom }}</div>
                        <div class="text-[10px] text-slate-400">ID: {{ pay.stripe_transaction_id || 'Cash/Manual' }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 text-sm font-extrabold text-slate-900">{{ formatCurrency(pay.montant) }}</td>
                <td class="px-6 py-4 text-sm text-slate-600">{{ new Date(pay.mois_concerne).toLocaleDateString('fr-FR', { month: 'long', year: 'numeric' }) }}</td>
                <td class="px-6 py-4">
                    <span 
                        class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider"
                        :class="{
                            'bg-emerald-100 text-emerald-700': pay.statut === 'paid',
                            'bg-orange-100 text-orange-700 animate-pulse': pay.statut === 'pending',
                            'bg-red-100 text-red-700': pay.statut === 'failed'
                        }"
                    >
                        {{ pay.statut === 'paid' ? 'Validé' : (pay.statut === 'pending' ? 'En attente' : 'Échoué') }}
                    </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
