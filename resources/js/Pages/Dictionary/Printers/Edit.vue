<script setup>
import { Head } from '@inertiajs/vue3';
import Layout from '@/Shared/Layout.vue';
import { computed } from 'vue';
import Breadcrumbs from '@/Shared/Breadcrumbs.vue';
import Form from './Form.vue';

defineOptions({
  layout: Layout
});

const props = defineProps({
  printer: {
    type: Object,
    required: true,
  },
  labels: {
    type: Object,
    required: true,
  },
  manufacturers: {
    type: Array,
    required: true,
  },
});

const title = computed(() => `${props.printer.vendor} ${props.printer.model}`);

</script>
<template>
  <Head :title="title" />

  <Breadcrumbs
    :home="{ label: 'Главная', url: route('home') }"
    :items="[
      { label: 'Принтеры (справочник)', url: route('dictionary.printers.index') },
      { label: `${printer.vendor} ${props.printer.model}`, url: route('dictionary.printers.show', { printer: printer.id }) },
      { label: 'Редактирование' },
    ]"
  />

  <Form
    :title="title"
    :labels="labels"
    :printer="printer"
    :manufacturers="manufacturers"
  />
</template>
