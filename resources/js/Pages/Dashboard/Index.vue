<script setup>
import Layout from '@/Shared/Layout.vue';
import { Head } from '@inertiajs/vue3';
import LastOperationsInstalled from './Partials/LastInstalledConsumables.vue';
import Chart from './Partials/Chart.vue';

defineOptions({
  layout: Layout,
});

defineProps({
  appName: String,
  auth: Object,
});

</script>
<template>
  <Head :title="appName" />

  <div class="grid grid-cols-1 gap-4 items-stretch">
    <LastOperationsInstalled />
    <div class="grid grid-cols-2 gap-4">
      <div class="col-span bg-white shadow rounded p-4 grid content-center">
        <Chart
          class="w-full"
          :url="route('chart.last-installed')"
          header="Динамика установки расходных материалов"
          chart-title="Установлено расходных материалов"
          chart-bg-color="#ef4444"
        />
      </div>
      <div class="col-span bg-white shadow rounded p-4 grid content-center">
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
