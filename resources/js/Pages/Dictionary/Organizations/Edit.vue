<script setup>
import { Head } from '@inertiajs/vue3';
import Layout from '@/Shared/Layout.vue';
import { computed } from 'vue';
import Breadcrumbs from '@/Shared/Breadcrumbs.vue';
import Form from './Form.vue';

defineOptions({
  layout: Layout
});

const { organization, labels } = defineProps({
  organization: Object,
  labels: Object,
});

const title = computed(() => `${organization.name} (${organization.code})`);
</script>
<template>
  <Head :title="title" />

  <Breadcrumbs
    :home="{ label: 'Главная', url: route('dashboard') }"
    :items="[
      { label: 'Справочники' },
      { label: 'Организации', url: route('dictionary.organizations.index') },
      { label: title, url: route('dictionary.organizations.show', { organization: organization.code }) },
      { label: 'Редактирование' },
    ]"
  />

  <Form
    :labels="labels"
    :organization="organization"
    :title="title"
  />
</template>
