<script setup>
import { Head, router } from '@inertiajs/vue3';
import pickBy from 'lodash/pickBy';
import Layout from '@/Shared/Layout.vue';
import Tag from 'primevue/tag';
import { reactive, ref, watch } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import InputText from 'primevue/inputtext';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import MultiSelect from 'primevue/multiselect';
import Breadcrumbs from '@/Shared/Breadcrumbs.vue';
import Button from 'primevue/button';
import { debounce } from 'lodash';

const props = defineProps({
  filters: Object,
  users: Array,
  roles: Array,
});

defineOptions({
  layout: Layout
});

const filters = reactive(props.filters);

const form = reactive({
  search: filters.search,
  role: filters.role,
  trashed: filters.trashed,
});

watch(
  () => form,
  debounce(() => {
    router.get(route('users.index'), pickBy(form), { preserveState: true });
  }, 300),
  { deep: true }
);


const create = () => {
  router.get(route('users.create'));
};

const onRowSelect = (event) => {
  router.get(route('users.edit', { user: event.data.id }));
};

const refTableUsers = ref(null);

const onPageChange = () => {
  const elementTableUsers = refTableUsers.value.$el;
  if (elementTableUsers) {
    elementTableUsers.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }
};

const title = 'Пользователи';
</script>
<template>
  <div>
    <Head :title="title" />

    <Breadcrumbs
      :home="{ label: 'Главная', url: route('home') }"
      :items="[
        { label: title },
      ]"
    />

    <div class="flex justify-stretch bg-white rounded-md shadow overflow-hidden mt-4">
      <DataTable
        ref="refTableUsers"
        :value="users"
        paginator
        :rows="10"
        data-key="id"
        :meta-key-selection="false"
        class="w-full"
        table-style="min-width: 50rem"
        selection-mode="single"
        @row-select="onRowSelect"
        @page="onPageChange"
      >
        <template #header>
          <div class="flex justify-between">
            <Button @click="create">
              Добавить
            </Button>
            <div class="flex">
              <MultiSelect
                v-model="form.role"
                :options="roles"
                option-value="name"
                option-label="description"
                placeholder="Роли"
                class="w-56"
              />
              <IconField icon-position="left" class="ml-3">
                <InputIcon><i class="pi pi-search" /></InputIcon>
                <InputText v-model="form.search" placeholder="Поиск" />
              </IconField>
            </div>
          </div>
        </template>

        <Column field="org_code" header="Код НО" />
        <Column field="name" header="Учетная запись">
          <template #body="{ data }">
            <div class="flex items-center">
              <img v-if="data.photo" class="block -my-2 mr-2 w-5 h-5 rounded-full" :src="data.photo">
              {{ data.name }}
            </div>
          </template>
        </Column>
        <Column field="fio" header="ФИО" />
        <Column field="department" header="Отдел" />
        <Column field="lotus_mail" header="Коммуникация" class="w-50">
          <template #body="{ data }">
            <div v-if="data.telephone">
              <i class="pi pi-phone me-1" />
              {{ data.telephone }}
            </div>
            <div v-if="data.lotus_mail">
              <i class="pi pi-send me-1 mt-2" />
              {{ data.lotus_mail }}
            </div>
          </template>
        </Column>
        <Column header="Роли" class="w-64">
          <template #body="{ data }">
            <div v-if="data.roles?.length > 0" class="grid grid-cols-1 gap-2 text-sm font-medium">
              <div
                v-for="role in data.roles"
                :key="role.name"
                class="bg-blue-100 text-blue-800 px-2.5 py-0.5 rounded text-center"
              >
                {{ role.description }}
              </div>
            </div>
            <span v-else class="bg-yellow-100 text-yellow-800 px-2.5 py-0.5 rounded">
              Нет ролей
            </span>
          </template>
        </Column>
        <Column header="Контекст" class="w-52">
          <template #body="{ data }">
            <div v-if="data.organizations.length > 0" class="grid grid-cols-1 gap-2 text-sm font-medium">
              <div
                v-for="organization in data.organizations"
                :key="organization.code"
                class="bg-blue-100 text-blue-800 px-2.5 py-0.5 rounded"
              >
                {{ organization.name }}
              </div>
            </div>
            <span v-else class="bg-yellow-100 text-yellow-800 px-2.5 py-0.5 rounded">
              Без контекста
            </span>
          </template>
        </Column>
        <Column header="Статус">
          <template #body="{ data }">
            <Tag v-if="data.deleted_at" severity="danger" icon="pi pi-times" value="Удалена" />
            <Tag v-else severity="success" icon="pi pi-check" value="Действующая" />
          </template>
        </Column>
      </DataTable>
    </div>
  </div>
</template>
