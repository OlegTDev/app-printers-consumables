<script setup>
import Layout from '@/Shared/Layout';
import Card from 'primevue/card';
import { inject } from 'vue';
import Breadcrumbs from '@/Shared/Breadcrumbs';
import { Head, router } from '@inertiajs/vue3';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import { useConfirm } from "primevue/useconfirm";
import TableTitle from '@/Shared/TableTitle';

defineOptions({
    layout: Layout,
});

const props = defineProps({
    consumable: Object,
    cartridgeColors: Object,
    consumableTypeValue: String,
    consumableLabels: Object,

    printers: Object,
    printersNotIn: Object,
    printerLabels: Object,
});

const urls = inject('urls');
const moment = inject('moment');
const auth = inject('auth');
const consumable = props.consumable;
const consumableLabels = props.consumableLabels;
const cartridgeColors = props.cartridgeColors;
const confirm = useConfirm();

const title = `${props.consumableTypeValue} ${consumable.name}`;
const LogActions = inject('LogActions');

const createRelation = () => {
    router.get(urls.dictionary.consumables.printers.index(consumable.id));
};

const deleteRelation = (id) => {
    confirm.require({
        message: 'Вы уверены, что хотите удалить связь?',
        header: 'Удаление связи',
        accept: () => {
            const url = urls.dictionary.consumables.printers.delete(consumable.id, id);
            router.delete(url, { onSuccess: () => {
                LogActions.save(url, 'DELETE', 'Удаление связи с принтером', Object.assign(consumable, { id_printer: id }));
            }});
        },
    })
};

const goToEdit = () => router.get(urls.dictionary.consumables.edit(consumable.id));



const deleteConsumable = () => {
    confirm.require({
        message: 'Вы уверены, что хотите удалить запись?',
        header: 'Удаление записи',
        accept: () => {
            const url = urls.dictionary.consumables.delete(consumable.id);
            router.delete(url, { onSuccess: () => {
                LogActions.save(url, 'DELETE', 'Удаление расходного материала', props.consumable);
            }});
        },
    })
};

</script>
<template>

    <Head :title="title" />

    <Breadcrumbs :home="{ label: 'Главная', url: urls.home }" :items="[
        { label: 'Расходные материалы (справочник)', url: urls.dictionary.consumables.index() },
        { label: title },
    ]" />

    <Card>
        <template #title> {{ props.consumable.name }} </template>
        <template #content>
            <table class="w-1/2 text-left text-gray-700">
                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                    <th class="px-6 py-4">{{ consumableLabels.type }}</th>
                    <td class="px-6 py-4">
                        {{ props.consumableTypeValue }}
                    </td>
                </tr>
                <tr v-if="consumable.type === 'cartridge'" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                    <th class="px-6 py-4">{{ consumableLabels.color }}</th>
                    <td class="px-6 py-4">
                        <div class="flex">
                            <div :class="['rounded-full', 'size-4', 'mr-2', cartridgeColors[consumable.color]['bg']]"></div>
                            <div>
                                {{ cartridgeColors[consumable.color]['name'] }}
                            </div>
                        </div>
                    </td>
                </tr>
                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                    <th class="px-6 py-4">
                        {{ consumableLabels.description }}
                    </th>
                    <td class="px-6 py-4">
                        <div>
                            {{ consumable.description }}
                        </div>
                    </td>
                </tr>
                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                    <th class="px-6 py-4">
                        {{ consumableLabels.author }}
                    </th>
                    <td class="px-6 py-4">
                        <div>
                            {{ consumable.author.fio ?? consumable.author.name }}
                        </div>
                    </td>
                </tr>
                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                    <th class="px-6 py-4">
                        {{ consumableLabels.created_at }}
                    </th>
                    <td class="px-6 py-4">
                        <div>
                            <i class="far fa-calendar"></i>
                            {{ moment(consumable.created_at).fromNow() }}
                            ({{ moment(consumable.created_at).format('LLLL') }})
                        </div>
                    </td>
                </tr>
                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                    <th class="px-6 py-4">
                        {{ consumableLabels.updated_at }}
                    </th>
                    <td class="px-6 py-4">
                        <div>
                            <i class="far fa-calendar-alt"></i>
                            {{ moment(consumable.updated_at).fromNow() }}
                            ({{ moment(consumable.updated_at).format('LLLL') }})
                        </div>
                    </td>
                </tr>
            </table>

            <div class="flex justify-between mt-10" v-if="auth.can('admin', 'editor-dictionary')">
                <Button class="font-bold" @click="goToEdit">Редактировать</Button>
                <Button severity="danger" class="font-bold" @click="deleteConsumable">Удалить</Button>
            </div>
        </template>
    </Card>

    <Card class="mt-2">
        <template #content>
            <DataTable :value="printers">
                <template #header>
                    <TableTitle class="border-b border-gray-200 pb-2">Привязки к принтерам</TableTitle>
                    <div class="flex justify-between mt-5">
                        <Button v-if="auth.can('admin', 'editor-dictionary')" type="button" severity="info" @click="createRelation">
                            Добавить привязку к принтеру
                        </Button>
                    </div>
                </template>

                <Column header="#" headerStyle="width:3rem">
                    <template #body="data">
                        {{ data.index + 1 }}
                    </template>
                </Column>
                <Column field="vendor" header="Производитель" sortable />
                <Column field="model" header="Модель" sortable />
                <Column field="is_color_print" header="Цветная печать" sortable>
                    <template #body="{ data }">
                        {{ data.is_color_print ? 'Да' : 'Нет' }}
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
