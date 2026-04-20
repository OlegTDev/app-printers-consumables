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
    consumable: Object,
    cartridgeColors: Object,
    consumableTypes: Object,
    consumableTypeValue: String,
});
const labels = reactive(props.labels);
const title = 'Редактирование расходного материала';
const urls = inject('urls');
const cartridgeColors = reactive(props.cartridgeColors);

</script>

<template>
    <Head :title="title" />

    <Breadcrumbs :home="{ label: 'Главная', url: urls.home }" :items="[
        { label: 'Расходные материалы (справочник)', url: urls.dictionary.consumables.index() },
        { label: `${props.consumableTypeValue} ${props.consumable.name}`, url: urls.dictionary.consumables.show(props.consumable.id) },
        { label: title },
    ]" />

    <Form
        :isNew="false"
        :labels="labels"
        :consumable="consumable"
        :cartridgeColors="cartridgeColors"
        :consumableTypes="consumableTypes"
    ></Form>

</template>

