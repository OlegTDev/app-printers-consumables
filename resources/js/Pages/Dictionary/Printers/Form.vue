<script setup>
import InputText from 'primevue/inputtext';
import Label from '@/Shared/Label.vue';
import { useForm, router } from '@inertiajs/vue3';
import Button from 'primevue/button';
import { useConfirm } from "primevue/useconfirm";
import Card from '@/Shared/Card.vue';
import Title from '@/Shared/Title.vue';
import FieldRowVertical from '@/Shared/Form/FieldRowVertical.vue';
import Select from 'primevue/select';
import ToggleSwitch from 'primevue/toggleswitch';
import Message from 'primevue/message';
import { watch } from 'vue';

const props = defineProps({
  isNew: {
    type: Boolean,
    default: false,
  },
  labels: {
    type: Object,
  },
  printer: {
    type: Object,
    default: () => ({
      vendor: null,
      name: null,
      is_color_print: false,
    }),
  },
  manufacturers: {
    type: Array,
    default: () => [],
  },
  title: {
    type: String,
    default: '',
  },
});

const confirm = useConfirm();

const form = useForm({
  vendor: props.printer?.vendor,
  model: props.printer?.model,
  is_color_print: props.printer?.is_color_print,
});

const save = () => {
  if (props.isNew) {
    const url = route('dictionary.printers.index');
    form.post(url);
  }
  else {
    const url = route('dictionary.printers.update', { printer: props.printer.id });
    form.put(url);
  }
};

const destroy = () => {
  confirm.require({
    message: 'Вы уверены, что хотите удалить?',
    header: 'Удаление',
    accept: () => {
      const url = route('dictionary.printers.destroy', { printer: props.printer.id });
      router.delete(url);
    },
  });
};

watch(
  () => form.data(),
  (newValues, oldValues) => {
    Object.keys(newValues).forEach(key => {
      if (newValues[key] !== oldValues[key] && form.errors[key]) {
        form.errors[key] = null;
      }
    });
  },
  { deep: true }
);
</script>
<template>
  <form @submit.prevent="save">
    <Card>
      <Title>{{ title }}</Title>

      <div class="w-1/2 grid gap-y-10">
        <FieldRowVertical>
          <template #label>
            <Label for="vendor">{{ labels.vendor }}</Label>
          </template>
          <template #field>
            <Select
              v-model="form.vendor"
              :options="manufacturers"
              option-label="label"
              option-value="value"
              :placeholder="labels.vendor"
              :invalid="!!form.errors?.vendor"
            />
          </template>
          <template #message>
            <Message v-if="form.errors?.vendor" class="mt-2" severity="error">
              {{ form.errors?.vendor }}
            </Message>
          </template>
        </FieldRowVertical>
        <FieldRowVertical>
          <template #label>
            <Label for="model">{{ labels.model }}</Label>
          </template>
          <template #field>
            <InputText
              v-model="form.model"
              :placeholder="labels.model"
              :invalid="!!form.errors?.model"
            />
          </template>
          <template #message>
            <Message v-if="form.errors?.model" class="mt-2" severity="error">
              {{ form.errors?.model }}
            </Message>
          </template>
        </FieldRowVertical>
        <FieldRowVertical>
          <template #label>
            <Label for="is_color_print">{{ labels.is_color_print }}</Label>
          </template>
          <template #field>
            <ToggleSwitch v-model="form.is_color_print" />
          </template>
          <template #message>
            <Message v-if="form.errors?.is_color_print" class="mt-2" severity="error">
              {{ form.errors?.is_color_print }}
            </Message>
          </template>
        </FieldRowVertical>
      </div>

      <template #footer>
        <div class="grid grid-cols-2 gap-x-2">
          <Button
            :loading="form.processing"
            class="font-bold"
            type="submit"
            label="Сохранить"
          />
          <Button v-if="!props.isNew" severity="danger" class="font-bold" type="button" @click="destroy">
            Удалить
          </Button>
        </div>
      </template>
    </Card>
  </form>
</template>
