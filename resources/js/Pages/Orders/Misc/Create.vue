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
});

const title = 'Оформление заказа';
</script>
<template>
  <Head :title="title" />

  <Breadcrumbs
    :home="{ label: 'Главная', url: route('home') }"
    :items="[
      { label: 'Заказ мелочей', url: route('orders.misc.index') },
      { label: title },
    ]"
  />
  <Form
    :title="title"
    is-new
    :labels="labels"
  />
</template>
