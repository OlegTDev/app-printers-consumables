<script setup>
import { Head, Link } from '@inertiajs/inertia-vue3'
import Layout from '@/Shared/Layout'
import { watch, reactive, ref, inject } from 'vue'
import Breadcrumbs from '@/Shared/Breadcrumbs'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import { Inertia } from '@inertiajs/inertia'
import TableTitle from '@/Shared/TableTitle'
import InputText from 'primevue/inputtext'
import pickBy from 'lodash/pickBy'
import throttle from 'lodash/throttle'

const props = defineProps({
    printers: Object,
    labels: Object,
    filters: Object,    
})

defineOptions({
    layout: Layout
})

const title = 'Принтеры (справочник)';
const urls = inject('urls');
const moment = inject('moment');
const auth = inject('auth');

const selectedRow = ref();
const filters = reactive(props.filters);
const form = reactive({
    search: filters.search,
});

watch(
    () => form,
    throttle(() => {
        Inertia.get(urls.dictionary.printers.index(), pickBy(form), { preserveState: true });
    }, 150),
    { deep: true }
);

const onRowSelect = (event) => {
    Inertia.get(urls.dictionary.printers.show(event.data.id));
};

const refTablePrintersDic = ref(null);

const onPageChange = () => {    
    const elementTablePrintersDic = refTablePrintersDic.value.$el;
    if (elementTablePrintersDic) {
        elementTablePrintersDic.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}


</script>
<template>
    
    <Head :title="title" />

    <Breadcrumbs :home="{ label: 'Главная', url: '/' }" :items="[
        { label: title },
    ]" />

    <div class="flex justify-stretch bg-white rounded-md shadow overflow-hidden mt-4">
        <DataTable 
            :value="printers"
            ref="refTablePrintersDic"
            paginator 
            :rows="10" 
            v-model:selection="selectedRow"
            dataKey="id" 
            :metaKeySelection="false"
            class="w-full" 
            tableStyle="min-width: 50rem" 
            selectionMode="single"
            @rowSelect="onRowSelect"
            @page="onPageChange"
        >
            <template #header>
                <TableTitle class="border-b border-gray-200 pb-2">{{ title }}</TableTitle>
                <div class="flex justify-between mt-5">
                    <Link :href="urls.dictionary.printers.create()" v-if="auth.can('admin', 'editor-dictionary')">
                        <Button type="button" severity="info">Добавить принтер</Button>
                    </Link>
                    <div v-else></div>                    
                    <span class="relative">
                        <i class="fas fa-search absolute top-2/4 -mt-2 left-3 text-surface-400"></i>
                        <InputText v-model="form.search" placeholder="Поиск" class="pl-10 font-normal"/>
                    </span>
                </div>                
            </template>
            <Column header="#" headerStyle="width:3rem">
                <template #body="slotProps">
                    {{ slotProps.index + 1 }}
                </template>
            </Column>
            <Column field="vendor" header="Производитель" sortable />
            <Column field="model" header="Модель" sortable />
            <Column field="is_color_print" header="Цветная печать" sortable>
                <template #body="{ data }">
                    {{ data.is_color_print ? 'Да' : 'Нет' }}
                </template>
            </Column>
            <Column field="created_at" header="Дата" sortable>
                <template #body="{ data }">       
                    <div class="grid grid-rows-2 gap-2">
                        <div v-tooltip="`Создано: ${moment(data.created_at).format('LLLL')}`">
                            <i class="far fa-calendar"></i>
                            {{ moment(data.created_at).fromNow() }}
                        </div>                    
                        <div v-if="data.created_at != data.updated_at" v-tooltip="`Изменено: ${moment(data.updated_at).format('LLLL')}`">
                            <i class="far fa-calendar-alt"></i>      
                            {{ moment(data.updated_at).fromNow() }}
                        </div>
                    </div>
                </template>
            </Column>
            <Column field="author.email" header="Автор" />
                   
            <template #empty> Нет данных </template>

        </DataTable>       
    </div>        
    
</template>