<script setup>
import Layout from '@/Shared/Layout.vue';
import { useConfirm } from 'primevue/useconfirm';
import { useDialog } from 'primevue/usedialog';
import { computed, defineAsyncComponent } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import Breadcrumbs from '@/Shared/Breadcrumbs.vue';
import Tag from 'primevue/tag';
import OrderStatusHistory from '../Shared/OrderStatusHistory.vue';
import Author from '@/Shared/DataTable/Author.vue';
import OrderStatus from '../Shared/OrderStatus.vue';
import Button from 'primevue/button';
import { createUrlWithParams } from '@/config/urls';
import { useConfig } from '@/Composables/useConfig';
import { useDate } from '@/Composables/useDate';
import Card from '@/Shared/Card.vue';
import Title from '@/Shared/Title.vue';
import DetailViewer from '@/Shared/DetailViewer.vue';
import Timestamps from '@/Shared/DataTable/Timestamps.vue';

defineOptions({
  layout: Layout,
});

const { urls } = useConfig();
const dialog = useDialog();
const confirm = useConfirm();
const { formatDate } = useDate();

const props = defineProps({
  auth: Object,
  orderConsumableDetail: Object,
  labels: Object,
  isAuthor: Boolean,
  buttons: Array,
  statuses: Object,
  consumableTypes: Object,
  cartridgeColors: Object,
});

const orderConsumableDetail = computed(() => props.orderConsumableDetail?.data || []);
const orderId = orderConsumableDetail.value.order?.id;

const ConfirmDialog = defineAsyncComponent(() => import('../Shared/ConfirmDialog.vue'));

const title = `Заказ № ${orderConsumableDetail.value.order.id} от ${formatDate(orderConsumableDetail.value?.order?.created_at, 'L')}`;

const openConfirmDialog = (params) => {
  const {
    url,
    idOrder,
    message,
    header,
    buttonLabel,
    btnSeverity = null,
    context = {},
    width = '50vw',
    breakpoints = {
      '960px': '75vw',
      '640px': '90vw'
    },
  } = params ?? {};
  dialog.open(ConfirmDialog, {
      props: {
        header,
        style: {
          width,
        },
        breakpoints,
        modal: true,
      },
      data: {
        idOrder,
        message,
        url: createUrlWithParams(url, context),
        buttonLabel,
        btnSeverity,
      }
    });
};

const actions = {
  edit: () => {
    router.get(urls.orders.consumables.edit(orderConsumableDetail.value.id));
  },
  delete: () => {
    confirm.require({
      message: 'Вы уверены, что хотите удалить заказ?',
      header: 'Удаление заказа',
      accept: () => {
        const url = createUrlWithParams(urls.orders.delete(orderId), { context: 'consumables' });
        router.delete(url);
      },
    });
  },
  cancel: () => {
    confirm.require({
      message: 'Вы уверены, что хотите отменить заказ?',
      header: 'Отмена заказа',
      accept: () => {
        const url = createUrlWithParams(urls.orders.cancel(orderId), { context: 'consumables' });
        router.put(url);
      },
    });
  },
  agree: () => {
    openConfirmDialog({
      url: urls.orders.agree(orderId),
      context: { context: 'consumables' },
      idOrder: orderId,
      message: props.labels.order.comment,
      header: 'Согласование',
      buttonLabel: 'Согласовать',
    });
  },
  reject: () => {
    openConfirmDialog({
      url: urls.orders.reject(orderId),
      context: { context: 'consumables' },
      idOrder: orderId,
      message: props.labels.order.comment,
      header: 'Отказать в согласовании',
      buttonLabel: 'Отказать',
      btnSeverity: 'danger',
    });
  },
  ordered: () => {
    openConfirmDialog({
      url: urls.orders.ordered(orderId),
      context: { context: 'consumables' },
      idOrder: orderId,
      message: props.labels.order.comment,
      header: 'Заказан',
      buttonLabel: 'Перевести в состояние "Заказан"',
    });
  },
  receive: () => {
    openConfirmDialog({
      url: urls.orders.receive(orderId),
      context: { context: 'consumables' },
      idOrder: orderId,
      message: props.labels.order.comment,
      header: 'Получен',
      buttonLabel: 'Перевести в состояние "Получен"',
    });
  },
  complete: () => {
    openConfirmDialog({
      url: urls.orders.complete(orderId),
      context: { context: 'consumables' },
      idOrder: orderId,
      message: props.labels.order.comment,
      header: 'Исполнено',
      buttonLabel: 'Исполнено',
    });
  },
};
</script>
<template>
  <Head :title="title" />

  <Breadcrumbs
    :home="{ label: 'Главная', url: '/' }"
    :items="[
      { label: 'Заказ картриджей', url: urls.orders.consumables.index() },
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
        <Author
          :user="{
            fio: orderConsumableDetail.order.requested.fio,
            name: orderConsumableDetail.order.requested.name,
            post: orderConsumableDetail.order.requested.post,
            department: orderConsumableDetail.order.requested.department,
          }"
        />
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
          <Button v-if="auth.isAdmin || isAuthor" class="font-bold" label="Редактировать" @click="actions.edit" />
          <Button v-if="auth.isAdmin" severity="danger" class="font-bold" label="Удалить" @click="actions.delete" />
        </div>
      </div>
    </template>
  </Card>
</template>
