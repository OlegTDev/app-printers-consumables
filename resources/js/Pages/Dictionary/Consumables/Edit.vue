<script setup>
import { Head } from '@inertiajs/vue3';
import Layout from '@/Shared/Layout';
import Form from './Form.vue';
import Breadcrumbs from '@/Shared/Breadcrumbs';
import { useConfig } from '@/Composables/useConfig';

defineOptions({
    layout: Layout
});

defineProps({
  labels: Object,
  consumable: Object,
  cartridgeColors: Object,
  consumableTypes: Object,
  consumableTypeValue: String,
});
const title = 'Редактирование расходного материала';
const { urls } = useConfig();

</script>
<template>
  <Head :title="title" />

  <Breadcrumbs
    :home="{ label: 'Главная', url: urls.home }"
    :items="[
      { label: 'Расходные материалы (справочник)', url: urls.dictionary.consumables.index() },
      { label: `${consumableTypeValue} ${consumable.name}`, url: urls.dictionary.consumables.show(consumable.id) },
      { label: title },
    ]"
  />

  <Form
    :labels="labels"
    :title="title"
    :consumable="consumable"
    :cartridge-colors="cartridgeColors"
    :consumable-types="consumableTypes"
  />
</template>

