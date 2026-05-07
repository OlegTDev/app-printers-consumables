<script setup>
import { usePage } from '@inertiajs/vue3'
import { reactive, watch } from 'vue'
import Toast from 'primevue/toast'
import { useToast } from 'primevue/usetoast'

const page = reactive(usePage());
const toast = reactive(useToast());
const defaultLife = 5000;

watch(
  () => page.props.flash,
  (flash) => {

    if (page.props?.errors) {
      Object.values(page.props.errors).forEach((value) => {
        toast.add({
          severity: 'error',
          summary: 'Ошибки',
          detail: value,
          life: defaultLife,
        })
      })
    }
    if (flash.error) {
      toast.add({
        severity: 'error',
        summary: 'Произошла ошибка',
        detail: flash.error,
        life: defaultLife,
      })
    }
    if (flash.success) {
      toast.add({
        severity: 'success',
        summary: 'Операция выполнена успешно',
        detail: flash.success,
        life: defaultLife,
      })
    }
  },
  { deep: true }
)

</script>

<template>
  <Toast position="bottom-right" />
</template>
