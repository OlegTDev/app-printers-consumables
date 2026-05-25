<script setup>
import Author from '@/Shared/DataTable/Author.vue';
import Timestamps from '@/Shared/DataTable/Timestamps.vue';
import { Button, Column, DataTable } from 'primevue';

defineProps({
  isAdmin: {
    type: Boolean,
    default: false,
  },
  userId: {
    type: Number,
    default: null,
  },
  actionTooltip: {
    type: String,
    default: 'Отменить',
  },
  actionSeverity: {
    type: String,
    default: 'danger',
  },
  paginator: {
    type: Boolean,
    default: true,
  },
  rows: {
    type: Number,
    default: 10,
  },
  dataKey: {
    type: String,
    default: 'id',
  },
  tableStyle: {
    type: String,
    default: 'min-width: 50rem',
  },
  selectionMode: {
    type: String,
    default: 'single',
  },
  loading: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['actionClick']);
</script>
<template>
  <DataTable
    :paginator
    :rows
    :data-key
    :table-style
    :selection-mode
    :loading
  >
    <Column header="#" field="id" header-style="width:3rem" sortable />
    <Column header="Количество" class="font-bold" field="count" sortable>
      <template #body="{ data }">
        <i class="pi pi-box text-green-800" />
        {{ data.count }} шт.
      </template>
    </Column>
    <Column header="Автор" field="author.name" sortable>
      <template #body="{ data }">
        <Author :user="data.author" />
      </template>
    </Column>
    <Column field="created_at" header="Дата" sortable>
      <template #body="{ data }">
        <Timestamps :created-at="data.created_at" :updated-at="data.updated_at" />
      </template>
    </Column>
    <Column header="">
      <template #body="{ data }">
        <Button
          v-if="isAdmin || data.id_author == userId"
          v-tooltip="actionTooltip"
          icon="fas fa-redo-alt fa-flip-horizontal"
          :severity="actionSeverity"
          @click="emit('actionClick', data.id)"
        />
      </template>
    </Column>
    <template #empty>
      Нет данных
    </template>
  </DataTable>
</template>
