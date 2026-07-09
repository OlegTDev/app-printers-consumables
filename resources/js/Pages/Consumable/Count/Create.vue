<script setup>
import { computed, ref, watch } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import Layout from '@/Shared/Layout.vue';
import Breadcrumbs from '@/Shared/Breadcrumbs.vue';
import Label from '@/Shared/Label.vue';
import InputNumber from 'primevue/inputnumber';
import Checkbox from 'primevue/checkbox';
import Steps from 'primevue/steps';
import Button from 'primevue/button';
import axios from 'axios';
import Message from 'primevue/message';
import Card from '@/Shared/Card.vue';
import Title from '@/Shared/Title.vue';
import FieldRowVertical from '@/Shared/Form/FieldRowVertical.vue';
import { Select } from 'primevue';
import { useNotification } from '@/Composables/useNotification';

defineOptions({
  layout: Layout,
});

const props = defineProps({
  auth: Object,
  consumables: Array,
  consumableCountLabels: Object,
  availableOrganizations: Array,
});

const title = 'Добавление';

const step = ref(0);

const { showError } = useNotification();

const form = useForm({
  id_consumable: null,
  count: null,
  selectedOrganizations: [props.auth.user.org_code],
  changeOrganization: false,
  step: step,
});

const save = () => {
  form.post(route('consumables.counts.store'), {
    onHttpException: () => {
      showError('Произошла ошибка');
    },
  });
};

const consumableData = ref();

const findConsumable = async() => {
  form.changeOrganization = false;
  consumableData.value = null;
  try {
    const response = await axios.get(route('consumables.counts.by-consumable', { idConsumable: form.id_consumable }));
    consumableData.value = response.data?.id ? response.data : null;
  } catch (e) {
    if (e.response?.status == 404) {
      consumableData.value = null;
    } else {
      showError(e.message);
      console.error(e);
    }
  }
};

const disabledNextBtn = computed(() => {
  if (step.value == 0) {
    return form.id_consumable == null;
  }
  if (step.value == 1) {
    return form.selectedOrganizations.length == 0;
  }
  return false;
});

const next = () => {
  step.value++;
};

const prev = () => {
  step.value--;
};

const items = ref([
  { label: 'Выбор расходного материала' },
  { label: 'Перечень организаций' },
  { label: 'Количество' },
]);

watch(
  () => form.id_consumable,
  (id) => {
    if (id) {
      findConsumable();
    } else {
      consumableData.value = null;
      step.value = 0;
    }
  }
);

watch(
  () => form.data(),
  (newData, oldData) => {
    Object.keys(form.errors).forEach(field => {
      if (newData[field] !== oldData[field]) {
        form.clearErrors(field);
      }
    });
  }
);
</script>
<template>
  <Head :title="title" />

  <Breadcrumbs
    :home="{ label: 'Главная', url: route('dashboard') }"
    :items="[
      {
        label: 'Количество расходных материалов',
        url: route('consumables.counts.index'),
      },
      { label: title },
    ]"
  />

  <form @submit.prevent="save">
    <Card>
      <Title>{{ title }}</Title>

      <Steps v-model:active-step="step" :model="items" />
      <div class="mt-10">
        <div v-if="step === 0">
          <FieldRowVertical>
            <template #label>
              <Label for="id_consumable">{{ consumableCountLabels.id_consumable }}</Label>
            </template>
            <template #field>
              <Select
                v-model="form.id_consumable"
                :invalid="form.errors?.id_consumable != null"
                filter
                :options="consumables"
                option-label="name"
                option-value="id"
                placeholder="Выберите расходный материал"
                class="w-full"
                show-clear
              />
            </template>
          </FieldRowVertical>
        </div>

        <div v-if="step === 1">
          <div v-if="!!consumableData" class="grid grid-cols-none gap-x-6 gap-y-8">
            <Message class="mt-4" severity="info" :closable="false">
              <div class="grid gap-1">
                <div>
                  Найден документ с текущим расходным материалом.
                </div>
                <div>
                  Идентификатор: <strong>{{ consumableData.id }}</strong>, коды организаций:
                  <strong>{{ consumableData?.organizations?.map(item => item.code).join(', ') }}</strong>,
                  текущее количество: <strong>{{ consumableData.count }}</strong>.
                </div>
                <div>
                  Для изменения перечня организаций установите галочку "Изменить список организаций" и укажите нужные организации.
                </div>
              </div>
            </Message>
            <div class="flex" input-id="organizations">
              <Checkbox
                v-model="form.changeOrganization"
                :binary="true"
                input-id="changeOrganization"
              />
              <Label for="changeOrganization" class="ms-2 cursor-pointer font-normal">
                Изменить список организаций
              </Label>
            </div>
          </div>
          <div
            v-if="(consumableData && form.changeOrganization) || !consumableData"
            class="grid grid-cols-none gap-x-6 gap-y-8 mt-6"
          >
            <div>
              <Label for="organizations">
                {{ consumableCountLabels.selectedOrganizations }}
              </Label>
              <div class="w-full" input-id="organizations">
                <div
                  v-for="organization in availableOrganizations"
                  :key="organization.code"
                  class="flex items-center mt-2"
                >
                  <Checkbox
                    v-model="form.selectedOrganizations"
                    :input-id="organization.code"
                    name="organizations"
                    :value="organization.code"
                  />
                  <Label :for="organization.code" class="ml-2 cursor-pointer font-normal">
                    {{ organization.label }}
                  </Label>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div v-if="step === 2">
          <FieldRowVertical>
            <template #label>
              <Label for="count">{{ consumableCountLabels.count }}</Label>
            </template>
            <template #field>
              <InputNumber
                v-model="form.count"
                :placeholder="consumableCountLabels.count"
                :invalid="!!form.errors?.count"
              />
            </template>
          </FieldRowVertical>
        </div>
      </div>

      <Message v-if="Object.keys(form.errors).length > 0" severity="error" class="mt-6">
        <div class="grid gap-y-2">
          <div v-for="error in Object.keys(form.errors)" :key="error">
            {{ form.errors[error] }}
          </div>
        </div>
      </Message>

      <template #footer>
        <div class="flex gap-x-2">
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
            :disabled="disabledNextBtn"
            severity="info"
            :loading="form.processing"
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
            label="Сохранить"
          />
        </div>
      </template>
    </Card>
  </form>
</template>
