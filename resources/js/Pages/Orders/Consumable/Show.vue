<script setup>
import Layout from '@/Shared/Layout';
import { useConfirm } from 'primevue/useconfirm';
import { useDialog } from 'primevue/usedialog';
import { defineAsyncComponent, inject, ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import Breadcrumbs from '@/Shared/Breadcrumbs';
import Card from 'primevue/card';
import Tag from 'primevue/tag';
import OrderStatusHistory from '../Shared/OrderStatusHistory.vue';
import Author from '@/Shared/DataTable/Author.vue';
import OrderStatus from '../Shared/OrderStatus.vue';
import Button from 'primevue/button';
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
  orderConsumableDetail: Object,
  labels: Object,
  isAuthor: Boolean,
  buttons: Array,
  statuses: Object,
  consumableTypes: Object,
  cartridgeColors: Object,
});

const orderConsumableDetail = props.orderConsumableDetail.data;
const orderId = orderConsumableDetail.order.id;

const ConfirmDialog = defineAsyncComponent(() => import('../Shared/ConfirmDialog.vue'));

const title = `Заказ № ${orderConsumableDetail.order.id} от ${moment(orderConsumableDetail.order.created_at).format('L')}`

const actions = {
  edit: () => {
    router.get(urls.orders.consumables.edit(orderConsumableDetail.id));
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
        idOrder: orderConsumableDetail.order.id,
        message: props.labels.order.comment,
        url: createUrlWithParams(urls.orders.agree(orderId), { context: 'consumables' }),
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
        url: createUrlWithParams(urls.orders.reject(orderId), { context: 'consumables' }),
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
        url: createUrlWithParams(urls.orders.ordered(orderId), { context: 'consumables' }),
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
        url: createUrlWithParams(urls.orders.receive(orderId), { context: 'consumables' }),
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
        url: createUrlWithParams(urls.orders.complete(orderId), { context: 'consumables' }),
        buttonLabel: 'Исполнено',
      }
    });
  },
}
</script>
<template>

  <Head :title="title" />

  <Breadcrumbs :home="{ label: 'Главная', url: '/' }" :items="[
    { label: 'Заказ картриджей', url: urls.orders.consumables.index() },
    { label: title },
  ]" />

  <Card>
    <template #title> {{ title }} </template>
    <template #content>
      <table class="w-1/2 text-left text-gray-700">
        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
          <th class="px-6 py-4">{{ labels.order_consumable.id_consumable }}</th>
          <td class="px-6 py-4">
            <div class="grid grid-rows-2 gap-4">
              <div>{{ consumableTypes[orderConsumableDetail.consumable.type] ?? orderConsumableDetail.consumable.type }}
              </div>
              <div>
                {{ orderConsumableDetail.consumable.name }}
              </div>
              <div v-if="orderConsumableDetail.consumable.type === 'cartridge'">
                <div class="flex">
                  <div
                    :class="['rounded-full', 'size-4', 'mr-2', cartridgeColors[orderConsumableDetail.consumable.color]['bg']]">
                  </div>
                  <div>
                    {{ cartridgeColors[orderConsumableDetail.consumable.color]['name'] }}
                  </div>
                </div>
              </div>
            </div>
          </td>
        </tr>
        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
          <th class="px-6 py-4">{{ labels.order.quantity }}</th>
          <td class="px-6 py-4">
            <Tag :value="orderConsumableDetail.order.quantity" />
          </td>
        </tr>
        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
          <th class="px-6 py-4">{{ labels.order.status }}</th>
          <td class="px-6 py-4">
            <OrderStatus :status="orderConsumableDetail.order.status" :statuses="statuses" />
          </td>
        </tr>
        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
          <th class="px-6 py-4">{{ labels.order.comment }}</th>
          <td class="px-6 py-4">
            {{ orderConsumableDetail.order.comment }}
          </td>
        </tr>

        <template v-if="orderConsumableDetail.order.service_request_number">
          <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
            <th class="px-6 py-4">{{ labels.order.service_request_number }}</th>
            <td class="px-6 py-4">
              {{ orderConsumableDetail.order.service_request_number }}
            </td>
          </tr>
        </template>
        <template v-if="orderConsumableDetail.order.service_request_number">
          <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
            <th class="px-6 py-4">{{ labels.order.service_request_date }}</th>
            <td class="px-6 py-4">
              {{ orderConsumableDetail.order.service_request_date
                ? moment(orderConsumableDetail.order.service_request_date).format('L')
                : null }}
            </td>
          </tr>
        </template>
        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
          <th class="px-6 py-4">{{ labels.order.status_history }}</th>
          <td class="px-6 py-4">
            <OrderStatusHistory :idOrder="orderConsumableDetail.order.id" :statuses="statuses" />
          </td>
        </tr>
        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
          <th class="px-6 py-4">{{ labels.order.requested_by }}</th>
          <td class="px-6 py-4">
            <Author :login="orderConsumableDetail.order.requested.name"
              :fullName="orderConsumableDetail.order.requested.fio" :post="orderConsumableDetail.order.requested.post"
              :department="orderConsumableDetail.order.requested.department" />
          </td>
        </tr>
        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
          <th class="px-6 py-4">{{ labels.order.created_at }}</th>
          <td class="px-6 py-4">
            {{ moment(orderConsumableDetail.order.created_at).format('L LTS') }}
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
