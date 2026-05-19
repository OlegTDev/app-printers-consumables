<script setup>
import Layout from '@/Shared/Layout.vue';
import { Head } from '@inertiajs/vue3';
import Breadcrumbs from '@/Shared/Breadcrumbs.vue';
import Form from './Form.vue';
import { useConfig } from '@/Composables/useConfig';

defineOptions({
  layout: Layout,
});

defineProps({
  labels: Object,
  consumableTypes: Object,
  cartridgeColors: Object,
});

const { urls } = useConfig();

const title = 'Оформление заказа картриджа';
</script>
<template>
  <Head :title="title" />

  <Breadcrumbs
    :home="{ label: 'Главная', url: '/' }"
    :items="[
      { label: 'Заказ картриджей', url: urls.orders.consumables.index() },
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
