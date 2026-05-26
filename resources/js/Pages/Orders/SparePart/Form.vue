<script setup>
import { useForm, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import Button from 'primevue/button';
import Steps from 'primevue/steps';
import Step1 from './Steps/Step1.vue';
import Step2 from './Steps/Step2.vue';
import Step3 from './Steps/Step3.vue';
import Message from 'primevue/message';
import Title from '@/Shared/Title.vue';
import Card from '@/Shared/Card.vue';


const props = defineProps({
  isNew: {
    type: Boolean,
    default: false,
  },
  spareParts: {
    type: Array,
    required: true,
  },
  labels: {
    type: Object,
    required: true,
  },
  orderSparePart: {
    type: Object,
    default: () => ({}),
  },
  title: {
    type: String,
    default: '',
  },
});

const step = ref(0);
const steps = ref([
  { label: 'Выбор принтера' },
  { label: 'Вызов специалиста / Выбор запчасти, количество' },
  { label: props.isNew ? 'Загрузка акта, комментарий' : 'Комментарий' },
]);

const form = useForm({
  id: props.orderSparePart?.id,
  id_printers_workplace: props.orderSparePart?.id_printers_workplace,
  id_spare_part: props.orderSparePart?.id_spare_part,
  call_specialist: props.orderSparePart?.call_specialist ?? false,
  comment: props.orderSparePart?.order?.comment,
  service_request_number: props.orderSparePart?.order?.service_request_number,
  service_request_date: props.orderSparePart?.order?.service_request_date,
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
};

const emitCallSpecialistSelected = (value) => {
  form.call_specialist = value;
  form.id_spare_part = null;
};

const emitConsumableSelected = (value) => {
  form.id_spare_part = value.id;
  delete form.errors.id_spare_part;
};

const emitSelectedFiles = (event) => {
  form.files = event.files || [];
  form.clearErrors('files');
};

const emitClearFiles = () => {
  form.reset('files');
};

const emitChangeComment = (event) => {
  form.comment = event.target.value;
};

const emitChangeServiceRequestNumber = (event) => {
  form.service_request_number = event.target.value;
};

const emitChangeServiceRequestDate = (date) => {
  form.service_request_date = date;
};

const urlOtherConsumablesForPrinter = computed(() => {
  if (form.id_printers_workplace) {
    return route('dictionary.consumables.other', { printer: idPrinter.value });
  }
  return null;
});

const save = () => {
  if (props.isNew) {
    form.post(route('orders.spare-parts.store'));
  } else {
    form.put(route('orders.spare-parts.update', { orderSparePartDetails: form.id }));
  }
};

const next = () => {
  step.value++;
};

const prev = () => {
  step.value--;
};

const home = () => {
  const url = props.isNew ? route('orders.spare-parts.index')
    : route('orders.spare-parts.show', { orderSparePartDetails: form.id });
  router.get(url);
};

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
  <form @submit.prevent="save">
    <Card>
      <Title>{{ title }}</Title>

      <div class="pb-4 border-b border-b-gray-200">
        <Steps v-model:active-step="step" :model="steps" />
      </div>
      <div class="p-10">
        <div v-if="step === 0">
          <Step1
            :labels="labels"
            :url-printers-all="route('workplace.all')"
            :selected-id="form.id_printers_workplace"
            @update:selected="emitPrintersWorkplacesSelected"
          />
        </div>
        <div v-if="step === 1 && form.id_printers_workplace">
          <Step2
            :url-other-consumables-for-printer="urlOtherConsumablesForPrinter"
            :label-call-specialist="labels.call_specialist"
            :label-consumable="labels.id_spare_part"
            :call-specialist="form.call_specialist"
            :spare-part-id="form.id_spare_part"
            @update:selected-call-specialist="emitCallSpecialistSelected"
            @update:selected-consumable="emitConsumableSelected"
          />
        </div>
        <div v-if="step === 2">
          <Step3
            :label-files="labels.files"
            :label-comment="labels.order.comment"
            :label-service-request-number="labels.order.service_request_number"
            :label-service-request-date="labels.order.service_request_date"

            :selected-files="form.files"
            :text-comment="form.comment"
            :service-request-number="form.service_request_number"
            :service-request-date="form.service_request_date"

            :is-new="isNew"

            @update:selected-files="emitSelectedFiles"
            @update:change-text-comment="emitChangeComment"
            @update:clear-files="emitClearFiles"
            @update:service-request-number="emitChangeServiceRequestNumber"
            @update:service-request-date="emitChangeServiceRequestDate"
          />
        </div>
      </div>
      <div v-if="Object.keys(form.errors).length > 0" class="p-10 t-0">
        <Message v-for="[field, error] in Object.entries(form.errors)" :key="field" :closable="false" severity="error">
          {{ error }}
        </Message>
      </div>

      <div v-if="form.progress" class="w-full bg-gray-100 rounded-full mt-4">
        <div
          class="bg-primary-500 text-xs font-medium text-white text-center p-0.5 leading-none rounded-full h-4 flex items-center justify-center"
          :style="{ width: (form.progress?.percentage ?? 0) + '%' }"
        >
          {{ form.progress?.percentage ?? 0 }}%
        </div>
      </div>

      <template #footer>
        <div class="flex justify-between w-full">
          <div class="flex gap-2">
            <Button
              v-if="step > 0"
              severity="info"
              :loading="form.processing"
              icon="pi pi-arrow-left"
              label="Назад"
              @click="prev"
            />
            <Button
              v-if="step < 2"
              severity="info"
              :loading="form.processing"
              :disabled="btnNextDisabled"
              icon="pi pi-arrow-right"
              label="Далее"
              icon-pos="right"
              @click="next"
            />
            <Button
              v-if="step === 2"
              type="submit"
              :loading="form.processing"
              icon="pi pi-save"
              :label="isNew ? 'Заказать' : 'Сохранить'"
            />
          </div>
          <div>
            <Button
              icon="pi pi-id-card"
              label="Вернуться"
              @click="home"
            />
          </div>
        </div>
      </template>
    </Card>
  </form>
</template>
