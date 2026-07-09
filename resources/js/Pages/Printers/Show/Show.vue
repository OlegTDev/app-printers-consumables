<script setup>
import { Head } from '@inertiajs/vue3';
import Layout from '@/Shared/Layout.vue';
import Breadcrumbs from '@/Shared/Breadcrumbs.vue';
import TabPanel from 'primevue/tabpanel';
import TabPrinterInfo from './TabPrinterInfo.vue';
import TabConsumables from './TabConsumables.vue';
import TabConsumablesInstalled from './TabConsumablesInstalled.vue';
import TabList from 'primevue/tablist';
import Tab from 'primevue/tab';
import Tabs from 'primevue/tabs';
import TabPanels from 'primevue/tabpanels';
import { ref } from 'vue';

defineOptions({
  layout: Layout,
});

const props = defineProps({
  printerWorkplace: Object,
  printerLabels: Object,
  printerWorkplaceLabels: Object,
  organization: Object,

  consumables: Array,
  consumableLabels: Object,
  consumableCountLabels: Object,
  consumableTypes: Object,
  cartridgeColors: Object,
});

const printer = props.printerWorkplace.printer;
const printerWorkplace = props.printerWorkplace;
const printerLabels = props.printerLabels;

const title = `${printer.vendor} ${printer.model} (${printerWorkplace.location})`;
const activeTab = ref("0");
</script>
<template>
  <Head :title="title" />

  <Breadcrumbs
    :home="{ label: 'Главная', url: route('home') }"
    :items="[
      { label: 'Принтеры', url: route('workplace.index') },
      { label: title },
    ]"
  />

  <Tabs v-model:value="activeTab">
    <TabList>
      <Tab value="0">
        Информация о принтере
      </Tab>
      <Tab value="1">
        Расходные материалы
      </Tab>
      <Tab value="2">
        Установленные расходные материалы
      </Tab>
    </TabList>
    <TabPanels>
      <TabPanel value="0">
        <TabPrinterInfo
          :title="title"
          :printer-labels="printerLabels"
          :printer-workplace-labels="printerWorkplaceLabels"
          :printer-workplace="printerWorkplace"
        />
      </TabPanel>
      <TabPanel value="1">
        <TabConsumables
          :consumables="printerWorkplace.printer.consumables"
          :consumable-labels="consumableLabels"
          :cartridge-colors="cartridgeColors"
          :consumable-count-labels="consumableCountLabels"
          :consumable-types="consumableTypes"
        />
      </TabPanel>
      <TabPanel value="2">
        <KeepAlive>
          <TabConsumablesInstalled
            v-if="activeTab == 2"
            :printer-id="printerWorkplace.id"
            :cartridge-colors="cartridgeColors"
            :consumable-types="consumableTypes"
            :consumable-labels="consumableLabels"
            :consumable-count-labels="consumableCountLabels"
          />
        </KeepAlive>
      </TabPanel>
    </TabPanels>
  </Tabs>
</template>
