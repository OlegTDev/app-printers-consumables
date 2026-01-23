<script setup>
import Layout from '@/Shared/Layout';
import { inject } from 'vue';
import { Head } from '@inertiajs/vue3';
import Breadcrumbs from '@/Shared/Breadcrumbs';
import Card from 'primevue/card';
import PrinterWorkplace from '@/Shared/DataTable/PrinterWorkplace';
import OrderStatus from '../Shared/OrderStatus';
import Author from '@/Shared/DataTable/Author';
import Button from 'primevue/button';
import { Inertia } from '@inertiajs/inertia';


defineOptions({
    layout: Layout,
});

const urls = inject('urls');
const moment = inject('moment');
const auth = inject('auth');

const props = defineProps({
    orderSparePartDetail: Object,
    labels: Object,
});

const orderSparePartDetail = props.orderSparePartDetail.data;

const title = `Заказ № ${orderSparePartDetail.order.id} от ${moment(orderSparePartDetail.order.created_at).format('L')}`

const actions = {
    edit: () => {
        Inertia.get(urls.orders.spareParts.edit(orderSparePartDetail.id));
    },
    delete: () => {

    },
}

</script>
<template>
    <Head :title="title" />

    <Breadcrumbs :home="{ label: 'Главная', url: '/' }" :items="[
        { label: 'Заказ запчастей', url: urls.orders.spareParts.index() },
        { label: title },
    ]" />
    
    <Card>
        <template #title> {{ title }} </template>
        <template #content>
            <table class="w-1/2 text-left text-gray-700">
                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                    <th class="px-6 py-4">{{ labels.order_spare_part.id_printers_workplace }}</th>
                    <td class="px-6 py-4">
                        <PrinterWorkplace 
                            :vendor="orderSparePartDetail.printerWorkplace.printer.vendor" 
                            :model="orderSparePartDetail.printerWorkplace.printer.model"
                            :is_color_print="orderSparePartDetail.printerWorkplace.printer.is_color_print"
                            :location="orderSparePartDetail.printerWorkplace.location"
                            :inventory_number="orderSparePartDetail.printerWorkplace.inventory_number"
                            :serial_number="orderSparePartDetail.printerWorkplace.serial_number"
                        />
                    </td>
                </tr>
                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                    <th class="px-6 py-4">{{ labels.order_spare_part.id_spare_part_or_call_specialist }}</th>
                    <td class="px-6 py-4">
                        <template v-if="orderSparePartDetail.call_specialist">
                            <i class="fa-solid fa-phone me-2"></i> {{ labels.order_spare_part.call_specialist }}
                        </template>
                        <template v-else>
                            {{ orderSparePartDetail.sparePart.name }}
                        </template>
                    </td>
                </tr>
                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                    <th class="px-6 py-4">{{ labels.order.status }}</th>
                    <td class="px-6 py-4">
                        <OrderStatus :status="orderSparePartDetail.order.status" :statusLabel="orderSparePartDetail.order.status_label" />
                    </td>
                </tr>
                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                    <th class="px-6 py-4">{{ labels.order.requested_by }}</th>
                    <td class="px-6 py-4">
                        <Author 
                            :login="orderSparePartDetail.order.requested.name"
                            :fullName="orderSparePartDetail.order.requested.fio"
                            :post="orderSparePartDetail.order.requested.post"
                            :department="orderSparePartDetail.order.requested.department"
                        />
                    </td>
                </tr>
            </table>

            <div class="flex justify-between mt-10" v-if="auth.can('admin', 'editor-dictionary')">
                <div class="flex gap-2">
                    <Button class="font-bold" @click="actions.edit">Согласовать</Button>
                    <Button severity="danger" class="font-bold" @click="actions.delete">Отказать в согласовании</Button>
                </div>
                <div class="flex gap-2">
                    <Button class="font-bold" @click="actions.edit">Редактировать</Button>
                    <Button severity="danger" class="font-bold" @click="actions.delete">Удалить</Button>
                </div>                
            </div>

        </template>
    </Card>

</template>