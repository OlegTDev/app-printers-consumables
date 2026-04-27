<script setup>
import { inject, ref, onMounted } from 'vue'
import ProgressSpinner from 'primevue/progressspinner'
import { router } from '@inertiajs/vue3'
import Message from 'primevue/message'
import TreeTable from 'primevue/treetable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import organizationService from '@/Services/organizationService'

const dialogRef = inject('dialogRef')
const loading = ref()
const errorMessage = ref()
const urls = inject('urls')
const organizations = ref()
let organizationLabels = {}
const selectedOrganization = ref()
const saving = ref(false)

const expandedKeys = ref({});

onMounted(async () => {
  selectedOrganization.value = dialogRef.value.data.auth.user.org_code;
  await loadOrganizations();
});

async function loadOrganizations() {
  try {
    loading.value = true;
    const data = await organizationService.fetch(urls.users.organizations.index());
    organizations.value = data.organizations;
    organizationLabels = data.labels;
    expandedKeys.value = organizationService.expandAll(organizations.value);
  } catch (error) {
    console.log(error);
    errorMessage.value = error.message;
  } finally {
    loading.value = false;
  }
}

const dialogClose = () => dialogRef.value.close()


const LogActions = inject('LogActions');

const change = (code, event) => {
  if (code !== selectedOrganization.value) {
    saving.value = true;
    const url = urls.users.organizations.change(code);
    router.post(url, {}, {
      onFinish: () => {
        LogActions.save(url, 'POST', 'Изменение организации у пользователя', {
          code: code,
        });

        dialogClose();
        router.get(window.location.href);
      }
    })
  }
}
</script>
<template>

  <div v-if="loading" class="flex justify-center">
    <ProgressSpinner />
  </div>

  <Message v-else-if="errorMessage" severity="error" :closable="false">
    {{ errorMessage }}
  </Message>

  <TreeTable v-else :value="organizations" tableStyle="min-width: 50rem" class="m-4" selectionMode="single"
    dataKey="code" v-model:expandedKeys="expandedKeys">
    <Column field="code" :header="organizationLabels.code" sortable expander />
    <Column field="name" :header="organizationLabels.name" sortable />
    <Column header="">
      <template #body="{ node: { data } }">
        <div v-if="saving" class="flex justify-center">
          <ProgressSpinner style="width: 1rem; height: 1rem" />
        </div>
        <template v-else>
          <i class="pi pi-check text-green-800" style="font-size: 1.5rem" v-if="data.code === selectedOrganization"></i>
          <Button v-else @click="change(data.code, $event)" size="small">
            Выбрать
          </Button>
        </template>
      </template>
    </Column>
  </TreeTable>

</template>
