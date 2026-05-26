<script setup>
import Layout from '@/Shared/Layout.vue';
import { watch, reactive, ref } from 'vue';
import Breadcrumbs from '@/Shared/Breadcrumbs.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import { Head, router } from '@inertiajs/vue3';
import InputText from 'primevue/inputtext';
import pickBy from 'lodash/pickBy';
import debounce from 'lodash/debounce';
import Card from '@/Shared/Card.vue';
import Title from '@/Shared/Title.vue';
import { useAuth } from '@/Composables/useAuth';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';

const props = defineProps({
  printers: Object,
  labels: Object,
  filters: Object,
  consumable: Object,
  consumableTypeValue: String,
});

defineOptions({
  layout: Layout,
});

const title = 'Привязка принтера';
const { can } = useAuth();

const loadingForm = ref(false);
const form = reactive({
  search: props.filters?.search,
});
watch(
  () => form.search,
  debounce(() => {
    const url = route('dictionary.consumables.printers.index', { consumable: props.consumable.id });
    router.get(url, pickBy(form),
      {
        preserveState: true,
        onStart: () => loadingForm.value = true,
        onFinish: () => loadingForm.value = false,
      }
    );
  }, 300)
);

const loadingAddPrinter = ref(false);
const addPrinter = (id) => {
  loadingAddPrinter.value = true;
  const url = route('dictionary.consumables.printers.add', { consumable: props.consumable.id, printer: id });
  router.post(url, {}, {
    onFinish: () => loadingAddPrinter.value = false,
  });
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

    <DataTable
      :value="printers"
      paginator
      :rows="10"
      data-key="id"
      :meta-key-selection="false"
      table-style="min-width: 50rem"
      selection-mode="single"
      :loading="loadingForm"
    >
      <template #header>
        <div class="flex justify-between">
          <div>
            <Button
              v-if="can('admin', 'editor-dictionary')"
              type="button"
              severity="secondary"
              @click="router.get(route('dictionary.consumables.show', { consumable: consumable.id }))"
            >
              <i class="fas fa-chevron-circle-left me-3" />
              Назад
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
      <Column v-if="can('admin', 'editor-dictionary')">
        <template #body="{ data }">
          <Button :disabled="loadingAddPrinter" @click="addPrinter(data.id)">
            <i class="pi pi-check" />
            Выбрать
          </Button>
        </template>
      </Column>

      <template #empty>
        Нет данных
      </template>
    </DataTable>
  </Card>
</template>
