<script setup>
import Layout from '@/Shared/Layout';
import { Head } from '@inertiajs/inertia-vue3';
import LastOperationsInstalled from './LastOperationsInstalled.vue';
import Chart from './Chart.vue';
import { inject } from 'vue';

defineOptions({
    layout: Layout,
})

const props = defineProps({
    appName: String,
    auth: Object,
});

const urls = inject('urls');


</script>
<template>

    <Head :title="appName" />

    <div class="grid grid-cols-4 gap-4 items-stretch">

        <LastOperationsInstalled :auth="auth" class="col-span-4" />

        <Chart class="col-span-2" :url="urls.chart.lastInstalled()" header="Динамика установки расходных материалов"
            chartTitle="Установлено расходных материалов" chartBgColor="#ef4444" />

        <Chart class="col-span-2" :url="urls.chart.lastAdded()" header="Динамика добавления расходных материалов"
            chartTitle="Добавлено расходных материалов" chartBgColor="#22c55e" />

    </div>
</template>
