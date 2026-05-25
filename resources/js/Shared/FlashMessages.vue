<script setup>
import { usePage } from '@inertiajs/vue3';
import { watch } from 'vue';
import Toast from 'primevue/toast';
import { useConfig } from '@/Composables/useConfig';
import { useToast } from 'primevue';

const page = usePage();
const toast = useToast();
const { config } = useConfig();

const defaultLife = config.toast.timeLife;

watch(
  () => [page.props.errors, page.props.flash],
  ([newErrors, newFlash]) => {

    if (newErrors && Object.keys(newErrors).length > 0) {
      Object.values(newErrors).forEach((message) => {
        toast.add({
          severity: 'error',
          summary: 'Ошибка валидации',
          detail: message,
          life: defaultLife,
        });
      });
    }

    if (newFlash?.error) {
      toast.add({
        severity: 'error',
        summary: 'Ошибка',
        detail: newFlash.error,
        life: defaultLife,
      });
    }

    if (newFlash?.success) {
      toast.add({
        severity: 'success',
        summary: 'Успешно',
        detail: newFlash?.success,
        life: defaultLife,
      });
    }
  },
);

</script>

<template>
  <Toast position="bottom-right" />
</template>
