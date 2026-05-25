<script setup>
import Button from 'primevue/button';
import { useAttrs } from 'vue';

defineProps({
  loading: Boolean,
});
useAttrs();
</script>

<template>
  <Button :disabled="loading" v-bind="$attrs" :class="$attrs.class">
    <div v-if="loading" class="btn-spinner mr-2" />
    <slot />
  </Button>
</template>
