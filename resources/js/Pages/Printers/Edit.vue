<script setup>
import { Head } from '@inertiajs/vue3';
import Layout from '@/Shared/Layout.vue';
import Breadcrumbs from '@/Shared/Breadcrumbs.vue';
import Form from './Form.vue';
import { useConfig } from '@/Composables/useConfig';

defineOptions({
    layout: Layout
});

const { urls } = useConfig();

defineProps({
  labels: { type: Object, required: true },
  printers: { type: Object, required: true },
  organizations: { type: Array, required: true },
  printerWorkplace: { type: Object, required: true },
});

const title = 'Редактирование';
</script>
<template>
  <Head :title="title" />

  <Breadcrumbs
    :home="{ label: 'Главная', url: urls.home }"
    :items="[
      { label: 'Принтеры', url: urls.printers.index() },
      { label: `${printerWorkplace.printer.vendor} ${printerWorkplace.printer.model} (${printerWorkplace.location})`, url: urls.printers.show(printerWorkplace.id) },
      { label: title },
    ]"
  />

  <Form
    :is-new="false"
    :labels="labels"
    :printers="printers"
    :printer-workplace="printerWorkplace"
    :organizations="organizations"
  />
</template>
