<script setup>
import Layout from '@/Shared/Layout';
import { Head } from '@inertiajs/vue3';
import Breadcrumbs from '@/Shared/Breadcrumbs';
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

  <Breadcrumbs :home="{ label: 'Главная', url: '/' }" :items="[
    { label: 'Заказ запчастей', url: urls.orders.spareParts.index() },
    {
      label: `Заказ № ${orderSparePartDetailData.order.id} от ${moment(orderSparePartDetailData.order.created_at).format('L')}`,
      url: urls.orders.spareParts.show(orderSparePartDetailData.order.id),
    },
    { label: title },
  ]" />

  <div class="flex justify-stretch bg-white rounded-md shadow overflow-hidden mt-4">

    <Form :isNew="false" :spareParts="sparePartsData" :labels="labels" :orderSparePart="orderSparePartDetailData" />

  </div>
</template>