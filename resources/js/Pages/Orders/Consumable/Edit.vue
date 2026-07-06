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
  orderConsumableDetail: Object,
  labels: Object,
  consumableTypes: Object,
  cartridgeColors: Object,
});

const title = 'Изменение заказа';
</script>
<template>
  <Head :title="title" />

  <Breadcrumbs
    :home="{ label: 'Главная', url: route('home') }"
    :items="[
      { label: 'Заказ запчастей', url: route('orders.consumables.index') },
      {
        label: orderConsumableDetail?.id
          ? `Заказ № ${orderConsumableDetail?.order?.id} от ${formatDate(orderConsumableDetail?.order?.created_at, 'L')}`
          : 'Загрузка',
        url: route('orders.consumables.show', { orderConsumableDetails: orderConsumableDetail?.id }),
      },
      { label: title },
    ]"
  />

  <Form
    :order-consumable="orderConsumableDetail"
    :labels="labels"
    :consumable-types="consumableTypes"
    :cartridge-colors="cartridgeColors"
    :title
  />
</template>
