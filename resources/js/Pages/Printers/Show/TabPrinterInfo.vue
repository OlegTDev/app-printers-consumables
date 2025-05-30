<script setup>
import Card from 'primevue/card';
import DetailViewer from '@/Shared/DetailViewer';
import { Inertia } from '@inertiajs/inertia';
import { inject } from 'vue';
import Button from 'primevue/button';
import { useConfirm } from 'primevue/useconfirm';

const props = defineProps({
    title: String,
    printerLabels: Object,
    printer: Object,
    printerWorkplaceLabels: Object,
    printerWorkplace: Object,
    auth: Object,
    urls: Object,
    organization: Object,
});

const confirm = useConfirm();
const LogActions = inject('LogActions');

const actions = {
    edit: () => Inertia.get(props.urls.printers.edit(props.printerWorkplace.id)),
    delete: () => confirm.require({
        message: 'Вы уверены, что хотите удалить?',
        header: 'Удаление',
        accept: () => {
            const url = props.urls.printers.delete(props.printerWorkplace.id);
            Inertia.delete(url, {
                onSuccess: () => {
                    LogActions.save(url, 'DELETE', 'Удаление принтера с рабочего места', printerWorkplace);
                },
            });
        },
    }),
};

</script>
<template>
    <Card>
        <template #title> {{ title }} </template>
        <template #content>
            <DetailViewer :items="[
                {
                    label: printerLabels.vendor,
                    value: printer.vendor
                },
                {
                    label: printerLabels.model,
                    value: printer.model
                },
                {
                    label: printerLabels.is_color_print,
                    value: printer.is_color_print ? 'Да' : 'Нет',
                },
                {
                    label: printerWorkplaceLabels.org_code,
                    value: `${organization.name} (${organization.code})`,
                },
                {
                    label: printerWorkplaceLabels.location,
                    value: printerWorkplace.location,
                },
                {
                    label: printerWorkplaceLabels.serial_number,
                    value: printerWorkplace.serial_number,
                },
                {
                    label: printerWorkplaceLabels.inventory_number,
                    value: printerWorkplace.inventory_number,
                },
                {
                    label: printerWorkplaceLabels.author,
                    value: printerWorkplace.author.fio ?? printerWorkplace.author.name,
                },
                {
                    label: printerWorkplaceLabels.created_at,
                    value: printerWorkplace.created_at,
                    is_date: true,
                    icon: 'far fa-calendar',
                },
                {
                    label: printerWorkplaceLabels.updated_at,
                    value: printerWorkplace.updated_at,
                    is_date: true,
                    icon: 'far fa-calendar-alt',
                },
            ]"></DetailViewer>

            <div v-if="auth.can('admin', 'editor-printer-workplace')" class="flex justify-between mt-10 font-bold">
                <Button @click="actions.edit">Редактировать</Button>
                <Button severity="danger" @click="actions.delete">Удалить</Button>
            </div>
        </template>
    </Card>
</template>