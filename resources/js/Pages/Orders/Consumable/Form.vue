<script setup>
import { useForm, router } from '@inertiajs/vue3';
import { onMounted, ref, watch } from 'vue';
import Button from 'primevue/button';
import Label from '@/Shared/Label.vue';
import consumablesService from '@/Services/consumablesService';
import InputNumber from 'primevue/inputnumber';
import Textarea from 'primevue/textarea';
import { useConfig } from '@/Composables/useConfig';
import { useNotification } from '@/Composables/useNotification';
import Card from '@/Shared/Card.vue';
import Title from '@/Shared/Title.vue';
import FieldRowVertical from '@/Shared/Form/FieldRowVertical.vue';
import Select from 'primevue/select';
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
  orderConsumable: {
    type: Object,
    default: () => {
      return {
        id: null,
        id_consumable: null,
        quantity: 1,
        consumable_type_id: null,
        color_id: null,
        order: {},
      };
    },
  },
  consumableTypes: {
    type: Object,
    required: true,
  },
  cartridgeColors: {
    type: Object,
    required: true,
  },
});
const { urls } = useConfig();
const { showError } = useNotification();

const form = useForm({
  id: props.orderConsumable?.id,
  id_consumable: props.orderConsumable.id_consumable,
  quantity: props.orderConsumable.quantity,
  comment: props.orderConsumable.order?.comment,
  service_request_number: props.orderConsumable.order?.service_request_number,
  service_request_date: props.orderConsumable.order?.service_request_date,
  is_new: props.isNew,
});

const consumablesData = ref([]);
const consumableSelected = ref({});
const loadingConsumables = ref(false);

onMounted(async () => {
  try {
    loadingConsumables.value = true;
    consumablesData.value = await consumablesService.fetch(urls.dictionary.consumables.notOther());
    consumableSelected.value = consumablesData.value.find((item) => item.id == form.id_consumable);
  } catch (error) {
    showError(error.message);
  } finally {
    loadingConsumables.value = false;
  }
});

const onConsumableChange = (event) => {
  form.id_consumable = event.value?.id ?? null;
};

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
            <Label for="id_consumable">{{ labels.id_consumable }}</Label>
          </template>
          <template #field>
            <Select
              v-model="consumableSelected"
              filter
              show-clear
              :options="consumablesData"
              option-label="label"
              placeholder="Выберите расходный материал"
              :loading="loadingConsumables"
              @change="onConsumableChange"
            >
              <template #value="{ value, placeholder }">
                <div v-if="value" class="grid gap-y-2">
                  <div class="grid grid-rows-2 gap-2">
                    <div>{{ consumableTypes[value.type] ?? value.type }}</div>
                    <div>
                      {{ value.name }}
                    </div>
                    <div v-if="value.type === 'cartridge'">
                      <div class="flex">
                        <div :class="['rounded-full', 'size-4', 'mr-2', cartridgeColors[value.color]?.bg]" />
                        <div>
                          {{ cartridgeColors[value.color]?.name }}
                        </div>
                      </div>
                    </div>
                    <div class="text-gray-500">
                      {{ value.description }}
                    </div>
                  </div>
                </div>
                <span v-else>
                  {{ placeholder }}
                </span>
              </template>
              <template #option="{ option }">
                <div v-if="option" class="grid gap-y-2">
                  <div class="grid grid-rows-2 gap-2">
                    <div>{{ consumableTypes[option.type] ?? option.type }}</div>
                    <div>
                      {{ option.name }}
                    </div>
                    <div v-if="option.type === 'cartridge'">
                      <div class="flex">
                        <div :class="['rounded-full', 'size-4', 'mr-2', cartridgeColors[option.color]?.bg]" />
                        <div>
                          {{ cartridgeColors[option.color]['name'] }}
                        </div>
                      </div>
                    </div>
                    <div class="text-gray-500">
                      {{ option.description }}
                    </div>
                  </div>
                </div>
              </template>
              <template #empty>
                Нет доступных расходных материалов
              </template>
            </Select>
          </template>
          <template #message>
            <Message v-if="form.errors?.id_consumable" class="mt-2" severity="error">
              {{ form.errors?.id_consumable }}
            </Message>
          </template>
        </FieldRowVertical>
        <FieldRowVertical>
          <template #label>
            <Label for="quantity">{{ labels.quantity }}</Label>
          </template>
          <template #field>
            <InputNumber v-model="form.quantity" input-id="quantity" placeholder="Введите количество" show-buttons />
          </template>
          <template #message>
            <Message v-if="form.errors?.quantity" class="mt-2" severity="error">
              {{ form.errors?.quantity }}
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
