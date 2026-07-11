<script setup>
import { Head, router } from '@inertiajs/vue3';
import Layout from '@/Shared/Layout.vue';
import { reactive } from 'vue';
import Column from 'primevue/column';
import MultiSelect from 'primevue/multiselect';
import Breadcrumbs from '@/Shared/Breadcrumbs.vue';
import Button from 'primevue/button';
import Title from '@/Shared/Title.vue';
import Card from '@/Shared/Card.vue';
import RemoteDataTable from '@/Shared/DataTable/RemoteDataTable.vue';

const props = defineProps({
  items: Object,
  query: Object,
  roles: Array,
});

defineOptions({
  layout: Layout
});

const form = reactive({
  roles: props.query?.roles,
});

const create = () => {
  router.get(route('users.create'));
};

const onRowSelect = (event) => {
  router.get(route('users.edit', { user: event.data.id }));
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

    <Card>
      <Title>{{ title }}</Title>

      <RemoteDataTable
        :model="items"
        :url="route('users.index')"
        data-key="id"
        selection-mode="single"
        :filters="form"
        @row-select="onRowSelect"
      >
        <template #header>
          <Button @click="create">
            Добавить
          </Button>
        </template>
        <template #filters>
          <MultiSelect
            v-model="form.roles"
            :options="roles"
            option-value="name"
            option-label="description"
            placeholder="Роли"
            class="w-56"
          />
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
            <i v-if="data.deleted_at" v-tooltip.left="`Учетная запись удалена`" class="pi pi-times text-red-600" style="font-weight: 600;" />
            <i v-else v-tooltip.isLeftHandSideExpression="`Действующая учетная запись`" class="pi pi-check text-primary" style="font-weight: 600;" />
          </template>
        </Column>
      </RemoteDataTable>
    </Card>
  </div>
</template>
