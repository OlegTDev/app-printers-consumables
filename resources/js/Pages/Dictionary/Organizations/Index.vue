<script setup>
import { Head, router } from '@inertiajs/vue3';
import Layout from '@/Shared/Layout.vue';
import { reactive, ref, watch } from 'vue';
import Breadcrumbs from '@/Shared/Breadcrumbs.vue';
import Column from 'primevue/column';
import Button from 'primevue/button';
import TreeTable from 'primevue/treetable';
import Card from '@/Shared/Card.vue';
import Title from '@/Shared/Title.vue';
import Timestamps from '@/Shared/DataTable/Timestamps.vue';
import { pickBy } from 'lodash';
import { debounce } from 'lodash';
import { IconField, InputIcon, InputText } from 'primevue';

const props = defineProps({
  items: Object,
  labels: Object,
  query: Object,

  filters: Object,
  organizations: Object,
});

defineOptions({
  layout: Layout
});

const title = 'Организации';
const selectedRow = ref({});
const queryRef = ref({});
const loading = ref(false);

const onRowSelect = (event) => {
  router.get(route('dictionary.organizations.show', { organization: event.data.code }));
};

const form = reactive({
  search: queryRef.value?.search || props.query?.search || '',
  page: 1,
  sortField: queryRef.value.sortField || props.query?.sortField || null,
  sortOrder: props.query?.sortOrder ? (props.query.sortOrder === 'asc' ? 1 : -1) : null,
});

const update = () => {
  router.get(route('dictionary.organizations.index'), pickBy(form), {
    preserveState: true,
    replace: true,
    onStart: () => loading.value = true,
    onFinish: () => {
      loading.value = false;
      queryRef.value = props.query || {};
    },
  });
};

watch(
  () => form.search,
  debounce(() => {
    form.page = 1;
    update();
  }, 300)
);

const onLazyChange = (event) => {
  form.page = event.page + 1;
  form.sortField = event.sortField;
  form.sortOrder = event.sortOrder;
  update();
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

      :value="items.data"
      :loading="loading"

      :total-records="items.total"
      :first="(items.current_page - 1) * items.per_page"
      :sort-field="form.sortField"
      :sort-order="form.sortOrder"

      selection-mode="single"
      table-style="min-width: 50rem"
      @node-select="onRowSelect"
      @page="onLazyChange($event)"
      @sort="onLazyChange($event)"
    >
      <template #header>
        <div class="flex justify-between">
          <Button type="button" severity="info" @click="router.get(route('dictionary.organizations.create'))">
            Добавить организацию
          </Button>
          <IconField icon-position="left" class="w-72">
            <InputIcon>
              <i class="pi pi-search" />
            </InputIcon>
            <InputText v-model="form.search" placeholder="Поиск" />
          </IconField>
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
