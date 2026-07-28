<script setup>
import { Head } from '@inertiajs/vue3';
import Layout from '@/Shared/Layout.vue';
import Form from './Form.vue';
import Breadcrumbs from '@/Shared/Breadcrumbs.vue';

defineOptions({
  layout: Layout
});

defineProps({
  labels: Object,
});

const title = 'Добавление организации';
</script>

<template>
  <Head :title="title" />

  <Breadcrumbs
    :home="{ label: 'Главная', url: route('dashboard') }"
    :items="[
      { label: 'Справочники' },
      { label: 'Организации', url: route('dictionary.organizations.index') },
      { label: title },
    ]"
  />

  <Form
    is-new
    :title="title"
    :labels="labels"
  />
</template>

