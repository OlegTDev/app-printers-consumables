<script setup>
import { Head } from '@inertiajs/inertia-vue3'
import Layout from '@/Shared/Layout'
import { onMounted, watch, reactive, ref, inject } from 'vue'
import { initFlowbite } from 'flowbite'
import Breadcrumbs from '@/Shared/Breadcrumbs'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import { Inertia } from '@inertiajs/inertia'
import TableTitle from '@/Shared/TableTitle'
import InputText from 'primevue/inputtext'
import pickBy from 'lodash/pickBy'
import throttle from 'lodash/throttle'
import Badge from 'primevue/badge'

const props = defineProps({
    printersWorkplace: Array,
    printerLabels: Object,
    printerWorkplaceLabels: Object,
    filters: Object, 
    cartridgeColors: Object,
    consumableTypes: Object,   
});

defineOptions({
    layout: Layout
});
const urls = inject('urls');
const moment = inject('moment');
const auth = inject('auth');

onMounted(() => {
    initFlowbite()
})

const selectedRow = ref()
const filters = reactive(props.filters)
const form = reactive({
    search: filters.search,
})

watch(
    () => form,
    throttle(() => {
        Inertia.get(urls.printers.index(), pickBy(form), { preserveState: true })
    }, 150),
    { deep: true }
)

const refTablePrintersWorkplace = ref(null);

const onPageChange = () => {    
    const elementTablePrintersWorkplace = refTablePrintersWorkplace.value.$el;
    if (elementTablePrintersWorkplace) {
        elementTablePrintersWorkplace.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}
const onRowSelect = (event) => {
    actions.show(event.data.id);
};

const title = 'Принтеры';

const actions = {
    create: () => Inertia.get(urls.printers.create()),
    show: (id) => Inertia.get(urls.printers.show(id)),
};

const showConsumables = ref({});
const toggleConsumable = (id) => {   
    if (showConsumables.value[id] == true) {
        showConsumables.value[id] = false;
    }
    else {
        showConsumables.value[id] = true;
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
            :value="printersWorkplace"
            ref="refTablePrintersWorkplace"
            paginator :rows="10" 
            v-model:selection="selectedRow"
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
                    <Button @click="actions.create" v-if="auth.can('admin', 'editor-printer-workplace')" severity="info">
                        Добавить принтер
                    </Button>                    
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
            <Column field="printer.vendor" :header="printerWorkplaceLabels.id_printer" @click="columnClick(false)" sortable>
                <template #body="{ data }">
                    <div class="flex align-items-center gap-2">                       
                        <span>{{ `${data.printer.vendor} ${data.printer.model}` }}</span>         
                        <div v-if="data.printer.is_color_print" v-tooltip="'Цветная печать'">        
                            <svg                                 
                                version="1.1" xmlns="http://www.w3.org/2000/svg" 
                                xmlns:xlink="http://www.w3.org/1999/xlink" 
                                viewBox="0 0 512 512" xml:space="preserve" fill="#000000"
                                class="h-4 w-4"
                                >                               
                                <g> 
                                    <circle style="fill:#EA348B;" cx="156.775" cy="341.5" r="156.775"></circle>
                                    <circle style="fill:#FFDA44;" cx="355.225" cy="341.5" r="156.775"></circle>
                                    <circle style="fill:#99EFF2;" cx="255.53" cy="170.5" r="156.775"></circle>
                                        <path style="fill:#FFFFFF;" d="M399.023,199.001c0,0-100.395-13.715-143.023,21.116c-42.88-35.039-142.023-21.116-142.023-21.116 s34.389,98.73,86.343,118.271c-8.463,54.14,55.184,124.018,55.681,124.018c0.499,0,64.229-70.123,55.621-124.374 C363.214,297.131,399.023,199.001,399.023,199.001z"></path> 
                                        <path style="fill:#91DC5A;" d="M256,220.117c14.291,11.678,26.754,26.087,36.53,43.019c9.888,17.128,16.168,35.347,19.092,53.78 c51.591-19.786,90.066-66.045,98.784-122.155C357.575,174.829,298.627,185.286,256,220.117z"></path> 
                                        <path style="fill:#006DF0;" d="M200.32,317.272c2.9-18.554,9.198-36.898,19.152-54.136c9.774-16.932,22.238-31.339,36.529-43.019 c-42.88-35.039-102.273-45.415-155.344-25.003C109.535,251.405,148.367,297.731,200.32,317.272z"></path> 
                                        <path d="M256,220.117c-14.291,11.678-26.753,26.087-36.529,43.019c-9.952,17.24-16.251,35.584-19.152,54.136 c17.167,6.458,35.759,9.999,55.182,9.999c19.778,0,38.695-3.671,56.121-10.356c-2.926-18.434-9.204-36.653-19.092-53.78 C282.756,246.204,270.291,231.797,256,220.117z"></path> 
                                        <path style="fill:#D80027;" d="M311.622,316.916c-17.426,6.683-36.343,10.356-56.121,10.356c-19.423,0-38.015-3.54-55.182-9.999 c-8.463,54.14,12.08,110.071,55.681,145.633C299.699,427.266,320.232,371.167,311.622,316.916z"></path> 
                                </g>
                            </svg>
                        </div>       
                    </div>
                </template>
            </Column>      
            <Column header="Расходные материалы">
                <template #body="{ data }"> 
                    <Button size="small" severity="secondary" @click="toggleConsumable(data.id)" outlined>
                        <template v-if="showConsumables[data.id] !== true">
                            <i class="pi pi-angle-double-down"></i>&nbsp;Развернуть
                        </template>
                        <template v-else>
                            <i class="pi pi-angle-double-up"></i>&nbsp;Свернуть
                        </template>
                    </Button>
                    
                    <div v-if="showConsumables[data.id]" class="grid gap-y-2 divide-y divide-slate-300 divide-dashed text-sm mt-4">
                        <div class="flex flex-row gap-1 pt-1" v-for="consumable in data?.printer?.consumables_deep">
                            <div class="grid content-center">
                                <div v-if="consumable.consumable_count != undefined">
                                    <Badge 
                                        :value="consumable.consumable_count.count"  
                                        :severity="consumable.consumable_count.count <= 1 ? 'danger' 
                                            : (consumable.consumable_count.count < 10 ? 'warning' : 'success')"
                                    ></Badge>
                                </div>
                                <div v-else>
                                    <Badge 
                                        :value="0"                                    
                                        severity="danger"
                                    />
                                </div>
                            </div>
                            <div class="grid"> 
                                <div>
                                    {{ consumableTypes[consumable.type] ?? consumable.type }}
                                </div>
                                <div>
                                    {{ consumable.name }}
                                </div>
                                <div v-if="consumable.type === 'cartridge'">
                                    <div class="flex">
                                        <div :class="['rounded-full', 'size-4', 'mr-2', cartridgeColors[consumable.color]['bg']]"></div>
                                        <div>
                                            {{ cartridgeColors[consumable.color]['name'] }}
                                        </div>
                                    </div>               
                                </div>
                            </div>                        
                        </div> 
                    </div> 
                </template>
            </Column>  
            <Column field="location" :header="printerWorkplaceLabels.location" sortable />
            <Column field="serial_number" :header="printerWorkplaceLabels.serial_number" sortable />
            <Column field="inventory_number" :header="printerWorkplaceLabels.inventory_number" sortable />
            <Column field="created_at" :header="printerWorkplaceLabels.date" sortable>
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
            
            <template #empty> Нет данных </template>

        </DataTable>    
        
    </div>        
    
</template>