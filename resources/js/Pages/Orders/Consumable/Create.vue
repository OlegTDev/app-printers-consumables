<script setup>
import Layout from '@/Shared/Layout.vue';
import { Head } from '@inertiajs/vue3';
import Breadcrumbs from '@/Shared/Breadcrumbs.vue';
import Form from './Form.vue';

defineOptions({
  layout: Layout,
});

defineProps({
  labels: Object,
  consumableTypes: Object,
  cartridgeColors: Object,
});

const title = 'Оформление заказа картриджа';
</script>
<template>
  <Head :title="title" />

  <Breadcrumbs
    :home="{ label: 'Главная', url: route('home') }"
    :items="[
      { label: 'Заказ картриджей', url: route('orders.consumables.index') },
      { label: title },
    ]"
  />

  <Form
    is-new
    :title="title"
    :labels="labels"
    :consumable-types="consumableTypes"
    :cartridge-colors="cartridgeColors"
  />
</template>
