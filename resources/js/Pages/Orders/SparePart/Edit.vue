<script setup>
import Layout from '@/Shared/Layout.vue';
import { Head } from '@inertiajs/vue3';
import Breadcrumbs from '@/Shared/Breadcrumbs.vue';
import { computed, inject } from 'vue';
import Form from './Form.vue';

defineOptions({
  layout: Layout,
});

const urls = inject('urls');
const moment = inject('moment');

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
    :home="{ label: 'Главная', url: '/' }"
    :items="[
      { label: 'Заказ запчастей', url: urls.orders.spareParts.index() },
      {
        label: `Заказ № ${orderSparePartDetailData.order.id} от ${moment(orderSparePartDetailData.order.created_at).format('L')}`,
        url: urls.orders.spareParts.show(orderSparePartDetailData.id),
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
