<script setup>
import Layout from '@/Shared/Layout.vue';
import { Head, router } from '@inertiajs/vue3';
import { computed, defineAsyncComponent } from 'vue';
import Breadcrumbs from '@/Shared/Breadcrumbs.vue';
import Card from '@/Shared/Card.vue';
import OrderStatus from '../Shared/OrderStatus.vue';
import Author from '@/Shared/DataTable/Author.vue';
import Button from 'primevue/button';
import { useDialog } from 'primevue/usedialog';
import OrderStatusHistory from '../Shared/OrderStatusHistory.vue';
import { useConfirm } from 'primevue/useconfirm';
import { createUrlWithParams } from '@/config/urls';
import { useConfig } from '@/Composables/useConfig';
import { useDate } from '@/Composables/useDate';
import Title from '@/Shared/Title.vue';
import DetailViewer from '@/Shared/DetailViewer.vue';
import Timestamps from '@/Shared/DataTable/Timestamps.vue';

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

const { urls } = useConfig();
const dialog = useDialog();
const confirm = useConfirm();
const { formatDate } = useDate();

const ConfirmDialog = defineAsyncComponent(() => import('../Shared/ConfirmDialog.vue'));

const orderMiscDetail = computed(() => props.orderMiscDetail?.data || []);
const orderId = computed(() => orderMiscDetail.value.order.id);

const title = `Заказ № ${orderMiscDetail.value.order.id} от ${formatDate(orderMiscDetail.value.order.created_at, 'L')}`;

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
    router.get(urls.orders.misc.edit(orderMiscDetail.value.id));
  },
  editFiles: () => {
    router.get(urls.orders.misc.editFiles(orderMiscDetail.value.id));
  },
  delete: () => {
    confirm.require({
      message: 'Вы уверены, что хотите удалить заказ?',
      header: 'Удаление заказа',
      accept: () => {
        const url = createUrlWithParams(urls.orders.delete(orderId.value), { context: 'misc' });
        router.delete(url);
      },
    });
  },
  cancel: () => {
    confirm.require({
      message: 'Вы уверены, что хотите отменить заказ?',
      header: 'Отмена заказа',
      accept: () => {
        const url = createUrlWithParams(urls.orders.cancel(orderId.value), { context: 'misc' });
        router.put(url);
      },
    });
  },
  agree: () => {
    openConfirmDialog({
      url: urls.orders.agree(orderId.value),
      context: { context: 'misc' },
      idOrder: orderId.value,
      message: props.labels.order.comment,
      header: 'Согласование',
      buttonLabel: 'Согласовать',
    });
  },
  reject: () => {
    openConfirmDialog({
      url: urls.orders.reject(orderId.value),
      context: { context: 'misc' },
      idOrder: orderId.value,
      message: props.labels.order.comment,
      header: 'Отказать в согласовании',
      buttonLabel: 'Отказать',
      btnSeverity: 'danger',
    });
  },
  ordered: () => {
    openConfirmDialog({
      url: urls.orders.ordered(orderId.value),
      context: { context: 'misc' },
      idOrder: orderId.value,
      message: props.labels.order.comment,
      header: 'Заказан',
      buttonLabel: 'Перевести в состояние "Заказан"',
    });
  },
  receive: () => {
    openConfirmDialog({
      url: urls.orders.receive(orderId.value),
      context: { context: 'misc' },
      idOrder: orderId.value,
      message: props.labels.order.comment,
      header: 'Получен',
      buttonLabel: 'Перевести в состояние "Получен"',
    });
  },
  complete: () => {
    openConfirmDialog({
      url: urls.orders.complete(orderId.value),
      context: { context: 'misc' },
      idOrder: orderId.value,
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
      { label: 'Заказ мелочей', url: urls.orders.misc.index() },
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
        <Author :user="orderMiscDetail.order.requested" />
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
