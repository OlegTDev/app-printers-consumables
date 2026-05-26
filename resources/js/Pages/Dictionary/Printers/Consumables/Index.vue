<script setup>
import { Head, router } from '@inertiajs/vue3';
import Layout from '@/Shared/Layout.vue';
import { watch, reactive } from 'vue';
import Breadcrumbs from '@/Shared/Breadcrumbs.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import pickBy from 'lodash/pickBy';
import { useAuth } from '@/Composables/useAuth';
import Title from '@/Shared/Title.vue';
import Card from '@/Shared/Card.vue';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import { debounce } from 'lodash';

const props = defineProps({
  printer: Object,
  filters: Object,
  consumables: Object,
  consumableTypes: Object,
  consumableLabels: Object,
  cartridgeColors: Object,
});

defineOptions({
  layout: Layout
});

const title = `Привязка расходного материала`;
const { can } = useAuth();
const filters = reactive(props.filters);

const form = reactive({
  search: filters.search,
});
watch(
  () => form,
  debounce(() => {
    router.get(route('dictionary.printers.consumables.index', { printer: props.printer.id }), pickBy(form), { preserveState: true });
  }, 300),
  { deep: true }
);

const addConsumable = (id) => {
  const url = route('dictionary.printers.consumables.add', { printer: props.printer.id, consumable: id });
  router.post(url);
};

</script>
<template>
  <Head :title="title" />

  <Breadcrumbs
    :home="{ label: 'Главная', url: route('home') }"
    :items="[
      { label: 'Справочники', },
      { label: 'Принтеры', url: route('dictionary.printers.index') },
      { label: title },
    ]"
  />

  <Card>
    <Title>{{ title }}</Title>

    <DataTable
      :value="consumables"
      paginator
      :rows="10"
      data-key="id"
      :meta-key-selection="false"
      table-style="min-width: 50rem"
      selection-mode="single"
    >
      <template #header>
        <div class="flex justify-between">
          <div>
            <Button type="button" severity="secondary" outlined @click="router.get(route('dictionary.printers.show', { printer: printer.id }))">
              <i class="pi pi-arrow-circle-left" />
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
      <Column :header="consumableLabels.type">
        <template #body="{ data }">
          {{ consumableTypes[data.type] ?? data.type }}
        </template>
      </Column>
      <Column field="name" :header="consumableLabels.name" sortable>
        <template #body="{ data }">
          <div class="grid grid-rows-2 gap-4">
            <div>
              {{ data.name }}
            </div>
            <div v-if="data.type === 'cartridge'">
              <div class="flex">
                <div :class="['rounded-full', 'size-4', 'mr-2', props.cartridgeColors[data.color]?.bg]" />
                <div>
                  {{ props.cartridgeColors[data.color]?.name }}
                </div>
              </div>
            </div>
          </div>
        </template>
      </Column>
      <Column header="">
        <template v-if="can('admin', 'editor-dictionary')" #body="{ data }">
          <Button @click="addConsumable(data.id)">
            <i class="fas fa-check me-3" />
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
