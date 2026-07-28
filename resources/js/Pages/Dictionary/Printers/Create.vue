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
  manufacturers: Array,
});
const title = 'Добавление принтера';

</script>

<template>
  <Head :title="title" />

  <Breadcrumbs
    :home="{ label: 'Главная', url: route('home') }"
    :items="[
      { label: 'Принтеры (справочник)', url: route('dictionary.printers.index') },
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

