<script setup>
import Layout from '@/Shared/Layout.vue';
import { Head, router } from '@inertiajs/vue3';
import Breadcrumbs from '@/Shared/Breadcrumbs.vue';
import { useConfirm } from "primevue/useconfirm";
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Card from '@/Shared/Card.vue';
import Title from '@/Shared/Title.vue';
import DetailViewer from '@/Shared/DetailViewer.vue';
import Author from '@/Shared/DataTable/Author.vue';
import Timestamps from '@/Shared/DataTable/Timestamps.vue';
import { useAuth } from '@/Composables/useAuth';

defineOptions({
  layout: Layout,
});

const props = defineProps({
  printer: Object,
  consumables: Object,
  consumablesNotIn: Object,
  printerLabels: Object,

  cartridgeColors: Object,
  consumableTypes: Object,
  consumableLabels: Object,
});

const confirm = useConfirm();

const { can } = useAuth();

const title = `${props.printer.vendor} ${props.printer.model}`;
const goToEdit = () => router.get(route('dictionary.printers.edit', { printer: props.printer.id }));

const deletePrinter = () => {
  confirm.require({
    message: 'Вы уверены, что хотите удалить запись?',
    header: 'Удаление записи',
    accept: () => {
      const url = route('dictionary.printers.destroy', { printer: props.printer.id });
      router.delete(url);
    },
  });
};

const createRelation = () => {
  router.get(route('dictionary.printers.consumables.index', { printer: props.printer.id }));
};

const deleteRelation = (consumableId) => {
  confirm.require({
    message: 'Вы уверены, что хотите удалить связь?',
    header: 'Удаление связи',
    accept: () => {
      const url = route('dictionary.printers.consumables.destroy', { printer: props.printer.id, consumable: consumableId });
      router.delete(url);
    },
  });
};

</script>

<template>
  <Head :title="title" />

  <Breadcrumbs
    :home="{ label: 'Главная', url: route('home') }"
    :items="[
      { label: 'Справочники', },
      { label: 'Принтеры', url: route('dictionary.printers.index') },
      { label: title },
    ]"
  />

  <Card>
    <Title>{{ title }}</Title>

    <DetailViewer
      :items="[
        { label: printerLabels.vendor, value: printer.vendor },
        { label: printerLabels.model, value: printer.model },
        { label: printerLabels.is_color_print, value: printer.is_color_print ? 'Да': 'Нет' },
        { label: printerLabels.author, keySlot: 'author', },
        { label: printerLabels.date || 'Дата', keySlot: 'date', },
      ]"
    >
      <template #author>
        <Author :user="printer.author" class-main="text-base" />
      </template>
      <template #date>
        <Timestamps :created-at="printer.created_at" :updated-at="printer.updated_at" />
      </template>
    </DetailViewer>

    <template #footer>
      <Button class="font-bold" @click="goToEdit">
        Редактировать
      </Button>
      <Button severity="danger" class="font-bold" @click="deletePrinter">
        Удалить
      </Button>
    </template>
  </Card>

  <Card class="mt-2">
    <DataTable :value="consumables">
      <template #header>
        <Title :h="2">
          Привязки к расходным материалам
        </Title>
        <div class="flex justify-between mt-5">
          <Button v-if="can('admin', 'editor-dictionary')" type="button" severity="info" @click="createRelation">
            Добавить привязку к расходному материалу
          </Button>
        </div>
      </template>

      <Column header="#" field="id" header-style="width:3rem" />
      <Column field="type" :header="consumableLabels.type" sortable>
        <template #body="{ data }">
          {{ props.consumableTypes[data.type] ?? data.type }}
        </template>
      </Column>
      <Column field="name" :header="consumableLabels.name" sortable>
        <template #body="{ data }">
          <div class="grid grid-rows-2 gap-4">
            <div>
              {{ data.name }}
            </div>
            <div v-if="data.type === 'cartridge'">
              <div class="flex">
                <div :class="['rounded-full', 'size-4', 'mr-2', props.cartridgeColors[data.color]?.bg]" />
                <div>
                  {{ props.cartridgeColors[data.color]['name'] }}
                </div>
              </div>
            </div>
          </div>
        </template>
      </Column>
      <Column v-if="can('admin', 'editor-dictionary')" header="">
        <template #body="{ data }">
          <Button v-tooltip="`Удалить привязку`" severity="danger" type="button" @click="deleteRelation(data.id)">
            <i class="fas fa-times" />
          </Button>
        </template>
      </Column>
      <template #empty>
        Нет данных
      </template>
    </DataTable>
  </Card>
</template>
