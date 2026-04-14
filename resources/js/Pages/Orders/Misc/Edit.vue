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
  orderMiscDetail: Object,
  labels: Object,
});

const orderMiscDetail = props.orderMiscDetail?.data || [];
const title = 'Изменение заказа';
</script>
<template>
  <Head :title="title" />

  <Breadcrumbs :home="{ label: 'Главная', url: '/' }" :items="[
    { label: 'Заказ мелочей', url: urls.orders.misc.index() },
    {
      label: `Заказ № ${orderMiscDetail.order.id} от ${moment(orderMiscDetail.order.created_at).format('L')}`,
      url: urls.orders.misc.show(orderMiscDetail.id),
    },
    { label: title },
  ]" />

  <div class="flex justify-stretch bg-white rounded-md shadow overflow-hidden mt-4">

    <Form :isNew="false" :orderMisc="orderMiscDetail" :labels="labels" />

  </div>
</template>
