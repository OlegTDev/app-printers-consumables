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
  cartridgeColors: Object,
  consumableTypes: Object,
});

const title = 'Добавление расходного материала';
</script>

<template>
  <div>
    <Head :title="title" />
    <Breadcrumbs
      :home="{ label: 'Главная', url: route('dashboard') }"
      :items="[
        { label: 'Расходные материалы (справочник)', url: route('dictionary.consumables.index') },
        { label: title },
      ]"
    />

    <Form
      :title="title"
      is-new
      :labels="labels"
      :cartridge-colors="cartridgeColors"
      :consumable-types="consumableTypes"
    />
  </div>
</template>

