<script setup>
import Layout from '@/Shared/Layout';
import { inject } from 'vue';
import { Head } from '@inertiajs/inertia-vue3';
import Breadcrumbs from '@/Shared/Breadcrumbs';
import Card from 'primevue/card';
import { Inertia } from '@inertiajs/inertia';
import { useConfirm } from "primevue/useconfirm";
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import TableTitle from '@/Shared/TableTitle';

defineOptions({
    layout: Layout,
});

const props = defineProps({
    printer: Object,
    consumables: Object,
    consumablesNotIn: Object,
    printerLabels: Object,

    cartridgeColors: Object,
    consumableTypes: Object,
    consumableLabels: Object,
});

const urls = inject('urls');
const moment = inject('moment');
const auth = inject('auth');
const printer = props.printer;
const printerLabels = props.printerLabels;
const confirm = useConfirm();

const title = `${printer.vendor} ${printer.model}`;
const goToEdit = () => Inertia.get(urls.dictionary.printers.edit(printer.id));

const LogActions = inject('LogActions');

const deletePrinter = () => {
    confirm.require({
        message: 'Вы уверены, что хотите удалить запись?',
        header: 'Удаление записи',
        accept: () => {
            const url = urls.dictionary.printers.delete(printer.id);
            Inertia.delete(url, {
                onSuccess: () => {
                    LogActions.save(url, 'DELETE', 'Удаление принтера', printer);
                },
            });
        },
    })    
};

const createRelation = () => {
    Inertia.get(urls.dictionary.printers.consumables.index(printer.id));
};

const deleteRelation = (id) => {
    confirm.require({
        message: 'Вы уверены, что хотите удалить связь?',
        header: 'Удаление связи',
        accept: () => {
            const url = urls.dictionary.printers.consumables.delete(printer.id, id);
            Inertia.delete(url, {
                onSuccess: () => {
                    LogActions.save(url, 'DELETE', 'Удаление связи с расходным материалом', Object.assign(printer, { id_consumable: id }));
                },
            });
        },
    })    
};

</script>

<template>

    <Head :title="title" /> 

    <Breadcrumbs :home="{ label: 'Главная', url: urls.home }" :items="[
        { label: 'Справочники', },
        { label: 'Принтеры', url: urls.dictionary.printers.index() },
        { label: title },
    ]" />

    <Card>
        <template #title> {{ title }} </template>
        <template #content>
            <table class="w-1/2 text-left text-gray-700">
                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                    <th class="px-6 py-4">{{ printerLabels.vendor }}</th>
                    <td class="px-6 py-4">
                        {{ printer.vendor }}
                    </td>
                </tr>
                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                    <th class="px-6 py-4">{{ printerLabels.model }}</th>
                    <td class="px-6 py-4">
                        <div class="flex">
                            {{ printer.model }}
                        </div>
                    </td>
                </tr>
                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                    <th class="px-6 py-4">{{ printerLabels.is_color_print }}</th>
                    <td class="px-6 py-4">
                        <div class="flex">
                            {{ printer.is_color_print ? 'Да': 'Нет' }}
                        </div>
                    </td>
                </tr>
                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                    <th class="px-6 py-4">
                        {{ printerLabels.author }}
                    </th>
                    <td class="px-6 py-4">
                        <div>                            
                            {{ printer.author?.fio ?? printer.author?.name }}
                        </div> 
                    </td>
                </tr>
                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                    <th class="px-6 py-4">
                        {{ printerLabels.created_at }}
                    </th>
                    <td class="px-6 py-4">
                        <div>
                            <i class="far fa-calendar"></i>
                            {{ moment(printer.created_at).fromNow() }}
                            ({{ moment(printer.created_at).format('LLLL') }})
                        </div> 
                    </td>
                </tr>
                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                    <th class="px-6 py-4">
                        {{ printerLabels.updated_at }}
                    </th>
                    <td class="px-6 py-4">
                        <div>
                            <i class="far fa-calendar-alt"></i>      
                            {{ moment(printer.updated_at).fromNow() }}
                            ({{ moment(printer.updated_at).format('LLLL') }})
                        </div>
                    </td>
                </tr>
            </table>

            <div class="flex justify-between mt-10" v-if="auth.can('admin', 'editor-dictionary')">
                <Button class="font-bold" @click="goToEdit">Редактировать</Button>
                <Button severity="danger" class="font-bold" @click="deletePrinter">Удалить</Button>
            </div>
        </template>
    </Card>

    <Card class="mt-2">        
        <template #content>
            <DataTable :value="consumables">
                <template #header>
                    <TableTitle class="border-b border-gray-200 pb-2">Привязки к расходным материалам</TableTitle>
                    <div class="flex justify-between mt-5">
                        <Button v-if="auth.can('admin', 'editor-dictionary')" type="button" severity="info" @click="createRelation">
                            Добавить привязку к расходному материалу
                        </Button>                        
                    </div>                
                </template>

                <Column header="#" headerStyle="width:3rem">
                    <template #body="data">
                        {{ data.index + 1 }}
                    </template>
                </Column>
                <Column field="type" :header="consumableLabels.type" sortable>
                    <template #body="{ data }">
                        {{ props.consumableTypes[data.type] ?? data.type }}
                    </template>
                </Column>
                <Column field="name" :header="consumableLabels.name" sortable>
                    <template #body="{ data }">
                        <div class="grid grid-rows-2 gap-4">
                            <div>
                                {{ data.name }}
                            </div>
                            <div v-if="data.type === 'cartridge'">
                                <div class="flex">
                                    <div :class="['rounded-full', 'size-4', 'mr-2', props.cartridgeColors[data.color]['bg']]"></div>
                                    <div>
                                        {{ props.cartridgeColors[data.color]['name'] }}
                                    </div>
                                </div>               
                            </div>
                        </div>
                    </template>
                </Column>
                <Column header="" v-if="auth.can('admin', 'editor-dictionary')">
                    <template #body="{ data }">                                                 
                        <Button severity="danger" type="button" v-tooltip="`Удалить привязку`" @click="deleteRelation(data.id)">
                            <i class="fas fa-times"></i>
                        </Button>
                    </template>
                </Column>

                <template #empty> Нет данных </template>
            </DataTable>
        </template>
    </Card>

</template>