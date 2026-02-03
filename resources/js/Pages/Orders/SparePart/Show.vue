<script setup>
import Layout from '@/Shared/Layout';
import { defineAsyncComponent, inject } from 'vue';
import { Head } from '@inertiajs/vue3';
import Breadcrumbs from '@/Shared/Breadcrumbs';
import Card from 'primevue/card';
import PrinterWorkplace from '@/Shared/DataTable/PrinterWorkplace';
import OrderStatus from '../Shared/OrderStatus';
import Author from '@/Shared/DataTable/Author';
import Button from 'primevue/button';
import { Inertia } from '@inertiajs/inertia';
import { useDialog } from 'primevue/usedialog';
import OrderStatusHistory from '../Shared/OrderStatusHistory.vue';
import { useConfirm } from 'primevue/useconfirm';
import { createUrlWithParams } from '@/config/urls';

defineOptions({
  layout: Layout,
});

const urls = inject('urls');
const moment = inject('moment');
const auth = inject('auth');
const dialog = useDialog();
const confirm = useConfirm();

const props = defineProps({
  orderSparePartDetail: Object,
  labels: Object,
  orderStatusPending: String,
  orderStatusInProgress: String,
  orderStatusCancelled: String,
  isAuthor: Boolean,
});

const ConfirmDialog = defineAsyncComponent(() => import('../Shared/ConfirmDialog.vue'));

const orderSparePartDetail = props.orderSparePartDetail.data;

const title = `Заказ № ${orderSparePartDetail.order.id} от ${moment(orderSparePartDetail.order.created_at).format('L')}`

const actions = {
  edit: () => {
    Inertia.get(urls.orders.spareParts.edit(orderSparePartDetail.id));
  },
  editFiles: () => {
    Inertia.get(urls.orders.spareParts.editFiles(orderSparePartDetail.id));
  },
  delete: () => {
    confirm.require({
      message: 'Вы уверены, что хотите удалить заказ?',
      header: 'Удаление заказа',
      accept: () => {
        const url = createUrlWithParams(urls.orders.delete(orderSparePartDetail.id), { context: 'spare-parts' });
        Inertia.delete(url);
      },
    });
  },
  cancel: () => {
    confirm.require({
      message: 'Вы уверены, что хотите отменить заказ?',
      header: 'Отмена заказа',
      accept: () => {
        const url = createUrlWithParams(urls.orders.cancel(orderSparePartDetail.id), { context: 'spare-parts' });
        Inertia.put(url);
      },
    });
  },
  approve: () => {
    dialog.open(ConfirmDialog, {
      props: {
        header: 'Согласование',
        style: {
          width: '50vw',
        },
        breakpoints: {
          '960px': '75vw',
          '640px': '90vw'
        },
        modal: true,
      },
      data: {
        idOrder: orderSparePartDetail.order.id,
        message: props.labels.order.comment,
        url: createUrlWithParams(urls.orders.approve(orderSparePartDetail.id), { context: 'spare-parts' }),
        buttonLabel: 'Согласовать',
      }
    });
  },
  reject: () => {
    dialog.open(ConfirmDialog, {
      props: {
        header: 'Отказать в согласовании',
        style: {
          width: '50vw',
        },
        breakpoints: {
          '960px': '75vw',
          '640px': '90vw'
        },
        modal: true,
      },
      data: {
        idOrder: orderSparePartDetail.order.id,
        message: props.labels.order.comment,
        url: createUrlWithParams(urls.orders.reject(orderSparePartDetail.id), { context: 'spare-parts' }),
        buttonLabel: 'Отказать',
        btnSeverity: 'danger',
      }
    });
  },
  completed: () => {
    dialog.open(ConfirmDialog, {
      props: {
        header: 'Исполнено',
        style: {
          width: '50vw',
        },
        breakpoints: {
          '960px': '75vw',
          '640px': '90vw'
        },
        modal: true,
      },
      data: {
        idOrder: orderSparePartDetail.order.id,
        message: props.labels.order.comment,
        url: createUrlWithParams(urls.orders.completed(orderSparePartDetail.id), { context: 'spare-parts' }),
        buttonLabel: 'Исполнено',
      }
    });
  },
}

