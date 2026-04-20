<script setup>
import Layout from '@/Shared/Layout';
import { Head } from '@inertiajs/vue3';
import Breadcrumbs from '@/Shared/Breadcrumbs';
import { inject } from 'vue';
import Form from './Form.vue';

defineOptions({
  layout: Layout,
});

const urls = inject('urls');

const props = defineProps({
  labels: Object,
});

const title = 'Оформление заказа';
</script>
<template>

  <Head :title="title" />

  <Breadcrumbs :home="{ label: 'Главная', url: '/' }" :items="[
    { label: 'Заказ мелочей', url: urls.orders.misc.index() },
    { label: title },
  ]" />

  <div class="rounded-lg bg-white shadow-sm border border-gray-200">

    <Form :isNew="true" :labels="labels" />

  </div>
</template>
