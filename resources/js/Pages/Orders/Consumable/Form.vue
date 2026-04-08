<script setup>
import { useForm } from '@inertiajs/inertia-vue3';
import { computed, inject, onMounted, reactive, ref } from 'vue';
import Button from 'primevue/button';
import { Inertia } from '@inertiajs/inertia';
// import Steps from 'primevue/steps';
// import Step1 from './Steps/Step1';
// import Step2 from './Steps/Step2';
// import Step3 from './Steps/Step3';
import Message from 'primevue/message';
import Label from '@/Shared/Label.vue';
import Dropdown from 'primevue/dropdown';
import { useToast } from 'primevue/usetoast';
import consumablesService from '@/Services/consumablesService';
import InputNumber from 'primevue/inputnumber';


const props = defineProps({
  isNew: Boolean,
  labels: Object,
  orderConsumable: Object,
});
const urls = inject('urls');
const config = inject('config');
const toast = reactive(useToast());


const form = useForm({
  id: props.orderConsumable?.id,
  id_consumable: props.orderConsumable?.id_consumable,
  quantity: props.orderConsumable?.quantity ?? 1,
  comment: props.orderConsumable?.order.comment,
  service_request_number: props.orderConsumable?.order.service_request_number,
  service_request_date: props.orderConsumable?.order.service_request_date,
  is_new: props.isNew,
});

const consumablesData = ref([]);
const consumableSelected = ref();
const loadingConsumables = ref(false);

onMounted(async () => {
  try {
    loadingConsumables.value = true;
    consumablesData.value = await consumablesService.fetch(urls.dictionary.consumables.notOther());
    consumableSelected.value = consumablesData.value.find((item) => item.id == form.id_consumable);
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Ошибка',
      detail: error.message,
      life: config.toast.timeLife,
    });
    console.error(error);
  } finally {
    loadingConsumables.value = false;
  }
});


const onConsumableChange = (event) => {
  form.id_consumable = event.value?.id ?? null;
  delete form.errors.id_consumable;
};



// const emitPrintersWorkplacesSelected = (value) => {
//   form.id_printers_workplace = value?.id;
//   if (value?.id) {
//     delete form.errors.id_printers_workplace;
//     idConsumable.value = value?.id_printer;
//   }
//   form.id_spare_part = null;
// }

// const emitCallSpecialistSelected = (value) => {
//   form.call_specialist = value;
//   form.id_spare_part = null;
// }

// const emitConsumableSelected = (value) => {
//   form.id_spare_part = value.id;
//   delete form.errors.id_spare_part;
// }

// const emitSelectedFiles = (event) => {
//   form.files = event.target.files;
//   delete form.errors.files;
// }


// const emitChangeComment = (event) => {
//   form.comment = event.target.value;
// }

// const emitChangeServiceRequestNumber = (event) => {
//   form.service_request_number = event.target.value;
// }

// const emitChangeServiceRequestDate = (event) => {
//   form.service_request_date = event.target.value;
// }


const save = () => {
  if (props.isNew) {
    form.post(urls.orders.consumables.store());
  } else {
    form.put(urls.orders.consumables.update(form.id));
  }
};


const home = () => {
  const url = props.isNew ? urls.orders.consumables.index()
    : urls.orders.consumables.show(form.id);
  Inertia.get(url);
}

</script>
<template>
  <form @submit.prevent="save" class="w-full">
    <div class="p-10">

      <Label for="id_consumable">{{ labels.id_consumable }}</Label>
      <Dropdown v-model="consumableSelected" filter showClear :options="consumablesData"
        optionLabel="label" placeholder="Выберите расходный материал" class="w-full" @change="onConsumableChange"
        :loading="loadingConsumables">
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
    <div class="p-10">
      <Label for="quantity">{{ labels.quantity }}</Label>
      <InputNumber v-model="form.quantity" placeholder="Введите количество" showButtons id="quantity" />
    </div>

    <div class="p-10 t-0" v-if="Object.keys(form.errors).length > 0">
      <Message v-for="[field, error] in Object.entries(form.errors)" :key="field" :closable="false" severity="error">
        {{ error }}
      </Message>
    </div>
    <div class="p-5 bg-gray-50 border-t border-gray-100 w-full">
      <div class="flex justify-between w-full">
        <div class="flex gap-2">
          <Button type="submit" :loading="form.processing" icon="pi pi-save" :label="isNew ? 'Заказать' : 'Сохранить'" />
        </div>
        <div>
          <Button @click="home" icon="pi pi-id-card" label="Вернуться" />
        </div>
      </div>
    </div>

    <div v-if="form.progress" class="w-full bg-gray-100 rounded-full mt-4">
      <div
        class="bg-primary-500 text-xs font-medium text-white text-center p-0.5 leading-none rounded-full h-4 flex items-center justify-center"
        :style="{ width: (form.progress?.percentage ?? 0) + '%' }">
        {{ form.progress?.percentage ?? 0 }}%
      </div>
    </div>
  </form>

</template>
