<script setup>
import Layout from '@/Shared/Layout.vue';
import { Head, router } from '@inertiajs/vue3';
import { computed, defineAsyncComponent } from 'vue';
import Breadcrumbs from '@/Shared/Breadcrumbs.vue';
import Card from '@/Shared/Card.vue';
import OrderStatus from '../Shared/OrderStatus.vue';
import Author from '@/Shared/DataTable/Author.vue';
import Button from 'primevue/button';
import OrderStatusHistory from '../Shared/OrderStatusHistory.vue';
import { useDate } from '@/Composables/useDate';
import Title from '@/Shared/Title.vue';
import DetailViewer from '@/Shared/DetailViewer.vue';
import Timestamps from '@/Shared/DataTable/Timestamps.vue';
import { useActions } from '../Composables/useActions';

defineOptions({
  layout: Layout,
});

const props = defineProps({
  auth: Object,
  orderMiscDetail: Object,
  labels: Object,
  isAuthor: Boolean,
  buttons: Array,
  statuses: Object,
});

const { formatDate } = useDate();

const orderId = computed(() => props.orderMiscDetail.order.id);
const title = `Заказ № ${props.orderMiscDetail.order.id} от ${formatDate(props.orderMiscDetail.order.created_at, 'L')}`;
const ConfirmDialog = defineAsyncComponent(() => import('../Shared/ConfirmDialog.vue'));
const {
  agree,
  reject,
  ordered,
  cancel,
  complete,
  receive,
  remove,
} = useActions('misc', ConfirmDialog, orderId, props.labels.order?.comment || '');

const actions = {
  edit: () => router.get(route('orders.misc.edit', { orderMiscDetails: props.orderMiscDetail.id })),
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
      { label: 'Заказ мелочей', url: route('orders.misc.index') },
      { label: title },
    ]"
  />

  <Card>
    <Title>{{ title }}</Title>

    <DetailViewer
      :items="[
        { label: labels.name, value: orderMiscDetail.name },
        { label: labels.description, value: orderMiscDetail.description },
        { label: labels.order.status, keySlot: 'status' },
        { label: labels.order.comment, value: orderMiscDetail.order.comment },
        { label: labels.order.status_history, keySlot: 'history' },
        { label: labels.order.requested_by, keySlot: 'author' },
        { label: labels.order.created_at, keySlot: 'date' },
      ]"
    >
      <template #status>
        <OrderStatus :status="orderMiscDetail.order.status" :statuses="statuses" />
      </template>
      <template #history>
        <OrderStatusHistory :id-order="orderMiscDetail.order.id" :statuses="statuses" />
      </template>
      <template #author>
        <Author :user="orderMiscDetail.order?.requested || {}" />
      </template>
      <template #date>
        <Timestamps :created-at="orderMiscDetail.order.created_at" />
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
          <Button v-if="auth.isAdmin || isAuthor" class="font-bold" label="Редактировать" @click="actions.edit" />
          <Button v-if="auth.isAdmin" severity="danger" class="font-bold" label="Удалить" @click="actions.delete" />
        </div>
      </div>
    </template>
  </Card>
</template>
