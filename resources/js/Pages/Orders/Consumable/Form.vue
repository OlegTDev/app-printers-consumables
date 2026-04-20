<script setup>
import { useForm, router } from '@inertiajs/vue3';
import { inject, onMounted, reactive, ref } from 'vue';
import Button from 'primevue/button';
import Label from '@/Shared/Label.vue';
import Dropdown from 'primevue/dropdown';
import { useToast } from 'primevue/usetoast';
import consumablesService from '@/Services/consumablesService';
import InputNumber from 'primevue/inputnumber';
import Textarea from 'primevue/textarea';
import InlineMessage from 'primevue/inlinemessage';

const props = defineProps({
  isNew: Boolean,
  labels: Object,
  orderConsumable: Object,
  consumableTypes: Object,
  cartridgeColors: Object,
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
}

</script>
<template>
  <form @submit.prevent="save" class="w-full">
    <div class="p-10">

      <Label for="id_consumable">{{ labels.id_consumable }}</Label>
      <Dropdown v-model="consumableSelected" filter showClear :options="consumablesData" optionLabel="label"
        placeholder="Выберите расходный материал" class="w-full" @change="onConsumableChange"
        :loading="loadingConsumables">
        <template #value="{ value, placeholder }">
          <div v-if="value" class="grid gap-y-2">
            <div class="grid grid-rows-2 gap-2">
              <div>{{ consumableTypes[value.type] ?? value.type }}</div>
              <div>
                {{ value.name }}
              </div>
              <div v-if="value.type === 'cartridge'">
                <div class="flex">
                  <div :class="['rounded-full', 'size-4', 'mr-2', cartridgeColors[value.color]['bg']]"></div>
                  <div>
                    {{ cartridgeColors[value.color]['name'] }}
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
                  <div :class="['rounded-full', 'size-4', 'mr-2', cartridgeColors[option.color]['bg']]"></div>
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
      </Dropdown>
      <div>
        <InlineMessage v-if="form.errors?.id_consumable" class="mt-2" severity="error">{{ form.errors?.id_consumable }}
        </InlineMessage>
      </div>
    </div>
    <div class="p-10">
      <Label for="quantity">{{ labels.quantity }}</Label>
      <InputNumber v-model="form.quantity" placeholder="Введите количество" showButtons id="quantity" />
      <div>
        <InlineMessage v-if="form.errors?.quantity" class="mt-2" severity="error">{{ form.errors?.quantity }}
        </InlineMessage>
      </div>
    </div>

    <div class="p-10">
      <Label for="comment">{{ labels.order.comment }}</Label>
      <Textarea v-model="form.comment" class="w-full" rows="5" />
    </div>

    <div class="p-5 bg-gray-50 border-t border-gray-100 w-full">
      <div class="flex justify-between w-full">
        <div class="flex gap-2">
          <Button type="submit" :loading="form.processing" icon="pi pi-save"
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
