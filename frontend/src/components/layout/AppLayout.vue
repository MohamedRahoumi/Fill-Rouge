<script setup>
import Sidebar from './Sidebar.vue';
import Topbar from './Topbar.vue';
import { ref } from 'vue';

const isCollapsed = ref(false);

const toggleSidebar = () => {
  isCollapsed.value = !isCollapsed.value;
};
</script>

<template>
  <div class="flex min-h-screen bg-slate-50">
    <!-- Sidebar -->
    <Sidebar :collapsed="isCollapsed" />

    <!-- Main Content -->
    <div 
      class="main-content flex-1 min-h-screen transition-all duration-300"
      :class="isCollapsed ? 'ml-[80px]' : 'ml-[260px]'"
    >
      <Topbar @toggle-sidebar="toggleSidebar" />

      <main class="page-content p-6 animate-fade-in">
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
