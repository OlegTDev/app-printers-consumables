<script setup>
import Layout from '@/Shared/Layout.vue';
import { Head } from '@inertiajs/vue3';
import LastOperationsInstalled from './Partials/LastInstalledConsumables.vue';
import Chart from './Partials/Chart.vue';
import { ref } from 'vue';

defineOptions({
  layout: Layout,
});

defineProps({
  appName: String,
  auth: Object,
});

const chartLastInstalledRefreshTrigger = ref(0);

const handleConsumableInstalled = () => {
  chartLastInstalledRefreshTrigger.value++;
};

</script>
<template>
  <Head :title="appName" />

  <div class="grid gap-4 items-stretch w-full">
    <LastOperationsInstalled @consumable:install="handleConsumableInstalled" />
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div class="bg-white shadow rounded p-5 flex flex-col justify-center min-w-0 overflow-hidden">
        <Chart
          :key="chartLastInstalledRefreshTrigger"
          class="w-full"
          :url="route('chart.last-installed')"
          header="Динамика установки расходных материалов"
          chart-title="Установлено расходных материалов"
          chart-bg-color="#ef4444"
        />
      </div>
      <div class="bg-white shadow rounded p-5 flex flex-col justify-center min-w-0 overflow-hidden">
        <Chart
          class="w-full"
          :url="route('chart.last-added')"
          header="Динамика добавления расходных материалов"
          chart-title="Добавлено расходных материалов"
          chart-bg-color="#22c55e"
        />
      </div>
    </div>
  </div>
</template>
