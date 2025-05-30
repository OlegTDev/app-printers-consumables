<script setup>
import { Head } from '@inertiajs/inertia-vue3'
import Layout from '@/Shared/Layout'
import Breadcrumbs from '@/Shared/Breadcrumbs';
import TabPanel from 'primevue/tabpanel';
import TabView from 'primevue/tabview';
import { inject } from 'vue';
import TabPrinterWorkplace from './TabPrinterWorkplace.vue';
import TabConsumableCount from './TabConsumableCount.vue';
import TabConsumableCountInstalled from './TabConsumableCountInstalled.vue';


defineOptions({
    layout: Layout
});
const urls = inject('urls');

const props = defineProps({
    organizations: Object,
})

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
}

</script>
<template>

    <Head :title="title" />

    <Breadcrumbs :home="{ label: 'Главная', url: '/' }" :items="[
        { label: title },
    ]" />

    <div class="card">
        <TabView>

            <TabPanel header="Принтеры на местах">
                <TabPrinterWorkplace :url="urls.reports.exportPrintersWorkplace()" :organizations="organizations"
                    @downloadFile="downloadFile" />
            </TabPanel>

            <TabPanel header="Остатки расходных материалов">
                <TabConsumableCount :url="urls.reports.exportConsumableCount()" :organizations="organizations"
                    @downloadFile="downloadFile" />
            </TabPanel>

            <TabPanel header="Количество установленных расходных материалов">
                <TabConsumableCountInstalled :url="urls.reports.exportConsumableInstalledCount()"
                    :organizations="organizations" @downloadFile="downloadFile" />
            </TabPanel>

        </TabView>
    </div>

</template>