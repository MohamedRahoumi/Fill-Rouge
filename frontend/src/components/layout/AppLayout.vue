<script setup>
import Sidebar from './Sidebar.vue';
import Topbar from './Topbar.vue';
import { ref, onMounted, onUnmounted } from 'vue';

const isCollapsed = ref(false);
const isMobile = ref(false);
const isMobileSidebarOpen = ref(false);

const updateViewport = () => {
  isMobile.value = window.innerWidth < 1024;
  if (!isMobile.value) {
    isMobileSidebarOpen.value = false;
  }
};

const toggleSidebar = () => {
  if (isMobile.value) {
    isMobileSidebarOpen.value = !isMobileSidebarOpen.value;
    return;
  }
  isCollapsed.value = !isCollapsed.value;
};

const closeMobileSidebar = () => {
  isMobileSidebarOpen.value = false;
};

onMounted(() => {
  updateViewport();
  window.addEventListener('resize', updateViewport);
});

onUnmounted(() => {
  window.removeEventListener('resize', updateViewport);
});
</script>

<template>
  <div class="flex min-h-screen bg-slate-50">
    <div
      v-if="isMobileSidebarOpen"
      class="fixed inset-0 z-[90] bg-slate-900/40 lg:hidden"
      @click="closeMobileSidebar"
    ></div>

    <!-- Sidebar -->
    <Sidebar :collapsed="isCollapsed" :mobile-open="isMobileSidebarOpen" @close-mobile="closeMobileSidebar" />

    <!-- Main Content -->
    <div 
      class="main-content flex-1 min-h-screen transition-all duration-300"
      :class="isMobile ? 'ml-0' : (isCollapsed ? 'ml-[80px]' : 'ml-[260px]')"
    >
      <Topbar @toggle-sidebar="toggleSidebar" />

      <main class="page-content p-4 sm:p-6 animate-fade-in">
        <slot></slot>
      </main>
    </div>
  </div>
</template>

<style scoped>
.animate-fade-in {
  animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
  0% { opacity: 0; transform: translateY(10px); }
  100% { opacity: 1; transform: translateY(0); }
}
</style>
