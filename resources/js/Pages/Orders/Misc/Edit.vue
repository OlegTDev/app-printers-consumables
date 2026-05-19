<script setup>
import Layout from '@/Shared/Layout.vue';
import { Head } from '@inertiajs/vue3';
import Breadcrumbs from '@/Shared/Breadcrumbs.vue';
import { computed } from 'vue';
import Form from './Form.vue';
import { useConfig } from '@/Composables/useConfig';
import { useDate } from '@/Composables/useDate';

defineOptions({
  layout: Layout,
});

const props = defineProps({
  orderMiscDetail: Object,
  labels: Object,
});

const { formatDate } = useDate();
const { urls } = useConfig();
const orderMiscDetail = computed(() => props.orderMiscDetail?.data || {});
const title = 'Изменение заказа';
</script>
<template>
  <Head :title="title" />

  <Breadcrumbs
    :home="{ label: 'Главная', url: '/' }"
    :items="[
      { label: 'Заказ мелочей', url: urls.orders.misc.index() },
      {
        label: `Заказ № ${orderMiscDetail.order.id} от ${formatDate(orderMiscDetail.order.created_at, 'L')}`,
        url: urls.orders.misc.show(orderMiscDetail.id),
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
