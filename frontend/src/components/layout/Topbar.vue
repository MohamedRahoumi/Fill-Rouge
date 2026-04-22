<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useAuthStore } from '../../stores/auth';
import api from '../../services/api';

const authStore = useAuthStore();
const emit = defineEmits(['toggle-sidebar']);

const props = defineProps({
  pageTitle: {
    type: String,
    default: 'Dashboard'
  }
});

const isNotifOpen = ref(false);
const notifications = ref([]);
const unreadCount = ref(0);
const loadingNotifications = ref(false);

const toggleNotif = () => {
  isNotifOpen.value = !isNotifOpen.value;
  if (isNotifOpen.value) {
    fetchNotifications();
  }
};

const handleSidebarToggle = () => {
  emit('toggle-sidebar');
};

const closeNotif = () => {
  isNotifOpen.value = false;
};

// Handle clicks outside notification dropdown
const handleClickOutside = (event) => {
  if (isNotifOpen.value && !event.target.closest('#notifDropdown') && !event.target.closest('#notifToggle')) {
    closeNotif();
  }
};

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
  fetchNotifications();
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});

const fetchNotifications = async () => {
  loadingNotifications.value = true;
  try {
    const response = await api.get('/notifications', {
      params: { limit: 20 }
    });
    notifications.value = response.data.notifications || [];
    unreadCount.value = response.data.unread_count || 0;
  } catch (error) {
    notifications.value = [];
    unreadCount.value = 0;
  } finally {
    loadingNotifications.value = false;
  }
};

const markAsRead = async (notification) => {
  if (!notification || notification.est_lu) {
    return;
  }

  try {
    await api.post(`/notifications/${notification.id}/read`);
    notification.est_lu = true;
    unreadCount.value = Math.max(0, unreadCount.value - 1);
  } catch (error) {
    console.error('Unable to mark notification as read', error);
  }
};

const markAllAsRead = async () => {
  try {
    await api.post('/notifications/read-all');
    notifications.value = notifications.value.map((n) => ({ ...n, est_lu: true }));
    unreadCount.value = 0;
  } catch (error) {
    console.error('Unable to mark all notifications as read', error);
  }
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('fr-FR', {
    day: '2-digit',
    month: 'short',
    hour: '2-digit',
    minute: '2-digit'
  });
};

const today = new Date().toLocaleDateString('fr-FR');
</script>

<template>
  <header class="topbar sticky top-0 z-40 bg-white/80 backdrop-blur-md border-b border-slate-200 px-4 sm:px-6 py-3 sm:py-4 flex items-center justify-between shadow-sm gap-3">
    <div class="flex items-center gap-4">
      <button id="sidebarToggle" @click="handleSidebarToggle" class="w-8 h-8 rounded-lg hover:bg-slate-100 transition-colors flex items-center justify-center text-slate-500">
        <i class="fas fa-bars text-sm"></i>
      </button>
      <h1 class="text-base sm:text-lg font-extrabold text-slate-900 tracking-tight truncate max-w-[42vw] sm:max-w-none">{{ pageTitle }}</h1>
    </div>
    
    <div class="flex items-center gap-2 sm:gap-4">
      <div class="relative">
        <button 
          id="notifToggle" 
          @click="toggleNotif"
          class="w-8 h-8 rounded-lg hover:bg-slate-100 transition-colors flex items-center justify-center text-slate-500 relative"
        >
          <i class="fas fa-bell text-sm"></i>
          <span v-if="unreadCount > 0" class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 text-white text-[9px] font-bold rounded-full flex items-center justify-center animate-pulse-slow">
            {{ unreadCount }}
          </span>
        </button>

        <div v-if="isNotifOpen" id="notifDropdown" class="absolute right-0 mt-2 w-[360px] max-w-[90vw] bg-white border border-slate-200 rounded-xl shadow-xl z-50 overflow-hidden">
          <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
            <h4 class="text-sm font-bold text-slate-800">Notifications</h4>
            <button v-if="unreadCount > 0" @click="markAllAsRead" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700">Tout marquer lu</button>
          </div>

          <div class="max-h-[360px] overflow-y-auto">
            <p v-if="loadingNotifications" class="px-4 py-6 text-sm text-slate-500 text-center">Chargement...</p>
            <p v-else-if="notifications.length === 0" class="px-4 py-6 text-sm text-slate-500 text-center">Aucune notification.</p>
            <div v-else class="divide-y divide-slate-100">
              <button
                v-for="notification in notifications"
                :key="notification.id"
                @click="markAsRead(notification)"
                class="w-full text-left px-4 py-3 hover:bg-slate-50 transition-colors"
                :class="{ 'bg-emerald-50/60': !notification.est_lu }"
              >
                <div class="flex items-start justify-between gap-3">
                  <div>
                    <p class="text-sm font-semibold text-slate-800">{{ notification.titre }}</p>
                    <p class="text-xs text-slate-500 mt-1">{{ notification.message }}</p>
                    <p class="text-[10px] text-slate-400 mt-2">{{ formatDate(notification.created_at) }}</p>
                  </div>
                  <span v-if="!notification.est_lu" class="w-2.5 h-2.5 rounded-full bg-emerald-500 mt-1"></span>
                </div>
              </button>
            </div>
          </div>
        </div>
      </div>
      
      <div class="hidden sm:block w-px h-6 bg-slate-200"></div>
      <div class="hidden sm:flex items-center gap-2 text-sm text-slate-500">
        <i class="far fa-calendar-alt text-xs"></i>
        <span class="font-medium">{{ today }}</span>
      </div>
    </div>
  </header>
</template>
