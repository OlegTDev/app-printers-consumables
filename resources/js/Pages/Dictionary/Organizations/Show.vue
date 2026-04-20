<script setup>
import Layout from '@/Shared/Layout';
import { inject } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import Breadcrumbs from '@/Shared/Breadcrumbs';
import Card from 'primevue/card';
import { useConfirm } from "primevue/useconfirm";
import Button from 'primevue/button';
import Detail from '@/Shared/Detail.vue';

defineOptions({
    layout: Layout,
});

const props = defineProps({
    organization: Object,
    labels: Object,
});

const urls = inject('urls');
const organization = props.organization;
const labels = props.labels;
const confirm = useConfirm();

const title = `${organization.name} (${organization.code})`;
const goToEdit = () => router.get(urls.dictionary.organizations.edit(organization.code));

const LogActions = inject('LogActions');

const deleteOrganization = () => {
    confirm.require({
        message: 'Вы уверены, что хотите удалить запись?',
        header: 'Удаление записи',
        accept: () => {
            const url = urls.dictionary.organizations.delete(organization.code);
            router.delete(url, {
                onSuccess: () => {
                    LogActions.save(url, 'DELETE', 'Удаление организации', organization);
                },
            });
        },
    });
};
</script>

<template>

    <Head :title="title" />

    <Breadcrumbs :home="{ label: 'Главная', url: urls.home }" :items="[
        { label: 'Справочники' },
        { label: 'Организации', url: urls.dictionary.organizations.index() },
        { label: title },
    ]" />

    <Card>
        <template #title> {{ title }} </template>
        <template #content>

            <Detail :items="[
                { label: labels.code, value: organization.code },
                { label: labels.parent, value: organization.parent },
                { label: labels.name, value: organization.name },
                { label: labels.created_at, value: organization.created_at, format: 'date' },
                { label: labels.updated_at, value: organization.updated_at, format: 'date' },
            ]">
            </Detail>

            <div class="flex justify-between mt-10">
                <Button class="font-bold" @click="goToEdit">Редактировать</Button>
                <Button severity="danger" class="font-bold" @click="deleteOrganization">Удалить</Button>
            </div>
        </template>
    </Card>

</template>
