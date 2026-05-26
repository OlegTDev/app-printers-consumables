<script setup>
import Layout from '@/Shared/Layout.vue';
import { watch, reactive, ref } from 'vue';
import Breadcrumbs from '@/Shared/Breadcrumbs.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import { Head, Link, router } from '@inertiajs/vue3';
import InputText from 'primevue/inputtext';
import pickBy from 'lodash/pickBy';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import Card from '@/Shared/Card.vue';
import Title from '@/Shared/Title.vue';
import { useAuth } from '@/Composables/useAuth';
import Author from '@/Shared/DataTable/Author.vue';
import Timestamps from '@/Shared/DataTable/Timestamps.vue';
import { debounce } from 'lodash';

const props = defineProps({
  consumables: Object,
  labels: Object,
  filters: Object,
  consumableTypes: Object,
  cartridgeColors: Object,
  totalRecords: Number,
  currentPage: Number,
  firstPage: Number,
});

defineOptions({
  layout: Layout
});

const title = 'Расходные материалы (справочник)';
const { can } = useAuth();

const loading = ref(false);
const selectedRow = ref({});
const filters = reactive(props.filters);
const form = reactive({
  search: filters.search,
  page: 1,
  sortField: null,
  sortOrder: null,
});

const updateFilters = () => {
  const url = route('dictionary.consumables.index');
  router.get(url, pickBy(form), {
    preserveState: true,
    replace: true,
    onStart: () => loading.value = true,
    onFinish: () => loading.value = false,
  });
};

watch(
  () => form.search,
  debounce(() => {
    form.page = 1;
    updateFilters();
  }, 300)
);

const onRowSelect = (event) => {
  const url = route('dictionary.consumables.show', { consumable: event.data.id });
  router.get(url);
};

const refTableConsumablesDic = ref(null);

const onLazyChange = (event) => {
  form.page = event.page + 1;
  form.sortField = event.sortField;
  form.sortOrder = event.sortOrder;
  updateFilters();
};

</script>
<template>
  <Head :title="title" />

  <Breadcrumbs
    :home="{ label: 'Главная', url: route('dashboard') }"
    :items="[
      { label: title },
    ]"
  />

  <Card>
    <Title>{{ title }}</Title>

    <DataTable
      ref="refTableConsumablesDic"
      v-model:selection="selectedRow"
      lazy
      :loading="loading"
      :value="consumables.data"
      paginator
      :rows="consumables.per_page"
      :total-records="consumables.total"
      :first="(consumables.current_page - 1) * consumables.per_page"
      data-key="id"
      :meta-key-selection="false"
      table-style="min-width: 50rem"
      selection-mode="single"
      @row-select="onRowSelect"
      @page="onLazyChange($event)"
      @sort="onLazyChange($event)"
    >
      <template #header>
        <div class="flex justify-between">
          <div>
            <Link v-if="can('admin', 'editor-dictionary')" :href="route('dictionary.consumables.create')">
              <Button type="button" severity="info">
                Добавить расходный материал
              </Button>
            </Link>
          </div>

          <IconField icon-position="left" class="w-72">
            <InputIcon>
              <i class="pi pi-search" />
            </InputIcon>
            <InputText v-model="form.search" placeholder="Поиск" />
          </IconField>
        </div>
      </template>
      <Column header="#" header-style="width:3rem">
        <template #body="{ data }">
          {{ data.id }}
        </template>
      </Column>
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
          <Timestamps :created_at="data.created_at" :updated_at="data.updated_at" />
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
    </DataTable>
  </Card>
</template>
