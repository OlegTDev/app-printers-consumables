<script setup>
import DetailViewer from '@/Shared/DetailViewer.vue';
import { router } from '@inertiajs/vue3';
import Button from 'primevue/button';
import { useConfirm } from 'primevue/useconfirm';
import { useAuth } from '@/Composables/useAuth';
import { computed } from 'vue';
import Title from '@/Shared/Title.vue';
import { useConfig } from '@/Composables/useConfig';

const props = defineProps({
  title: String,
  printerLabels: Object,
  printer: Object,
  printerWorkplaceLabels: Object,
  printerWorkplace: Object,
  organization: Object,
});

const confirm = useConfirm();
const { can } = useAuth();
const { urls } = useConfig();

const actions = {
  edit: () => router.get(urls.printers.edit(props.printerWorkplace.id)),
  delete: () => confirm.require({
    message: 'Вы уверены, что хотите удалить?',
    header: 'Удаление',
    accept: () => {
      const url = urls.printers.delete(props.printerWorkplace.id);
      router.delete(url);
    },
  }),
};

const items = computed(() => [
  {
    label: props.printerLabels.vendor,
    value: props.printer.vendor
  },
  {
    label: props.printerLabels.model,
    value: props.printer.model
  },
  {
    label: props.printerLabels.is_color_print,
    value: props.printer.is_color_print ? 'Да' : 'Нет',
  },
  {
    label: props.printerWorkplaceLabels.org_code,
    value: `${props.organization.name} (${props.organization.code})`,
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
  {
    label: props.printerWorkplaceLabels.author,
    value: props.printerWorkplace.author.fio ?? props.printerWorkplace.author.name,
  },
  {
    label: props.printerWorkplaceLabels.created_at,
    value: props.printerWorkplace.created_at,
    is_date: true,
    icon: 'far fa-calendar',
  },
  {
    label: props.printerWorkplaceLabels.updated_at,
    value: props.printerWorkplace.updated_at,
    is_date: true,
    icon: 'far fa-calendar-alt',
  },
]);

</script>
<template>
  <Title>
    {{ title }}
  </Title>

  <DetailViewer :items="items" />

  <div v-if="can('admin', 'editor-printer-workplace')" class="flex justify-between mt-10 font-bold">
    <Button @click="actions.edit">
      Редактировать
    </Button>
    <Button severity="danger" @click="actions.delete">
      Удалить
    </Button>
  </div>
</template>
