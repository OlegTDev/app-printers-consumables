<script setup>
import { Head } from '@inertiajs/vue3'
import Layout from '@/Shared/Layout'
import { inject } from 'vue'
import Breadcrumbs from '@/Shared/Breadcrumbs'
import Form from './Form'

defineOptions({
    layout: Layout
});
const urls = inject('urls');
const props = defineProps({
    labels: Object,
    printers: Object,
    printerWorkplace: Object,
    organizations: Array,
});
const title = 'Редактирование';

</script>
<template>

    <Head :title="title" />

    <Breadcrumbs :home="{ label: 'Главная', url: urls.home }" :items="[
        { label: 'Принтеры', url: urls.printers.index() },
        { label: `${printerWorkplace.printer.vendor} ${printerWorkplace.printer.model} (${printerWorkplace.location})`, url: urls.printers.show(printerWorkplace.id) },
        { label: title },
    ]" />

    <Form
        :isNew="false"
        :labels="labels"
        :printers="printers"
        :printerWorkplace="printerWorkplace"
        :organizations="organizations"
    ></Form>

</template>

