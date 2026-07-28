<script setup>
import Layout from '@/Shared/Layout.vue';
import { computed, defineAsyncComponent } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import Breadcrumbs from '@/Shared/Breadcrumbs.vue';
import Tag from 'primevue/tag';
import OrderStatusHistory from '../Shared/OrderStatusHistory.vue';
import Author from '@/Shared/DataTable/Author.vue';
import OrderStatus from '../Shared/OrderStatus.vue';
import Button from 'primevue/button';
import { useDate } from '@/Composables/useDate';
import Card from '@/Shared/Card.vue';
import Title from '@/Shared/Title.vue';
import DetailViewer from '@/Shared/DetailViewer.vue';
import Timestamps from '@/Shared/DataTable/Timestamps.vue';
import { useActions } from '../Composables/useActions';
import { useAuth } from '@/Composables/useAuth';

defineOptions({
  layout: Layout,
});

const props = defineProps({
  orderConsumableDetail: Object,
  labels: Object,
  isAuthor: Boolean,
  buttons: Array,
  statuses: Object,
  consumableTypes: Object,
  cartridgeColors: Object,
});

const { formatDate } = useDate();
const title = `Заказ № ${props.orderConsumableDetail?.order?.id || '-'} от ${formatDate(props.orderConsumableDetail?.order?.created_at, 'L')}`;
const orderId = computed(() => props.orderConsumableDetail?.order?.id);
const ConfirmDialog = defineAsyncComponent(() => import('../Shared/ConfirmDialog.vue'));
const {
  agree,
  reject,
  ordered,
  cancel,
  complete,
  receive,
  remove,
} = useActions('consumables', ConfirmDialog, orderId, props.labels.order?.comment || '');
const { isAdmin } = useAuth();

const actions = {
  edit: () => {
    router.get(route('orders.consumables.edit', { orderConsumableDetails: props.orderConsumableDetail?.id }));
  },
  delete: () => remove(route('orders.destroy', { order: orderId.value })),
  cancel: () => cancel(route('orders.cancel', { order: orderId.value })),
  agree: () => agree(route('orders.agree', { order: orderId.value })),
  reject: () => reject(route('orders.reject', { order: orderId.value })),
  ordered: () => ordered(route('orders.ordered', { order: orderId.value })),
  receive: () => receive(route('orders.receive', { order: orderId.value })),
  complete: () => complete(route('orders.complete', { order: orderId.value })),
};
</script>
<template>
  <Head :title="title" />

  <Breadcrumbs
    :home="{ label: 'Главная', url: route('home') }"
    :items="[
      { label: 'Заказ картриджей', url: route('orders.consumables.index') },
      { label: title },
    ]"
  />

  <Card>
    <Title>{{ title }}</Title>

    <DetailViewer
      :items="[
        { label: labels.order_consumable.id_consumable, keySlot: 'detail' },
        { label: labels.order.quantity, keySlot: 'quantity' },
        { label: labels.order.status, keySlot: 'status' },
        { label: labels.order.comment, value: orderConsumableDetail.order.comment },
        { label: labels.order.service_request_number, value: orderConsumableDetail.order.service_request_number },
        {
          label: labels.order.service_request_date,
          value: formatDate(orderConsumableDetail.order.service_request_date, 'L'),
          hide: !orderConsumableDetail.order.service_request_number,
        },
        { label: labels.order.status_history, keySlot: 'history' },
        { label: labels.order.requested_by, keySlot: 'author' },
        { label: labels.order.created_at, keySlot: 'date' },
      ]"
    >
      <template #detail>
        <div class="grid gap-3">
          <div>{{ consumableTypes[orderConsumableDetail.consumable.type] ?? orderConsumableDetail.consumable.type }}</div>
          <div>
            {{ orderConsumableDetail.consumable.name }}
          </div>
          <div v-if="orderConsumableDetail.consumable.type === 'cartridge'">
            <div class="flex">
              <div :class="['rounded-full', 'size-4', 'mr-2', cartridgeColors[orderConsumableDetail.consumable.color]?.bg]" />
              <div>
                {{ cartridgeColors[orderConsumableDetail.consumable.color]['name'] }}
              </div>
            </div>
          </div>
        </div>
      </template>
      <template #quantity>
        <Tag :value="orderConsumableDetail.order.quantity" />
      </template>
      <template #status>
        <OrderStatus :status="orderConsumableDetail.order.status" :statuses="statuses" />
      </template>
      <template #history>
        <OrderStatusHistory :id-order="orderConsumableDetail.order.id" :statuses="statuses" />
      </template>
      <template #author>
        <Author :user="orderConsumableDetail.order.requested" />
      </template>
      <template #date>
        <Timestamps
          :created-at="orderConsumableDetail.order.created_at"
          :updated-at="orderConsumableDetail.order.updated_at"
        />
      </template>
    </DetailViewer>

    <template #footer>
      <div class="flex justify-between w-full">
        <div>
          <div class="flex gap-2">
            <Button v-if="buttons.includes('agreed')" severity="info" class="font-bold" label="Согласовать" @click="actions.agree" />
            <Button v-if="buttons.includes('rejected')" severity="danger" class="font-bold" label="Отказать в согласовании" @click="actions.reject" />
            <Button v-if="buttons.includes('ordered')" severity="info" class="font-bold" label="Заказан" @click="actions.ordered" />
            <Button v-if="buttons.includes('received')" severity="info" class="font-bold" label="Получен" @click="actions.receive" />
            <Button v-if="buttons.includes('completed')" severity="success" class="font-bold" label="Исполнено" @click="actions.complete" />
            <Button v-if="buttons.includes('cancelled')" severity="danger" class="font-bold" label="Отменить" @click="actions.cancel" />
          </div>
        </div>

        <div class="flex gap-2">
          <Button v-if="isAdmin || isAuthor" class="font-bold" label="Редактировать" @click="actions.edit" />
          <Button v-if="isAdmin" severity="danger" class="font-bold" label="Удалить" @click="actions.delete" />
        </div>
      </div>
    </template>
  </Card>
</template>
