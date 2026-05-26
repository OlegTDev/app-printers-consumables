<script setup>
import Layout from '@/Shared/Layout.vue';
import { Head } from '@inertiajs/vue3';
import Breadcrumbs from '@/Shared/Breadcrumbs.vue';
import { computed } from 'vue';
import Form from './Form.vue';

defineOptions({
  layout: Layout,
});

const props = defineProps({
  spareParts: {
    type: Object,
  },
  labels: {
    type: Object,
  },
});

const sparePartsData = computed(() => props.spareParts?.data || []);
const title = 'Оформление заказа';
</script>
<template>
  <Head :title="title" />

  <Breadcrumbs
    :home="{ label: 'Главная', url: route('home') }"
    :items="[
      { label: 'Заказ запчастей', url: route('orders.spare-parts.index') },
      { label: title },
    ]"
  />

  <Form
    is-new
    :title="title"
    :spare-parts="sparePartsData"
    :labels="labels"
  />
</template>
