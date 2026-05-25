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
});
const { urls } = useConfig();

const title = 'Добавление организации';
</script>

<template>
  <Head :title="title" />

  <Breadcrumbs
    :home="{ label: 'Главная', url: urls.home }"
    :items="[
      { label: 'Справочники' },
      { label: 'Организации', url: urls.dictionary.organizations.index() },
      { label: title },
    ]"
  />

  <Form
    is-new
    :title="title"
    :labels="labels"
  />
</template>

