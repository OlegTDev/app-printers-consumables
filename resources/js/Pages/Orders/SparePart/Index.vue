<script setup>
import Layout from '@/Shared/Layout.vue';
import { Head, router } from '@inertiajs/vue3';
import Breadcrumbs from '@/Shared/Breadcrumbs.vue';
import DataTable from 'primevue/datatable';
import Card from '@/Shared/Card.vue';
import InputText from 'primevue/inputtext';
import Column from 'primevue/column';
import { computed, onMounted, reactive, ref, watch } from 'vue';
import Button from 'primevue/button';
import OrderStatus from '../Shared/OrderStatus.vue';
import PrinterWorkplace from '@/Shared/DataTable/PrinterWorkplace.vue';
import Author from '@/Shared/DataTable/Author.vue';
import Timestamps from '@/Shared/DataTable/Timestamps.vue';
import { pickBy, debounce } from 'lodash';
import axios from 'axios';
import TreeSelect from 'primevue/treeselect';
import Title from '@/Shared/Title.vue';
import { useNotification } from '@/Composables/useNotification';
import InputIcon from 'primevue/inputicon';
import IconField from 'primevue/iconfield';
import Select from 'primevue/select';
import { useDate } from '@/Composables/useDate';

defineOptions({
  layout: Layout,
});

const props = defineProps({
  filters: {
    type: Object,
    required: true,
  },
  orders: {
    type: Object,
    default: () => ({ data: []}),
  },
  labels: {
    type: Object,
    required: true,
  },
  statuses: {
    type: Object,
    required: true,
  },
});

const listStatuses = computed(() =>
  Object.keys(props.statuses || {}).map((key) => ({
    key,
    label: props.statuses[key].label,
  }))
);

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
const { formatDate } = useDate();

const form = reactive({
  search: props.filters?.search,
  status: props.filters?.status,
  organizations: propsFiltersOrganizations.value,
});

const organizations = ref();
const loadDataOrgs = async () => {
  try {
    const response = await axios.get(route('users.organizations'));
    if (
      response.data?.organizations &&
      Array.isArray(response.data.organizations)
    ) {
      organizations.value = response.data.organizations.map((item) => ({
        key: item.code,
        label: item.code,
      }));
    }
  } catch (error) {
    showError(error.message);
  }
};

onMounted(() => {
  loadDataOrgs();
});

const actions = {
  create: () => router.get(route('orders.spare-parts.create')),
  show: (id) => router.get(route('orders.spare-parts.show', { orderSparePartDetails: id })),
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
    router.get(route('orders.spare-parts.index'), picked, { preserveState: true });
  }, 300)
);

const title = 'Заказ запчастей';
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
      class="w-full"
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
      <Column header="#" header-style="width:3rem">
        <template #body="{ data }">
          {{ data.id }}
        </template>
      </Column>
      <Column :header="labels.order.status">
        <template #body="{ data }">
          <OrderStatus :status="data.order.status" :statuses="statuses" />
        </template>
      </Column>
      <Column
        :header="labels.order_spare_part.id_spare_part_or_call_specialist"
      >
        <template #body="{ data }">
          <template v-if="data.call_specialist">
            <i class="pi pi-phone text-primary-600 me-2" />
            {{ labels.order_spare_part.call_specialist }}
          </template>
          <template v-else>
            <div class="flex flex-col gap-3">
              <div>
                {{ data.sparePart.name }}
              </div>
              <div v-if="data.sparePart.description" class="text-gray-500">
                {{ data.sparePart.description }}
              </div>
            </div>
          </template>
        </template>
      </Column>
      <Column :header="labels.order_spare_part.id_printers_workplace">
        <template #body="{ data }">
          <PrinterWorkplace
            :vendor="data.printerWorkplace?.printer?.vendor"
            :model="data.printerWorkplace?.printer?.model"
            :is-color-print="data.printerWorkplace?.printer?.is_color_print"
            :location="data.printerWorkplace?.location"
            :inventory-number="data.printerWorkplace?.inventory_number"
            :serial-number="data.printerWorkplace?.serial_number"
          />
        </template>
      </Column>
      <Column :header="labels.order.org_code">
        <template #body="{ data }">
          {{ data.order?.organization?.name }}
          ({{ data.order?.organization?.code }})
        </template>
      </Column>
      <Column :header="labels.order?.service_request">
        <template #body="{ data }">
          <template v-if="data.order.service_request_number">
            № {{ data.order?.service_request_number }}
          </template>
          <template v-if="data.order.service_request_date">
            от {{ formatDate(data.order.service_request_date, 'L') }}
          </template>
        </template>
      </Column>
      <Column :header="labels.order.requested_by">
        <template #body="{ data: { order } }">
          <Author :user="order?.requested || {}" />
        </template>
      </Column>
      <Column header="Дата">
        <template #body="{ data }">
          <Timestamps
            :created-at="data.order.created_at"
            :updated-at="data.order.updated_at"
          />
        </template>
      </Column>

      <template #empty>
        Нет данных
      </template>
    </DataTable>
  </Card>
</template>
