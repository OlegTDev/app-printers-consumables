<script setup>
import Layout from '@/Shared/Layout';
import { Head } from '@inertiajs/vue3';
import Breadcrumbs from '@/Shared/Breadcrumbs';
import { inject } from 'vue';
import Form from './Form.vue';

defineOptions({
  layout: Layout,
});

const urls = inject('urls');
const moment = inject('moment');

const props = defineProps({
  orderConsumableDetail: Object,
  labels: Object,
  consumableTypes: Object,
  cartridgeColors: Object,
});

const orderConsumableDetail = props.orderConsumableDetail?.data || [];
const title = 'Изменение заказа';
</script>
<template>
  <Head :title="title" />

  <Breadcrumbs :home="{ label: 'Главная', url: '/' }" :items="[
    { label: 'Заказ запчастей', url: urls.orders.consumables.index() },
    {
      label: `Заказ № ${orderConsumableDetail.order.id} от ${moment(orderConsumableDetail.order.created_at).format('L')}`,
      url: urls.orders.consumables.show(orderConsumableDetail.id),
    },
    { label: title },
  ]" />

  <div class="flex justify-stretch bg-white rounded-md shadow overflow-hidden mt-4">

    <Form :isNew="false" :orderConsumable="orderConsumableDetail" :labels="labels" :consumableTypes="consumableTypes" :cartridgeColors="cartridgeColors" />

  </div>
</template>
