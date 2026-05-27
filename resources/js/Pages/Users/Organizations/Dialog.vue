<script setup>
import { inject, ref, onMounted } from 'vue';
import ProgressSpinner from 'primevue/progressspinner';
import { router } from '@inertiajs/vue3';
import Message from 'primevue/message';
import TreeTable from 'primevue/treetable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import organizationService from '@/Services/organizationService';

const dialogRef = inject('dialogRef');
const loading = ref(true);
const errorMessage = ref(null);
const organizations = ref([]);
const organizationLabels = ref({});
const selectedOrganization = ref();
const saving = ref(null);

const expandedKeys = ref({});

onMounted(async () => {
  selectedOrganization.value = dialogRef.value.data.auth.user.org_code;
  await loadOrganizations();
});

const loadOrganizations = async() => {
  try {
    loading.value = true;
    const data = await organizationService.fetch(route('users.organizations'));
    organizations.value = data.organizations;
    organizationLabels.value = data.labels;
    expandedKeys.value = organizationService.expandAll(organizations.value);
  } catch (error) {
    errorMessage.value = error.message;
    console.error(error);
  } finally {
    loading.value = false;
  }
};

const dialogClose = () => dialogRef.value.close();

const change = (code) => {
  if (code !== selectedOrganization.value) {
    saving.value = code;
    const url = route('users.organizations.change', { organization: code });
    router.post(url, {}, {
      onFinish: () => {
        dialogClose();
        router.get(window.location.href);
        saving.value = null;
      },
    });
  }
};
</script>
<template>
  <div v-if="loading" class="flex justify-center">
    <ProgressSpinner />
  </div>

  <Message v-else-if="errorMessage" severity="error" :closable="false">
    {{ errorMessage }}
  </Message>


  <TreeTable
    v-else
    v-model:expanded-keys="expandedKeys"
    :value="organizations"
    table-style="min-width: 50rem"
    class="m-4"
    selection-mode="single"
    data-key="code"
  >
    <Column field="code" :header="organizationLabels.code" sortable expander />
    <Column field="name" :header="organizationLabels.name" sortable />
    <Column header="">
      <template #body="{ node: { data } }">
        <div v-if="saving === data.code" class="flex justify-center">
          <ProgressSpinner style="width: 1rem; height: 1rem" />
        </div>
        <template v-else>
          <i v-if="data.code === selectedOrganization" class="pi pi-check text-green-800" style="font-size: 1.5rem" />
          <Button v-else size="small" :disabled="!!saving" @click="change(data.code)">
            Выбрать
          </Button>
        </template>
      </template>
    </Column>
  </TreeTable>
</template>
