<script setup>
import { Head } from '@inertiajs/vue3';
import Layout from '@/Shared/Layout.vue';
import Form from './Form.vue';
import Breadcrumbs from '@/Shared/Breadcrumbs.vue';
import { useConfig } from '@/Composables/useConfig';

defineOptions({
    layout: Layout
});

defineProps({
    labels: Object,
    manufacturers: Array,
});
const title = 'Добавление принтера';
const { urls } = useConfig();

</script>

<template>
  <Head :title="title" />

  <Breadcrumbs
    :home="{ label: 'Главная', url: urls.home }"
    :items="[
      { label: 'Принтеры (справочник)', url: urls.dictionary.printers.index() },
      { label: title },
    ]"
  />

  <Form
    is-new
    :title="title"
    :labels="labels"
    :manufacturers="manufacturers"
  />
</template>

