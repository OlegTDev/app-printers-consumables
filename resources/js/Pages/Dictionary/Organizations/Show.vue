<script setup>
import Layout from '@/Shared/Layout.vue';
import { Head, router } from '@inertiajs/vue3';
import Breadcrumbs from '@/Shared/Breadcrumbs.vue';
import { useConfirm } from "primevue/useconfirm";
import Button from 'primevue/button';
import { useConfig } from '@/Composables/useConfig';
import Card from '@/Shared/Card.vue';
import Title from '@/Shared/Title.vue';
import Timestamps from '@/Shared/DataTable/Timestamps.vue';
import DetailViewer from '@/Shared/DetailViewer.vue';

defineOptions({
  layout: Layout,
});

const props = defineProps({
  organization: Object,
  labels: Object,
});

const { urls } = useConfig();
const confirm = useConfirm();

const title = `${props.organization.name} (${props.organization.code})`;
const goToEdit = () => router.get(urls.dictionary.organizations.edit(props.organization.code));

const deleteOrganization = () => {
    confirm.require({
        message: 'Вы уверены, что хотите удалить запись?',
        header: 'Удаление записи',
        accept: () => {
            const url = urls.dictionary.organizations.delete(props.organization.code);
            router.delete(url);
        },
    });
};
</script>
<template>
  <Head :title="title" />

  <Breadcrumbs
    :home="{ label: 'Главная', url: urls.home }"
    :items="[
      { label: 'Справочники' },
      { label: 'Организации', url: urls.dictionary.organizations.index() },
      { label: title },
    ]"
  />
  <Card>
    <Title>{{ title }}</Title>

    <DetailViewer
      :items="[
        { label: labels.code, value: organization.code },
        { label: labels.parent, value: organization.parent },
        { label: labels.name, value: organization.name },
        { label: labels.date, keySlot: 'date' },
      ]"
    >
      <template #date>
        <Timestamps :created-at="organization.created_at" :updated-at="organization.updated_at" />
      </template>
    </DetailViewer>

    <template #footer>
      <Button class="font-bold" @click="goToEdit">
        Редактировать
      </Button>
      <Button severity="danger" class="font-bold" @click="deleteOrganization">
        Удалить
      </Button>
    </template>
  </Card>
</template>
