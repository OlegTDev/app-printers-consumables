<script setup>
import consumablesService from '@/Services/consumablesService';
import { onMounted, ref } from 'vue';
import Label from '@/Shared/Label.vue';
import { useNotification } from '@/Composables/useNotification';
import FieldRowVertical from '@/Shared/Form/FieldRowVertical.vue';
import { Select, ToggleSwitch } from 'primevue';

const props = defineProps({
  urlOtherConsumablesForPrinter: {
    type: String,
  },
  labelCallSpecialist: {
    type: String,
  },
  labelConsumable: {
    type: String,
  },
  callSpecialist: {
    type: Boolean,
  },
  sparePartId: {
    type: Number,
  },
});

const emit = defineEmits(['update:selectedCallSpecialist', 'update:selectedConsumable']);
const { showError } = useNotification();

const callSpecialistSelected = ref(props.callSpecialist);
const consumablesData = ref([]);
const consumableSelected = ref();
const loading = ref(false);

const onCallSpecialistChange = () => {
  consumableSelected.value = null;
  emit('update:selectedCallSpecialist', callSpecialistSelected.value);
};

const onConsumableChange = (event) => {
  emit('update:selectedConsumable', event.value);
};

onMounted(async () => {
  try {
    loading.value = true;
    consumablesData.value = await consumablesService.fetch(props.urlOtherConsumablesForPrinter);
    consumableSelected.value = consumablesData.value.find((item) => item.id == props.sparePartId);
  } catch (error) {
    showError(error.message);
  } finally {
    loading.value = false;
  }
});
</script>
<template>
  <div class="grid gap-y-8">
    <FieldRowVertical>
      <template #label>
        <Label for="call_specialist">{{ labelCallSpecialist }}</Label>
      </template>
      <template #field>
        <ToggleSwitch v-model="callSpecialistSelected" @change="onCallSpecialistChange" />
      </template>
    </FieldRowVertical>

    <FieldRowVertical v-if="!callSpecialistSelected">
      <template #label>
        <Label for="id_spare_part">{{ labelConsumable }}</Label>
      </template>
      <template #field>
        <Select
          v-model="consumableSelected"
          show-clear
          filter
          :options="consumablesData"
          option-label="name"
          placeholder="Выберите запчасть"
          :loading="loading"
          @change="onConsumableChange"
        >
          <template #value="{ value, placeholder }">
            <div v-if="value" class="grid gap-y-2">
              <div class="flex gap-x-2">
                {{ value.name }}
              </div>
              <div class="text-gray-500">
                {{ value?.description }}
              </div>
            </div>
            <span v-else>
              {{ placeholder }}
            </span>
          </template>
          <template #option="{ option }">
            <div v-if="option" class="grid gap-y-2">
              <div class="flex gap-x-2">
                {{ option.name }}
              </div>
              <div class="text-gray-500">
                {{ option.description }}
              </div>
            </div>
          </template>
        </Select>
      </template>
    </FieldRowVertical>
  </div>
</template>
