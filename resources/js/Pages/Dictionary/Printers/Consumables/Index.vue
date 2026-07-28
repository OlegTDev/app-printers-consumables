<script setup>
import { Head, router } from '@inertiajs/vue3';
import Layout from '@/Shared/Layout.vue';
import Breadcrumbs from '@/Shared/Breadcrumbs.vue';
import Column from 'primevue/column';
import Button from 'primevue/button';
import { useAuth } from '@/Composables/useAuth';
import Title from '@/Shared/Title.vue';
import Card from '@/Shared/Card.vue';
import RemoteDataTable from '@/Shared/DataTable/RemoteDataTable.vue';

const props = defineProps({
  printer: Object,
  items: Object,
  consumableTypes: Object,
  consumableLabels: Object,
  cartridgeColors: Object,
});

defineOptions({
  layout: Layout
});

const title = `Привязка расходного материала`;
const { can } = useAuth();

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

    <RemoteDataTable
      :model="items"
      :url="route('dictionary.printers.consumables.index', { printer: props.printer.id })"
      selection-mode="single"
    >
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
    </RemoteDataTable>
  </Card>
</template>
