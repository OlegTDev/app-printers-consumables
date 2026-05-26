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

const { formatDate } = useDate();

const props = defineProps({
  orderSparePartDetail: Object,
  spareParts: Object,
  labels: Object,
});

const sparePartsData = computed(() => props.spareParts?.data || []);
const orderSparePartDetailData = computed(() => props.orderSparePartDetail?.data || {});
const title = 'Изменение заказа';
</script>
<template>
  <Head :title="title" />

  <Breadcrumbs
    :home="{ label: 'Главная', url: route('home') }"
    :items="[
      { label: 'Заказ запчастей', url: route('orders.spare-parts.index') },
      {
        label: `Заказ № ${orderSparePartDetailData.order.id} от ${formatDate(orderSparePartDetailData.order.created_at, 'L')}`,
        url: route('orders.spare-parts.show', { orderSparePartDetails: orderSparePartDetailData.id }),
      },
      { label: title },
    ]"
  />

  <Form
    :spare-parts="sparePartsData"
    :labels="labels"
    :order-spare-part="orderSparePartDetailData"
  />
</template>
