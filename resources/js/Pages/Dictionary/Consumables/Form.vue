<script setup>
import { useForm } from '@inertiajs/vue3';
import Layout from '@/Shared/Layout';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import { computed, watch } from 'vue';
import Label from '@/Shared/Label';
import Textarea from 'primevue/textarea';
import { useConfig } from '@/Composables/useConfig';
import Select from 'primevue/select';
import Card from '@/Shared/Card.vue';
import Title from '@/Shared/Title.vue';
import FieldRowVertical from '@/Shared/Form/FieldRowVertical.vue';
import Message from 'primevue/message';

defineOptions({
  layout: Layout,
});

const props = defineProps({
  isNew: {
    type: Boolean,
    default: false,
  },
  consumable: {
    type: Object,
    default: () => ({
      type: null,
      name: null,
      color: null,
      description: null,
    }),
  },
  title: {
    type: String,
    default: '',
  },
  consumableTypes: {
    type: Object,
    default: () => ({}),
  },
  labels: {
    type: Object,
    required: true,
  },
  cartridgeColors: {
    type: Object,
    required: true,
  },
});

const { urls } = useConfig();
const form = useForm({
  type: props.consumable.type,
  name: props.consumable.name,
  color: props.consumable.color,
  description: props.consumable.description,
});

const consumableTypes = computed(() =>
  Object.keys(props.consumableTypes).map((key) => ({
    code: key,
    name: props.consumableTypes[key],
  }))
);

const colors = computed(() =>
  Object.keys(props.cartridgeColors).map((key) => ({
    code: key,
    name: props.cartridgeColors[key]['name'],
    color: props.cartridgeColors[key]['color'],
  }))
);

const save = () => {
  if (props.isNew) {
    form.post(urls.dictionary.consumables.store());
  } else {
    form.put(urls.dictionary.consumables.update(props.consumable.id));
  }
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

watch(
  () => form.type,
  (newType) => {
    if (newType !== 'cartridge') {
      form.color = null;
    }
  }
);

</script>
<template>
  <form @submit.prevent="save">
    <Card>
      <Title>{{ title }}</Title>

      <div class="w-1/2 grid gap-y-10">
        <FieldRowVertical>
          <template #label>
            <Label for="type">{{ labels.type }}</Label>
          </template>
          <template #field>
            <Select
              id="type"
              v-model="form.type"
              :options="consumableTypes"
              option-value="code"
              option-label="name"
              :placeholder="labels.type"
              :invalid="form.errors?.type?.length > 0"
              show-clear
            />
          </template>
          <template #message>
            <Message v-if="form.errors?.type" class="mt-2" severity="error">
              {{ form.errors?.type }}
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
        <FieldRowVertical v-if="form.type == 'cartridge'">
          <template #label>
            <Label for="color">{{ labels.color }}</Label>
          </template>
          <template #field>
            <Select
              v-model="form.color"
              :options="colors"
              :invalid="form.errors?.color?.length > 0"
              option-value="code"
              option-label="name"
              :placeholder="labels.color"
              show-clear
            >
              <template #value="slotProps">
                <div v-if="slotProps.value" class="flex items-center">
                  <div
                    class="rounded-full size-4 mr-2"
                    :class="cartridgeColors[slotProps.value]?.bg"
                  />
                  <div>{{ cartridgeColors[slotProps.value]?.name }}</div>
                </div>
                <span v-else>
                  {{ slotProps.placeholder }}
                </span>
              </template>
              <template #option="slotProps">
                <div class="flex items-center">
                  <div
                    class="rounded-full size-4 mr-2"
                    :class="cartridgeColors[slotProps.option.code]?.bg"
                  />
                  <div>{{ slotProps.option.name }}</div>
                </div>
              </template>
            </Select>
          </template>
          <template #message>
            <Message v-if="form.errors?.color" class="mt-2" severity="error">
              {{ form.errors?.color }}
            </Message>
          </template>
        </FieldRowVertical>
        <FieldRowVertical>
          <template #label>
            <Label for="name">{{ labels.description }}</Label>
          </template>
          <template #field>
            <Textarea
              v-model="form.description"
              rows="5"
              :placeholder="labels.description"
              :invalid="form.errors?.description?.length > 0"
            />
          </template>
          <template #message>
            <Message v-if="form.errors?.description" class="mt-2" severity="error">
              {{ form.errors?.description }}
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
        </div>
      </template>
    </Card>
  </form>
</template>
