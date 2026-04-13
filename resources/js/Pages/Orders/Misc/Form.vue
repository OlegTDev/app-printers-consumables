<script setup>
import { useForm } from '@inertiajs/inertia-vue3';
import { computed, inject, reactive, ref } from 'vue';
import Button from 'primevue/button';
import { Inertia } from '@inertiajs/inertia';
import Message from 'primevue/message';
import { useToast } from 'primevue/usetoast';
import Dropdown from 'primevue/dropdown';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import InlineMessage from 'primevue/inlinemessage';
import Label from '@/Shared/Label.vue';


const props = defineProps({
  isNew: Boolean,
  labels: Object,
  orderMisc: Object,
});
const urls = inject('urls');
const config = inject('config');
const toast = reactive(useToast());

const form = useForm({
  id: props.orderMisc?.id,
  name: props.orderMisc?.name,
  description: props.orderMisc?.description,
  comment: props.orderMisc?.order.comment,
  is_new: props.isNew,
});


const save = () => {
  if (props.isNew) {
    form.post(urls.orders.misc.store());
  } else {
    form.put(urls.orders.misc.update(form.id));
  }
};

const home = () => {
  const url = props.isNew ? urls.orders.misc.index()
    : urls.orders.misc.show(form.id);
  Inertia.get(url);
}

</script>
<template>
  <form @submit.prevent="save" class="w-full">
    <div class="p-10">
      <Label for="name">{{ labels.name }}</Label>
      <InputText v-model="form.name" class="w-full" />
      <div>
        <InlineMessage v-if="form.errors?.name" class="mt-2" severity="error">{{ form.errors?.name }}
        </InlineMessage>
      </div>
    </div>
    <div class="p-10">
      <Label for="description">{{ labels.description }}</Label>
      <Textarea v-model="form.description" class="w-full" rows="5" />
      <div>
        <InlineMessage v-if="form.errors?.description" class="mt-2" severity="error">{{ form.errors?.description }}
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
