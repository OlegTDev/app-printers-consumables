<script setup>
import { computed, reactive, ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import Layout from '@/Shared/Layout.vue';
import InputText from 'primevue/inputtext';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import Breadcrumbs from '@/Shared/Breadcrumbs.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import pickBy from 'lodash/pickBy';
import throttle from 'lodash/throttle';
import Card from '@/Shared/Card.vue';
import Title from '@/Shared/Title.vue';
import { useAuth } from '@/Composables/useAuth';
import { Select } from 'primevue';

defineOptions({
  layout: Layout,
});

const props = defineProps({
  consumablesCounts: Array,
  consumableLabels: Object,
  consumableCountLabels: Object,
  filters: Object,
  consumableTypes: Object,
  cartridgeColors: Object,
});

const { can } = useAuth();

const title = 'Количество расходных материалов';

const form = reactive({
  search: props.filters.search || '',
  consumableType: props.filters.consumableType,
});

const consumableTypesDropdown = computed(() => Object.keys(props.consumableTypes || {}).map(key => ({
  value: key,
  name: props.consumableTypes[key],
})));

watch(
  () => form,
  throttle(() => {
    router.get(route('consumables.counts.index'), pickBy(form), {
      preserveState: true,
      preserveScroll: true,
    });
  }, 300),
  { deep: true }
);

const actions = {
  create: () => router.get(route('consumables.counts.create')),
  show: (event) => router.get(route('consumables.counts.show', { count: event.data.id })),
};

const refTableConsumableCount = ref(null);

const onPageChange = () => {
  const elementTableConsumableCount = refTableConsumableCount.value.$el;
  if (elementTableConsumableCount) {
    elementTableConsumableCount.scrollIntoView({
      behavior: 'smooth',
      block: 'start',
    });
  }
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

    <DataTable
      ref="refTableConsumableCount"
      :value="consumablesCounts"
      paginator
      :rows="10"
      data-key="id"
      :meta-key-selection="false"
      table-style="min-width: 50rem"
      selection-mode="single"
      @row-select="actions.show"
      @page="onPageChange"
    >
      <template #header>
        <div class="flex justify-between">
          <div>
            <Button
              v-if="can('admin', 'add-consumables')"
              severity="info"
              @click="actions.create"
            >
              Добавить
            </Button>
          </div>
          <div class="flex">
            <IconField icon-position="left" class="w-72">
              <InputIcon>
                <i class="pi pi-search" />
              </InputIcon>
              <InputText v-model="form.search" placeholder="Поиск" />
            </IconField>

            <Select
              v-model="form.consumableType"
              class="w-72"
              show-clear
              :options="consumableTypesDropdown"
              option-label="name"
              option-value="value"
              :placeholder="consumableLabels.type"
            />
          </div>
        </div>
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
    </DataTable>
  </Card>
</template>
