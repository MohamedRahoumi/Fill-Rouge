<script setup>
import { onMounted, onUnmounted } from 'vue';

const props = defineProps({
  show: Boolean,
  title: String,
  subtitle: String,
  width: {
    type: String,
    default: 'max-w-md'
  }
});

const emit = defineEmits(['close']);

const close = () => {
  emit('close');
};

const handleKeydown = (e) => {
  if (e.key === 'Escape' && props.show) {
    close();
  }
};

onMounted(() => window.addEventListener('keydown', handleKeydown));
onUnmounted(() => window.removeEventListener('keydown', handleKeydown));
</script>

<template>
  <Teleport to="body">
    <Transition name="modal-fade">
      <div v-if="show" class="fixed inset-0 z-[200] flex items-center justify-center p-3 sm:p-4">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-[2px]" @click="close"></div>
        
        <!-- Modal Content -->
        <div 
          class="relative bg-white w-full rounded-[24px] sm:rounded-[32px] shadow-2xl overflow-hidden animate-modal-in max-h-[90vh] overflow-y-auto"
          :class="width"
        >
          <!-- Header -->
          <div class="px-5 py-4 sm:px-8 sm:py-6 border-b border-slate-100 flex items-center justify-between bg-white gap-3">
            <div>
              <h3 class="text-lg sm:text-xl font-bold text-slate-900">{{ title }}</h3>
              <p v-if="subtitle" class="text-sm text-slate-500 mt-0.5">{{ subtitle }}</p>
            </div>
            <button @click="close" class="w-10 h-10 rounded-full hover:bg-slate-50 flex items-center justify-center text-slate-400 transition-colors">
              <i class="fas fa-times"></i>
            </button>
          </div>
          
          <!-- Body -->
          <div class="px-5 py-4 sm:px-8 sm:py-6">
            <slot></slot>
          </div>
          
          <!-- Footer -->
          <div v-if="$slots.footer" class="px-5 py-4 sm:px-8 sm:py-6 bg-slate-50/50 border-t border-slate-100 flex items-center justify-end gap-3">
            <slot name="footer"></slot>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.modal-fade-enter-active, .modal-fade-leave-active {
  transition: opacity 0.3s ease;
}
.modal-fade-enter-from, .modal-fade-leave-to {
  opacity: 0;
}

.animate-modal-in {
  animation: modalIn 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes modalIn {
  from { opacity: 0; transform: scale(0.95) translateY(10px); }
  to { opacity: 1; transform: scale(1) translateY(0); }
}
</style>
