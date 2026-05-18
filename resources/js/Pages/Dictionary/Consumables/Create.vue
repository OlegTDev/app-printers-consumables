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
  cartridgeColors: Object,
  consumableTypes: Object,
});

const { urls } = useConfig();

const title = 'Добавление расходного материала';
</script>

<template>
  <Head :title="title" />
  <Breadcrumbs
    :home="{ label: 'Главная', url: urls.home }"
    :items="[
      { label: 'Расходные материалы (справочник)', url: urls.dictionary.consumables.index() },
      { label: title },
    ]"
  />

  <Form
    :title="title"
    :is-new
    :labels="labels"
    :cartridge-colors="cartridgeColors"
    :consumable-types="consumableTypes"
  />
</template>

