<script setup>
import { Head, router } from '@inertiajs/vue3';
import Layout from '@/Shared/Layout.vue';
import { ref } from 'vue';
import Breadcrumbs from '@/Shared/Breadcrumbs.vue';
import Column from 'primevue/column';
import Button from 'primevue/button';
import TreeTable from 'primevue/treetable';
import Card from '@/Shared/Card.vue';
import Title from '@/Shared/Title.vue';
import Timestamps from '@/Shared/DataTable/Timestamps.vue';

defineProps({
  organizations: Object,
  labels: Object,
  filters: Object,
});

defineOptions({
  layout: Layout
});

const title = 'Организации';
const selectedRow = ref({});

const onRowSelect = (event) => {
  router.get(route('dictionary.organizations.show', { organization: event.data.code }));
};
</script>
<template>
  <Head :title="title" />

  <Breadcrumbs
    :home="{ label: 'Главная', url: route('dashboard') }"
    :items="[
      { label: 'Справочники' },
      { label: title },
    ]"
  />

  <Card>
    <Title>{{ title }}</Title>

    <TreeTable
      v-model:selection-keys="selectedRow"
      :value="organizations"
      :meta-key-selection="false"
      paginator
      :rows="10"
      selection-mode="single"
      table-style="min-width: 50rem"
      @node-select="onRowSelect"
    >
      <template #header>
        <div class="flex justify-between">
          <Button type="button" severity="info" @click="router.get(route('dictionary.organizations.create'))">
            Добавить организацию
          </Button>
        </div>
      </template>
      <Column field="code" :header="labels.code" sortable expander />
      <Column field="name" :header="labels.name" sortable />
      <Column field="created_at" :header="labels.date" sortable>
        <template #body="{ node: { data } }">
          <Timestamps :created-at="data.created_at" :updated-at="data.updated_at" />
        </template>
      </Column>
      <template #empty>
        Нет данных
      </template>
    </TreeTable>
  </Card>
</template>
