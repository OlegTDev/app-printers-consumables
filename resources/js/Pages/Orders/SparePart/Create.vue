<script setup>
import Layout from '@/Shared/Layout';
import { Head } from '@inertiajs/vue3';
import Breadcrumbs from '@/Shared/Breadcrumbs';
import { computed, inject } from 'vue';
import Form from './Form.vue';

defineOptions({
    layout: Layout,
});

const urls = inject('urls');

const props = defineProps({    
    spareParts: Object,
    labels: Object,
});

const sparePartsData = computed(() => props.spareParts?.data || []);
const title = 'Оформление заказа';

</script>
<template>
    <Head :title="title" />    

    <Breadcrumbs :home="{ label: 'Главная', url: '/' }" :items="[
        { label: 'Заказ запчастей', url: urls.orders.spareParts.index() },
        { label: title },
    ]" />

    <div class="flex justify-stretch bg-white rounded-md shadow overflow-hidden mt-4">
        
        <Form :isNew="true" :spareParts="sparePartsData" :labels="labels" />

    </div>
</template>