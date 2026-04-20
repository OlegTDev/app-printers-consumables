<script setup>
import Layout from '@/Shared/Layout';
import { Head, router } from '@inertiajs/vue3';
import { defineAsyncComponent, inject } from 'vue';
import Breadcrumbs from '@/Shared/Breadcrumbs';
import Card from 'primevue/card';
import OrderStatus from '../Shared/OrderStatus';
import Author from '@/Shared/DataTable/Author';
import Button from 'primevue/button';
import { useDialog } from 'primevue/usedialog';
import OrderStatusHistory from '../Shared/OrderStatusHistory.vue';
import { useConfirm } from 'primevue/useconfirm';
import { createUrlWithParams } from '@/config/urls';

defineOptions({
  layout: Layout,
});

const urls = inject('urls');
const moment = inject('moment');
const dialog = useDialog();
const confirm = useConfirm();

const props = defineProps({
  auth: Object,
  orderMiscDetail: Object,
  labels: Object,
  isAuthor: Boolean,
  buttons: Array,
  statuses: Object,
});

const ConfirmDialog = defineAsyncComponent(() => import('../Shared/ConfirmDialog.vue'));

const orderMiscDetail = props.orderMiscDetail.data;
const orderId = orderMiscDetail.order.id;

const title = `Заказ № ${orderMiscDetail.order.id} от ${moment(orderMiscDetail.order.created_at).format('L')}`

const actions = {
  edit: () => {
    router.get(urls.orders.misc.edit(orderMiscDetail.id));
  },
  editFiles: () => {
    router.get(urls.orders.misc.editFiles(orderMiscDetail.id));
  },
  delete: () => {
    confirm.require({
      message: 'Вы уверены, что хотите удалить заказ?',
      header: 'Удаление заказа',
      accept: () => {
        const url = createUrlWithParams(urls.orders.delete(orderId), { context: 'misc' });
        router.delete(url);
      },
    });
  },
  cancel: () => {
    confirm.require({
      message: 'Вы уверены, что хотите отменить заказ?',
      header: 'Отмена заказа',
      accept: () => {
        const url = createUrlWithParams(urls.orders.cancel(orderId), { context: 'misc' });
        router.put(url);
      },
    });
  },
  agree: () => {
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
        idOrder: orderId,
        message: props.labels.order.comment,
        url: createUrlWithParams(urls.orders.agree(orderId), { context: 'misc' }),
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
        idOrder: orderId,
        message: props.labels.order.comment,
        url: createUrlWithParams(urls.orders.reject(orderId), { context: 'misc' }),
        buttonLabel: 'Отказать',
        btnSeverity: 'danger',
      }
    });
  },
  ordered: () => {
    dialog.open(ConfirmDialog, {
      props: {
        header: 'Заказан',
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
        idOrder: orderId,
        message: props.labels.order.comment,
        url: createUrlWithParams(urls.orders.ordered(orderId), { context: 'misc' }),
        buttonLabel: 'Перевести в состояние "Заказан"',
      }
    });
  },
  receive: () => {
    dialog.open(ConfirmDialog, {
      props: {
        header: 'Получен',
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
        idOrder: orderId,
        message: props.labels.order.comment,
        url: createUrlWithParams(urls.orders.receive(orderId), { context: 'misc' }),
        buttonLabel: 'Перевести в состояние "Получен"',
      }
    });
  },
  complete: () => {
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
        idOrder: orderId,
        message: props.labels.order.comment,
        url: createUrlWithParams(urls.orders.complete(orderId), { context: 'misc' }),
        buttonLabel: 'Исполнено',
      }
    });
  },
}

</script>
<template>
  <Head :title="title" />

  <Breadcrumbs :home="{ label: 'Главная', url: '/' }" :items="[
    { label: 'Заказ мелочей', url: urls.orders.misc.index() },
    { label: title },
  ]" />

  <Card>
    <template #title> {{ title }} </template>
    <template #content>
      <table class="w-1/2 text-left text-gray-700">
        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
          <th class="px-6 py-4">{{ labels.name }}</th>
          <td class="px-6 py-4">{{ orderMiscDetail.name }}</td>
        </tr>
        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
          <th class="px-6 py-4">{{ labels.description }}</th>
          <td class="px-6 py-4">{{ orderMiscDetail.description }}</td>
        </tr>
        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
          <th class="px-6 py-4">{{ labels.order.status }}</th>
          <td class="px-6 py-4">
            <OrderStatus :status="orderMiscDetail.order.status" :statuses="statuses" />
          </td>
        </tr>
        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
          <th class="px-6 py-4">{{ labels.order.comment }}</th>
          <td class="px-6 py-4">
            {{ orderMiscDetail.order.comment }}
          </td>
        </tr>

        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
          <th class="px-6 py-4">{{ labels.order.status_history }}</th>
          <td class="px-6 py-4">
            <OrderStatusHistory :idOrder="orderMiscDetail.order.id" :statuses="statuses" />
          </td>
        </tr>
        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
          <th class="px-6 py-4">{{ labels.order.requested_by }}</th>
          <td class="px-6 py-4">
            <Author :login="orderMiscDetail.order.requested.name"
              :fullName="orderMiscDetail.order.requested.fio" :post="orderMiscDetail.order.requested.post"
              :department="orderMiscDetail.order.requested.department" />
          </td>
        </tr>
        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
          <th class="px-6 py-4">{{ labels.order.created_at }}</th>
          <td class="px-6 py-4">
            {{ moment(orderMiscDetail.order.created_at).format('L LTS') }}
          </td>
        </tr>
      </table>

      <div class="flex justify-between mt-10" vif="auth.can('admin', 'order-approver')">

        <div class="flex gap-2">
          <Button v-if="buttons.includes('agreed')" severity="info" class="font-bold" @click="actions.agree">Согласовать</Button>
          <Button v-if="buttons.includes('rejected')" severity="danger" class="font-bold" @click="actions.reject">Отказать в согласовании</Button>
          <Button v-if="buttons.includes('ordered')" severity="info" class="font-bold" @click="actions.ordered">Заказан</Button>
          <Button v-if="buttons.includes('received')" severity="info" class="font-bold" @click="actions.receive">Получен</Button>
          <Button v-if="buttons.includes('completed')" severity="success" class="font-bold" @click="actions.complete">Исполнено</Button>
          <Button v-if="buttons.includes('cancelled')" severity="danger" class="font-bold" @click="actions.cancel">Отменить</Button>
        </div>

        <div class="flex gap-2">
          <Button v-if="auth.isAdmin || isAuthor" class="font-bold" @click="actions.edit">Редактировать</Button>
          <Button v-if="auth.isAdmin" severity="danger" class="font-bold" @click="actions.delete">Удалить</Button>
        </div>

      </div>

    </template>
  </Card>

</template>
