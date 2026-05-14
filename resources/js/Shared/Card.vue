<script setup>
defineProps({
  paddingBodyClasses: {
    type: Array,
    default: () => ['p-10'],
  },
  paddingFooterClasses: {
    type: Array,
    default: () => ['px-10', 'py-5'],
  },
});
</script>
<template>
  <div class="rounded-lg bg-white shadow-sm border border-gray-200">
    <div :class="paddingBodyClasses">
      <slot />
    </div>
    <div v-if="$slots.footer" class="flex items-center justify-between bg-gray-50 border-t border-gray-100 w-full" :class="paddingFooterClasses">
      <slot name="footer" />
    </div>
  </div>
</template>
