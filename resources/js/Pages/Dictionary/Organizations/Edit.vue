<script setup>
import { Head } from '@inertiajs/vue3'
import Layout from '@/Shared/Layout'
import { computed, inject } from 'vue'
import Breadcrumbs from '@/Shared/Breadcrumbs'
import Form from './Form'

defineOptions({
    layout: Layout
});

const props = defineProps({
    organization: Object,
    labels: Object,
});
const title = computed({
    get() {
        return `${props.organization.name} (${props.organization.code})`;
    }
});
const urls = inject('urls');
</script>
<template>

    <Head :title="title" />

    <Breadcrumbs :home="{ label: 'Главная', url: urls.home }" :items="[
        { label: 'Справочники' },
        { label: 'Организации', url: urls.dictionary.organizations.index() },
        { label: title, url: urls.dictionary.organizations.show(props.organization.code) },
        { label: 'Редактирование' },
    ]" />

    <Form :isNew="false" :labels="labels" :organization="organization"></Form>

</template>

