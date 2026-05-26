<script setup>
import { defineAsyncComponent, onMounted, ref } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import TableTitle from '@/Shared/TableTitle.vue';
import Badge from 'primevue/badge';
import Button from 'primevue/button';
import { useDialog } from 'primevue/usedialog';
import fetchService from '@/Services/fetchService';
import { useAuth } from '@/Composables/useAuth';
import { useNotification } from '@/Composables/useNotification';
import { useDate } from '@/Composables/useDate';
import { useUser } from '@/Composables/useUser';

const { can } = useAuth();
const { formatDate, fromNow } = useDate();
const { fullUserInfo } = useUser();
const dialog = useDialog();
const { showError } = useNotification();

const consumables = ref([]);
const cartridgeColors = ref({});
const consumableTypes = ref({});
const loading = ref(false);

const updateData = async () => {
  try {
    loading.value = true;
    const url = route('consumables.counts.installed.last');
    const data = await fetchService.fetch(url);
    consumables.value = data.data.map(item => ({
      ...item,
      author: fullUserInfo(item.author),
    }));
    cartridgeColors.value = data.cartridgeColors;
    consumableTypes.value = data.consumableTypes;
  } catch (e) {
    showError(e.response?.data?.message || e.message || 'Ошибка загрузки данных');
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  updateData();
});

const title = `Установленные расходные материалы`;

const InstalledDialog = defineAsyncComponent(
  () =>
    import('@/Pages/Consumable/InstallConsumableDialog/InstallConsumableDialog.vue')
);

const btnInstalledDialog = () => {
  dialog.open(InstalledDialog, {
    props: {
      header: 'Вычесть расходный материал',
      style: {
        width: '50vw',
      },
      breakpoints: {
        '960px': '75vw',
        '640px': '90vw',
      },
      modal: true,
    },
    onClose: (options) => {
      if (options.data?.updated) {
        updateData();
      }
    },
  });
};

const fieldPrinter = (item) => {
  const p = item.printer_workplace?.printer;
  return p ? `${p.vendor} ${p.model}` : 'Принтер не указан';
};
</script>
<template>
  <DataTable
    :value="consumables"
    :loading="loading"
    paginator
    :rows="3"
    data-key="id"
    class="w-full rounded shadow h-120 bg-white"
  >
    <template #header>
      <div class="flex justify-between">
        <TableTitle>{{ title }}</TableTitle>
        <Button
          v-if="can('admin', 'subtract-consumable')"
          label="Вычесть расходный материал"
          icon="pi pi-minus-circle"
          severity="danger"
          size="small"
          @click="btnInstalledDialog"
        />
      </div>
    </template>
    <Column header="Расходный материал" sortable field="count">
      <template #body="{ data }">
        <div class="grid grid-rows-2 gap-2">
          <div class="text-nowrap">
            <Badge
              v-tooltip="`Количество`"
              :value="data.count"
              severity="success"
              size="large"
              class="me-2"
            />
            {{ consumableTypes[data.consumable_count?.consumable?.type] }}
            {{ data.consumable_count?.consumable?.name }}
          </div>
          <div v-if="data.consumable_count?.consumable?.type == 'cartridge'">
            <div class="flex">
              <div
                :class="[
                  'rounded-full',
                  'size-4',
                  'mr-2',
                  cartridgeColors[data.consumable_count?.consumable?.color]?.bg,
                ]"
              />
              <div>
                {{ cartridgeColors[data.consumable_count?.consumable?.color]?.name }}
              </div>
            </div>
          </div>
        </div>
      </template>
    </Column>
    <Column header="Принтер" :field="fieldPrinter" sortable>
      <template #body="{ data }">
        <div
          v-tooltip="`Серийный номер: ${data.printer_workplace?.serial_number}, инвентарный номер: ${data.printer_workplace?.inventory_number}`"
          class="grid grid-rows-2 gap-2"
          placeholder="Bottom"
        >
          <div>
            <i class="pi pi-print" />
            {{ data.printer_workplace?.printer?.vendor }}
            {{ data.printer_workplace?.printer?.model }}
          </div>
          <div>{{ data.printer_workplace?.location }} кабинет</div>
        </div>
      </template>
    </Column>
    <Column header="Исполнитель">
      <template #body="{ data: { author } }">
        <!-- {{ data.author?.fio ?? data.author?.name }} -->
        <div class="grid gap-y-1">
          <span class="font-medium">
            {{ author.fio }}
            <span v-if="author.name">
              ({{ author.name }})
            </span>
          </span>
          <span v-if="author.post" class="text-xs text-gray-500">
            {{ author.post }}
          </span>
          <span v-if="author.department" class="text-xs text-gray-500">
            {{ author.department }}
          </span>
          <span v-if="author.telephone" class="text-xs text-gray-500">
            {{ author.telephone }}
          </span>
        </div>
      </template>
    </Column>
    <Column header="Дата" field="created_at" sortable>
      <template #body="{ data }">
        <div class="grid gap-y-2">
          <div>{{ fromNow(data.created_at) }}</div>
          <div>{{ formatDate(data.created_at) }}</div>
        </div>
      </template>
    </Column>
  </DataTable>
</template>
