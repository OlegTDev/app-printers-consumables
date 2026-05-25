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
});

const title = 'Оформление заказа';
const { urls } = useConfig();
</script>
<template>
  <Head :title="title" />

  <Breadcrumbs
    :home="{ label: 'Главная', url: '/' }"
    :items="[
      { label: 'Заказ мелочей', url: urls.orders.misc.index() },
      { label: title },
    ]"
  />
  <Form
    :title="title"
    is-new
    :labels="labels"
  />
</template>
