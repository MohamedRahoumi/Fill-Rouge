<script setup>
import { ref } from 'vue';
import { useParentStore } from '../../stores/parent';
import AppLayout from '../../components/layout/AppLayout.vue';
import BaseButton from '../../components/common/BaseButton.vue';
import BaseInput from '../../components/common/BaseInput.vue';

const parentStore = useParentStore();
const montant = ref(250); // Default amount
const mois = ref(new Date().toISOString().slice(0, 7)); // Current month YYYY-MM
const cardNumber = ref('4242 4242 4242 4242');
const expMonth = ref('12');
const expYear = ref(String(new Date().getFullYear() + 1));
const cvv = ref('123');
const loading = ref(false);
const paymentFeedback = ref({ type: '', message: '' });

const handlePayment = async () => {
    loading.value = true;
    paymentFeedback.value = { type: '', message: '' };
    try {
        // Simplified flow: Direct API call if real Stripe isn't configured
        const result = await parentStore.payDirect({
            montant: montant.value,
            mois_concerne: mois.value,
            card_number: cardNumber.value,
            exp_month: Number(expMonth.value),
            exp_year: Number(expYear.value),
            cvv: cvv.value,
        });
        if (result?.paiement?.statut === 'paid') {
            paymentFeedback.value = {
                type: 'success',
                message: 'Paiement valide automatiquement avec succes.'
            };
        } else if (result?.paiement?.statut === 'pending') {
            paymentFeedback.value = {
                type: 'info',
                message: 'Paiement en cours de traitement.'
            };
        } else {
            paymentFeedback.value = {
                type: 'error',
                message: 'Paiement refuse. Essayez une autre carte de test.'
            };
        }
    } catch (err) {
        console.error(err);
        paymentFeedback.value = {
            type: 'error',
            message: err?.response?.data?.message || 'Erreur lors du paiement.'
        };
    } finally {
        loading.value = false;
    }
};

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'MAD' }).format(amount);
};
</script>

<template>
  <AppLayout pageTitle="Effectuer un Paiement">
    <div class="max-w-4xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-8 animate-fade-in">
        
        <!-- Payment Form -->
        <div class="bg-white p-10 rounded-[48px] border border-slate-200 shadow-sm space-y-8">
            <div>
                <h3 class="text-2xl font-black text-slate-800">Finaliser le règlement</h3>
                <p class="text-sm text-slate-500 mt-1">Sécurisé par Stripe & MiniFoot Academy</p>
            </div>

            <div
                v-if="paymentFeedback.message"
                class="p-4 rounded-2xl border text-sm font-semibold"
                :class="{
                  'bg-emerald-50 border-emerald-200 text-emerald-700': paymentFeedback.type === 'success',
                  'bg-sky-50 border-sky-200 text-sky-700': paymentFeedback.type === 'info',
                  'bg-red-50 border-red-200 text-red-700': paymentFeedback.type === 'error',
                }"
            >
                {{ paymentFeedback.message }}
            </div>

            <form @submit.prevent="handlePayment" class="space-y-6">
                <div class="grid grid-cols-2 gap-4">
                    <BaseInput v-model="montant" label="Montant (MAD)" type="number" required />
                    <BaseInput v-model="mois" label="Mois concerné" type="month" required />
                </div>

                <div class="p-6 bg-slate-50 rounded-3xl border border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-4 text-slate-400">
                        <i class="fas fa-wallet text-2xl"></i>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Total à payer</p>
                            <p class="text-2xl font-black text-slate-800">{{ formatCurrency(montant) }}</p>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50/60 space-y-4">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Carte bancaire fake</p>
                        <BaseInput v-model="cardNumber" label="Numéro de carte" placeholder="4242 4242 4242 4242" required />
                        <div class="grid grid-cols-3 gap-3">
                            <BaseInput v-model="expMonth" label="Mois" type="number" placeholder="12" required />
                            <BaseInput v-model="expYear" label="Année" type="number" placeholder="2027" required />
                            <BaseInput v-model="cvv" label="CVV" type="password" placeholder="123" required />
                        </div>
                    </div>

                    <div class="p-4 border border-brand-100 bg-brand-50/30 rounded-2xl flex items-center gap-4">
                        <i class="fab fa-stripe text-brand-600 text-3xl"></i>
                        <p class="text-xs text-brand-700 font-medium">Cartes fake Stripe: 4242 4242 4242 4242 (succès), 4000 0000 0000 0002 (refus), 4000 0000 0000 9995 (fonds insuffisants), 4000 0025 0000 3155 (3D Secure).</p>
                    </div>
                </div>

                <BaseButton type="submit" :loading="loading" class="w-full !py-4 !text-lg">
                    Régler maintenant
                </BaseButton>
            </form>
        </div>

        <!-- History / Context -->
        <div class="space-y-6">
            <div class="bg-white p-8 rounded-[40px] border border-slate-200 shadow-sm">
                <h4 class="text-lg font-bold text-slate-800 mb-4">Informations importantes</h4>
                <ul class="space-y-4">
                    <li class="flex gap-3 text-sm text-slate-600">
                        <i class="fas fa-check-circle text-brand-500 mt-1"></i>
                        Le paiement mensuel doit être effectué avant le 5 de chaque mois.
                    </li>
                    <li class="flex gap-3 text-sm text-slate-600">
                        <i class="fas fa-check-circle text-brand-500 mt-1"></i>
                        Après paiement par carte, votre règlement est validé automatiquement.
                    </li>
                    <li class="flex gap-3 text-sm text-slate-600">
                        <i class="fas fa-check-circle text-brand-500 mt-1"></i>
                        Conservez vos reçus numériques disponibles dans votre historique.
                    </li>
                </ul>
            </div>

            <div class="bg-slate-900 rounded-[40px] p-8 text-white relative overflow-hidden shadow-xl shadow-slate-900/20">
                <div class="relative z-10">
                    <h4 class="text-lg font-bold mb-2">Besoin d'aide ?</h4>
                    <p class="text-slate-400 text-sm">Contactez notre support administratif pour toute question concernant vos règlements.</p>
                    <BaseButton variant="ghost" class="mt-6 !bg-white/10 !border-white/20 !text-white hover:!bg-white/20">Nous contacter</BaseButton>
                </div>
                <i class="fas fa-headset absolute -right-4 -bottom-4 text-8xl text-white/10 -rotate-12"></i>
            </div>
        </div>
    </div>
  </AppLayout>
</template>
