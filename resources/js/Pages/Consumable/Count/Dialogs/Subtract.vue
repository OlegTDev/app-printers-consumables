<script setup>
import axios from 'axios';
import { computed, inject, onMounted, ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Label from '@/Shared/Label.vue';
import ProgressSpinner from 'primevue/progressspinner';
import Message from 'primevue/message';
import InputNumber from 'primevue/inputnumber';
import Button from 'primevue/button';
import { useNotification } from '@/Composables/useNotification';
import FieldRowVertical from '@/Shared/Form/FieldRowVertical.vue';
import PrinterWorkplaceSelect from '@/Components/PrinterWorkplace/PrinterWorkplaceSelect.vue';

const dialogRef = inject('dialogRef');
const { showError } = useNotification();
const dialogData = computed(() => dialogRef.value?.data || {});

const printersWorkplaces = ref([]);
const selectedPrinter = ref(null);
const loading = ref(false);

const form = useForm({
  id_consumable_count: dialogData.value.idConsumableCount,
  id_printer_workplace: null,
  count: dialogData.value.count || 1,
});

const handlePrinterChange = (printer) => {
  selectedPrinter.value = printer;
  form.id_printer_workplace = printer?.id;
};

onMounted(async () => {
  loading.value = true;
  try {
    const url = route('printers-workplace.list', {
      consumable: dialogData.value.idConsumable,
    });
    const response = await axios.get(url);
    if (Array.isArray(response.data)) {
      printersWorkplaces.value = response.data;
    }
  } catch (error) {
    showError(error.message);
  } finally {
    loading.value = false;
  }
});

const save = () => {
  const idConsumable = dialogData.value.idConsumable;
  const idConsumableCount = dialogData.value.idConsumableCount;
  const url = route('consumables.counts.installed.store', { consumable: idConsumable, count: idConsumableCount });
  form.post(url, {
    onSuccess: () => dialogRef.value.close(),
  });
};
</script>
<template>
  <form @submit.prevent="save">
    <div v-if="loading" class="flex justify-center items-center p-10">
      <ProgressSpinner style="width: 50px; height: 50px" stroke-width="4" />
    </div>

    <div v-else-if="printersWorkplaces.length == 0">
      <Message severity="warn" :closable="false">
        Нет привязки принтеров к данному расходному материалу и текущей
        организации, либо нет таких принтеров на рабочих местах!
      </Message>
    </div>

    <div v-else class="grid gap-y-8">
      <FieldRowVertical>
        <template #label>
          <Label for="id_printer_workplace">Принтер</Label>
        </template>
        <template #field>
          <PrinterWorkplaceSelect
            :model-value="selectedPrinter"
            :options="printersWorkplaces"
            :invalid="!!form.errors?.id_printer_workplace"
            input-id="id_printer_workplace"
            @update:model-value="handlePrinterChange"
          />
        </template>
        <template #message>
          <Message
            v-if="form.errors?.id_printer_workplace"
            class="mt-2 justify-start"
            severity="error"
          >
            {{ form.errors?.id_printer_workplace }}
          </Message>
        </template>
      </FieldRowVertical>
      <FieldRowVertical>
        <template #label>
          <Label for="count">Количество</Label>
        </template>
        <template #field>
          <InputNumber
            v-model="form.count"
            placeholder="Количество"
            :invalid="!!form.errors?.count"
            :min="1"
            show-buttons
            button-layout="horizontal"
            input-id="count"
            input-class="text-center w-24"
          >
            <template #incrementicon>
              <i class="pi pi-plus" />
            </template>
            <template #decrementicon>
              <span class="pi pi-minus" />
            </template>
          </InputNumber>
        </template>
        <template #message>
          <Message
            v-if="form.errors?.count"
            class="mt-2"
            severity="error"
          >
            {{ form.errors?.count }}
          </Message>
        </template>
      </FieldRowVertical>
      <div class="flex justify-end">
        <Button
          type="submit"
          :loading="form.processing"
          icon="pi pi-save"
          label="Сохранить"
        />
      </div>
    </div>
  </form>
</template>
