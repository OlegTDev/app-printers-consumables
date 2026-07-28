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
  xaxis: {
    type: 'date',
    labels: {
      formatter: function (value) {
        return value != null ? formatDate(value, 'L') : null;
      },
    },
  },
  chart: {
    type: 'area',
    width: '100%',
    height: 350,
    redrawOnParentResize: true,
    redrawOnWindowResize: true,
    sparkline: {
      enabled: false,
    }
  },
  stroke: {
    curve: 'smooth',
    width: 3
  }
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
  <div v-if="loadingChart" class="flex justify-center items-center w-full">
    <ProgressSpinner style="width: 50px; height: 50px" stroke-width="4" />
  </div>

  <div v-else class="w-full flex flex-col min-w-0 overflow-hidden">
    <h3 v-if="header" class="text-base font-semibold text-surface-700 dark:text-surface-300 mb-4 px-2">
      {{ header }}
    </h3>

    <div class="w-full min-w-0 overflow-hidden">
      <ApexChart
        v-if="loadedChart"
        title
        :chart-options="chartOptions"
        :chart-series="chartSeries"
        type="area"
        height="350"
      />
    </div>
  </div>
</template>
