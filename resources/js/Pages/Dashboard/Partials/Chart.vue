<script setup>
import axios from 'axios';
import { onMounted, ref } from 'vue';
import ApexChart from '@/Components/ApexChart.vue';
import { useDate } from '@/Composables/useDate';
import { useNotification } from '@/Composables/useNotification';
import ProgressSpinner from 'primevue/progressspinner';

const props = defineProps({
  url: {
    type: String,
    required: true,
  },
  header: {
    type: String,
    default: '',
  },
  chartTitle: {
    type: String,
    default: '',
  },
  chartBgColor: {
    type: String,
    default: '#008FFB',
  },
});

const { formatDate } = useDate();
const notification = useNotification();
const loadingChart = ref(false);
const loadedChart = ref(false);
const chartOptions = ref({
  title: {
    text: props.header,
  },
  xaxis: {
    type: 'date',
    labels: {
      formatter: function (value) {
        return value != null ? formatDate(value, 'L') : null;
      },
    },
  },
});
const chartSeries = ref([]);

const updateChart = async () => {
  loadingChart.value = true;
  loadedChart.value = false;
  try {
    const { data } = await axios.get(props.url);

    if (Array.isArray(data)) {
      const seriesData = data.map(item => ({
        x: item.date,
        y: item.count,
      }));

      chartSeries.value = [{
        name: props.chartTitle,
        data: seriesData,
        color: props.chartBgColor,
      }];

    };
  } catch (error) {
    notification.error(error.message);
  } finally {
    loadingChart.value = false;
    loadedChart.value = true;
  }
};

onMounted(() => {
  loadingChart.value = true;
  updateChart();
});

</script>
<template>
  <progress-spinner
    v-if="loadingChart"
    style="width: 50px; height: 50px"
    animation-duration=".5s"
    stroke-width="8"
    fill="transparent"
    aria-label="Custom ProgressSpinner"
  />
  <div v-else>
    <ApexChart v-if="loadedChart" :chart-options="chartOptions" :chart-series="chartSeries" :title="header" />
  </div>
</template>
