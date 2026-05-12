<script setup>
import { useNotification } from '@/Composables/useNotification';
import Label from '@/Shared/Label.vue';
import Select from 'primevue/select';
import { onMounted, ref } from 'vue';
import fetchService from '@/Services/fetchService';
import Message from 'primevue/message';

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
    default: 'id_consumable_count',
  },
  label: {
    type: String,
    default: 'Выберите расходный материал',
  },
  placeholder: {
    type: String,
    default: 'Выберите расходный материал',
  },
});

defineEmits(['update:selected']);

const consumableSelected = ref();
const consumablesLoading = ref(false);
const consumableData = ref();
const cartridgeColors = ref({});
const consumableTypes = ref({});

const { showError } = useNotification();

function formatLabel(item, consumableTypes, cartridgeColors) {
  let colorText = '';
  if (item.type === 'cartridge') {
    colorText = `${item.color} ${cartridgeColors[item.color]?.name ?? null}`;
  }
  return `${consumableTypes[item.type]} ${item.name} ${colorText} ${item.serial_number ?? ''}`.trim();
}

onMounted(async () => {
  consumablesLoading.value = true;
  try {
    const data = await fetchService.fetch(props.url);
    cartridgeColors.value = data.cartridgeColors;
    consumableTypes.value = data.consumableTypes;
    consumableData.value = [];
    if (Array.isArray(data.consumables)) {
      consumableData.value = data.consumables.map((item) => ({
        ...item,
        label: formatLabel(item, consumableTypes, cartridgeColors),
        isDisabled: item.count < 1,
      }));
    }
  } catch (e) {
    showError(e.message);
  } finally {
    consumablesLoading.value = false;
  }
});

</script>
<template>
  <div>
    <Label :for="id">{{ label }}</Label>
    <Select
      v-model="consumableSelected"
      :invalid="error"
      filter
      :options="consumableData"
      option-label="label"
      :placeholder="placeholder"
      class="w-full"
      option-disabled="isDisabled"
      :loading="consumablesLoading"
      empty-message="Нет расходных материалов"
      :input-id="id"
      @change="$emit('update:selected', $event.value)"
    >
      <template #value="slotProps">
        <div v-if="slotProps.value" class="grid gap-y-2">
          <div class="grid gap-x-1">
            {{ consumableTypes[slotProps.value?.type] }}
            {{ slotProps.value.name }}
          </div>
          <div v-if="slotProps.value.type == 'cartridge'">
            <div class="flex">
              <div :class="['rounded-full', 'size-4', 'mr-2', cartridgeColors[slotProps.value.color]?.bg]" />
              <div>
                {{ cartridgeColors[slotProps.value?.color]?.name }}
              </div>
            </div>
          </div>
          <div v-if="slotProps.value.count > 0" class="text-gray-400">
            Доступно: {{ slotProps.value.count }}
          </div>
          <div v-else class="text-red-600">
            Отсутствует
          </div>
        </div>
        <span v-else>
          {{ slotProps.placeholder }}
        </span>
      </template>
      <template #option="slotProps">
        <div class="grid gap-y-2">
          <div class="grid gap-x-1">
            {{ consumableTypes[slotProps.option.type] }}
            {{ slotProps.option.name }}
          </div>
          <div v-if="slotProps.option.type == 'cartridge'">
            <div class="flex">
              <div :class="['rounded-full', 'size-4', 'mr-2', cartridgeColors[slotProps.option.color]?.bg]" />
              <div>
                {{ cartridgeColors[slotProps.option.color]?.name }}
              </div>
            </div>
          </div>
          <div v-if="slotProps.option.count > 0" class="text-gray-400">
            Доступно: {{ slotProps.option.count }}
          </div>
          <div v-else class="text-red-600">
            Отсутствует
          </div>
        </div>
      </template>
    </Select>
    <Message v-if="error" class="mt-2" severity="error">
      {{ error }}
    </Message>
  </div>
</template>
