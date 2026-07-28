<script setup>
import { ref } from 'vue';
import axios from 'axios';
import Panel from 'primevue/panel';
import Message from 'primevue/message';
import Button from 'primevue/button';
import { useReportError } from './Composables/useReportErrors';
import TreeSelectOrganizations from './TreeSelectOrganizations.vue';

const props = defineProps({
  url: String,
  organizations: Object,
});

const emit = defineEmits(['downloadFile']);
const { handleError, displayErrors } = useReportError();

const form = ref({
  selectedOrganizations: [],
});

const loading = ref(false);

const exportToExcel = async() => {
  loading.value = true;
  displayErrors.value = [];

  try {
    const response = await axios.get(props.url, {
      params: form.value,
      responseType: 'blob',
    });
    emit('downloadFile', response.data, 'consumable-count.xlsx');
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
