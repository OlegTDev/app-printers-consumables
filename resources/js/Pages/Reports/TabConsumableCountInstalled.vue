<script setup>
import { useDate } from '@/Composables/useDate';
import axios from 'axios';
import Button from 'primevue/button';
import Checkbox from 'primevue/checkbox';
import Message from 'primevue/message';
import Panel from 'primevue/panel';
import { ref } from 'vue';
import { useReportError } from './Composables/useReportErrors';
import InputGroup from 'primevue/inputgroup';
import InputGroupAddon from 'primevue/inputgroupaddon';
import DatePicker from 'primevue/datepicker';
import TreeSelectOrganizations from './TreeSelectOrganizations.vue';


const props = defineProps({
  url: String,
  organizations: Object,
});
const { handleError, displayErrors } = useReportError();
const { moment, formatDate } = useDate();

const emit = defineEmits(['downloadFile']);
const form = ref({
  selectedOrganizations: [],
  withoutPeriod: ref(true),
  dateFrom: moment().subtract(1, 'months').toDate(),
  dateTo: moment().toDate(),
});
const loading = ref(false);

const exportToExcel = async () => {
  loading.value = true;
  displayErrors.value = [];

  try {
    const response = await axios.get(props.url, {
      params: {
        ...form.value,
        dateFrom: formatDate(form.value.dateFrom, 'YYYY-MM-DD'),
        dateTo: formatDate(form.value.dateTo, 'YYYY-MM-DD'),
      },
      responseType: 'blob',
    });
    emit('downloadFile', response.data, 'consumable-installed-count.xlsx');
  }
  catch (error) {
    handleError(error);
  }
  finally {
    loading.value = false;
  }
};
</script>
<template>
  <form @submit.prevent="exportToExcel">
    <Panel header="Список организаций">
      <TreeSelectOrganizations
        :list-organizations="organizations"
        :default-selected-organizations="organizations"
        @update:selected-orgs="(orgs) => form.selectedOrganizations = orgs"
      />
      <!-- <div v-for="organization in organizations" :key="organization.code" class="flex items-center mt-2">
        <Checkbox
          v-model="form.selectedOrganizations"
          :input-id="`consumable_count_installed_${organization.code}`"
          name="organizations"
          :value="organization.code"
        />
        <label :for="`consumable_count_installed_${organization.code}`" class="ml-2 cursor-pointer">
          {{ organization.label }}
        </label>
      </div> -->
    </Panel>
    <Panel header="Период" class="mt-4">
      <div class="flex items-center mt-2">
        <Checkbox v-model="form.withoutPeriod" input-id="without-period-consumable-installed" :binary="true" />
        <label for="without-period-consumable-installed" class="ml-2 cursor-pointer">
          Без учета периода
        </label>
      </div>

      <div v-if="!form.withoutPeriod" class="mt-3 grid grid-cols-6 gap-2">
        <InputGroup>
          <InputGroupAddon>с</InputGroupAddon>
          <DatePicker v-model="form.dateFrom" date-format="dd.mm.yy" />
        </InputGroup>
        <InputGroup>
          <InputGroupAddon>по</InputGroupAddon>
          <DatePicker v-model="form.dateTo" date-format="dd.mm.yy" />
        </InputGroup>
      </div>
    </Panel>
    <Message v-if="displayErrors.length > 0" severity="error" :closable="false">
      <ul>
        <template v-for="errors of displayErrors">
          <li v-for="error of errors" :key="error">
            {{ error }}
          </li>
        </template>
      </ul>
    </Message>
    <div class="mt-4">
      <Button :loading="loading" icon="pi pi-file-excel" label="Экспорт" type="submit" />
    </div>
  </form>
</template>
