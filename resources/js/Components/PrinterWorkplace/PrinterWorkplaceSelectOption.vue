<script setup>
import IconColorPrint from '@/Shared/IconColorPrint.vue';

defineProps({
  values: {
    type: Object,
    default: () => ({
      location: '',
      inventory_number: '',
      serial_number: '',
      printer: {
        vendor: '',
        model: '',
        is_color_print: false,
      },
    }),
  },
});
</script>
<template>
  <div class="gap-y-8">
    <div class="gap-x-4">
      <i class="pi pi-map-marker" />
      {{ values.location }} каб.
    </div>
    <div class="flex gap-x-4">
      {{ `${values.printer?.vendor} ${values.printer?.model}` }}
      <span v-if="values.printer?.is_color_print">
        <IconColorPrint class="h-4 w-4" />
      </span>
    </div>
    <div class="text-gray-500">
      инвентарный: {{ values.inventory_number }},
      серийный: {{ values.serial_number }}
    </div>
  </div>
</template>
