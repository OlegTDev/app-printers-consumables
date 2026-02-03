<script setup>
import Label from '@/Shared/Label';
import Dropdown from 'primevue/dropdown';
import { useToast } from 'primevue/usetoast';
import { inject, onMounted, reactive, ref } from 'vue';
import IconColorPrint from '@/Shared/IconColorPrint';
import printersWorkplaceService from '@/Services/printersWorkplaceService';

const emit = defineEmits(['update:selected']);
const props = defineProps({
  labels: Object,
  urlPrintersAll: String,
  selectedId: Number,
});

const config = inject('config');
const toast = reactive(useToast());

const printersWorkplacesData = ref([]);
const printersWorkplacesSelected = ref();

onMounted(async () => {
  try {
    printersWorkplacesData.value = await printersWorkplaceService.fetch(props.urlPrintersAll);
    printersWorkplacesSelected.value = printersWorkplacesData.value.find((item) => item.id == props.selectedId);
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Ошибка',
      detail: error.message,
      life: config.toast.timeLife,
    });
    console.error(error);
  }
});

const onChangePrinterWorkplace = (event) => {
  emit('update:selected', event.value);
};

</script>
<template>
  <div>
    <Label for="id_printers_workplace">{{ labels.id_printers_workplace }}</Label>
    <Dropdown v-model="printersWorkplacesSelected" filter showClear :options="printersWorkplacesData" optionLabel="label"
      placeholder="Выберите принтер" class="w-full" @change="onChangePrinterWorkplace">
      <template #value="slotProps">
        <div v-if="slotProps.value" class="grid gap-y-2">
          <div class="flex gap-x-2">
            <i class="fa-solid fa-location-dot"></i>
            {{ slotProps.value.location }} каб.
          </div>
          <div class="flex gap-x-2">
            {{ `${slotProps.value.vendor} ${slotProps.value.model}` }}
            <span v-if="slotProps.value.is_color_print">
              <IconColorPrint class="h-4 w-4" />
            </span>
          </div>
          <div class="text-gray-500">
            инвентарный: {{ slotProps.value.inventory_number }}, серийный: {{
              slotProps.value.serial_number }}
          </div>
        </div>
        <span v-else>
          {{ slotProps.placeholder }}
        </span>
      </template>
      <template #option="slotProps">
        <div class="grid gap-y-2">
          <div class="flex gap-x-2">
            <i class="fa-solid fa-location-dot"></i>
            {{ slotProps.option.location }} каб.
          </div>
          <div class="flex gap-x-2">
            {{ `${slotProps.option.vendor} ${slotProps.option.model}` }}
            <span v-if="slotProps.option.is_color_print">
              <IconColorPrint class="h-4 w-4" />
            </span>
          </div>
          <div class="text-gray-500">
            инвентарный: {{ slotProps.option.inventory_number }}, серийный: {{
              slotProps.option.serial_number }}
          </div>
        </div>
      </template>
    </Dropdown>
  </div>
</template>
