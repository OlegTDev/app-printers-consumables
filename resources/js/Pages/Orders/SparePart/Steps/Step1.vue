<script setup>
import Label from '@/Shared/Label.vue';
import { onMounted, ref } from 'vue';
import IconColorPrint from '@/Shared/IconColorPrint.vue';
import printersWorkplaceService from '@/Services/printersWorkplaceService';
import { useNotification } from '@/Composables/useNotification';
import FieldRowVertical from '@/Shared/Form/FieldRowVertical.vue';
import { Select } from 'primevue';

const emit = defineEmits(['update:selected']);
const props = defineProps({
  labels: {
    type: Object,
  },
  urlPrintersAll: {
    type: String,
  },
  selectedId: {
    type: Number,
  },
});

const { showError } = useNotification();
const printersWorkplacesData = ref([]);
const printersWorkplacesSelected = ref({});
const loading = ref(false);

onMounted(async () => {
  try {
    loading.value = true;
    printersWorkplacesData.value = await printersWorkplaceService.fetch(props.urlPrintersAll);
    printersWorkplacesSelected.value = printersWorkplacesData.value.find((item) => item.id == props.selectedId);
  } catch (error) {
    showError(error.message);
  } finally {
    loading.value = false;
  }
});

const onChangePrinterWorkplace = (event) => {
  emit('update:selected', event.value);
};
</script>
<template>
  <FieldRowVertical>
    <template #label>
      <Label for="id_printers_workplace">{{ labels.id_printers_workplace }}</Label>
    </template>
    <template #field>
      <Select
        v-model="printersWorkplacesSelected"
        filter
        show-clear
        :options="printersWorkplacesData"
        option-label="label"
        placeholder="Выберите принтер"
        :loading="loading"
        @change="onChangePrinterWorkplace"
      >
        <template #value="slotProps">
          <div v-if="slotProps.value" class="grid gap-y-2">
            <div class="flex gap-x-2 font-bold">
              <i class="pi pi-map-marker" />
              {{ slotProps.value.location }} каб.
            </div>
            <div class="flex gap-x-2">
              {{ `${slotProps.value.vendor} ${slotProps.value.model}` }}
              <span v-if="slotProps.value.is_color_print">
                <IconColorPrint class="h-4 w-4" />
              </span>
            </div>
            <div class="text-gray-500">
              инвентарный: {{ slotProps.value.inventory_number }},
              серийный: {{ slotProps.value.serial_number }}
            </div>
          </div>
          <span v-else>
            {{ slotProps.placeholder }}
          </span>
        </template>
        <template #option="{ option }">
          <div class="grid gap-y-2">
            <div class="flex gap-x-2 font-bold">
              <i class="pi pi-map-marker" />
              {{ option.location }} каб.
            </div>
            <div class="flex gap-x-2">
              {{ `${option.vendor} ${option.model}` }}
              <span v-if="option.is_color_print">
                <IconColorPrint class="h-4 w-4" />
              </span>
            </div>
            <div class="text-gray-500">
              инвентарный: {{ option.inventory_number }},
              серийный: {{ option.serial_number }}
            </div>
          </div>
        </template>
      </Select>
    </template>
  </FieldRowVertical>
</template>
