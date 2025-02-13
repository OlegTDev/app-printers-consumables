<script setup>
import { computed, inject, reactive, ref, watch } from 'vue';
import { Head } from '@inertiajs/inertia-vue3'
import Layout from '@/Shared/Layout'
import InputText from 'primevue/inputtext'
import IconField from 'primevue/iconfield'
import InputIcon from 'primevue/inputicon'
import Breadcrumbs from '@/Shared/Breadcrumbs'
import DataTable from 'primevue/datatable'
import Badge from 'primevue/badge'
import Column from 'primevue/column'
import Button from 'primevue/button'
import { Inertia } from '@inertiajs/inertia'
import TableTitle from '@/Shared/TableTitle'
import pickBy from 'lodash/pickBy'
import throttle from 'lodash/throttle'
import Dropdown from 'primevue/dropdown'


defineOptions({
    layout: Layout,    
});
const props = defineProps({
    consumablesCounts: Array,
    consumableLabels: Object,
    consumableCountLabels: Object,
    filters: Object,
    consumableTypes: Object,
    cartridgeColors: Object,
});
const urls = inject('urls');
const auth = inject('auth');

const title = 'Количество расходных материалов';
const filters = reactive(props.filters);
const consumableLabels = props.consumableLabels;

const form = reactive({
    search: filters.search,
    consumableType: filters.consumableType,
});

const consumableTypesDropdown = computed(() => {   
    let res = []
    Object.keys(props.consumableTypes).forEach((key) => {
        res.push({ value: key, name: props.consumableTypes[key] })        
    })
    return res
});

watch(
    () => form,
    throttle(() => {
        Inertia.get(urls.consumables.counts.index(), pickBy(form), { preserveState: true })
    }, 150),
    { deep: true }
)

const actions = {
    create: () => Inertia.get(urls.consumables.counts.create()),
    show: (event) => Inertia.get(urls.consumables.counts.show(event.data.id)),
};

const refTableConsumableCount = ref(null);

const onPageChange = () => {    
    const elementTableConsumableCount = refTableConsumableCount.value.$el;
    if (elementTableConsumableCount) {
        elementTableConsumableCount.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

</script>
<template>

    <Head :title="title" />    

    <Breadcrumbs :home="{ label: 'Главная', url: urls.home }" :items="[
        { label: title },
    ]" />

    <div class="flex justify-stretch bg-white rounded-md shadow overflow-hidden mt-4">
        
        <DataTable :value="consumablesCounts"
            ref="refTableConsumableCount"
            paginator 
            :rows="10" 
            dataKey="id" 
            :metaKeySelection="false"
            class="w-full" 
            tableStyle="min-width: 50rem" 
            selectionMode="single"
            @rowSelect="actions.show"    
            @page="onPageChange"
        >
            <template #header>
                <TableTitle class="border-b border-gray-200 pb-2">{{ title }}</TableTitle>
                <div class="flex justify-between mt-5">
                    
                    <Button v-if="auth.can('admin', 'add-consumables')" severity="info" @click="actions.create">
                        Добавить
                    </Button>

                    <div class="flex">
                        <IconField iconPosition="left" class="w-72">
                            <InputIcon>
                                <i class="pi pi-search" />
                            </InputIcon>
                            <InputText v-model="form.search" placeholder="Поиск" />
                        </IconField>
                        
                        <Dropdown 
                            class="w-72"
                            v-model="form.consumableType" 
                            showClear 
                            :options="consumableTypesDropdown" 
                            optionLabel="name" 
                            optionValue="value" 
                            :placeholder="consumableLabels.type" 
                        />
                    </div>
                    
                </div>                
            </template>
            <Column header="#" headerStyle="width:3rem">
                <template #body="slotProps">
                    {{ slotProps.index + 1 }}
                </template>                        
            </Column>
            <Column :header="consumableLabels.type" field="consumable.type">
                <template #body="{ data }">
                    {{ consumableTypes[data.consumable.type] }}
                </template>
            </Column>
            <Column :header="consumableLabels.name" field="consumable.name">
                <template #body="{ data }">
                    <div class="grid grid-rows-2 gap-4">
                        <div>
                            {{ data.consumable.name }}
                        </div>
                        <div v-if="data.consumable.type === 'cartridge'">
                            <div class="flex">
                                <div :class="['rounded-full', 'size-4', 'mr-2', cartridgeColors[data.consumable.color]['bg']]"></div>
                                <div>
                                    {{ cartridgeColors[data.consumable.color]['name'] }}
                                </div>
                            </div>               
                        </div>
                    </div>
                </template>
            </Column>
            <Column :header="consumableCountLabels.count" field="count">
                <template #body="{ data }">
                    <Badge :value="data.count" size="large" :severity="data.count <= 1 ? 'danger' : (data.count < 10 ? 'warning' : 'success')"></Badge>
                </template>
            </Column>           
            
            <template #empty> Нет данных </template>

        </DataTable>

    </div>



</template>