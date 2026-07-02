<script setup>
import Layout from '@/Shared/Layout.vue';
import Breadcrumbs from '@/Shared/Breadcrumbs.vue';
import Column from 'primevue/column';
import Button from 'primevue/button';
import { Head, router } from '@inertiajs/vue3';
import Card from '@/Shared/Card.vue';
import Title from '@/Shared/Title.vue';
import { useAuth } from '@/Composables/useAuth';
import RemoteDataTable from '@/Shared/DataTable/RemoteDataTable.vue';

const props = defineProps({
  items: Object,
  labels: Object,
  consumable: Object,
  consumableTypeValue: String,
});

defineOptions({
  layout: Layout,
});

const title = 'Привязка принтера';
const { can } = useAuth();

const addPrinter = (id) => {
  const url = route('dictionary.consumables.printers.store', { consumable: props.consumable.id, printer: id });
  router.post(url);
};
</script>
<template>
  <Head :title="title" />

  <Breadcrumbs
    :home="{ label: 'Главная', url: '/' }"
    :items="[
      {
        label: 'Расходные материалы (справочник)',
        url: route('dictionary.consumables.index'),
      },
      {
        label: `${consumableTypeValue} ${consumable.name}`,
        url: route('dictionary.consumables.show', { consumable: consumable.id }),
      },
      { label: title },
    ]"
  />

  <Card>
    <Title>{{ title }}</Title>

    <RemoteDataTable
      :model="items"
      :url="route('dictionary.consumables.printers.index', { consumable: props.consumable.id })"
      selection-mode="single"
      data-key="id"
    >
      <template #header>
        <Button
          type="button"
          severity="secondary"
          @click="router.get(route('dictionary.consumables.show', { consumable: consumable.id }))"
        >
          <i class="fas fa-chevron-circle-left me-3" />
          Назад
        </Button>
      </template>
      <Column header="#" field="id" header-style="width:3rem" />
      <Column field="vendor" header="Производитель" sortable />
      <Column field="model" header="Модель" sortable />
      <Column field="is_color_print" header="Цветная печать" sortable>
        <template #body="{ data }">
          {{ data.is_color_print ? 'Да' : 'Нет' }}
        </template>
      </Column>
      <Column v-if="can('admin', 'editor-dictionary')">
        <template #body="{ data }">
          <Button @click="addPrinter(data.id)">
            <i class="pi pi-check" />
            Выбрать
          </Button>
        </template>
      </Column>

      <template #empty>
        Нет данных
      </template>
    </RemoteDataTable>
  </Card>
</template>
