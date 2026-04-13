<script setup>
defineProps({
  label: String,
  modelValue: [String, Number],
  type: {
    type: String,
    default: 'text'
  },
  placeholder: String,
  error: String,
  icon: String,
  required: Boolean
});

defineEmits(['update:modelValue']);
</script>

<template>
  <div class="space-y-1.5">
    <label v-if="label" class="block text-sm font-semibold text-slate-700">
      <i v-if="icon" :class="icon" class="text-xs mr-2 text-slate-400"></i>
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>
    <div class="relative">
      <input
        :type="type"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
        :placeholder="placeholder"
        :required="required"
        class="w-full bg-slate-50 border-[1.5px] border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 placeholder:text-slate-400"
        :class="{ 'border-red-300 focus:border-red-400 focus:ring-red-100': error }"
      >
    </div>
    <p v-if="error" class="text-xs text-red-500 flex items-center gap-1 mt-1">
      <i class="fas fa-exclamation-circle text-[10px]"></i>
      {{ error }}
    </p>
  </div>
</template>
