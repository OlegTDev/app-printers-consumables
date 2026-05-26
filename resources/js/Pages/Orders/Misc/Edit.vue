<script setup>
import Layout from '@/Shared/Layout.vue';
import { Head } from '@inertiajs/vue3';
import Breadcrumbs from '@/Shared/Breadcrumbs.vue';
import { computed } from 'vue';
import Form from './Form.vue';
import { useDate } from '@/Composables/useDate';

defineOptions({
  layout: Layout,
});

const props = defineProps({
  orderMiscDetail: Object,
  labels: Object,
});

const { formatDate } = useDate();
const orderMiscDetail = computed(() => props.orderMiscDetail?.data || {});
const title = 'Изменение заказа';
</script>
<template>
  <Head :title="title" />

  <Breadcrumbs
    :home="{ label: 'Главная', url: route('home') }"
    :items="[
      { label: 'Заказ мелочей', url: route('orders.misc.index') },
      {
        label: `Заказ № ${orderMiscDetail.order.id} от ${formatDate(orderMiscDetail.order.created_at, 'L')}`,
        url: route('orders.misc.show', { orderMiscDetails: orderMiscDetail.id }),
      },
      { label: title },
    ]"
  />

  <Form
    :title="title"
    :order-misc="orderMiscDetail"
    :labels="labels"
  />
</template>
