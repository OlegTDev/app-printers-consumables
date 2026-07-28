<script setup>
import Layout from '@/Shared/Layout.vue';
import { Head } from '@inertiajs/vue3';
import Breadcrumbs from '@/Shared/Breadcrumbs.vue';
import Form from './Form.vue';
import { useDate } from '@/Composables/useDate';

defineOptions({
  layout: Layout,
});

const { formatDate } = useDate();

defineProps({
  orderSparePartDetail: Object,
  labels: Object,
});

const title = 'Изменение заказа';
</script>
<template>
  <Head :title="title" />

  <Breadcrumbs
    :home="{ label: 'Главная', url: route('home') }"
    :items="[
      { label: 'Заказ запчастей', url: route('orders.spare-parts.index') },
      {
        label: `Заказ № ${orderSparePartDetail.order.id} от ${formatDate(orderSparePartDetail.order.created_at, 'L')}`,
        url: route('orders.spare-parts.show', { orderSparePartDetails: orderSparePartDetail.id }),
      },
      { label: title },
    ]"
  />

  <Form
    :labels="labels"
    :title
    :order-spare-part="orderSparePartDetail"
  />
</template>
