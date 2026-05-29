<script setup>
import { DataTable, IconField, InputIcon, InputText } from 'primevue';
import { reactive, ref, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { debounce, pickBy } from 'lodash';

const { url, model } = defineProps({
  url: {
    type: String,
    required: true,
    default: '',
  },
  model: {
    type: Object,
    default: () => ({
      data: [],
      total: 0,
      current_page: 1,
      per_page: 10,
    }),
    required: true,
  },
  withSearch: {
    type: Boolean,
    default: true,
  },
});

const loading = ref(false);
const query = ref({});
const pageProps = usePage().props;

const form = reactive({
  search: query.value?.search || pageProps.query?.search || '',
  page: 1,
  sortField: query.value.sortField || pageProps.query?.sortField || null,
  sortOrder: pageProps.query?.sortOrder ? (pageProps.query.sortOrder === 'asc' ? 1 : -1) : null,
});

const update = () => {
  router.get(url, pickBy(form), {
    preserveState: true,
    replace: true,
    onStart: () => loading.value = true,
    onFinish: () => {
      loading.value = false;
      query.value = pageProps.query || {};
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
  <DataTable
    v-bind="$attrs"
    lazy
    paginator
    :value="model.data"
    :loading="loading"
    :rows="model.per_page"

    :total-records="model.total"
    :first="(model.current_page - 1) * model.per_page"
    :sort-field="form.sortField"
    :sort-order="form.sortOrder"

    @page="onLazyChange($event)"
    @sort="onLazyChange($event)"
  >
    <template #header>
      <div class="flex justify-between">
        <div>
          <slot name="header" />
        </div>
        <IconField v-if="withSearch" icon-position="left" class="w-72">
          <InputIcon>
            <i class="pi pi-search" />
          </InputIcon>
          <InputText v-model="form.search" placeholder="Поиск" />
        </IconField>
      </div>
    </template>
    <slot />
  </DataTable>
</template>
