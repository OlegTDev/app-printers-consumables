<script setup>
import { Select } from 'primevue';
import PrinterWorkplaceSelectOption from './PrinterWorkplaceSelectOption.vue';

defineProps({
  modelValue: {
    type: Object,
  },
  filter: {
    type: Boolean,
    default: true,
  },
  optionLabel: {
    type: String,
    default: 'name',
  },
  placeholder: {
    type: String,
    default: 'Выберите принтер на рабочем месте',
  },
  showClear: {
    type: Boolean,
    default: true,
  },
});

const emit = defineEmits(['update:modelValue']);
</script>
<template>
  <Select
    :model-value="modelValue"
    filter
    :option-label
    :placeholder
    :show-clear
    @update:model-value="emit('update:modelValue', $event)"
  >
    <template #value="slotProps">
      <PrinterWorkplaceSelectOption
        v-if="slotProps.value"
        :values="slotProps.value"
      />
      <span v-else>
        {{ slotProps.placeholder }}
      </span>
    </template>
    <template #option="slotProps">
      <PrinterWorkplaceSelectOption
        :values="slotProps.option"
      />
    </template>
  </Select>
</template>
