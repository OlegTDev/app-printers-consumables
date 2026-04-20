<script setup>
import { Head } from '@inertiajs/vue3'
import Layout from '@/Shared/Layout'
import Form from './Form'
import { inject } from 'vue'
import Breadcrumbs from '@/Shared/Breadcrumbs'

defineOptions({
    layout: Layout
});
const urls = inject('urls');
const props = defineProps({
    labels: Object,
    printers: Object,
    auth: Object,
    organizations: Array,
});
const title = 'Добавление принтера';
</script>

<template>
    <Head :title="title" />

    <Breadcrumbs :home="{ label: 'Главная', url: urls.home }" :items="[
        { label: 'Принтеры', url: urls.printers.index() },
        { label: title },
    ]" />

    <Form
        :isNew="true"
        :labels="labels"
        :printers="printers"
        :printerWorkplace="{ id: null, id_printer: null, location: null, serial_number: null, inventory_number: null, org_code: props.auth?.user?.org_code }"
        :organizations="organizations"
    ></Form>

</template>

