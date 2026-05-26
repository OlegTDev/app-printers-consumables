<script setup>
import Layout from '@/Shared/Layout.vue';
import { Head, router } from '@inertiajs/vue3';
import Breadcrumbs from '@/Shared/Breadcrumbs.vue';
import DataTable from 'primevue/datatable';
import { computed, onMounted, reactive, ref, watch } from 'vue';
import Button from 'primevue/button';
import TreeSelect from 'primevue/treeselect';
import InputText from 'primevue/inputtext';
import Column from 'primevue/column';
import OrderStatus from '../Shared/OrderStatus.vue';
import Author from '@/Shared/DataTable/Author.vue';
import Timestamps from '@/Shared/DataTable/Timestamps.vue';
import { pickBy } from 'lodash';
import axios from 'axios';
import { useNotification } from '@/Composables/useNotification';
import Card from '@/Shared/Card.vue';
import Title from '@/Shared/Title.vue';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import Select from 'primevue/select';
import { debounce } from 'lodash';


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
  return {};
});

const { showError } = useNotification();

const form = reactive({
  search: props.filters?.search,
  status: props.filters?.status,
  organizations: propsFiltersOrganizations.value,
});


const organizations = ref([]);
const loadDataOrgs = async() => {
  try {
    const response = await axios.get(route('users.organizations'));
    if (response.data?.organizations && Array.isArray(response.data.organizations)) {
      organizations.value = response.data.organizations.map((item) => ({
        key: item.code,
        label: item.code,
      }));
    }
  }
  catch (error) {
    showError(error.message);
  }
};

onMounted(() => {
  loadDataOrgs();
});


const actions = {
  create: () => router.get(route('orders.misc.create')),
  show: (id) => router.get(route('orders.misc.show', { orderMiscDetails: id })),
};

const onRowSelect = (event) => {
  actions.show(event.data.id);
};

watch(
  () => [form.search, form.status, form.organizations],
  debounce(() => {
    const picked = pickBy(form);
    if (picked.organizations) {
      picked.organizations = Object.keys(picked.organizations);
    }
    router.get(route('orders.misc.index'), picked, { preserveState: true });
  }, 300)
);

const title = 'Заказ мелочей';
</script>
<template>
  <Head :title="title" />

  <Breadcrumbs
    :home="{ label: 'Главная', url: '/' }"
    :items="[{ label: title }]"
  />

  <Card>
    <Title>{{ title }}</Title>

    <DataTable
      :value="orders?.data"
      paginator
      :rows="10"
      data-key="id"
      :meta-key-selection="false"
      table-style="min-width: 50rem"
      selection-mode="single"
      @row-select="onRowSelect"
    >
      <template #header>
        <div class="flex justify-between">
          <div>
            <Button severity="info" type="button" @click="actions.create">
              Заказать
            </Button>
          </div>
          <div class="flex justify-between gap-3">
            <TreeSelect
              v-model="form.organizations"
              :options="organizations"
              selection-mode="multiple"
              placeholder="Организации"
              class="w-xs"
            />
            <Select
              v-model="form.status"
              :options="listStatuses"
              option-label="label"
              option-value="key"
              placeholder="Статус"
              show-clear
              class="w-auto"
            />
            <IconField icon-position="left">
              <InputIcon><i class="pi pi-search" /></InputIcon>
              <InputText v-model="form.search" placeholder="Поиск" />
            </IconField>
          </div>
        </div>
      </template>
      <Column header="#" field="id" header-style="width:3rem" />
      <Column :header="labels.order.status">
        <template #body="{ data }">
          <OrderStatus :status="data.order.status" :statuses="statuses" />
        </template>
      </Column>
      <Column :header="labels.order_misc.name" field="name" />
      <Column :header="labels.order_misc.description" field="description" />
      <Column :header="labels.order.org_code">
        <template #body="{ data }">
          {{ data.order.organization.name }}
          ({{ data.order.organization.code }})
        </template>
      </Column>
      <Column :header="labels.order.requested_by">
        <template #body="{ data }">
          <Author
            :user="{
              fio: data.order.requested.fio,
              name: data.order.requested.name,
              post: data.order.requested.post,
              department: data.order.requested.department,
            }"
          />
        </template>
      </Column>
      <Column header="Дата">
        <template #body="{ data }">
          <Timestamps :created-at="data.order.created_at" :updated-at="data.order.updated_at" />
        </template>
      </Column>
      <template #empty>
        Нет данных
      </template>
    </DataTable>
  </Card>
</template>
