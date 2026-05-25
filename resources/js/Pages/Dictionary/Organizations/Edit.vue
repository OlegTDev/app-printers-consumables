<script setup>
import { Head } from '@inertiajs/vue3';
import Layout from '@/Shared/Layout.vue';
import { computed } from 'vue';
import Breadcrumbs from '@/Shared/Breadcrumbs.vue';
import Form from './Form.vue';
import { useConfig } from '@/Composables/useConfig';

defineOptions({
    layout: Layout
});

const props = defineProps({
  organization: Object,
  labels: Object,
});

const title = computed(() => `${props.organization.name} (${props.organization.code})`);
const { urls}  = useConfig();
</script>
<template>
  <Head :title="title" />

  <Breadcrumbs
    :home="{ label: 'Главная', url: urls.home }"
    :items="[
      { label: 'Справочники' },
      { label: 'Организации', url: urls.dictionary.organizations.index() },
      { label: title, url: urls.dictionary.organizations.show(props.organization.code) },
      { label: 'Редактирование' },
    ]"
  />

  <Form
    :labels="labels"
    :organization="organization"
    :title="title"
  />
</template>
