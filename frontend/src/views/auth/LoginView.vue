<script setup>
import { ref } from 'vue';
import { useAuthStore } from '../../stores/auth';
import { useRouter } from 'vue-router';

const authStore = useAuthStore();
const router = useRouter();
const email = ref('');
const password = ref('');
const showPassword = ref(false);
const error = ref('');
const loading = ref(false);

const togglePassword = () => {
    showPassword.value = !showPassword.value;
};

const handleLogin = async () => {
    loading.value = true;
    error.value = '';
    const success = await authStore.login(email.value, password.value);
    if (success) {
        // Redirect based on role
        if (authStore.user.role === 'superadmin') router.push('/admin/dashboard');
        else if (authStore.user.role === 'coach') router.push('/coach/dashboard');
        else if (authStore.user.role === 'parent') router.push('/parent/dashboard');
    } else {
        error.value = authStore.error;
    }
    loading.value = false;
};
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-[#e8f0f5] to-[#f5f9fc] p-4 relative overflow-hidden">
    
    <!-- Decorative background -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none z-0">
        <div class="absolute -top-[20%] -left-[10%] w-[600px] height-[600px] bg-[radial-gradient(circle,_#10b98108_0%,_transparent_70%)] rounded-full"></div>
        <div class="absolute -bottom-[15%] -right-[5%] w-[500px] height-[500px] bg-[radial-gradient(circle,_#3b82f605_0%,_transparent_70%)] rounded-full"></div>
        <svg class="absolute bottom-0 left-0 opacity-10 text-brand-500" width="100%" height="200" viewBox="0 0 1200 120" preserveAspectRatio="none">
            <path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z" fill="currentColor"></path>
        </svg>
    </div>

    <div class="relative z-10 w-full max-w-[880px] flex flex-wrap bg-white rounded-[32px] sm:rounded-[48px] shadow-2xl overflow-hidden animate-fade-in shadow-black/10">
        
        <!-- Left Side - Branding -->
        <div class="flex-1 min-w-[240px] bg-gradient-to-br from-brand-500 to-brand-600 p-6 sm:p-10 flex flex-col justify-between text-white">
            <div>
                <div class="inline-flex items-center justify-center w-14 h-14 bg-white/20 rounded-2xl mb-8">
                    <i class="fas fa-futbol text-3xl"></i>
                </div>
                <h2 class="text-3xl font-bold mb-3 tracking-tight">Bienvenue</h2>
                <p class="text-sm opacity-90 leading-relaxed">Accédez à votre espace dédié pour suivre la progression, gérer les séances et consulter les évaluations.</p>
            </div>

            <div class="mt-10 space-y-5">
                <div class="flex gap-4">
                    <div class="w-8 h-8 bg-white/15 rounded-xl flex items-center justify-center">
                        <i class="fas fa-chalkboard-user text-xs"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold">Suivi personnalisé</p>
                        <p class="text-[11px] opacity-75">Évaluations et présences en temps réel</p>
                    </div>
                </div>
                <div class="flex gap-4">
                    <div class="w-8 h-8 bg-white/15 rounded-xl flex items-center justify-center">
                        <i class="fas fa-chart-line text-xs"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold">Statistiques détaillées</p>
                        <p class="text-[11px] opacity-75">Progrès techniques et physiques</p>
                    </div>
                </div>
                <div class="flex gap-4">
                    <div class="w-8 h-8 bg-white/15 rounded-xl flex items-center justify-center">
                        <i class="fas fa-credit-card text-xs"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold">Paiements sécurisés</p>
                        <p class="text-[11px] opacity-75">Gestion financière intégrée</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side - Form -->
        <div class="flex-1 min-w-[260px] p-6 sm:p-12 bg-white">
            <div class="mb-10">
                <h3 class="text-2xl font-bold text-slate-900 mb-2">Connexion</h3>
                <p class="text-sm text-slate-500">Entrez vos identifiants pour continuer</p>
            </div>

            <form @submit.prevent="handleLogin" class="space-y-6">
                <!-- Email -->
                <div>
                    <label for="email" class="block mb-2 text-sm font-semibold text-slate-700">
                        <i class="fas fa-envelope text-xs mr-2 text-slate-400"></i>
                        Adresse email
                    </label>
                    <input
                        v-model="email"
                        id="email"
                        type="email"
                        placeholder="ex: parent@minifoot.com"
                        required
                        class="w-full bg-slate-50 border-[1.5px] border-slate-200 rounded-2xl px-[18px] py-[14px] text-sm text-slate-800 outline-none transition-all focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10"
                    >
                </div>

                <!-- Password -->
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <label for="password" class="text-sm font-semibold text-slate-700">
                            <i class="fas fa-lock text-xs mr-2 text-slate-400"></i>
                            Mot de passe
                        </label>
                        <a href="#" class="text-[11px] text-slate-400 hover:text-brand-500 transition-colors">Mot de passe oublié ?</a>
                    </div>
                    <div class="relative">
                        <input
                            v-model="password"
                            id="password"
                            :type="showPassword ? 'text' : 'password'"
                            placeholder="••••••••"
                            required
                            class="w-full bg-slate-50 border-[1.5px] border-slate-200 rounded-2xl px-[18px] py-[14px] text-sm text-slate-800 outline-none transition-all focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10"
                        >
                        <i 
                            @click="togglePassword"
                            :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"
                            class="fas absolute right-[18px] top-1/2 -translate-y-1/2 text-slate-400 text-base cursor-pointer hover:text-brand-500 transition-colors"
                        ></i>
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between pointer-events-none opacity-50">
                    <label class="flex items-center gap-2 cursor-not-allowed">
                        <input type="checkbox" class="w-[18px] h-[18px] accent-brand-500">
                        <span class="text-sm text-slate-600">Se souvenir de moi</span>
                    </label>
                </div>

                <!-- Error Message -->
                <div v-if="error" class="p-3.5 bg-red-50 border border-red-100 rounded-xl flex items-center gap-2 text-red-600 text-xs animate-shake">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>{{ error }}</span>
                </div>

                <!-- Submit Button -->
                <button 
                    type="submit" 
                    :disabled="loading"
                    class="w-full bg-brand-500 hover:bg-brand-600 active:scale-[0.98] disabled:opacity-70 disabled:cursor-wait text-white font-bold py-3.5 px-5 rounded-full shadow-lg shadow-brand-500/25 transition-all flex items-center justify-center gap-2"
                >
                    <i v-if="loading" class="fas fa-circle-notch fa-spin"></i>
                    <i v-else class="fas fa-arrow-right-to-bracket"></i>
                    {{ loading ? 'Chargement...' : 'Se connecter' }}
                </button>
            </form>

            <div class="mt-10 pt-6 border-t border-slate-50 text-center">
                <p class="text-[11px] text-slate-400">
                    <i class="fas fa-shield-alt mr-1.5"></i>
                    Connexion sécurisée — Environnement privé
                </p>
            </div>
        </div>
    </div>
  </div>
</template>

<style scoped>
.animate-shake {
    animation: shake 0.5s cubic-bezier(.36,.07,.19,.97) both;
}

@keyframes shake {
    10%, 90% { transform: translate3d(-1px, 0, 0); }
    20%, 80% { transform: translate3d(2px, 0, 0); }
    30%, 50%, 70% { transform: translate3d(-4px, 0, 0); }
    40%, 60% { transform: translate3d(4px, 0, 0); }
}
</style>
