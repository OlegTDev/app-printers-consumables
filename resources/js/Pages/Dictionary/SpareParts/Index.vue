<script setup>
import Layout from '@/Shared/Layout';
import { inject, reactive, watch } from 'vue';
import { Head } from '@inertiajs/inertia-vue3';
import Breadcrumbs from '@/Shared/Breadcrumbs';
import Button from 'primevue/button';
import TableTitle from '@/Shared/TableTitle';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import InputText from 'primevue/inputtext';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import { Inertia } from '@inertiajs/inertia';
import throttle from 'lodash/throttle';
import pickBy from 'lodash/pickBy';


defineOptions({
    layout: Layout,
});

const props = defineProps({
    items: Array,
    filters: Object,
    labels: Object,
});

const urls = inject('urls');
const auth = inject('auth');
const moment = inject('moment');

const title = 'Запчасти';
const filters = reactive(props.filters);

const form = reactive({
    search: filters.search,
});

const actions = {
    create: () => Inertia.get(urls.dictionary.spareParts.create()),
};

const onRowSelect = (event) => {
    Inertia.get(urls.dictionary.spareParts.edit(event.data.id));
};

watch(
    () => form,
    throttle(() => {
        Inertia.get(urls.dictionary.spareParts.index(), pickBy(form), { preserveState: true });
    }, 150),
    { deep: true }
);

</script>
<template>

    <Head :title="title" />

    <Breadcrumbs :home="{ label: 'Главная', url: urls.home }" :items="[
        { label: title },
    ]" />

    <div class="flex justify-stretch bg-white rounded-md shadow overflow-hidden mt-4">

        <DataTable :value="items" paginator :rows="10" dataKey="id" :metaKeySelection="false" class="w-full"
            tableStyle="min-width: 50rem" selectionMode="single" @rowSelect="onRowSelect">

            <template #header>
                <TableTitle class="border-b border-gray-200 pb-2">{{ title }}</TableTitle>
                <div class="flex justify-between mt-5">

                    <Button v-if="auth.can('admin', 'editor-dictionary')" severity="info" @click="actions.create">
                        Добавить
                    </Button>

                    <div class="flex">
                        <IconField iconPosition="left" class="w-72">
                            <InputIcon>
                                <i class="pi pi-search" />
                            </InputIcon>
                            <InputText v-model="form.search" placeholder="Поиск" />
                        </IconField>
                    </div>

                </div>
            </template>

            <Column header="#" field="id" headerStyle="width:3rem"></Column>
            <Column :header="labels.name" field="name"></Column>
            <Column :header="labels.description" field="description"></Column>
            <Column :header="labels.author">
                <template #body="{ data: { author } }">
                    {{ author.name }}
                    ({{ author?.fio }})
                </template>
            </Column>
            <Column :header="labels.timestamps">
                <template #body="{ data }">
                    <div class="grid grid-rows-2 gap-2">
                        <div v-tooltip="`Создано: ${moment(data.created_at).format('LLLL')}`">
                            <i class="far fa-calendar"></i>
                            {{ moment(data.created_at).fromNow() }}
                        </div>
                        <div v-if="data.created_at != data.updated_at"
                            v-tooltip="`Изменено: ${moment(data.updated_at).format('LLLL')}`">
                            <i class="far fa-calendar-alt"></i>
                            {{ moment(data.updated_at).fromNow() }}
                        </div>
                    </div>
                </template>
            </Column>

        </DataTable>

    </div>
</template>