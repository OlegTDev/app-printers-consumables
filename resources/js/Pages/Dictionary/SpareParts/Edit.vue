<script setup>
import { Head } from '@inertiajs/inertia-vue3'
import Layout from '@/Shared/Layout'
import { ref, computed, inject } from 'vue'
import Breadcrumbs from '@/Shared/Breadcrumbs'
import Form from './Form'

defineOptions({
    layout: Layout
});

const props = defineProps({
    sparePart: Object,
    labels: Object,
});
const urls = inject('urls');

const title = props.sparePart.name;

</script>
<template>

    <Head :title="title" />

    <Breadcrumbs :home="{ label: 'Главная', url: urls.home }" :items="[
        { label: 'Запчасти', url: urls.dictionary.spareParts.index() },
        { label: `${props.sparePart.name}` },
        { label: 'Редактирование' },
    ]" />

    <Form :isNew="false" :labels="labels" :sparePart="sparePart"></Form>

</template>