</script>
<template>

  <Head :title="title" />

  <Breadcrumbs :home="{ label: 'Главная', url: '/' }" :items="[
    { label: 'Заказ запчастей', url: urls.orders.spareParts.index() },
    { label: title },
  ]" />

  <Card>
    <template #title> {{ title }} </template>
    <template #content>
      <table class="w-1/2 text-left text-gray-700">
        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
          <th class="px-6 py-4">{{ labels.order_spare_part.id_printers_workplace }}</th>
          <td class="px-6 py-4">
            <PrinterWorkplace :vendor="orderSparePartDetail.printerWorkplace.printer.vendor"
              :model="orderSparePartDetail.printerWorkplace.printer.model"
              :is_color_print="orderSparePartDetail.printerWorkplace.printer.is_color_print"
              :location="orderSparePartDetail.printerWorkplace.location"
              :inventory_number="orderSparePartDetail.printerWorkplace.inventory_number"
              :serial_number="orderSparePartDetail.printerWorkplace.serial_number" />
          </td>
        </tr>
        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
          <th class="px-6 py-4">{{ labels.order_spare_part.call_specialist }}</th>
          <td class="px-6 py-4">
            {{ orderSparePartDetail.call_specialist ? 'Да' : 'Нет' }}
          </td>
        </tr>
        <template v-if="!orderSparePartDetail.call_specialist">
        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
          <th class="px-6 py-4">{{ labels.order_spare_part.id_spare_part }}</th>
          <td class="px-6 py-4">
            {{ orderSparePartDetail?.sparePart?.name }}
          </td>
        </tr>
        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
          <th class="px-6 py-4">{{ labels.order.quantity }}</th>
          <td class="px-6 py-4">
            {{ orderSparePartDetail?.order?.quantity }}
          </td>
        </tr>
        </template>
        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
          <th class="px-6 py-4">{{ labels.order.status }}</th>
          <td class="px-6 py-4">
            <OrderStatus :status="orderSparePartDetail.order.status"
              :statusLabel="orderSparePartDetail.order.status_label" />
          </td>
        </tr>
        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
          <th class="px-6 py-4">{{ labels.order.comment }}</th>
          <td class="px-6 py-4">
            {{ orderSparePartDetail.order.comment }}
          </td>
        </tr>
        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
          <th class="px-6 py-4">{{ labels.order_spare_part.files }}</th>
          <td class="px-6 py-4">
            <ul v-if="orderSparePartDetail.files" class="space-y-2">
              <li v-for="item in orderSparePartDetail.files" :key="item.id" class="flex items-center">
                <i class="pi pi-file"></i>
                <a :href="item.url_file_download" target="_blank" class="ms-2">{{ item.basename }}</a>
              </li>
            </ul>
          </td>
        </tr>
        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
          <th class="px-6 py-4">{{ labels.order.status_history }}</th>
          <td class="px-6 py-4">
            <OrderStatusHistory :idOrder="orderSparePartDetail.order.id" />
          </td>
        </tr>
        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
          <th class="px-6 py-4">{{ labels.order.requested_by }}</th>
          <td class="px-6 py-4">
            <Author :login="orderSparePartDetail.order.requested.name"
              :fullName="orderSparePartDetail.order.requested.fio" :post="orderSparePartDetail.order.requested.post"
              :department="orderSparePartDetail.order.requested.department" />
          </td>
        </tr>
        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
          <th class="px-6 py-4">{{ labels.order.created_at }}</th>
          <td class="px-6 py-4">
            {{ moment(orderSparePartDetail.order.created_at).format('L LTS') }}
          </td>
        </tr>
      </table>

      <div class="flex justify-between mt-10" v-if="auth.can('admin', 'order-approver')">

        <div class="flex gap-2" v-if="orderSparePartDetail.order.status == orderStatusPending">
          <Button severity="info" class="font-bold" @click="actions.approve">Согласовать</Button>
          <Button severity="danger" class="font-bold" @click="actions.reject">Отказать в согласовании</Button>
        </div>

        <div class="flex gap-2" v-if="orderSparePartDetail.order.status == orderStatusInProgress">
          <Button severity="info" class="font-bold" @click="actions.completed">Исполнено</Button>
        </div>

        <div v-if="orderSparePartDetail.order.status == orderStatusInProgress && (auth.can('admin') || isAuthor)">
          <Button severity="danger" class="font-bold" @click="actions.cancel">Отменить</Button>
        </div>

        <div v-if="auth.can('admin')" class="flex gap-2">
          <Button class="font-bold" @click="actions.editFiles">Редактировать файлы</Button>
          <Button class="font-bold" @click="actions.edit">Редактировать</Button>
          <Button severity="danger" class="font-bold" @click="actions.delete">Удалить</Button>
        </div>
      </div>

    </template>
  </Card>

</template>
