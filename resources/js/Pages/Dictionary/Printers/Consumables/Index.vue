<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import Layout from '@/Shared/Layout'
import { watch, reactive, inject } from 'vue'
import Breadcrumbs from '@/Shared/Breadcrumbs'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import TableTitle from '@/Shared/TableTitle'
import InputText from 'primevue/inputtext'
import pickBy from 'lodash/pickBy'
import throttle from 'lodash/throttle'

const props = defineProps({
    printer: Object,
    filters: Object,
    consumables: Object,
    consumableTypes: Object,
    consumableLabels: Object,
    cartridgeColors: Object,
});

defineOptions({
    layout: Layout
});
const printer = props.printer;
const title = `Привязка расходного материала`;
const urls = inject('urls');
const auth = inject('auth');
const consumableLabels = props.consumableLabels;
const filters = reactive(props.filters);

const form = reactive({
    search: filters.search,
});
watch(
    () => form,
    throttle(() => {
        router.get(urls.dictionary.printers.consumables.index(printer.id), pickBy(form), { preserveState: true });
    }, 150),
    { deep: true }
);

const LogActions = inject('LogActions');

const addConsumable = (id) => {
    const url = urls.dictionary.printers.consumables.add(printer.id, id);
    router.post(url, {
        onSuccess: () => {
            LogActions.save(url, 'POST', 'Добавление привязки к расходному материалу', Object.assign(printer, { id_consumable: id }));
            router.get(urls.dictionary.printers.show(printer.id));
        },
    })
};

</script>
<template>

    <Head :title="title" />

    <Breadcrumbs :home="{ label: 'Главная', url: urls.dictionary.home }" :items="[
        { label: 'Справочники', },
        { label: 'Принтеры', url: urls.dictionary.printers.index() },
        { label: title },
    ]" />

    <div class="flex justify-stretch bg-white rounded-md shadow overflow-hidden mt-4">
        <DataTable :value="consumables"
            paginator :rows="10"
            dataKey="id" :metaKeySelection="false"
            class="w-full" tableStyle="min-width: 50rem" selectionMode="single"
        >
            <template #header>
                <TableTitle class="border-b border-gray-200 pb-2">{{ title }}</TableTitle>
                <div class="flex justify-between mt-5">
                    <Link :href="urls.dictionary.printers.show(printer.id)">
                        <Button type="button" severity="secondary" outlined>
                            <i class="fas fa-chevron-circle-left me-3"></i>
                            Назад
                        </Button>
                    </Link>
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
            <Column :header="consumableLabels.type">
                <template #body="{ data }">
                    {{ consumableTypes[data.type] ?? data.type }}
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
            <Column header="">
                <template #body="{ data }" v-if="auth.can('admin', 'editor-dictionary')">
                    <Button @click="addConsumable(data.id)">
                        <i class="fas fa-check me-3"></i>
                        Выбрать
                    </Button>
                </template>
            </Column>

            <template #empty> Нет данных </template>

        </DataTable>
    </div>

</template>
