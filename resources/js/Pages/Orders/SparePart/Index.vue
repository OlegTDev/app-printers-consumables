<script setup>
import Layout from '@/Shared/Layout';
import { Head } from '@inertiajs/vue3';
import Breadcrumbs from '@/Shared/Breadcrumbs';
import DataTable from 'primevue/datatable';
import InputText from 'primevue/inputtext';
import Column from 'primevue/column';
import TableTitle from '@/Shared/TableTitle';
import { inject, reactive } from 'vue';
import { Inertia } from '@inertiajs/inertia';
import Button from 'primevue/button';

defineOptions({
    layout: Layout,
});

const props = defineProps({
    filters: Object,
    orders: Array,
});

const urls = inject('urls');
const form = reactive({
    search: props.filters?.search,
});

const actions = {
    create: () => Inertia.get(urls.orders.spareParts.create()),
    show: (id) => Inertia.get(urls.orders.spareParts.show(id)),
};

const title = 'Заказ запчастей';

</script>
<template>

    <Head :title="title" />

    <Breadcrumbs :home="{ label: 'Главная', url: '/' }" :items="[
        { label: title },
    ]" />

    <div class="flex justify-stretch bg-white rounded-md shadow overflow-hidden mt-4">

        <DataTable 
            :value="orders"             
            paginator 
            :rows="10"            
            dataKey="id" 
            :metaKeySelection="false" 
            class="w-full"
            tableStyle="min-width: 50rem" 
            selectionMode="single" 
            @page="onPageChange" 
            @rowSelect="onRowSelect"
        >
            <template #header>
                <TableTitle class="border-b border-gray-200 pb-2">{{ title }}</TableTitle>
                <div class="flex justify-between mt-5">
                    <Button @click="actions.create" severity="info">
                        Заказать
                    </Button>
                    <span class="relative">
                        <i class="fas fa-search absolute top-2/4 -mt-2 left-3 text-surface-400"></i>
                        <InputText v-model="form.search" placeholder="Поиск" class="pl-10 font-normal" />
                    </span>
                </div>
            </template>
            <Column header="#" headerStyle="width:3rem">
                <template #body="slotProps">
                    {{ slotProps.index + 1 }}
                </template>
            </Column>

            <template #empty> Нет данных </template>

        </DataTable>

    </div>
</template>