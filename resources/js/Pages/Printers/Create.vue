<script setup>
import { Head } from '@inertiajs/vue3';
import Layout from '@/Shared/Layout.vue';
import Form from './Form.vue';
import Breadcrumbs from '@/Shared/Breadcrumbs.vue';

defineOptions({
  layout: Layout
});

defineProps({
  labels: { type: Object, required: true },
  printers: { type: Object, required: true },
  organizations: { type: Array, required: true },
});

const title = 'Добавление принтера';
</script>
<template>
  <Head :title="title" />

  <Breadcrumbs
    :home="{ label: 'Главная', url: route('home') }"
    :items="[
      { label: 'Принтеры', url: route('workplace.index') },
      { label: title },
    ]"
  />

  <Form
    :is-new="true"
    :labels="labels"
    :printers="printers"
    :organizations="organizations"
    :title
  />
</template>
