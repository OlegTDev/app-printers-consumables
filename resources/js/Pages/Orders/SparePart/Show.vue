<script setup>
import Layout from '@/Shared/Layout.vue';
import { computed, defineAsyncComponent } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import Breadcrumbs from '@/Shared/Breadcrumbs.vue';
import Card from '@/Shared/Card.vue';
import PrinterWorkplace from '@/Shared/DataTable/PrinterWorkplace.vue';
import OrderStatus from '../Shared/OrderStatus.vue';
import Author from '@/Shared/DataTable/Author.vue';
import Button from 'primevue/button';
import OrderStatusHistory from '../Shared/OrderStatusHistory.vue';
import Title from '@/Shared/Title.vue';
import DetailViewer from '@/Shared/DetailViewer.vue';
import { useDate } from '@/Composables/useDate';
import Timestamps from '@/Shared/DataTable/Timestamps.vue';
import { useActions } from '../Composables/useActions';

defineOptions({
  layout: Layout,
});

const props = defineProps({
  auth: Object,
  orderSparePartDetail: Object,
  labels: Object,
  isAuthor: Boolean,
  buttons: Array,
  statuses: Object,
});

const ConfirmDialog = defineAsyncComponent(() => import('../Shared/ConfirmDialog.vue'));
const { formatDate } = useDate();
const orderSparePartDetail = computed(() => props.orderSparePartDetail.data || []);
const orderId = computed(() => orderSparePartDetail.value?.order?.id || null);
const {
  agree,
  reject,
  ordered,
  cancel,
  complete,
  receive,
  remove
} = useActions('spare-parts', ConfirmDialog, orderId, props.labels.order?.comment || '');

const actions = {
  edit: () => router.get(route('orders.spare-parts.edit', { orderSparePartDetails: orderSparePartDetail.value.id })),
  editFiles: () => router.get(route('orders.spare-parts.files.edit', { orderSparePartDetails: orderSparePartDetail.value.id })),
  delete: () => remove(route('orders.destroy', { order: orderId.value })),
  cancel: () => cancel(route('orders.cancel', { order: orderId.value })),
  agree: () => agree(route('orders.agree', { order: orderId.value })),
  reject: () => reject(route('orders.reject', { order: orderId.value })),
  ordered: () => ordered(route('orders.ordered', { order: orderId.value })),
  receive: () => receive(route('orders.receive', { order: orderId.value })),
  complete: () => complete(route('orders.complete', { order: orderId.value })),
};

const title = computed(() => `Заказ № ${orderSparePartDetail.value.order.id} от ${formatDate(orderSparePartDetail.value.order.created_at, 'L')}`);
</script>
<template>
  <Head :title="title" />

  <Breadcrumbs
    :home="{ label: 'Главная', url: route('home') }"
    :items="[
      { label: 'Заказ запчастей', url: route('orders.spare-parts.index') },
      { label: title },
    ]"
  />

  <Card>
    <Title>{{ title }}</Title>

    <DetailViewer
      :items="[
        { label: labels.order_spare_part.id_printers_workplace, keySlot: 'printer' },
        { label: labels.order_spare_part.call_specialist, value: orderSparePartDetail.call_specialist ? 'Да' : 'Нет' },
        { label: labels.order_spare_part.id_spare_part, value: orderSparePartDetail?.sparePart?.name, hide: orderSparePartDetail.call_specialist },
        { label: labels.order.status, keySlot: 'status' },
        { label: labels.order.comment, value: orderSparePartDetail.order.comment },
        { label: labels.order_spare_part.files, keySlot: 'files' },
        { label: labels.order.service_request_number, value: orderSparePartDetail.order.service_request_number },
        { label: labels.order.service_request_date, value: formatDate(orderSparePartDetail.order.service_request_date, 'L') },
        { label: labels.order.status_history, keySlot: 'history' },

        { label: labels.order.requested_by, keySlot: 'author' },
        { label: labels.order.created_at, keySlot: 'date' },
      ]"
    >
      <template #printer>
        <PrinterWorkplace
          :vendor="orderSparePartDetail.printerWorkplace?.printer?.vendor"
          :model="orderSparePartDetail.printerWorkplace?.printer?.model"
          :is-color-print="orderSparePartDetail.printerWorkplace?.printer?.is_color_print"
          :location="orderSparePartDetail.printerWorkplace?.location"
          :inventory-number="orderSparePartDetail.printerWorkplace?.inventory_number"
          :serial-number="orderSparePartDetail.printerWorkplace?.serial_number"
        />
      </template>
      <template #status>
        <OrderStatus :status="orderSparePartDetail.order?.status" :statuses="statuses" />
      </template>
      <template #files>
        <ul v-if="orderSparePartDetail.files" class="space-y-2">
          <li
            v-for="item in orderSparePartDetail.files"
            :key="item.id"
            class="flex items-center"
          >
            <i class="pi pi-file text-gray-400" />
            <a :href="item.url_file_download" target="_blank" class="ms-2">
              {{ item.basename }}
            </a>
          </li>
        </ul>
        <span v-else>Нет файлов</span>
      </template>
      <template #history>
        <OrderStatusHistory :id-order="orderSparePartDetail.order?.id" :statuses="statuses" />
      </template>
      <template #author>
        <Author :user="orderSparePartDetail.order?.requested" />
      </template>
      <template #date>
        <Timestamps :created-at="orderSparePartDetail.order?.created_at" />
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
          <Button v-if="auth.isAdmin || isAuthor" class="font-bold" label="Редактировать файлы" @click="actions.editFiles" />
          <Button v-if="auth.isAdmin || isAuthor" class="font-bold" label="Редактировать" @click="actions.edit" />
          <Button v-if="auth.isAdmin" severity="danger" class="font-bold" label="Удалить" @click="actions.remove" />
        </div>
      </div>
    </template>
  </Card>
</template>
