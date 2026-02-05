<script setup>
import consumablesService from '@/Services/consumablesService';
import { useToast } from 'primevue/usetoast';
import { inject, onMounted, reactive, ref } from 'vue';
import InputSwitch from 'primevue/inputswitch';
import Label from '@/Shared/Label';
import Dropdown from 'primevue/dropdown';

const props = defineProps({
  urlOtherConsumablesForPrinter: String,

  labelCallSpecialist: String,
  labelConsumable: String,

  callSpecialist: Boolean,
  sparePartId: Number,
});

const emit = defineEmits(['update:selectedCallSpecialist', 'update:selectedConsumable']);
const config = inject('config');
const toast = reactive(useToast());

const callSpecialistSelected = ref(props.callSpecialist);
const consumablesData = ref([]);
const consumableSelected = ref();
const loading = ref(false);

const onCallSpecialistChange = (event) => {
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
    toast.add({
      severity: 'error',
      summary: 'Ошибка',
      detail: error.message,
      life: config.toast.timeLife,
    });
    console.error(error);
  } finally {
    loading.value = false;
  }
})
</script>
<template>
  <div>
    <Label for="call_specialist">{{ labelCallSpecialist }}</Label>
    <InputSwitch v-model="callSpecialistSelected" @change="onCallSpecialistChange" />
  </div>
  <template v-if="!callSpecialistSelected">
    <div class="mt-10">
      <Label for="id_spare_part">{{ labelConsumable }}</Label>
      <Dropdown v-model="consumableSelected" showClear filter :options="consumablesData" optionLabel="name"
        placeholder="Выберите запчасть" class="w-full" @change="onConsumableChange" :loading="loading">
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
      </Dropdown>
    </div>
  </template>
</template>
