<script setup>
import Layout from '@/Shared/Layout'
import { watch, reactive, ref, inject } from 'vue'
import Breadcrumbs from '@/Shared/Breadcrumbs'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import { Head, Link, router } from '@inertiajs/vue3'
import TableTitle from '@/Shared/TableTitle'
import InputText from 'primevue/inputtext'
import pickBy from 'lodash/pickBy'
import throttle from 'lodash/throttle'
import IconField from 'primevue/iconfield'
import InputIcon from 'primevue/inputicon'


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
const urls = inject('urls');
const moment = inject('moment');
const auth = inject('auth');

const selectedRow = ref();
const consumableTypes = ref(props.consumableTypes);
const cartridgeColors = ref(props.cartridgeColors);
const labels = ref(props.labels);
const filters = reactive(props.filters);
const form = reactive({
  search: filters.search,
  page: null,
  sortField: null,
  sortOrder: null,
});

const updateFilters = (params = {}) => {
  router.get(urls.dictionary.consumables.index(), pickBy(form), {
    preserveState: true,
    replace: true,
  });
};

watch(
  () => form,
  throttle(() => {
    updateFilters();
  }, 150),
  { deep: true }
);

const onRowSelect = (event) => {
  router.get(urls.dictionary.consumables.show(event.data.id));
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

  <Breadcrumbs :home="{ label: 'Главная', url: '/' }" :items="[
    { label: title },
  ]" />

  <div class="flex justify-stretch bg-white rounded-md shadow overflow-hidden mt-4">
    <DataTable
      lazy
      :value="consumables.data"
      paginator
      :rows="consumables.per_page"
      :totalRecords="consumables.total"
      :first="(consumables.current_page - 1) * consumables.per_page"

      ref="refTableConsumablesDic"
      v-model:selection="selectedRow"
      @rowSelect="onRowSelect"
      @page="onLazyChange($event)"
      @sort="onLazyChange($event)"
      dataKey="id"
      :metaKeySelection="false"
      class="w-full"
      tableStyle="min-width: 50rem"
      selectionMode="single"
    >
      <template #header>
        <TableTitle class="border-b border-gray-200 pb-2">{{ title }}</TableTitle>
        <div class="flex justify-between mt-5">
          <Link :href="urls.dictionary.consumables.create()" v-if="auth.can('admin', 'editor-dictionary')">
            <Button type="button" severity="info">Добавить расходный материал</Button>
          </Link>

          <IconField iconPosition="left" class="w-72">
            <InputIcon>
              <i class="pi pi-search" />
            </InputIcon>
            <InputText v-model="form.search" placeholder="Поиск" />
          </IconField>
        </div>
      </template>
      <Column header="#" headerStyle="width:3rem">
        <template #body="slotProps">
          {{ slotProps.index + 1 }}
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
                <div :class="['rounded-full', 'size-4', 'mr-2', cartridgeColors[data.color]['bg']]"></div>
                <div>
                  {{ cartridgeColors[data.color]['name'] }}
                </div>
              </div>
            </div>
          </div>
        </template>
      </Column>
      <Column field="description" :header="labels.description" sortable />
      <Column field="created_at" header="Дата" sortable>
        <template #body="{ data }">
          <div class="grid grid-rows-2 gap-2">
            <div v-tooltip="`Создано: ${moment(data.created_at).format('LLLL')}`">
              <i class="far fa-calendar"></i>
              {{ moment(data.created_at).fromNow() }}
            </div>
            <div v-if="data.created_at != data.updated_at"
              v-tooltip="`Изменено: ${moment(data.updated_at).format('LLLL')}`">
              <i class="far fa-calendar-alt"></i>
              {{ moment(data.updated_at).fromNow() }}
            </div>
          </div>
        </template>
      </Column>
      <Column field="author.email" header="Автор" />

      <template #empty> Нет данных </template>
    </DataTable>
  </div>

</template>
