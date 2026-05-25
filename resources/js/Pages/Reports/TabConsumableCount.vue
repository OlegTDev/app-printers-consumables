<script setup>
import { ref } from 'vue';
import axios from 'axios';
import Panel from 'primevue/panel';
import Checkbox from 'primevue/checkbox';
import Message from 'primevue/message';
import Button from 'primevue/button';
import { useReportError } from './Composables/useReportErrors';

const props = defineProps({
  url: String,
  organizations: Object,
});

const emit = defineEmits(['downloadFile']);
const { handleError, displayErrors } = useReportError();

const form = ref({
  selectedOrganizations: Object.values(props.organizations).map((item) => item.code),
}, );

const loading = ref(false);

const exportToExcel = async() => {
  loading.value = true;
  displayErrors.value = [];

  try {
    const response = await axios.post(props.url, form.value, { responseType: 'blob' });
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
      <div v-for="organization in organizations" :key="organization.code" class="flex items-center mt-2">
        <Checkbox
          :id="organization.code"
          v-model="form.selectedOrganizations"
          name="organizations"
          :value="organization.code"
        />
        <label :for="organization.code" class="ml-2 cursor-pointer">
          {{ organization.label }}
        </label>
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
