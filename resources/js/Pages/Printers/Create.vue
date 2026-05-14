<script setup>
import { Head } from '@inertiajs/vue3';
import Layout from '@/Shared/Layout';
import Form from './Form';
import Breadcrumbs from '@/Shared/Breadcrumbs';
import { useConfig } from '@/Composables/useConfig';

defineOptions({
  layout: Layout
});

const { urls } = useConfig();

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
    :home="{ label: 'Главная', url: urls.home }"
    :items="[
      { label: 'Принтеры', url: urls.printers.index() },
      { label: title },
    ]"
  />

  <Form
    :is-new="true"
    :labels="labels"
    :printers="printers"
    :organizations="organizations"
  />
</template>
