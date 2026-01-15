<script setup>
import { inject } from 'vue';
import { Head } from '@inertiajs/inertia-vue3';
import Layout from '@/Shared/Layout';
import Breadcrumbs from '@/Shared/Breadcrumbs';
import Form from './Form.vue';


defineOptions({
    layout: Layout,
});
const props = defineProps({
    auth: Object,
    labels: Object,
});
const urls = inject('urls');
const title = 'Добавить запчасть';

</script>
<template>

    <Head :title="title" />

    <Breadcrumbs :home="{ label: 'Главная', url: urls.home }" :items="[
        { label: 'Запчасти', url: urls.dictionary.spareParts.index() },
        { label: title },
    ]" />

    <Form :isNew="true" :labels="labels" :sparePart="{ name: null, description: null }"></Form>

</template>