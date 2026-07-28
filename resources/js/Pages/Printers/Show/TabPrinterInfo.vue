<script setup>
import DetailViewer from '@/Shared/DetailViewer.vue';
import { router } from '@inertiajs/vue3';
import Button from 'primevue/button';
import { useConfirm } from 'primevue/useconfirm';
import { useAuth } from '@/Composables/useAuth';
import { computed } from 'vue';
import Title from '@/Shared/Title.vue';
import Timestamps from '@/Shared/DataTable/Timestamps.vue';
import Author from '@/Shared/DataTable/Author.vue';

const props = defineProps({
  title: String,
  printerLabels: Object,
  printerWorkplaceLabels: Object,
  printerWorkplace: Object,
});

const confirm = useConfirm();
const { can } = useAuth();

const actions = {
  edit: () => router.get(route('workplace.edit', { workplace: props.printerWorkplace.id })),
  delete: () => confirm.require({
    message: 'Вы уверены, что хотите удалить?',
    header: 'Удаление',
    accept: () => {
      const url = route('workplace.destroy', { workplace: props.printerWorkplace.id });
      router.delete(url);
    },
  }),
};

const items = computed(() => [
  {
    label: props.printerLabels.vendor,
    value: props.printerWorkplace.printer.vendor
  },
  {
    label: props.printerLabels.model,
    value: props.printerWorkplace.printer.model
  },
  {
    label: props.printerLabels.is_color_print,
    value: props.printerWorkplace.printer.is_color_print ? 'Да' : 'Нет',
  },
  {
    label: props.printerWorkplaceLabels.org_code,
    value: `${props.printerWorkplace.organization.name} (${props.printerWorkplace.organization.code})`,
  },
  {
    label: props.printerWorkplaceLabels.location,
    value: props.printerWorkplace.location,
  },
  {
    label: props.printerWorkplaceLabels.serial_number,
    value: props.printerWorkplace.serial_number,
  },
  {
    label: props.printerWorkplaceLabels.inventory_number,
    value: props.printerWorkplace.inventory_number,
  },
  { label: props.printerWorkplaceLabels.author, keySlot: 'author' },
  { label: props.printerWorkplaceLabels.updated_at, keySlot: 'dateCreated' },
  { label: props.printerWorkplaceLabels.updated_at, keySlot: 'dateUpdated' },
]);

</script>
<template>
  <Title>
    {{ title }}
  </Title>

  <DetailViewer :items="items">
    <template #author>
      <Author :user="props.printerWorkplace.author || {}" />
    </template>
    <template #dateCreated>
      <Timestamps :created-at="printerWorkplace.created_at" />
    </template>
    <template #dateUpdated>
      <Timestamps :updated-at="printerWorkplace.updated_at" />
    </template>
  </DetailViewer>

  <div v-if="can('admin', 'editor-printer-workplace')" class="flex justify-between mt-10 font-bold">
    <Button @click="actions.edit">
      Редактировать
    </Button>
    <Button severity="danger" @click="actions.delete">
      Удалить
    </Button>
  </div>
</template>
