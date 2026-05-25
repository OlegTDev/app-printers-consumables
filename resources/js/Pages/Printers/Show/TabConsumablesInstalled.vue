<script setup>
import axios from 'axios';
import { onMounted, ref } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import { useNotification } from '@/Composables/useNotification';
import { useDate } from '@/Composables/useDate';
import { useConfig } from '@/Composables/useConfig';
import Author from '@/Shared/DataTable/Author.vue';
import Badge from 'primevue/badge';

const props = defineProps({
  printerId: Number,
  consumableTypes: Object,
  cartridgeColors: Object,
  consumableLabels: Object,
  consumableCountLabels: Object,
});

const items = ref([]);
const loading = ref(false);
const { formatDate } = useDate();
const { showError } = useNotification();
const { urls } = useConfig();

onMounted(async () => {
  loading.value = true;
  try {
    const url = urls.printers.consumablesInstalled(props.printerId);
    const resp = await axios.get(url);
    items.value = await resp.data;
  } catch (error) {
    showError(error.message);
  }
  loading.value = false;
});
</script>
<template>
  <DataTable
    :value="items"
    paginator
    :rows="10"
    data-key="id"
    :meta-key-selection="false"
    class="w-full"
    table-style="min-width: 50rem"
    selection-mode="single"
    :loading="loading"
  >
    <Column header="Дата" field="date_installed">
      <template #body="{ data }">
        {{ formatDate(data.date_installed) }}
      </template>
    </Column>
    <Column :header="consumableLabels.type" field="type">
      <template #body="{ data }">
        {{ consumableTypes[data.type] }}
      </template>
    </Column>
    <Column :header="consumableLabels.name" field="name">
      <template #body="{ data }">
        <div class="grid grid-rows-2 gap-4">
          <div>
            {{ data.name }}
          </div>
          <div v-if="data.type === 'cartridge'">
            <div class="flex">
              <div
                :class="[
                  'rounded-full',
                  'size-4',
                  'mr-2',
                  cartridgeColors[data.color]?.bg,
                ]"
              />
              <div>
                {{ cartridgeColors[data.color]?.name }}
              </div>
            </div>
          </div>
        </div>
      </template>
    </Column>
    <Column :header="consumableCountLabels.count" field="count">
      <template #body="{ data }">
        <Badge
          :value="data.count ?? 0"
          size="large"
          severity="success"
        />
      </template>
    </Column>
    <Column header="Исполнитель">
      <template #body="{ data }">
        <Author
          :user="{
            name: data.user_name,
            fio: data.user_fio,
            department: data.user_department,
            post: data.user_post,
          }"
        />
      </template>
    </Column>

    <template #empty>
      Нет данных
    </template>
  </DataTable>
</template>
