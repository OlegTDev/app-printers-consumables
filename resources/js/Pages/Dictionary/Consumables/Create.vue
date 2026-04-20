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
    cartridgeColors: Object,
    consumableTypes: Object,
});
const labels = reactive(props.labels);
const title = 'Добавление расходного материала';
const urls = inject('urls');
const cartridgeColors = reactive(props.cartridgeColors);

</script>

<template>
    <Head :title="title" />

    <Breadcrumbs :home="{ label: 'Главная', url: urls.home }" :items="[
        { label: 'Расходные материалы (справочник)', url: urls.dictionary.consumables.index() },
        { label: title },
    ]" />

    <Form
        :isNew="true"
        :labels="labels"
        :consumable="{ type: null, name: null, color: null, description: null }"
        :cartridgeColors="cartridgeColors"
        :consumableTypes="consumableTypes"
    ></Form>

</template>

