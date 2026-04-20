<script setup>
import Layout from '@/Shared/Layout';
import { Head, router } from '@inertiajs/vue3';
import Breadcrumbs from '@/Shared/Breadcrumbs';
import DataTable from 'primevue/datatable';
import { computed, inject, reactive, ref, watch } from 'vue';
import { useToast } from 'primevue/usetoast';
import TableTitle from '@/Shared/TableTitle';
import Button from 'primevue/button';
import TreeSelect from 'primevue/treeselect';
import Dropdown from 'primevue/dropdown';
import InputText from 'primevue/inputtext';
import Column from 'primevue/column';
import OrderStatus from '../Shared/OrderStatus.vue';
import Author from '@/Shared/DataTable/Author.vue';
import Timestamps from '@/Shared/DataTable/Timestamps.vue';
import { throttle } from 'lodash';
import { pickBy } from 'lodash';
import axios from 'axios';


defineOptions({
  layout: Layout,
});

const props = defineProps({
  filters: Object,
  orders: Object,
  labels: Object,
  statuses: Object,
});

const listStatuses = computed(() => {
  let result = [];
  Object.entries(props.statuses).forEach(([key, label]) => result.push({ key, label: label.label }));
  return result;
});

const propsFiltersOrganizations = computed(() => {
  if (props.filters?.organizations) {
    return props.filters.organizations.reduce((acc, val) => {
      acc[val] = true;
      return acc;
    }, {});
  }
});

const urls = inject('urls');
const config = inject('config');
const toast = reactive(useToast());
const form = reactive({
  search: props.filters?.search,
  status: props.filters?.status,
  organizations: propsFiltersOrganizations.value,
});


const organizations = ref();
const loadDataOrgs = () => {
  axios.get(urls.users.organizations.index())
    .then((response) => {
      organizations.value = response.data.organizations;
      if (Array.isArray(organizations.value)) {
        organizations.value.forEach((item) => item.label = item.code);
      }
    })
    .catch((error) => {
      toast.add({
        severity: 'error',
        summary: 'Ошибка',
        detail: error.message,
        life: config.toast.timeLife,
      });
      console.error(error);
    })
};
loadDataOrgs();


const actions = {
  create: () => router.get(urls.orders.misc.create()),
  show: (id) => router.get(urls.orders.misc.show(id)),
};

const onRowSelect = (event) => {
  router.get(urls.orders.misc.show(event.data.id));
}

watch(
  () => form,
  throttle(() => {
    const picked = pickBy(form);
    picked.organizations = Object.keys(picked.organizations ?? {});
    router.get(urls.orders.misc.index(), pickBy(picked), { preserveState: true });
  }, 150),
  { deep: true }
);


const title = 'Заказ мелочей';

</script>
<template>
  <Head :title="title" />

  <Breadcrumbs :home="{ label: 'Главная', url: '/' }" :items="[
    { label: title },
  ]" />

  <div class="flex justify-stretch bg-white rounded-md shadow overflow-hidden mt-4">
    <DataTable :value="orders?.data" paginator :rows="10" dataKey="id" :metaKeySelection="false" class="w-full"
      tableStyle="min-width: 50rem" selectionMode="single" @rowSelect="onRowSelect">
      <template #header>
        <TableTitle class="border-b border-gray-200 pb-2">{{ title }}</TableTitle>
        <div class="flex justify-between mt-5">
          <Button @click="actions.create" severity="info">
            Заказать
          </Button>
          <div>
            <TreeSelect selectionMode="multiple" v-model="form.organizations" :options="organizations"
              placeholder="Организации" class="w-xs me-2" />
            <Dropdown v-model="form.status" :options="listStatuses" optionLabel="label" optionValue="key"
              placeholder="Статус" showClear class="w-20rem me-2" />
            <span class="relative">
              <i class="fas fa-search absolute top-2/4 -mt-2 left-3 text-surface-400"></i>
              <InputText v-model="form.search" placeholder="Поиск" class="pl-10 font-normal" />
            </span>
          </div>
        </div>
      </template>
      <Column header="#" headerStyle="width:3rem">
        <template #body="{ data }">
          {{ data.id }}
        </template>
      </Column>
      <Column :header="labels.order.status">
        <template #body="{ data }">
          <OrderStatus :status="data.order.status" :statuses="statuses" />
        </template>
      </Column>
      <Column :header="labels.order_misc.name" field="name"></Column>
      <Column :header="labels.order_misc.description" field="description"></Column>
      <Column :header="labels.order.org_code">
        <template #body="{ data }">
          {{ data.order.organization.name }}
          ({{ data.order.organization.code }})
        </template>
      </Column>
      <Column :header="labels.order.requested_by">
        <template #body="{ data }">
          <Author :login="data.order.requested.name" :fullName="data.order.requested.fio"
            :post="data.order.requested.post" :department="data.order.requested.department" />
        </template>
      </Column>
      <Column header="Дата">
        <template #body="{ data }">
          <Timestamps :created_at="data.order.created_at" :updated_at="data.order.updated_at" />
        </template>
      </Column>

      <template #empty> Нет данных </template>

    </DataTable>
  </div>

</template>
