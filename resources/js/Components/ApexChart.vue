<script setup>
import apexchart from 'vue3-apexcharts';
import ru from 'apexcharts/dist/locales/ru.json';
import { ref } from 'vue';

const props = defineProps({
  chartOptions: {
    type: Object,
    required: true,
  },
  title: {
    type: String,
    required: true,
  },
  chartSeries: {
    type: Array,
    required: true,
  },
  chartType: {
    type: String,
    default: 'line',
  },
});

const chartOptionsMerged = ref({
  chart: {
    locales: [ru],
    defaultLocale: 'ru',
  },
  stroke: {
    curve: "smooth",
  },
  title: {
    text: props.header,
  },
  ...props.chartOptions,
});

</script>
<template>
  <apexchart :type="chartType" :options="chartOptionsMerged" :series="chartSeries" />
</template>
