<script setup>
import axios from 'axios';
import { ref } from 'vue';
import Inplace from 'primevue/inplace';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import OrderStatus from '../Shared/OrderStatus.vue';
import Author from '@/Shared/DataTable/Author.vue';
import { useConfig } from '@/Composables/useConfig';
import { useNotification } from '@/Composables/useNotification';
import { useDate } from '@/Composables/useDate';

const props = defineProps({
  idOrder: Number,
  statuses: Object,
});

const { urls } = useConfig();
const items = ref();
const labels = ref();
const { showError } = useNotification();
const { formatDate } = useDate();

const loadData = async () => {
  try {
    const resp = await axios.get(urls.orders.statusHistory(props.idOrder));
    items.value = resp.data.statuses;
    labels.value = resp.data.labels;
  } catch (error) {
    showError(error.message);
  }
};
</script>
<template>
  <Inplace @open="loadData">
    <template #display>
      <span class="pi pi-history" style="vertical-align: middle" />
      <span style="margin-left: 0.5rem; vertical-align: middle">Показать</span>
    </template>
    <template #content>
      <DataTable :value="items" class="w-full">
        <Column field="status" :header="labels?.status">
          <template #body="{ data }">
            <OrderStatus :status="data?.status" :statuses="statuses" />
          </template>
        </Column>
        <Column field="author" :header="labels?.author">
          <template #body="{ data }">
            <Author
              :user="{
                fio: data?.author.fio,
                name: data?.author.name,
                post: data?.author.post,
                department: data?.author.department,
              }"
            />
          </template>
        </Column>
        <Column field="comment" :header="labels?.comment" />
        <Column field="type" :header="labels?.created_at">
          <template #body="{ data }">
            {{ formatDate(data?.created_at,'L LTS') }}
          </template>
        </Column>
      </DataTable>
    </template>
  </Inplace>
</template>
