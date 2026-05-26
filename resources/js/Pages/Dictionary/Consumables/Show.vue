<script setup>
import Layout from '@/Shared/Layout.vue';
import Breadcrumbs from '@/Shared/Breadcrumbs.vue';
import { Head, router } from '@inertiajs/vue3';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import { useConfirm } from 'primevue/useconfirm';
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
  consumable: Object,
  cartridgeColors: Object,
  consumableTypeValue: String,
  consumableLabels: Object,
  printers: Object,
  printersNotIn: Object,
  printerLabels: Object,
});

const { can } = useAuth();
const confirm = useConfirm();
const consumable = props.consumable;
const consumableLabels = props.consumableLabels;
const cartridgeColors = props.cartridgeColors;

const title = `${props.consumableTypeValue} ${consumable.name}`;

const createRelation = () => {
  const url = route('dictionary.consumables.printers.index', { consumable: consumable.id });
  router.get(url);
};

const deleteRelation = (id) => {
  confirm.require({
    message: 'Вы уверены, что хотите удалить связь?',
    header: 'Удаление связи',
    accept: () => {
      const url = route('dictionary.consumables.printers.destroy', { consumable: consumable.id, printer: id });
      router.delete(url);
    },
  });
};

const goToEdit = () =>
  router.get(route('dictionary.consumables.edit', { consumable: consumable.id }));

const deleteConsumable = () => {
  confirm.require({
    message: 'Вы уверены, что хотите удалить запись?',
    header: 'Удаление записи',
    accept: () => {
      const url = route('dictionary.consumables.destroy', { consumable: consumable.id });
      router.delete(url);
    },
  });
};
</script>
<template>
  <Head :title="title" />

  <Breadcrumbs
    :home="{ label: 'Главная', url: route('dashboard') }"
    :items="[
      {
        label: 'Расходные материалы (справочник)',
        url: route('dictionary.consumables.index'),
      },
      { label: title },
    ]"
  />

  <Card>
    <Title>{{ props.consumable.name }} </Title>

    <DetailViewer
      :items="[
        {
          label: consumableLabels.type,
          value: props.consumableTypeValue,
        },
        {
          label: consumableLabels.color,
          hide: consumable.type !== 'cartridge',
          keySlot: 'color',
        },
        {
          label: consumableLabels.description,
          value: consumable.description || '-',
        },
        {
          label: consumableLabels.author,
          keySlot: 'author',
        },
        {
          label: consumableLabels.date,
          keySlot: 'date',
        },
      ]"
    >
      <template #color>
        <div class="flex">
          <div class="rounded-full size-4 mr-2" :class="[cartridgeColors[consumable.color]['bg']]" />
          <div>
            {{ cartridgeColors[consumable.color]['name'] }}
          </div>
        </div>
      </template>
      <template #author>
        <Author :user="consumable.author" class-main="text-base" />
      </template>
      <template #date>
        <Timestamps :created-at="consumable.created_at" :updated-at="consumable.updated_at" />
      </template>
    </DetailViewer>

    <template #footer>
      <Button class="font-bold" @click="goToEdit">
        Редактировать
      </Button>
      <Button class="font-bold" severity="danger" @click="deleteConsumable">
        Удалить
      </Button>
    </template>
  </Card>

  <Card class="mt-2">
    <template #default>
      <DataTable :value="printers">
        <template #header>
          <Title :h="2">
            Привязки к принтерам
          </Title>
          <div class="flex justify-between mt-5">
            <Button v-if="can('admin', 'editor-dictionary')" class="font-bold" type="button" severity="info" @click="createRelation">
              Добавить привязку к принтеру
            </Button>
          </div>
        </template>

        <Column header="#" header-style="width:3rem">
          <template #body="data">
            {{ data.index + 1 }}
          </template>
        </Column>
        <Column field="vendor" header="Производитель" sortable />
        <Column field="model" header="Модель" sortable />
        <Column field="is_color_print" header="Цветная печать" sortable>
          <template #body="{ data }">
            {{ data.is_color_print ? 'Да' : 'Нет' }}
          </template>
        </Column>
        <Column v-if="can('admin', 'editor-dictionary')">
          <template #body="{ data }">
            <Button
              v-tooltip="`Удалить привязку`"
              severity="danger"
              type="button"
              @click="deleteRelation(data.id)"
            >
              <i class="pi pi-times" />
            </Button>
          </template>
        </Column>

        <template #empty>
          Нет данных
        </template>
      </DataTable>
    </template>
  </Card>
</template>
