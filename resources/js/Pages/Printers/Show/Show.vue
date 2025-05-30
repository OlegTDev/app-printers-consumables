<script setup>
import { Head } from '@inertiajs/inertia-vue3'
import Layout from '@/Shared/Layout'
import { inject } from 'vue'
import Breadcrumbs from '@/Shared/Breadcrumbs'
import TabView from 'primevue/tabview'
import TabPanel from 'primevue/tabpanel'
import TabPrinterInfo from './TabPrinterInfo.vue'
import TabConsumables from './TabConsumables.vue'
import TabConsumablesInstalled from './TabConsumablesInstalled.vue'

defineOptions({
    layout: Layout
});

const urls = inject('urls');
const auth = inject('auth');
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
</script>
<template>
    <div>

        <Head :title="title" />

        <Breadcrumbs :home="{ label: 'Главная', url: urls.home }" :items="[
            { label: 'Принтеры', url: urls.printers.index() },
            { label: title },
        ]" />

        <TabView :lazy="true">
            
            <TabPanel header="Информация о принтере">
                <TabPrinterInfo :urls="urls" :title="title" :printer="printer" :printerLabels="printerLabels"
                    :printerWorkplaceLabels="printerWorkplaceLabels" :printerWorkplace="printerWorkplace"
                    :organization="organization" :auth="auth" />
            </TabPanel>
            
            <TabPanel header="Расходные материалы">
                <TabConsumables :consumables="consumables" :consumableLabels="consumableLabels"
                    :cartridgeColors="cartridgeColors" :consumableCountLabels="consumableCountLabels"
                    :consumableTypes="consumableTypes" />
            </TabPanel>
            
            <TabPanel header="Установленные расходные материалы">
                <TabConsumablesInstalled :urls="urls" :printerId="printerWorkplace.id"
                    :cartridgeColors="cartridgeColors" :consumableTypes="consumableTypes"
                    :consumableLabels="consumableLabels" :consumableCountLabels="consumableCountLabels" />
            </TabPanel>
            
        </TabView>

    </div>
</template>
