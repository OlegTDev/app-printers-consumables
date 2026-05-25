<script setup>
import { Head } from '@inertiajs/vue3';
import Layout from '@/Shared/Layout.vue';
import Breadcrumbs from '@/Shared/Breadcrumbs.vue';
import TabPanel from 'primevue/tabpanel';
import { markRaw } from 'vue';
import TabPrinterWorkplace from './TabPrinterWorkplace.vue';
import TabConsumableCount from './TabConsumableCount.vue';
import TabConsumableCountInstalled from './TabConsumableCountInstalled.vue';
import { useConfig } from '@/Composables/useConfig';
import Tabs from 'primevue/tabs';
import TabList from 'primevue/tablist';
import Tab from 'primevue/tab';
import TabPanels from 'primevue/tabpanels';

defineOptions({
  layout: Layout
});

const { urls } = useConfig();

defineProps({
  organizations: Object,
});

const title = 'Отчеты';

const downloadFile = (data, fileName) => {
  const href = URL.createObjectURL(data);
  const link = document.createElement('a');
  link.href = href;
  link.setAttribute('download', fileName);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  URL.revokeObjectURL(href);
};

const tabs = [
  {
    id: 0,
    name: 'Принтеры на местах',
    component: markRaw(TabPrinterWorkplace),
    url: urls.reports.exportPrintersWorkplace(),
  },
  {
    id: 1,
    name: 'Остатки расходных материалов',
    component: markRaw(TabConsumableCount),
    url: urls.reports.exportConsumableCount(),
  },
   {
    id: 2,
    name: 'Количество установленных расходных материалов',
    component: markRaw(TabConsumableCountInstalled),
    url: urls.reports.exportConsumableInstalledCount(),
  },
];
</script>
<template>
  <Head :title="title" />

  <Breadcrumbs
    :home="{ label: 'Главная', url: '/' }"
    :items="[{ label: title }]"
  />

  <div class="card">
    <Tabs :value="0">
      <TabList>
        <Tab v-for="tab in tabs" :key="tab.id" :value="tab.id">
          {{ tab.name }}
        </Tab>
      </TabList>
      <TabPanels>
        <TabPanel v-for="tab in tabs" :key="tab.id" :value="tab.id">
          <component
            :is="tab.component"
            :url="tab.url"
            :organizations="organizations"
            @download-file="downloadFile"
          />
        </TabPanel>
      </TabPanels>
    </Tabs>
  </div>
</template>
