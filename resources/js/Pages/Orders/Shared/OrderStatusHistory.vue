<script setup>
import axios from 'axios';
import { inject, ref } from 'vue';
import Inplace from 'primevue/inplace';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import OrderStatus from '../Shared/OrderStatus';
import Author from '@/Shared/DataTable/Author';

const props = defineProps({
  idOrder: Number,
  statuses: Object,
});

const urls = inject('urls');
const moment = inject('moment');
const data = ref();
const labels = ref();

const loadData = async () => {
  const resp = await axios.get(urls.orders.statusHistory(props.idOrder));
  data.value = resp.data.statuses;
  labels.value = resp.data.labels;
}
</script>
<template>
  <Inplace @open="loadData">
    <template #display>
      <span class="pi pi-angle-double-down" style="vertical-align: middle"></span>
      <span style="margin-left: 0.5rem; vertical-align: middle">Показать</span>
    </template>
    <template #content>
      <DataTable :value="data" class="w-full">
        <Column field="status" :header="labels?.status">
          <template #body="{ data }">
            <OrderStatus :status="data?.status" :statuses="statuses" />
          </template>
        </Column>
        <Column field="author" :header="labels?.author">
          <template #body="{ data }">
            <Author
              :login="data?.author.name"
              :fullName="data?.author.fio"
              :post="data?.author.post"
              :department="data?.author.department" />
          </template>
        </Column>
        <Column field="comment" :header="labels?.comment" />
        <Column field="type" :header="labels?.created_at">
          <template #body="{ data }">
            {{ moment(data?.created_at).format('L LTS') }}
          </template>
        </Column>
      </DataTable>
    </template>
  </Inplace>
</template>
