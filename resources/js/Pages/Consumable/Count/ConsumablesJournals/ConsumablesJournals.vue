<script setup>
import axios from 'axios';
import { ref, onMounted, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { useConfirm } from 'primevue/useconfirm';
import { useAuth } from '@/Composables/useAuth';
import { Tab, TabList, TabPanels, Tabs, TabPanel } from 'primevue';
import JournalTable from './JournalTable.vue';
import { useNotification } from '@/Composables/useNotification';


const { consumableCount } = defineProps({
  consumable: Object,
  consumableCount: Object,
});

const { can } = useAuth();
const { showError } = useNotification();
const confirm = useConfirm();

const dataItemsAdded = ref();
const dataItemsInstalled = ref();

const tableAddedLoading = ref(false);
const tableInstalledLoading = ref(false);

const loadData = async(url, refObj, refLoading) => {
  refLoading.value = true;
  try {
    const response = await axios.get(url);
    refObj.value = Array.isArray(response.data) ? response.data : [];
  } catch (error) {
    showError(error.message);
  } finally {
    refLoading.value = false;
  }
};

const loadDataAdded = () => {
  const url = route('consumables.counts.added.index', { consumable: consumableCount.id_consumable, count: consumableCount.id });
  loadData(url, dataItemsAdded, tableAddedLoading);
};

const loadDataInstalled = () => {
  const url = route('consumables.counts.installed.index', { consumable: consumableCount.id_consumable, count: consumableCount.id });
  loadData(url, dataItemsInstalled, tableInstalledLoading);
};

watch(
  () => consumableCount.count,
  (newCount, oldCount) => {
    if (newCount > oldCount) {
      loadDataAdded();
    } else {
      loadDataInstalled();
    }
  },
);

onMounted(() => {
  loadDataAdded();
  loadDataInstalled();
});

const baseHandleUndo = (url, refLoading, finishCallback) => {
  confirm.require({
    message: 'Вы уверены, что хотите отменить операцию?',
    header: 'Отмена операции',
    accept: () => {
      refLoading.value = true;
      router.delete(url, {
        onFinish: () => {
          finishCallback();
        },
      });
    },
  });
};

const handleUndoAdded = (id) => {
  const url = route('consumables.counts.added.destroy', { consumable: consumableCount.id_consumable, count: consumableCount.id, added: id });
  baseHandleUndo(url, tableAddedLoading, loadDataAdded);
};

const handleUndoInstalled = (id) => {
  const url = route('consumables.counts.installed.destroy', { consumable: consumableCount.id_consumable, count: consumableCount.id, installed: id });
  baseHandleUndo(url, tableInstalledLoading, loadDataInstalled);
};
</script>
<template>
  <Tabs value="0">
    <TabList>
      <Tab value="0">
        <span class="text-green-600">
          <i class="pi pi-arrow-up-right" />
          Добавлены
        </span>
      </Tab>
      <Tab value="1">
        <span class="text-red-600">
          <i class="pi pi-arrow-down-right" />
          Установлены
        </span>
      </Tab>
    </TabList>
    <TabPanels>
      <TabPanel value="0">
        <JournalTable
          :value="dataItemsAdded"
          :loading="tableAddedLoading"
          :is-admin="can('admin')"
          @action-click="handleUndoAdded"
        />
      </TabPanel>
      <TabPanel value="1">
        <JournalTable
          :value="dataItemsInstalled"
          :loading="tableInstalledLoading"
          :is-admin="can('admin')"
          @action-click="handleUndoInstalled"
        />
      </TabPanel>
    </TabPanels>
  </Tabs>
</template>
