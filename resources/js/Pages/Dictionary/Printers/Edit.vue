<script setup>
import { Head } from '@inertiajs/vue3'
import Layout from '@/Shared/Layout'
import { ref, computed, inject } from 'vue'
import Breadcrumbs from '@/Shared/Breadcrumbs'
import Form from './Form'

defineOptions({
    layout: Layout
});

const props = defineProps({
    printer: Object,
    labels: Object,
    manufacturers: Array,
});
const labels = ref(props.labels);
const title = computed({
    get() {
        return props.printer.vendor + ' ' + props.printer.model;
    }
});
const urls = inject('urls');
</script>
<template>

    <Head :title="title" />

    <Breadcrumbs :home="{ label: 'Главная', url: urls.home }" :items="[
        { label: 'Принтеры (справочник)', url: urls.dictionary.printers.index() },
        { label: `${props.printer.vendor} ${props.printer.model}`, url: urls.dictionary.printers.show(props.printer.id) },
        { label: 'Редактирование' },
    ]" />

    <Form :isNew="false" :labels="labels" :printer="printer" :manufacturers="manufacturers"></Form>

</template>

