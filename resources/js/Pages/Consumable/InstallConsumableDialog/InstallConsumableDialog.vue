<script setup>
import { inject, ref } from 'vue';
import Button from 'primevue/button';
import { useForm } from '@inertiajs/vue3';
import Label from '@/Shared/Label.vue';
import InputNumber from 'primevue/inputnumber';
import Message from 'primevue/message';
import PrintersWorkplaceDropdown from './Partials/PrintersWorkplaceDropdown.vue';
import ConsumablesDropDown from './Partials/ConsumablesDropDown.vue';

const dialogRef = inject('dialogRef');
const step = ref(0);

const printersWorkplacesSelected = ref();
const consumableIsEmpty = ref(false);
const consumableSelected = ref();

const form = useForm({
  id_printer_workplace: null,
  id_consumable_count: null,
  count: 1,
  step: step,
});

const onChangePrinterWorkplace = (value) => {
  printersWorkplacesSelected.value = value;
  form.id_printer_workplace = value?.id || null;
  form.errors.id_printer_workplace = null;
  consumableSelected.value = null;
  form.id_consumable_count = null;
};

const onChangeConsumables = (value) => {
  consumableSelected.value = value;
  form.errors.id_consumable_count = null;
  form.id_consumable_count = value?.id || null;
};

const save = () => {
  if (!consumableSelected.value) {
    return;
  }
  const { id: idConsumable, id_consumable: idConsumableCount } = consumableSelected.value;
  const url = route('consumables.counts.installed.store', { consumable: idConsumable, count: idConsumableCount });
  form.post(url, {
    onSuccess: () => dialogRef.value.close({ updated: true }),
  });
};

</script>
<template>
  <form @submit.prevent="save">
    <div class="dialog">
      <PrintersWorkplaceDropdown
        :url="route('workplace.all')"
        :error="form.errors?.id_printer_workplace"
        @update:selected="onChangePrinterWorkplace"
      />

      <ConsumablesDropDown
        v-if="printersWorkplacesSelected"
        :key="printersWorkplacesSelected.id"
        :url="route('consumables.counts.list-by-printer', { printer: printersWorkplacesSelected.id_printer })"
        @update:selected="onChangeConsumables"
      />

      <div v-if="printersWorkplacesSelected && consumableSelected">
        <Label for="count">Количество</Label>
        <InputNumber
          v-model="form.count"
          class="w-full"
          placeholder="Количество"
          :invalid="form.errors?.count?.length > 0"
        />
        <Message v-if="form.errors?.count" class="mt-2" severity="error">
          {{ form.errors?.count }}
        </Message>
      </div>

      <div v-if="consumableSelected && !consumableIsEmpty">
        <Button type="submit" :loading="form.processing" icon="pi pi-save" label="Сохранить" />
      </div>
    </div>
  </form>
</template>
<style scoped>
  .dialog {
    display: grid;
    gap: 1rem;
  }
</style>
