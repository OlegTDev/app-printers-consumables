<script setup>
import printersWorkplaceService from '@/Services/printersWorkplaceService';
import { onMounted, ref } from 'vue';
import Select from 'primevue/select';
import Message from 'primevue/message';
import Label from '@/Shared/Label.vue';
import { useNotification } from '@/Composables/useNotification';
import IconColorPrint from '@/Shared/IconColorPrint.vue';

const props = defineProps({
  error: {
    type: String,
    default: null,
  },
  url: {
    type: String,
    required: true,
  },
  id: {
    type: String,
    default: 'id_printer_workplace',
  },
  label: {
    type: String,
    default: 'Выберите принтер',
  },
  placeholder: {
    type: String,
    default: 'Выберите принтер',
  },
});

defineEmits(['update:selected']);

const printersWorkplacesSelected = ref();
const loadingPrinters = ref(false);
const printersWorkplacesData = ref();

const { showError } = useNotification();

onMounted(async () => {
  loadingPrinters.value = true;
  try {
    const data = await printersWorkplaceService.fetch(props.url);
    if (Array.isArray(data)) {
      printersWorkplacesData.value = data;
    }
  } catch (e) {
    showError(e.message);
  } finally {
    loadingPrinters.value = false;
  }
});

</script>
<template>
  <div>
    <Label :for="id">{{ label }}</Label>
    <Select
      v-model="printersWorkplacesSelected"
      :invalid="error"
      filter
      :options="printersWorkplacesData"
      option-label="label"
      :placeholder="placeholder"
      class="w-full"
      :loading="loadingPrinters"
      show-clear
      :input-id="id"
      @change="$emit('update:selected', $event.value)"
    >
      <template #value="slotProps">
        <div v-if="slotProps.value" class="grid gap-y-2">
          <div class="flex gap-x-2">
            <i class="fa-solid fa-location-dot" />
            {{ slotProps.value?.location }} каб.
          </div>
          <div class="flex gap-x-2">
            {{ `${slotProps.value?.vendor} ${slotProps.value?.model}` }}
            <span v-if="slotProps.value.is_color_print">
              <IconColorPrint class="h-4 w-4" />
            </span>
          </div>
          <div class="text-gray-500">
            инвентарный: {{ slotProps.value?.inventory_number }}, серийный:
            {{ slotProps.value?.serial_number }}
          </div>
        </div>
        <span v-else>
          {{ slotProps.placeholder }}
        </span>
      </template>
      <template #option="slotProps">
        <div class="grid gap-y-2">
          <div class="flex gap-x-2">
            <i class="fa-solid fa-location-dot" />
            {{ slotProps.option?.location }} каб.
          </div>
          <div class="flex gap-x-2">
            {{ `${slotProps.option?.vendor} ${slotProps.option?.model}` }}
            <span v-if="slotProps.option.is_color_print">
              <IconColorPrint class="h-4 w-4" />
            </span>
          </div>
          <div class="text-gray-500">
            инвентарный: {{ slotProps.option?.inventory_number }}, серийный:
            {{ slotProps.option?.serial_number }}
          </div>
        </div>
      </template>
    </Select>
    <Message v-if="error" class="mt-2" severity="error">
      {{ error }}
    </Message>
  </div>
</template>
