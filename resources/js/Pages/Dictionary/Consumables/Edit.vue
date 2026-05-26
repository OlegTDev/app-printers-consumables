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
  consumable: Object,
  cartridgeColors: Object,
  consumableTypes: Object,
  consumableTypeValue: String,
});
const title = 'Редактирование расходного материала';

</script>
<template>
  <div>
    <Head :title="title" />

    <Breadcrumbs
      :home="{ label: 'Главная', url: route('dashboard') }"
      :items="[
        { label: 'Расходные материалы (справочник)', url: route('dictionary.consumables.index') },
        { label: `${consumableTypeValue} ${consumable.name}`, url: route('dictionary.consumables.show', { consumable: consumable.id }) },
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
  </div>
</template>

