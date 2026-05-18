<script setup>
import { Head, router } from '@inertiajs/vue3';
import Layout from '@/Shared/Layout.vue';
import { watch, reactive, ref } from 'vue';
import Breadcrumbs from '@/Shared/Breadcrumbs.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import pickBy from 'lodash/pickBy';
import throttle from 'lodash/throttle';
import { useConfig } from '@/Composables/useConfig';
import Card from '@/Shared/Card.vue';
import Title from '@/Shared/Title.vue';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import Timestamps from '@/Shared/DataTable/Timestamps.vue';
import Author from '@/Shared/DataTable/Author.vue';
import { useAuth } from '@/Composables/useAuth';

const props = defineProps({
    printers: Object,
    labels: Object,
    filters: Object,
});

defineOptions({
    layout: Layout
});

const title = 'Принтеры (справочник)';

const { urls } = useConfig();
const { can } = useAuth();

const selectedRow = ref();
const filters = reactive(props.filters);
const form = reactive({
  search: filters.search,
});

watch(
  () => form,
  throttle(() => {
    router.get(urls.dictionary.printers.index(), pickBy(form), { preserveState: true });
  }, 150),
  { deep: true }
);

const onRowSelect = (event) => {
  router.get(urls.dictionary.printers.show(event.data.id));
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
    :home="{ label: 'Главная', url: '/' }"
    :items="[
      { label: title },
    ]"
  />

  <Card>
    <Title>{{ title }}</Title>

    <DataTable
      ref="refTablePrintersDic"
      v-model:selection="selectedRow"
      :value="printers"
      paginator
      :rows="10"
      data-key="id"
      :meta-key-selection="false"
      table-style="min-width: 50rem"
      selection-mode="single"
      @row-select="onRowSelect"
      @page="onPageChange"
    >
      <template #header>
        <div class="flex justify-between mt-5">
          <div>
            <Button
              v-if="can('admin', 'editor-dictionary')"
              type="button"
              severity="info"
              @click="router.get(urls.dictionary.printers.create())"
            >
              Добавить принтер
            </Button>
          </div>

          <IconField icon-position="left" class="w-72">
            <InputIcon>
              <i class="pi pi-search" />
            </InputIcon>
            <InputText v-model="form.search" placeholder="Поиск" />
          </IconField>
        </div>
      </template>
      <Column header="#" field="id" header-style="width:3rem" />
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
    </DataTable>
  </Card>
</template>
