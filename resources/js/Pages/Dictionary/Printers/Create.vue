<script setup>
import { Head } from '@inertiajs/vue3'
import Layout from '@/Shared/Layout'
import Form from './Form'
import { inject, reactive, ref } from 'vue'
import Breadcrumbs from '@/Shared/Breadcrumbs'

defineOptions({
    layout: Layout
});

const props = defineProps({
    labels: Object,
    manufacturers: Array,
});
const labels = reactive(props.labels);
const title = 'Добавление принтера';
const urls = inject('urls');

</script>

<template>
    <Head :title="title" />

    <Breadcrumbs :home="{ label: 'Главная', url: urls.home }" :items="[
        { label: 'Принтеры (справочник)', url: urls.dictionary.printers.index() },
        { label: title },
    ]" />

    <Form :isNew="true" :labels="labels" :printer="{ vendor: null, name: null, is_color_print: false }" :manufacturers="manufacturers"></Form>

</template>

