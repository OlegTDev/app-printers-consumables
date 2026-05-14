<script setup>
import { computed } from 'vue';

const props = defineProps({
  h: { type: [Number, String], default: 1 }
});

const tagName = computed(() => `h${props.h}`);
const sizeClasses = computed(() => ({
  1: 'text-3xl font-bold',
  2: 'text-xl font-semibold text-slate-800 dark:text-slate-100',
  3: 'text-lg font-medium text-slate-700',
}[Number(props.h)] || 'text-base font-medium'));
</script>

<template>
  <div class="border-b border-slate-200 dark:border-slate-800 pb-3 mb-6 flex items-center gap-3">
    <!-- Боковой цветной маркер -->
    <span class="w-1 h-6 bg-indigo-600 rounded-full shrink-0" />

    <component
      :is="tagName"
      :class="sizeClasses"
    >
      <slot />
    </component>
  </div>
</template>
