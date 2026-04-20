<script setup>
import { Head } from '@inertiajs/vue3'
import Layout from '@/Shared/Layout'
import Form from './Form'
import { inject, reactive } from 'vue'
import Breadcrumbs from '@/Shared/Breadcrumbs'

defineOptions({
    layout: Layout
});

const props = defineProps({
    labels: Object,
});
const labels = reactive(props.labels);
const title = 'Добавление организации';
const urls = inject('urls');
</script>

<template>
    <Head :title="title" />

    <Breadcrumbs :home="{ label: 'Главная', url: urls.home }" :items="[
        { label: 'Справочники' },
        { label: 'Организации', url: urls.dictionary.organizations.index() },
        { label: title },
    ]" />

    <Form :isNew="true" :labels="labels" :organization="{ code: null, name: null }"></Form>

</template>

