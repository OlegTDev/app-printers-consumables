<script setup>
import Layout from '@/Shared/Layout.vue';
import { Head } from '@inertiajs/vue3';
import Breadcrumbs from '@/Shared/Breadcrumbs.vue';
import Form from './Form.vue';
import { useDate } from '@/Composables/useDate';
import { computed } from 'vue';

defineOptions({
  layout: Layout,
});

const { formatDate } = useDate();

const props = defineProps({
  orderConsumableDetail: Object,
  labels: Object,
  consumableTypes: Object,
  cartridgeColors: Object,
});

const orderConsumable = computed(() => props.orderConsumableDetail?.data || {});
const title = 'Изменение заказа';
</script>
<template>
  <Head :title="title" />

  <Breadcrumbs
    :home="{ label: 'Главная', url: route('home') }"
    :items="[
      { label: 'Заказ запчастей', url: route('orders.consumables.index') },
      {
        label: orderConsumable?.id
          ? `Заказ № ${orderConsumable?.order?.id} от ${formatDate(orderConsumable?.order?.created_at, 'L')}`
          : 'Загрузка',
        url: route('orders.consumables.show', { orderConsumableDetails: orderConsumable?.id }),
      },
      { label: title },
    ]"
  />

  <Form
    :order-consumable="orderConsumable"
    :labels="labels"
    :consumable-types="consumableTypes"
    :cartridge-colors="cartridgeColors"
  />
</template>
