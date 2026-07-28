<script setup>
import { Head, router } from '@inertiajs/vue3';
import Layout from '@/Shared/Layout.vue';
import { ref } from 'vue';
import Breadcrumbs from '@/Shared/Breadcrumbs.vue';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Card from '@/Shared/Card.vue';
import Title from '@/Shared/Title.vue';
import Timestamps from '@/Shared/DataTable/Timestamps.vue';
import Author from '@/Shared/DataTable/Author.vue';
import { useAuth } from '@/Composables/useAuth';
import RemoteDataTable from '@/Shared/DataTable/RemoteDataTable.vue';

defineProps({
  items: Object,
  labels: Object,
  filters: Object,
});

defineOptions({
  layout: Layout
});

const title = 'Принтеры';

const { can } = useAuth();

const onRowSelect = (event) => {
  router.get(route('dictionary.printers.show', { printer: event.data.id }));
};

const refTablePrintersDic = ref(null);

const onPageChange = () => {
  const elementTablePrintersDic = refTablePrintersDic.value.$el;
  if (elementTablePrintersDic) {
    elementTablePrintersDic.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }
};

</script>
<template>
  <Head :title="title" />

  <Breadcrumbs
    :home="{ label: 'Главная', url: route('home') }"
    :items="[
      { label: 'Справочники' },
      { label: title },
    ]"
  />

  <Card>
    <Title>{{ title }}</Title>

    <RemoteDataTable
      ref="refTablePrintersDic"
      data-key="id"
      :model="items"
      :url="route('dictionary.printers.index')"
      selection-mode="single"
      @row-select="onRowSelect"
      @page="onPageChange"
    >
      <template #header>
        <Button
          v-if="can('admin', 'editor-dictionary')"
          type="button"
          severity="info"
          @click="router.get(route('dictionary.printers.create'))"
        >
          Добавить принтер
        </Button>
      </template>
      <Column header="#" field="id" header-style="width:3rem" sortable />
      <Column field="vendor" header="Производитель" sortable />
      <Column field="model" header="Модель" sortable />
      <Column field="is_color_print" header="Цветная печать" sortable>
        <template #body="{ data }">
          {{ data.is_color_print ? 'Да' : 'Нет' }}
        </template>
      </Column>
      <Column field="created_at" header="Дата" sortable>
        <template #body="{ data }">
          <Timestamps :created-at="data.created_at" :updated-at="data.updated_at" />
        </template>
      </Column>
      <Column header="Автор">
        <template #body="{ data }">
          <Author :user="data.author" />
        </template>
      </Column>

      <template #empty>
        Нет данных
      </template>
    </RemoteDataTable>
  </Card>
</template>
