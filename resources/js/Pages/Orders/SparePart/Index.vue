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
import OrderStatus from '../Shared/OrderStatus';
import PrinterWorkplace from '@/Shared/DataTable/PrinterWorkplace';
import Author from '@/Shared/DataTable/Author';
import Timestamps from '@/Shared/DataTable/Timestamps';

defineOptions({
    layout: Layout,
});

const props = defineProps({
    filters: Object,
    orders: Object,
    labels: Object,
});

const urls = inject('urls');
const form = reactive({
    search: props.filters?.search,
});

const actions = {
    create: () => Inertia.get(urls.orders.spareParts.create()),
    show: (id) => Inertia.get(urls.orders.spareParts.show(id)),
};

const onRowSelect = (event) => {
    Inertia.get(urls.orders.spareParts.show(event.data.id));
}

const title = 'Заказ запчастей';

</script>
<template>

    <Head :title="title" />

    <Breadcrumbs :home="{ label: 'Главная', url: '/' }" :items="[
        { label: title },
    ]" />

    <div class="flex justify-stretch bg-white rounded-md shadow overflow-hidden mt-4">

        <DataTable 
            :value="orders?.data"             
            paginator 
            :rows="10"            
            dataKey="id" 
            :metaKeySelection="false" 
            class="w-full"
            tableStyle="min-width: 50rem" 
            selectionMode="single"
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
            <Column :header="labels.order.status">
                <template #body="{ data }">
                    <OrderStatus :status="data.order.status" :statusLabel="data.order.status_label" />
                </template>
            </Column>            
            <Column :header="labels.order_spare_part.id_spare_part_or_call_specialist">
                <template #body="{ data }">
                    <template v-if="data.call_specialist">
                        <i class="fa-solid fa-phone me-2"></i> {{ labels.order_spare_part.call_specialist }}
                    </template>
                    <template v-else>
                        {{ data.sparePart.name }}
                    </template>
                </template>
            </Column>
            <Column :header="labels.order_spare_part.id_printers_workplace">
                <template #body="{ data }">
                    <PrinterWorkplace 
                        :vendor="data.printerWorkplace.printer.vendor" 
                        :model="data.printerWorkplace.printer.model"
                        :is_color_print="data.printerWorkplace.printer.is_color_print"
                        :location="data.printerWorkplace.location"
                        :inventory_number="data.printerWorkplace.inventory_number"
                        :serial_number="data.printerWorkplace.serial_number"
                    />
                </template>
            </Column>
            <Column :header="labels.order.requested_by">
                <template #body="{ data }">
                    <Author 
                        :login="data.order.requested.name"
                        :fullName="data.order.requested.fio"
                        :post="data.order.requested.post"
                        :department="data.order.requested.department"
                    />
                </template>
            </Column>
            <Column header="Дата">
                <template #body="{ data }">
                    <Timestamps :created_at="data.order.created_at" :updated_at="data.order.updated_at" />
                </template>
            </Column>

            <template #empty> Нет данных </template>

        </DataTable>

    </div>
</template>