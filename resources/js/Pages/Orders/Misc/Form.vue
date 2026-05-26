<script setup>
import { useForm, router } from '@inertiajs/vue3';
import { watch } from 'vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Label from '@/Shared/Label.vue';
import Card from '@/Shared/Card.vue';
import Title from '@/Shared/Title.vue';
import FieldRowVertical from '@/Shared/Form/FieldRowVertical.vue';
import Message from 'primevue/message';

const props = defineProps({
  title: {
    type: String,
    default: '',
  },
  isNew: {
    type: Boolean,
    default: false,
  },
  labels: {
    type: Object,
    required: true,
  },
  orderMisc: {
    type: Object,
    default: () => ({
      id: null,
      name: null,
      description: null,
      comment: null,
      order: {},
    }),
  },
});

const form = useForm({
  id: props.orderMisc?.id,
  name: props.orderMisc?.name,
  description: props.orderMisc?.description,
  comment: props.orderMisc?.order.comment,
  is_new: props.isNew,
});

const save = () => {
  if (props.isNew) {
    form.post(route('orders.misc.store'));
  } else {
    form.put(route('orders.misc.update', { orderMiscDetails: form.id }));
  }
};

const home = () => {
  const url = props.isNew ? route('orders.misc.index')
    : route('orders.misc.show', { orderMiscDetails: form.id });
  router.get(url);
};

watch(
  () => form.data(),
  (newValues, oldValues) => {
    Object.keys(newValues).forEach(key => {
      if (newValues[key] !== oldValues[key] && form.errors[key]) {
        form.clearErrors(key);
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
            <Label for="name">{{ labels.name }}</Label>
          </template>
          <template #field>
            <InputText v-model="form.name" />
          </template>
          <template #message>
            <Message v-if="form.errors?.name" class="mt-2" severity="error">
              {{ form.errors?.name }}
            </Message>
          </template>
        </FieldRowVertical>
        <FieldRowVertical>
          <template #label>
            <Label for="description">{{ labels.description }}</Label>
          </template>
          <template #field>
            <Textarea v-model="form.description" rows="5" />
          </template>
          <template #message>
            <Message v-if="form.errors?.description" class="mt-2" severity="error">
              {{ form.errors?.description }}
            </Message>
          </template>
        </FieldRowVertical>
        <FieldRowVertical>
          <template #label>
            <Label for="comment">{{ labels.order.comment }}</Label>
          </template>
          <template #field>
            <Textarea v-model="form.comment" class="w-full" rows="5" />
          </template>
        </FieldRowVertical>
      </div>

      <template #footer>
        <div class="flex justify-between w-full">
          <div class="flex gap-2">
            <Button
              type="submit"
              :loading="form.processing"
              icon="pi pi-save"
              :label="isNew ? 'Заказать' : 'Сохранить'"
            />
          </div>
          <div>
            <Button icon="pi pi-id-card" label="Вернуться" type="button" @click="home" />
          </div>
        </div>
      </template>
    </Card>
  </form>
</template>
