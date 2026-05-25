<script setup>
defineProps({
  paddingBodyClasses: {
    type: [Array, String],
    default: 'p-10',
  },
  paddingHeaderClasses: {
    type: [Array, String],
    default: 'px-10 py-5',
  },
  paddingFooterClasses: {
    type: [Array, String],
    default: 'px-10 py-5',
  },
  headerContainerClasses: {
    type: [Array, String],
    default: 'flex items-center justify-between',
  },
  footerContainerClasses: {
    type: [Array, String],
    default: 'flex items-center justify-between',
  },
});
</script>
<template>
  <div class="rounded bg-white shadow-sm border border-gray-200">
    <div
      v-if="$slots.header"
      class="flex items-center justify-between bg-gray-50 border-b border-gray-100 w-full"
      :class="[paddingFooterClasses, headerContainerClasses]"
    >
      <slot name="header" />
    </div>
    <div :class="paddingBodyClasses">
      <slot />
    </div>
    <div
      v-if="$slots.footer"
      class="bg-gray-50 border-t border-gray-100 w-full"
      :class="[paddingFooterClasses, footerContainerClasses]"
    >
      <slot name="footer" />
    </div>
  </div>
</template>
