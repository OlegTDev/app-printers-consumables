<script setup>
import Button from 'primevue/button';

defineEmits(['restore']);
</script>

<template>
  <div
    class="flex items-center justify-between p-4 max-w-full bg-yellow-400 rounded"
  >
    <div class="flex items-center">
      <i class="fas fa-trash mr-2 font-medium text-yellow-800" />
      <div class="text-yellow-800 font-medium">
        <slot />
      </div>
    </div>
    <Button
      severity="warning"
      class="font-bold"
      @click="$emit('restore')"
    >
      Восстановить
    </Button>
  </div>
</template>
