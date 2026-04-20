<script setup>
import { inject, ref, onMounted } from 'vue'
import axios from 'axios'
import ProgressSpinner from 'primevue/progressspinner'
import { router } from '@inertiajs/vue3'
import Message from 'primevue/message'
import TreeTable from 'primevue/treetable'
import Column from 'primevue/column'
import Button from 'primevue/button'


const dialogRef = inject('dialogRef')
const loading = ref()
const errorMessage = ref()
const urls = inject('urls')
const organizations = ref()
let organizationLabels = {}
const selectedOrganization = ref()
const saving = ref(false)

onMounted(() => {
    selectedOrganization.value = dialogRef.value.data.auth.user.org_code
    loadData()
})

const dialogClose = () => dialogRef.value.close()

const loadData = () => {
    loading.value = true
    errorMessage.value = null
    axios.get(urls.users.organizations.index())
        .then((response) => {
            organizations.value = response.data.organizations;
            organizationLabels = response.data.organizationLabels;
        })
        .catch((error) => {
            console.log(error)
            errorMessage.value = error.message
        })
        .finally(() => {
            loading.value = false
        })
}

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

    <TreeTable v-else
        :value="organizations"
        tableStyle="min-width: 50rem"
        class="m-4"
        selectionMode="single"
        dataKey="code"
    >
        <Column field="code" :header="organizationLabels.code" sortable expander />
        <Column field="name" :header="organizationLabels.name" sortable />
        <Column header="">
            <template #body="{ node: { data } }">
                <div v-if="saving" class="flex justify-center">
                    <ProgressSpinner style="width: 1rem; height: 1rem" />
                </div>
                <Button v-else :disabled="data.code === selectedOrganization" @click="change(data.code, $event)" size="small">
                    {{ data.code !== selectedOrganization ? 'Выбрать' : 'Выбрано' }}
                </Button>
            </template>
        </Column>
    </TreeTable>

</template>
