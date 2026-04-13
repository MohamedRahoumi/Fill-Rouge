<script setup>
defineProps({
  variant: {
    type: String,
    default: 'primary' // primary, gold, ghost, danger, slate
  },
  loading: {
    type: Boolean,
    default: false
  },
  type: {
    type: String,
    default: 'button'
  },
  disabled: {
    type: Boolean,
    default: false
  }
});
</script>

<template>
  <button
    :type="type"
    :disabled="disabled || loading"
    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl font-bold transition-all active:scale-[0.98] disabled:opacity-60 disabled:cursor-not-allowed disabled:active:scale-100"
    :class="{
      'bg-brand-500 text-white hover:bg-brand-600 shadow-md shadow-brand-500/20': variant === 'primary',
      'bg-gold-500 text-white hover:bg-gold-600 shadow-md shadow-gold-500/20': variant === 'gold',
      'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50 shadow-sm': variant === 'ghost',
      'bg-red-50 text-red-600 border border-red-100 hover:bg-red-100': variant === 'danger',
      'bg-slate-800 text-white hover:bg-slate-900': variant === 'slate'
    }"
  >
    <i v-if="loading" class="fas fa-circle-notch fa-spin"></i>
    <slot></slot>
  </button>
</template>
