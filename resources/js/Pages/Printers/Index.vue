<script setup>
import { Head, router } from '@inertiajs/vue3';
import Layout from '@/Shared/Layout.vue';
import { ref } from 'vue';
import Breadcrumbs from '@/Shared/Breadcrumbs.vue';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Badge from 'primevue/badge';
import { useAuth } from '@/Composables/useAuth';
import Card from '@/Shared/Card.vue';
import PrinterWorkplace from '@/Shared/DataTable/PrinterWorkplace.vue';
import Title from '@/Shared/Title.vue';
import RemoteDataTable from '@/Shared/DataTable/RemoteDataTable.vue';
import Timestamps from '@/Shared/DataTable/Timestamps.vue';


defineProps({
  items: {
    type: Object,
    required: true,
  },
  query: {
    type: Object,
  },
  printerWorkplaceLabels: {
    type: Object,
    required: true,
  },
  cartridgeColors: {
    type: Object,
    required: true,
  },
  consumableTypes: {
    type: Object,
    required: true,
  },
});

defineOptions({
  layout: Layout
});

const { can } = useAuth();

const onRowSelect = (event) => {
  actions.show(event.data.id);
};

const title = 'Принтеры';

const actions = {
  create: () => router.get(route('workplace.create')),
  show: (id) => router.get(route('workplace.show', { workplace: id })),
};

const showConsumables = ref({});
const toggleConsumable = (id) => {
  showConsumables.value[id] = !showConsumables.value[id];
};

</script>
<template>
  <Head :title="title" />

  <Breadcrumbs
    :home="{ label: 'Главная', url: route('home') }"
    :items="[{ label: title }]"
  />

  <Card>
    <Title>{{ title }}</Title>

    <RemoteDataTable
      :model="items"
      :url="route('workplace.index')"
      data-key="id"
      selection-mode="single"
      @row-select="onRowSelect"
    >
      <template #header>
        <div class="flex justify-between">
          <div>
            <Button v-if="can('admin', 'editor-printer-workplace')" severity="info" @click="actions.create">
              Добавить принтер
            </Button>
          </div>
        </div>
      </template>
      <Column header="#" field="id" header-style="width:3rem" />
      <Column field="printer.vendor" :header="printerWorkplaceLabels.id_printer" sortable>
        <template #body="{ data: { printer} }">
          <PrinterWorkplace
            :vendor="printer.vendor"
            :model="printer.model"
            :is-color-print="printer.is_color_print"
          />
        </template>
      </Column>
      <Column header="Расходные материалы">
        <template #body="{ data }">
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
              <div v-for="consumable in data.printer?.consumables" :key="consumable.id" class="flex">
                <div class="content-center w-12">
                  <div v-if="consumable?.consumableCountCurrentOrganization">
                    <Badge
                      :value="consumable?.consumableCountCurrentOrganization?.count"
                      :severity="consumable?.consumableCountCurrentOrganization?.count <= 1 ? 'danger'
                        : (consumable?.consumableCountCurrentOrganization?.count < 10 ? 'warning' : 'success')"
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
      </Column>
      <Column field="location" :header="printerWorkplaceLabels.location" sortable />
      <Column field="serial_number" :header="printerWorkplaceLabels.serial_number" sortable />
      <Column field="inventory_number" :header="printerWorkplaceLabels.inventory_number" sortable />
      <Column field="created_at" :header="printerWorkplaceLabels.date" sortable>
        <template #body="{ data }">
          <Timestamps :created-at="data.created_at" :updated-at="data.updated_at" />
        </template>
      </Column>

      <template #empty>
        Нет данных
      </template>
    </RemoteDataTable>
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
