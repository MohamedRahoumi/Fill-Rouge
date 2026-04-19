<script setup>
import { ref, onMounted } from 'vue';
import { useAdminStore } from '../../stores/admin';
import AppLayout from '../../components/layout/AppLayout.vue';
import BaseButton from '../../components/common/BaseButton.vue';
import BaseInput from '../../components/common/BaseInput.vue';

const adminStore = useAdminStore();
const saving = ref(false);
const successMessage = ref('');

const notifForm = ref({
    titre: '',
    message: '',
    cible: 'all',
    user_ids: []
});

onMounted(() => {
    adminStore.fetchDashboard();
});

const handleSend = async () => {
    saving.value = true;
    successMessage.value = '';
    try {
        await adminStore.sendNotification(notifForm.value);
        successMessage.value = "Notification envoyée avec succès !";
        notifForm.value = { titre: '', message: '', cible: 'all', user_ids: [] };
        setTimeout(() => successMessage.value = '', 5000);
    } catch (err) {
        alert("Erreur lors de l'envoi");
    } finally {
        saving.value = false;
    }
};
</script>

<template>
  <AppLayout pageTitle="Centre de Notifications">
    <div class="max-w-4xl mx-auto space-y-8 animate-fade-in">
        
        <!-- Header -->
        <div class="bg-white p-8 rounded-[40px] border border-slate-200 shadow-sm flex items-center gap-6">
            <div class="w-16 h-16 rounded-3xl bg-amber-50 text-amber-600 flex items-center justify-center shadow-inner">
                <i class="fas fa-bullhorn text-2xl"></i>
            </div>
            <div>
                <h2 class="text-2xl font-black text-slate-900">Diffuser un message</h2>
                <p class="text-sm text-slate-500 font-medium tracking-tight">Communiquez avec les coachs et les parents de l'académie.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Form Section -->
            <div class="bg-white p-8 rounded-[40px] border border-slate-200 shadow-sm">
                <form @submit.prevent="handleSend" class="space-y-6">
                    <BaseInput v-model="notifForm.titre" label="Titre de la notification" placeholder="Ex: Rappel : Match amical ce samedi" required />
                    
                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-slate-700">Destinataires (Cible)</label>
                        <select v-model="notifForm.cible" class="w-full bg-slate-50 border-[1.5px] border-slate-200 rounded-2xl px-4 py-3 text-sm outline-none focus:border-brand-500 transition-all font-medium">
                            <option value="all">Tous le monde (Coachs & Parents)</option>
                            <option value="coaches">Uniquement les Coachs</option>
                            <option value="parents">Uniquement les Parents</option>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-slate-700">Message</label>
                        <textarea 
                            v-model="notifForm.message"
                            class="w-full bg-slate-50 border-[1.5px] border-slate-200 rounded-2xl px-4 py-3 text-sm outline-none focus:border-brand-500 transition-all font-medium min-h-[150px]"
                            placeholder="Écrivez votre message ici..."
                            required
                        ></textarea>
                    </div>

                    <div v-if="successMessage" class="p-4 bg-emerald-50 text-emerald-700 rounded-2xl text-xs font-bold border border-emerald-100 flex items-center gap-2">
                        <i class="fas fa-check-circle"></i> {{ successMessage }}
                    </div>

                    <BaseButton type="submit" class="w-full" :loading="saving">
                        <i class="fas fa-paper-plane mr-2"></i> Envoyer la notification
                    </BaseButton>
                </form>
            </div>

            <!-- Previews / Info -->
            <div class="space-y-6">
                <div class="bg-slate-900 p-8 rounded-[40px] text-white shadow-xl relative overflow-hidden">
                    <div class="relative z-10">
                        <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-4">Aperçu du message</h4>
                        <div class="space-y-3">
                            <h5 class="text-lg font-black">{{ notifForm.titre || 'Titre de l\'annonce' }}</h5>
                            <p class="text-sm text-slate-400 leading-relaxed">{{ notifForm.message || 'Votre message s\'affichera ici pour les destinataires...' }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-indigo-50 p-8 rounded-[40px] border border-indigo-100">
                    <h4 class="text-sm font-black text-indigo-900 mb-4 flex items-center gap-2">
                        <i class="fas fa-info-circle"></i> Conseils
                    </h4>
                    <ul class="space-y-3">
                        <li class="flex items-start gap-3">
                            <div class="w-1.5 h-1.5 rounded-full bg-indigo-400 mt-1.5 shrink-0"></div>
                            <p class="text-xs text-indigo-700 font-medium">Les notifications sont envoyées instantanément sur les comptes des utilisateurs.</p>
                        </li>
                        <li class="flex items-start gap-3">
                            <div class="w-1.5 h-1.5 rounded-full bg-indigo-400 mt-1.5 shrink-0"></div>
                            <p class="text-xs text-indigo-700 font-medium">Ciblez bien vos messages (ex: Parents pour les paiements, Coachs pour les réunions techniques).</p>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

    </div>
  </AppLayout>
</template>
