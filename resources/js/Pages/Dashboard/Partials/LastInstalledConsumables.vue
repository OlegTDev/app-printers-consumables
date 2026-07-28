<script setup>
import { defineAsyncComponent, onMounted, ref } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import { useDialog } from 'primevue/usedialog';
import fetchService from '@/Services/fetchService';
import { useAuth } from '@/Composables/useAuth';
import { useNotification } from '@/Composables/useNotification';
import { useUser } from '@/Composables/useUser';
import Title from '@/Shared/Title.vue';
import Author from '@/Shared/DataTable/Author.vue';
import Timestamps from '@/Shared/DataTable/Timestamps.vue';

const { can } = useAuth();
const { fullUserInfo } = useUser();
const dialog = useDialog();
const { showError } = useNotification();

const consumables = ref([]);
const cartridgeColors = ref({});
const consumableTypes = ref({});
const loading = ref(false);

const emit = defineEmits(['consumable:install']);

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
  () => import('@/Pages/Consumable/InstallConsumableDialog/InstallConsumableDialog.vue')
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
        emit('consumable:install');
        updateData();
      }
    },
  });
};

const fieldPrinter = (item) => {
  const p = item.printerWorkplace?.printer;
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
    class="bg-white border border-gray-100 shadow-sm overflow-hidden"
  >
    <template #header>
      <div class="flex justify-between">
        <Title :h="2" class="border-b-0" style="padding: 0; margin: 0;">
          {{ title }}
        </Title>
        <div>
          <Button
            v-if="can('admin', 'subtract-consumable')"
            label="Вычесть расходный материал"
            icon="pi pi-minus-circle"
            severity="secondary"
            @click="btnInstalledDialog"
          />
        </div>
      </div>
    </template>
    <Column header="Расходный материал" sortable field="count" class="align-middle">
      <template #body="{ data }">
        <div class="flex gap-4">
          <span
            v-tooltip="`Количество`"
            class="shrink-0 size-8 text-sm font-bold bg-gray-100 text-gray-800 rounded-full flex items-center justify-center border border-gray-200 shadow-inner"
          >
            {{ data.count || 1 }}
          </span>

          <div class="flex flex-col gap-0.5">
            <span class="font-semibold text-gray-900 text-sm">
              {{ consumableTypes[data.consumableCount?.consumable?.type] }}
              {{ data.consumableCount?.consumable?.name }}
            </span>
            <div
              v-if="data.consumableCount?.consumable?.type == 'cartridge'"
              class="flex items-center gap-1.5 text-sm text-gray-500 bg-gray-50 px-2 py-0.5 rounded-md border border-gray-100 w-fit"
            >
              <span
                class="size-2 rounded-full shadow-sm"
                :class="[
                  cartridgeColors[data.consumableCount?.consumable?.color]?.bg,
                ]"
              />
              <span>{{ cartridgeColors[data.consumableCount?.consumable?.color]?.name }}</span>
            </div>
          </div>
        </div>
      </template>
    </Column>
    <Column header="Принтер" :field="fieldPrinter" sortable>
      <template #body="{ data }">
        <div
          v-tooltip="`Серийный номер: ${data.printerWorkplace?.serial_number}, \n инв. номер: ${data.printerWorkplace?.inventory_number}`"
          class="flex flex-col gap-0.5 w-fit"
          placeholder="Bottom"
        >
          <div class="flex items-center gap-1.5 text-sm font-medium text-gray-800 group-hover:text-blue-600 transition-colors">
            <i class="pi pi-print text-gray-400 text-xs" />
            <span>{{ data.printerWorkplace?.printer?.vendor }} {{ data.printerWorkplace?.printer?.model }}</span>
          </div>
          <div class="text-sm text-gray-400 pl-4.5">
            {{ data.printerWorkplace?.location }} кабинет
          </div>
        </div>
      </template>
    </Column>
    <Column header="Исполнитель" class="align-middle">
      <template #body="{ data: { author } }">
        <Author :user="author" />
      </template>
    </Column>
    <Column header="Дата" field="created_at" sortable class="align-middle">
      <template #body="{ data }">
        <Timestamps :created-at="data.created_at" />
      </template>
    </Column>
  </DataTable>
</template>
