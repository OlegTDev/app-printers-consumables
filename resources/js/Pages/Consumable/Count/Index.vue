<script setup>
import { computed, reactive } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import Layout from '@/Shared/Layout.vue';
import Breadcrumbs from '@/Shared/Breadcrumbs.vue';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Card from '@/Shared/Card.vue';
import Title from '@/Shared/Title.vue';
import { Select } from 'primevue';
import RemoteDataTable from '@/Shared/DataTable/RemoteDataTable.vue';
import { useAuth } from '@/Composables/useAuth';

defineOptions({
  layout: Layout,
});

const props = defineProps({
  items: Object,
  query: Object,
  consumableLabels: Object,
  consumableCountLabels: Object,
  consumableTypes: Object,
  cartridgeColors: Object,
});

const { can } = useAuth();

const title = 'Количество расходных материалов';

const form = reactive({
  consumableType: props.query?.consumableType,
});

const consumableTypesDropdown = computed(() => Object.keys(props.consumableTypes || {}).map(key => ({
  value: key,
  name: props.consumableTypes[key],
})));

const actions = {
  create: () => router.get(route('consumables.counts.create')),
  show: (event) => router.get(route('consumables.counts.show', { count: event.data.id })),
};
</script>
<template>
  <Head :title="title" />

  <Breadcrumbs
    :home="{ label: 'Главная', url: route('dashboard') }"
    :items="[{ label: title }]"
  />

  <Card>
    <Title>{{ title }}</Title>

    <RemoteDataTable
      :url="route('consumables.counts.index')"
      :model="items"
      selection-mode="single"
      :filters="form"
      @row-select="actions.show"
    >
      <template #header>
        <Button v-if="can('admin', 'add-consumables')" severity="info" @click="actions.create">
          Добавить
        </Button>
      </template>
      <template #filters>
        <Select
          v-model="form.consumableType"
          class="w-72"
          show-clear
          :options="consumableTypesDropdown"
          option-label="name"
          option-value="value"
          :placeholder="consumableLabels.type"
        />
      </template>
      <Column header="#" field="id" header-style="width:3rem" />
      <Column :header="consumableLabels.type" field="consumable.type">
        <template #body="{ data }">
          {{ consumableTypes[data.consumable.type] }}
        </template>
      </Column>
      <Column :header="consumableLabels.name" field="consumable.name">
        <template #body="{ data }">
          <div class="grid grid-rows-2 gap-2">
            <div>
              {{ data.consumable.name }}
            </div>
            <div v-if="data.consumable.type === 'cartridge'">
              <div class="flex">
                <div
                  :class="[
                    'rounded-full',
                    'size-4',
                    'mr-2',
                    cartridgeColors[data.consumable.color]?.bg,
                  ]"
                />
                <div>
                  {{ cartridgeColors[data.consumable.color]?.name }}
                </div>
              </div>
            </div>
          </div>
        </template>
      </Column>
      <Column :header="consumableCountLabels.count" field="count">
        <template #body="{ data }">
          <i class="pi pi-box text-primary-600" />
          <span class="font-bold ms-2"> {{ data.count }} шт. </span>
        </template>
      </Column>
      <template #empty>
        Нет данных
      </template>
    </RemoteDataTable>
  </Card>
</template>
