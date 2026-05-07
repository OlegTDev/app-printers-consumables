<script setup>
const props = defineProps({
    forId: String,
});
</script>

<template>
  <label class="block leading-6 text-gray-500 font-bold" :for="forId">
    <slot />
  </label>
</template>
