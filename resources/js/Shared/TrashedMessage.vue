<script setup>
import Button from 'primevue/button';

defineEmits(['restore']);
</script>

<template>
  <div class="flex items-center justify-between p-2 max-w-full bg-yellow-400 rounded">
    <div class="flex items-center ms-4">
      <i class="pi pi-trash text-yellow-800 mr-2" />
      <div class="text-yellow-800 font-medium">
        <slot />
      </div>
    </div>
    <Button
      class="font-bold"
      severity="warn"
      type="button"
      @click="$emit('restore')"
    >
      Восстановить
    </Button>
  </div>
</template>
