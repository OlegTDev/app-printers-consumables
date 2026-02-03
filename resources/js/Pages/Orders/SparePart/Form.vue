<script setup>
import { useForm } from '@inertiajs/inertia-vue3';
import { computed, inject, ref } from 'vue';
import Button from 'primevue/button';
import { Inertia } from '@inertiajs/inertia';
import Steps from 'primevue/steps';
import Step1 from './Steps/Step1';
import Step2 from './Steps/Step2';
import Step3 from './Steps/Step3';
import Message from 'primevue/message';


const props = defineProps({
  isNew: Boolean,
  spareParts: Array,
  labels: Object,
  orderSparePart: Object,
});
const urls = inject('urls');

const form = useForm({
  id: props.orderSparePart?.id,
  id_printers_workplace: props.orderSparePart?.id_printers_workplace,
  id_spare_part: props.orderSparePart?.id_spare_part,
  call_specialist: props.orderSparePart?.call_specialist ?? false,
  comment: props.orderSparePart?.order?.comment,
  quantity: props.orderSparePart?.order?.quantity || 1,
  files: [],
  is_new: props.isNew,
  step: step,
});

const idPrinter = ref(props.orderSparePart?.printerWorkplace?.printer?.id);


const emitPrintersWorkplacesSelected = (value) => {
  form.id_printers_workplace = value?.id;
  if (value?.id) {
    delete form.errors.id_printers_workplace;
    idPrinter.value = value?.id_printer;
  }
  form.id_spare_part = null;
}

const emitCallSpecialistSelected = (value) => {
  form.call_specialist = value;
  form.id_spare_part = null;
}

const emitConsumableSelected = (value) => {
  form.id_spare_part = value.id;
  delete form.errors.id_spare_part;
}

const emitSelectedFiles = (event) => {
  form.files = event.target.files;
  delete form.errors.files;
}

const emitClearFiles = () => {
  form.files = [];
}

const emitChangeComment = (event) => {
  form.comment = event.target.value;
}

const emitQuantity = (value) => {
  form.quantity = value;
}

const urlOtherConsumablesForPrinter = computed(() => {
  if (form.id_printers_workplace) {
    return urls.dictionary.consumables.other(idPrinter.value);
  }
});


const save = () => {
  if (props.isNew) {
    form.post(urls.orders.spareParts.store());
  } else {
    form.put(urls.orders.spareParts.update(form.id));
  }
};

const step = ref(0);
const steps = ref([
  { label: 'Выбор принтера' },
  { label: 'Вызов специалиста / Выбор запчасти, количество' },
  { label: props.isNew ? 'Загрузка акта, комментарий' : 'Комментарий' },
]);

const next = () => {
  step.value++;
};

const prev = () => {
  step.value--
};

const home = () => {
  const url = props.isNew ? urls.orders.spareParts.index()
    : urls.orders.spareParts.show(form.id);
  Inertia.get(url);
}

const btnNextDisabled = computed(() => {
  if (step.value == 0 && form.id_printers_workplace) {
    return false;
  } else if (step.value == 1 && (form.id_spare_part || form.call_specialist)) {
    return false;
  }
  return true;
});

</script>
<template>
  <form @submit.prevent="save" class="w-full">
    <div class="py-4 border-b border-b-gray-200">
      <Steps :model="steps" v-model:activeStep="step" />
    </div>
    <div class="p-10">
      <div v-if="step === 0">
        <Step1
          :labels="labels"
          :urlPrintersAll="urls.printers.all()"
          :selectedId="form.id_printers_workplace"
          @update:selected="emitPrintersWorkplacesSelected"
        />
      </div>
      <div v-if="step === 1 && form.id_printers_workplace">
        <Step2
          :urlOtherConsumablesForPrinter="urlOtherConsumablesForPrinter"

          :labelCallSpecialist="labels.call_specialist"
          :labelConsumable="labels.id_spare_part"
          :labelQuantity="labels.quantity"

          :callSpecialist="form.call_specialist"
          :sparePartId="form.id_spare_part"
          :quantity="form.quantity"

          @update:selectedCallSpecialist="emitCallSpecialistSelected"
          @update:selectedConsumable="emitConsumableSelected"
          @update:quantity="emitQuantity"
        />
      </div>
      <div v-if="step === 2">
        <Step3
          :labelFiles="labels.files"
          :labelComment="labels.order.comment"

          :selectedFiles="form.files"
          :textComment="form.comment"

          @update:selectedFiles="emitSelectedFiles"
          @update:changeTextComment="emitChangeComment"
          @update:clearFiles="emitClearFiles"

          :isNew="props.isNew"
        />
      </div>
    </div>
    <div class="p-10 t-0" v-if="Object.keys(form.errors).length > 0">
      <Message v-for="[field, error] in Object.entries(form.errors)" :key="field" :closable="false" severity="error">
        {{ error }}
      </Message>
    </div>
    <div class="p-5 bg-gray-50 border-t border-gray-100 w-full">
      <div class="flex justify-between w-full">
        <div class="flex gap-2">
          <Button @click="prev" v-if="step > 0" severity="info" :loading="form.processing" icon="pi pi-arrow-left"
            label="Назад" />
          <Button @click="next" v-if="step < 2" severity="info" :loading="form.processing" :disabled="btnNextDisabled"
            icon="pi pi-arrow-right" label="Далее" iconPos="right" />
          <Button v-if="step === 2" type="submit" :loading="form.processing" icon="pi pi-save"
            :label="isNew ? 'Заказать' : 'Сохранить'" />
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
