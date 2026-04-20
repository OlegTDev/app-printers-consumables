<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import Layout from '@/Shared/Layout';
import { ref, inject } from 'vue';
import Breadcrumbs from '@/Shared/Breadcrumbs';
import Column from 'primevue/column';
import Button from 'primevue/button';
import TableTitle from '@/Shared/TableTitle';
import TreeTable from 'primevue/treetable';

const props = defineProps({
    organizations: Object,
    labels: Object,
    filters: Object,
});

defineOptions({
    layout: Layout
});

const title = 'Организации';
const urls = inject('urls');
const moment = inject('moment');

const selectedRow = ref();

const onRowSelect = (event) => {
    router.get(urls.dictionary.organizations.show(event.data.code));
};
</script>
<template>
    {{ selectedRow }}
    <Head :title="title" />

    <Breadcrumbs :home="{ label: 'Главная', url: urls.home }" :items="[
        { label: 'Справочники' },
        { label: title },
    ]" />

    <div class="flex justify-stretch bg-white rounded-md shadow overflow-hidden mt-4">
        <TreeTable
            :value="organizations"
            paginator
            :rows="10"
            v-model:selectionKeys="selectedRow"
            :metaKeySelection="false"
            @nodeSelect="onRowSelect"
            selectionMode="single"
            class="w-full"
            tableStyle="min-width: 50rem"
        >
            <template #header>
                <TableTitle class="border-b border-gray-200 pb-2">{{ title }}</TableTitle>
                <div class="flex justify-between mt-5">
                    <Link :href="urls.dictionary.organizations.create()">
                        <Button type="button" severity="info">Добавить организацию</Button>
                    </Link>
                </div>
            </template>
            <Column field="code" :header="labels.code" sortable expander />
            <Column field="name" :header="labels.name" sortable />
            <Column field="created_at" :header="labels.date" sortable>
                <template #body="{ node: { data } }">
                    <div class="grid grid-rows-2 gap-2">
                        <div v-tooltip="`Создано: ${moment(data.created_at).format('LLLL')}`">
                            <i class="far fa-calendar" v-if="data.created_at"></i>
                            {{ data.created_at ? moment(data.created_at).fromNow() : 'Дата не определена' }}
                        </div>
                        <div v-if="data.created_at != data.updated_at" v-tooltip="`Изменено: ${moment(data.updated_at).format('LLLL')}`">
                            <i class="far fa-calendar-alt"></i>
                            {{ data.updated_at ? moment(data.updated_at).fromNow() : 'Дата не определена' }}
                        </div>
                    </div>
                </template>
            </Column>

            <template #empty> Нет данных </template>

        </TreeTable>
    </div>

</template>
