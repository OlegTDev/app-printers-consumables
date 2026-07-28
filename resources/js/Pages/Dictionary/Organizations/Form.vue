<script setup>
import InputText from 'primevue/inputtext';
import Label from '@/Shared/Label.vue';
import { useForm, router } from '@inertiajs/vue3';
import Button from 'primevue/button';
import { useConfirm } from "primevue/useconfirm";
import Card from '@/Shared/Card.vue';
import Title from '@/Shared/Title.vue';
import FieldRowVertical from '@/Shared/Form/FieldRowVertical.vue';
import Message from 'primevue/message';
import { computed } from 'vue';

const props = defineProps({
  isNew: {
    type: Boolean,
    default: false,
  },
  labels: {
    type: Object,
    required: true,
  },
  organization: {
    type: Object,
    default: () => ({
      code: null,
      name: null,
    }),
  },
  title: {
    type: String,
    default: '',
  },
});

const confirm = useConfirm();

const form = useForm({
  code: props.organization.code,
  name: props.organization.name,
  parent: props.organization.parent,
});

const save = () => {
  if (props.isNew) {
    const url = route('dictionary.organizations.store');
    form.post(url);
  }
  else {
    const url = route('dictionary.organizations.update', { organization: props.organization.code });
    form.put(url);
  }
};

const errorText = computed(() => !props.isNew && !props.organization.code ? 'Ошибка! Не передан код организации!' : null);

const destroy = () => {
  confirm.require({
    message: 'Вы уверены, что хотите удалить?',
    header: 'Удаление',
    accept: () => {
      const url = route('dictionary.organizations.destroy', { organization: props.organization.code });
      router.delete(url);
    },
  });
};
</script>
<template>
  <form @submit.prevent="save">
    <Card>
      <Title>{{ title }}</Title>

      <div class="w-1/2 grid gap-y-10">
        <Message v-if="errorText" severity="error" class="mt-2">
          {{ errorText }}
        </Message>

        <FieldRowVertical>
          <template #label>
            <Label for="code">{{ labels.code }}</Label>
          </template>
          <template #field>
            <InputText
              v-model="form.code"
              :placeholder="labels.code"
              :invalid="form.errors?.code?.length > 0"
            />
          </template>
          <template #message>
            <Message v-if="form.errors?.code" class="mt-2" severity="error">
              {{ form.errors?.code }}
            </Message>
          </template>
        </FieldRowVertical>
        <FieldRowVertical>
          <template #label>
            <Label for="parent">{{ labels.parent }}</Label>
          </template>
          <template #field>
            <InputText
              v-model="form.parent"
              :placeholder="labels.parent"
              :invalid="form.errors?.parent?.length > 0"
            />
          </template>
          <template #message>
            <Message v-if="form.errors?.parent" class="mt-2" severity="error">
              {{ form.errors?.parent }}
            </Message>
          </template>
        </FieldRowVertical>
        <FieldRowVertical>
          <template #label>
            <Label for="name">{{ labels.name }}</Label>
          </template>
          <template #field>
            <InputText
              v-model="form.name"
              :placeholder="labels.name"
              :invalid="form.errors?.name?.length > 0"
            />
          </template>
          <template #message>
            <Message v-if="form.errors?.name" class="mt-2" severity="error">
              {{ form.errors?.name }}
            </Message>
          </template>
        </FieldRowVertical>
      </div>

      <template #footer>
        <Button :loading="form.processing" class="font-bold" type="submit" label="Сохранить" :disabled="errorText" />
        <Button
          v-if="!isNew"
          severity="danger"
          class="font-bold"
          type="button"
          :disabled="errorText"
          @click="destroy"
        >
          Удалить
        </Button>
      </template>
    </Card>
  </form>
</template>
