<script setup>
import { Head, router } from '@inertiajs/vue3';
import Layout from '@/Shared/Layout';
import { watch, ref } from 'vue';
import Breadcrumbs from '@/Shared/Breadcrumbs';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import TableTitle from '@/Shared/TableTitle';
import InputText from 'primevue/inputtext';
import pickBy from 'lodash/pickBy';
import Badge from 'primevue/badge';
import { useConfig } from '@/Composables/useConfig';
import { useDate } from '@/Composables/useDate';
import { useAuth } from '@/Composables/useAuth';
import Card from '@/Shared/Card.vue';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import PrinterWorkplace from '@/Shared/DataTable/PrinterWorkplace.vue';
import debounce from 'lodash/debounce';
import Skeleton from 'primevue/skeleton';

const props = defineProps({
  printersWorkplace: {
    type: Array,
    required: true,
  },
  printerWorkplaceLabels: {
    type: Object,
    required: true,
  },
  filters: {
    type: Object,
    required: false,
  },
  cartridgeColors: {
    type: Object,
    required: false,
  },
  consumableTypes: {
    type: Object,
    required: false,
  },
});

defineOptions({
  layout: Layout
});

const { urls } = useConfig();
const { fromNow, formatDate } = useDate();
const { can } = useAuth();

const selectedRow = ref({});

const form = ref({
  search: props.filters?.search,
});

watch(
  () => form.value,
  debounce(() => {
    router.get(urls.printers.index(), pickBy(form.value), {
      preserveState: true,
      onStart: () => {
        loading.value = true;
      },
      onFinish: () => {
        loading.value = false;
      },
    });
  }, 150),
  { deep: true }
);

const refTablePrintersWorkplace = ref(null);
const loading = ref(false);

const onPageChange = () => {
  const elementTablePrintersWorkplace = refTablePrintersWorkplace.value.$el;
  if (elementTablePrintersWorkplace) {
    elementTablePrintersWorkplace.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }
};
const onRowSelect = (event) => {
  actions.show(event.data.id);
};

const title = 'Принтеры';

const actions = {
  create: () => router.get(urls.printers.create()),
  show: (id) => router.get(urls.printers.show(id)),
};

const showConsumables = ref({});
const toggleConsumable = (id) => {
  showConsumables.value[id] = !showConsumables.value[id];
};

</script>
<template>
  <Head :title="title" />

  <Breadcrumbs
    :home="{ label: 'Главная', url: '/' }"
    :items="[{ label: title }]"
  />

  <Card :padding-body-classes="['p-5']">
    <DataTable
      ref="refTablePrintersWorkplace"
      v-model:selection="selectedRow"
      :value="printersWorkplace"
      paginator
      :rows="10"
      data-key="id"
      :meta-key-selection="false"
      table-style="min-width: 50rem"
      selection-mode="single"
      @page="onPageChange"
      @row-select="onRowSelect"
    >
      <template #header>
        <TableTitle class="border-b border-gray-200 pb-2">
          {{ title }}
        </TableTitle>
        <div class="flex justify-between mt-5">
          <div>
            <Button v-if="can('admin', 'editor-printer-workplace')" severity="info" @click="actions.create">
              Добавить принтер
            </Button>
          </div>
          <IconField icon-position="left" class="ml-3">
            <InputIcon><i class="pi pi-search" /></InputIcon>
            <InputText v-model="form.search" placeholder="Поиск" />
          </IconField>
        </div>
      </template>

      <Column header="#" field="id" header-style="width:3rem">
        <template #body="{ data }">
          <Skeleton v-if="loading" />
          <template v-else>
            {{ data.id }}
          </template>
        </template>
      </Column>
      <Column field="printer.vendor" :header="printerWorkplaceLabels.id_printer" sortable>
        <template #body="{ data: { printer} }">
          <Skeleton v-if="loading" />
          <PrinterWorkplace
            v-else
            :vendor="printer.vendor"
            :model="printer.model"
            :is-color-print="printer.is_color_print"
          />
        </template>
      </Column>
      <Column header="Расходные материалы">
        <template #body="{ data }">
          <Skeleton v-if="loading" />
          <template v-else>
            <Button size="small" severity="secondary" outlined @click.stop="toggleConsumable(data.id)">
              <template v-if="showConsumables[data.id] !== true">
                <i class="pi pi-angle-double-down" />&nbsp;Развернуть
              </template>
              <template v-else>
                <i class="pi pi-angle-double-up" />&nbsp;Свернуть
              </template>
            </Button>
            <Transition name="fade">
              <div
                v-if="showConsumables[data.id]"
                class="grid gap-y-2 divide-y divide-slate-300 divide-dashed text-sm mt-4"
                @click.stop
              >
                <div v-for="consumable in data?.printer?.consumables_deep" :key="consumable.id" class="flex">
                  <div class="content-center w-12">
                    <div v-if="consumable?.consumable_count">
                      <Badge
                        :value="consumable?.consumable_count?.count"
                        :severity="consumable?.consumable_count?.count <= 1 ? 'danger'
                          : (consumable?.consumable_count?.count < 10 ? 'warning' : 'success')"
                      />
                    </div>
                    <div v-else>
                      <Badge :value="0" severity="danger" />
                    </div>
                  </div>
                  <div>
                    <div>
                      {{ consumableTypes[consumable?.type] ?? consumable?.type }}
                    </div>
                    <div>
                      {{ consumable?.name }}
                    </div>
                    <div v-if="consumable?.type === 'cartridge'">
                      <div class="flex">
                        <div :class="['rounded-full', 'size-4', 'mr-2', cartridgeColors[consumable?.color]?.bg]" />
                        <div>
                          {{ cartridgeColors[consumable?.color]?.name }}
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </Transition>
          </template>
        </template>
      </Column>
      <Column field="location" :header="printerWorkplaceLabels.location" sortable>
        <template #body="{ data }">
          <Skeleton v-if="loading" />
          <template v-else>
            {{ data.location }}
          </template>
        </template>
      </Column>
      <Column field="serial_number" :header="printerWorkplaceLabels.serial_number" sortable>
        <template #body="{ data }">
          <Skeleton v-if="loading" />
          <template v-else>
            {{ data.serial_number }}
          </template>
        </template>
      </Column>
      <Column field="inventory_number" :header="printerWorkplaceLabels.inventory_number" sortable>
        <template #body="{ data }">
          <Skeleton v-if="loading" />
          <template v-else>
            {{ data.inventory_number }}
          </template>
        </template>
      </Column>
      <Column field="created_at" :header="printerWorkplaceLabels.date" sortable>
        <template #body="{ data }">
          <Skeleton v-if="loading" />
          <div v-else class="grid grid-rows-2 gap-2">
            <div v-tooltip="`Создано: ${formatDate(data.created_at)}`">
              <i class="far fa-calendar" />
              {{ fromNow(data.created_at) }}
            </div>
            <div
              v-if="data.created_at != data.updated_at"
              v-tooltip="`Изменено: ${formatDate(data.updated_at)}`"
            >
              <i class="far fa-calendar-alt" />
              {{ fromNow(data.updated_at) }}
            </div>
          </div>
        </template>
      </Column>

      <template #empty>
        Нет данных
      </template>
    </DataTable>
  </Card>
</template>
<style scoped>
.fade-enter-active {
  transition: all 0.3s ease-out;
}

.fade-leave-active {
  transition: all 0.3s cubic-bezier(1, 0.5, 0.8, 1);
}

.fade-enter-from,
.fade-leave-to {
  transform: translateY(-20px);
  opacity: 0;
}
</style>
