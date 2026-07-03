<script setup>
import Layout from '@/Shared/Layout.vue';
import { Head, router } from '@inertiajs/vue3';
import Breadcrumbs from '@/Shared/Breadcrumbs.vue';
import Button from 'primevue/button';
import { computed, onMounted, reactive, ref } from 'vue';
import TreeSelect from 'primevue/treeselect';
import Column from 'primevue/column';
import OrderStatus from '../Shared/OrderStatus.vue';
import Author from '@/Shared/DataTable/Author.vue';
import Timestamps from '@/Shared/DataTable/Timestamps.vue';
import Tag from 'primevue/tag';
import axios from 'axios';
import { useNotification } from '@/Composables/useNotification';
import Card from '@/Shared/Card.vue';
import Title from '@/Shared/Title.vue';
import Select from 'primevue/select';
import RemoteDataTable from '@/Shared/DataTable/RemoteDataTable.vue';


defineOptions({
  layout: Layout,
});

const props = defineProps({
  items: Object,
  query: Object,
  labels: Object,
  statuses: Object,
  consumableTypes: Object,
  cartridgeColors: Object,
});

const listStatuses = computed(() => {
  let result = [];
  Object.entries(props.statuses).forEach(([key, label]) => result.push({ key, label: label.label }));
  return result;
});

const initialOrganizations = computed(() => {
  if (props.query?.organizations) {
    const orgs = Array.isArray(props.query.organizations)
      ? props.query.organizations
      : [props.query.organizations];

    return orgs.reduce((acc, val) => {
      if (val) {
        acc[val] = true;
      }
      return acc;
    }, {});
  }
  return {};
});

const { showError } = useNotification();

const form = reactive({
  status: props.query?.status,
  organizations: initialOrganizations.value,
});

const computedFilters = computed(() => {
  const activeOrgs = Object.keys(form.organizations).filter(key => form.organizations[key]);
  return {
    status: form.status || null,
    organizations: activeOrgs.length ? activeOrgs : null,
  };
});

const organizations = ref();
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
  create: () => router.get(route('orders.consumables.create')),
  show: (id) => router.get(route('orders.consumables.show', { orderConsumableDetails: id })),
};

const onRowSelect = (event) => {
  actions.show(event.data.id);
};


const title = 'Заказ картриджей';
</script>
<template>
  <Head :title="title" />

  <Breadcrumbs :home="{ label: 'Главная', url: route('home') }" :items="[{ label: title }]" />

  <Card>
    <Title>{{ title }}</Title>

    <RemoteDataTable
      :model="items"
      :url="route('orders.consumables.index')"
      :filters="computedFilters"
      data-key="id"
      selection-mode="single"
      @row-select="onRowSelect"
    >
      <template #header>
        <Button severity="info" @click="actions.create">
          Заказать
        </Button>
      </template>
      <template #filters>
        <TreeSelect
          v-if="organizations"
          v-model="form.organizations"
          :options="organizations"
          selection-mode="multiple"
          placeholder="Организации"
          class="w-xs"
        />
        <div v-else class="w-64 h-10 bg-gray-100 animate-pulse rounded-md border border-gray-300" />

        <Select
          v-model="form.status"
          :options="listStatuses"
          option-label="label"
          option-value="key"
          placeholder="Статус"
          show-clear
          class="w-auto"
        />
      </template>
      <Column header="#" field="id" header-style="width:3rem" />
      <Column :header="labels.order.status">
        <template #body="{ data }">
          <OrderStatus :status="data.order.status" :statuses="statuses" />
        </template>
      </Column>
      <Column :header="labels.order_consumable.id_consumable">
        <template #body="{ data }">
          <div class="grid grid-rows-2 gap-4">
            <div>{{ consumableTypes[data.consumable.type] ?? data.consumable.type }}</div>
            <div>
              {{ data.consumable.name }}
            </div>
            <div v-if="data.consumable.type === 'cartridge'">
              <div class="flex">
                <div :class="['rounded-full', 'size-4', 'mr-2', cartridgeColors[data.consumable.color]?.bg]" />                <div>
                  {{ cartridgeColors[data.consumable.color]?.name }}
                </div>
              </div>
            </div>
            <div class="text-gray-500">
              {{ data.consumable.description }}
            </div>
          </div>
        </template>
      </Column>
      <Column :header="labels.order.quantity">
        <template #body="{ data }">
          <Tag :value="data.order.quantity" />
        </template>
      </Column>
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
    </RemoteDataTable>
  </Card>
</template>
