<script setup>
import { useAuthStore } from '../../stores/auth';
import { useRouter, useRoute } from 'vue-router';

defineProps({
  collapsed: {
    type: Boolean,
    default: false
  },
  mobileOpen: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['close-mobile']);

const authStore = useAuthStore();
const router = useRouter();
const route = useRoute();

const isActive = (path) => {
  return route.path.startsWith(path);
};

const handleLogout = async () => {
  await authStore.logout();
};

const handleNavClick = () => {
  if (window.innerWidth < 1024) {
    emit('close-mobile');
  }
};
</script>

<template>
  <aside 
    class="sidebar fixed left-0 top-0 bottom-0 w-[260px] bg-white border-r border-slate-200 shadow-sm z-[100] flex flex-col transform transition-transform duration-300 lg:translate-x-0" 
    :class="[mobileOpen ? 'translate-x-0' : '-translate-x-full', collapsed ? 'sidebar-collapsed' : '']"
    id="sidebar"
  >
    <div class="sidebar-logo p-6 border-b border-slate-100 flex items-center gap-3">
      <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-brand-500 to-brand-600 flex items-center justify-center shadow-md shadow-brand-500/30">
        <i class="fas fa-futbol text-white text-xl"></i>
      </div>
      <div>
        <div class="title text-sm font-extrabold text-slate-900 tracking-tight">MiniFoot</div>
        <div class="subtitle text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Academy</div>
      </div>
    </div>

    <nav class="sidebar-nav flex-1 py-4 px-3 overflow-y-auto" @click="handleNavClick">
      <!-- Admin Section -->
      <template v-if="authStore.user?.role === 'superadmin'">
        <div class="nav-section-title text-[10px] font-bold text-slate-400 uppercase tracking-[1px] px-2 py-2">Administration</div>
        <router-link to="/admin/dashboard" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-500 hover:bg-slate-50 hover:text-slate-900 transition-all" :class="{ 'active bg-emerald-50 text-brand-600 font-semibold border border-emerald-100': isActive('/admin/dashboard') }">
          <i class="fas fa-tachometer-alt w-5 text-center text-sm"></i>
          <span class="text-sm font-medium">Tableau de bord</span>
        </router-link>
        <router-link to="/admin/members" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-500 hover:bg-slate-50 hover:text-slate-900 transition-all" :class="{ 'active bg-emerald-50 text-brand-600 font-semibold border border-emerald-100': isActive('/admin/members') }">
          <i class="fas fa-chalkboard-user w-5 text-center text-sm"></i>
          <span class="text-sm font-medium">Membres (Coachs / Parents)</span>
        </router-link>
        <router-link to="/admin/joueurs" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-500 hover:bg-slate-50 hover:text-slate-900 transition-all" :class="{ 'active bg-emerald-50 text-brand-600 font-semibold border border-emerald-100': isActive('/admin/joueurs') }">
          <i class="fas fa-futbol w-5 text-center text-sm"></i>
          <span class="text-sm font-medium">Joueurs</span>
        </router-link>

        <div class="nav-section-title text-[10px] font-bold text-slate-400 uppercase tracking-[1px] px-2 py-2 mt-4">Structure</div>
        <router-link to="/admin/structure" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-500 hover:bg-slate-50 hover:text-slate-900 transition-all" :class="{ 'active bg-emerald-50 text-brand-600 font-semibold border border-emerald-100': isActive('/admin/structure') }">
          <i class="fas fa-layer-group w-5 text-center text-sm"></i>
          <span class="text-sm font-medium">Catégories & Groupes</span>
        </router-link>

        <div class="nav-section-title text-[10px] font-bold text-slate-400 uppercase tracking-[1px] px-2 py-2 mt-4">Financier</div>
        <router-link to="/admin/paiements" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-500 hover:bg-slate-50 hover:text-slate-900 transition-all" :class="{ 'active bg-emerald-50 text-brand-600 font-semibold border border-emerald-100': isActive('/admin/paiements') }">
          <i class="fas fa-credit-card w-5 text-center text-sm"></i>
          <span class="text-sm font-medium">Paiements</span>
        </router-link>
        <router-link to="/admin/notifications" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-500 hover:bg-slate-50 hover:text-slate-900 transition-all" :class="{ 'active bg-emerald-50 text-brand-600 font-semibold border border-emerald-100': isActive('/admin/notifications') }">
          <i class="fas fa-bullhorn w-5 text-center text-sm"></i>
          <span class="text-sm font-medium">Notifications</span>
        </router-link>
      </template>

      <!-- Coach Section -->
      <template v-else-if="authStore.user?.role === 'coach'">
        <div class="nav-section-title text-[10px] font-bold text-slate-400 uppercase tracking-[1px] px-2 py-2">Espace Coach</div>
        <router-link to="/coach/dashboard" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-500 hover:bg-slate-50 hover:text-slate-900 transition-all" :class="{ 'active bg-emerald-50 text-brand-600 font-semibold border border-emerald-100': isActive('/coach/dashboard') }">
          <i class="fas fa-tachometer-alt w-5 text-center text-sm"></i>
          <span class="text-sm font-medium">Dashboard</span>
        </router-link>
      </template>

      <!-- Parent Section -->
      <template v-else-if="authStore.user?.role === 'parent'">
        <div class="nav-section-title text-[10px] font-bold text-slate-400 uppercase tracking-[1px] px-2 py-2">Espace Parent</div>
        <router-link to="/parent/dashboard" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-500 hover:bg-slate-50 hover:text-slate-900 transition-all" :class="{ 'active bg-emerald-50 text-brand-600 font-semibold border border-emerald-100': isActive('/parent/dashboard') }">
          <i class="fas fa-home w-5 text-center text-sm"></i>
          <span class="text-sm font-medium">Dashboard</span>
        </router-link>
        <router-link to="/parent/paiement" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-500 hover:bg-slate-50 hover:text-slate-900 transition-all" :class="{ 'active bg-emerald-50 text-brand-600 font-semibold border border-emerald-100': isActive('/parent/paiement') }">
          <i class="fas fa-credit-card w-5 text-center text-sm"></i>
          <span class="text-sm font-medium">Paiements</span>
        </router-link>
      </template>
    </nav>

    <!-- User card + Logout -->
    <div class="sidebar-footer p-4 border-t border-slate-100 bg-slate-50/50">
      <div v-if="authStore.user" class="user-card flex items-center gap-3 p-3 rounded-xl bg-white border border-slate-200 mb-3 shadow-sm">
        <div class="user-avatar w-10 h-10 rounded-full bg-gradient-to-br from-brand-500 to-brand-600 flex items-center justify-center text-white font-bold shadow-md">
          {{ authStore.user.prenom?.charAt(0).toUpperCase() }}{{ authStore.user.nom?.charAt(0).toUpperCase() }}
        </div>
        <div class="flex-1">
          <div class="user-name text-sm font-bold text-slate-900">{{ authStore.user.prenom }} {{ authStore.user.nom }}</div>
          <div class="user-role text-[11px] font-semibold text-slate-500">
            <i class="fas text-[9px] mr-1" :class="authStore.user.role === 'superadmin' ? 'fa-crown' : (authStore.user.role === 'coach' ? 'fa-chalkboard-user' : 'fa-user')"></i>
            {{ authStore.user.role?.replace('_', ' ') }}
          </div>
        </div>
      </div>
      
      <button @click="handleLogout" class="logout-btn w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 border border-red-200 transition-all text-sm font-semibold">
        <i class="fas fa-sign-out-alt text-sm"></i>
        <span class="logout-label">Déconnexion</span>
      </button>
    </div>
  </aside>
</template>

<style scoped>
@reference "../../style.css";
.nav-link.router-link-active {
  @apply bg-emerald-50 text-brand-600 font-semibold border border-emerald-100;
}

@media (min-width: 1024px) {
  .sidebar.sidebar-collapsed {
    width: 80px;
  }

  .sidebar.sidebar-collapsed .sidebar-logo {
    justify-content: center;
    padding-left: 0.75rem;
    padding-right: 0.75rem;
  }

  .sidebar.sidebar-collapsed .title,
  .sidebar.sidebar-collapsed .subtitle,
  .sidebar.sidebar-collapsed .nav-section-title,
  .sidebar.sidebar-collapsed .nav-link span,
  .sidebar.sidebar-collapsed .user-name,
  .sidebar.sidebar-collapsed .user-role,
  .sidebar.sidebar-collapsed .logout-label {
    display: none;
  }

  .sidebar.sidebar-collapsed .nav-link {
    justify-content: center;
    padding-left: 0.5rem;
    padding-right: 0.5rem;
  }

  .sidebar.sidebar-collapsed .user-card {
    justify-content: center;
    padding-left: 0.5rem;
    padding-right: 0.5rem;
  }

  .sidebar.sidebar-collapsed .logout-btn {
    padding-left: 0.5rem;
    padding-right: 0.5rem;
  }
}
</style>
