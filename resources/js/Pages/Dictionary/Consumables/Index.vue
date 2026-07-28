<script setup>
import Layout from '@/Shared/Layout.vue';
import { ref } from 'vue';
import Breadcrumbs from '@/Shared/Breadcrumbs.vue';
import Column from 'primevue/column';
import Button from 'primevue/button';
import { Head, router } from '@inertiajs/vue3';
import Card from '@/Shared/Card.vue';
import Title from '@/Shared/Title.vue';
import { useAuth } from '@/Composables/useAuth';
import Author from '@/Shared/DataTable/Author.vue';
import Timestamps from '@/Shared/DataTable/Timestamps.vue';
import RemoteDataTable from '@/Shared/DataTable/RemoteDataTable.vue';

defineProps({
  items: Object,
  labels: Object,
  consumableTypes: Object,
  cartridgeColors: Object,
});

defineOptions({
  layout: Layout
});

const title = 'Расходные материалы';
const { can } = useAuth();
const onRowSelect = (event) => {
  const url = route('dictionary.consumables.show', { consumable: event.data.id });
  router.get(url);
};
const refTableConsumablesDic = ref(null);

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

    <RemoteDataTable
      ref="refTableConsumablesDic"
      :model="items"
      :url="route('dictionary.consumables.index')"
      selection-mode="single"
      @row-select="onRowSelect"
    >
      <template #header>
        <Button
          v-if="can('admin', 'editor-dictionary')"
          type="button"
          severity="info"
          @click="router.get(route('dictionary.consumables.create'))"
        >
          Добавить расходный материал
        </Button>
      </template>
      <Column header="#" field="id" header-style="width:3rem" sortable />
      <Column field="type" :header="labels.type" sortable>
        <template #body="{ data }">
          {{ consumableTypes[data.type] ?? data.type }}
        </template>
      </Column>
      <Column field="name" :header="labels.name" sortable>
        <template #body="{ data }">
          <div class="grid grid-rows-2 gap-4">
            <div>
              {{ data.name }}
            </div>
            <div v-if="data.type === 'cartridge'">
              <div class="flex">
                <div class="rounded-full size-4 mr-2" :class="[cartridgeColors[data.color]?.bg]" />
                <div>
                  {{ cartridgeColors[data.color]?.name }}
                </div>
              </div>
            </div>
          </div>
        </template>
      </Column>
      <Column field="description" :header="labels.description" sortable />
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
