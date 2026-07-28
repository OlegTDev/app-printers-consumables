<script setup>
import Button from 'primevue/button';

defineEmits(['restore']);
</script>

<template>
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 mb-6 rounded-xl border border-amber-200 bg-amber-50/50 dark:bg-amber-950/20 dark:border-amber-900/50 shadow-sm transition-all">
    <div class="flex items-center gap-3">
      <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 shrink-0">
        <i class="pi pi-trash text-lg" />
      </div>
      <div>
        <h4 class="text-sm font-semibold text-amber-900 dark:text-amber-300">
          Учетная запись деактивирована
        </h4>
        <p class="text-xs text-amber-700/80 dark:text-amber-400/70 mt-0.5">
          Пользователь был удален. Вы можете вернуть его к работе со всеми связанными данными.
        </p>
      </div>
    </div>
    <Button
      label="Восстановить доступ"
      icon="pi pi-undo"
      severity="warn"
      raised
      class="w-full sm:w-auto px-4 font-medium bg-amber-500 hover:bg-amber-600 text-white border-none shadow-sm shrink-0"
      @click="$emit('restore')"
    />
  </div>
</template>
