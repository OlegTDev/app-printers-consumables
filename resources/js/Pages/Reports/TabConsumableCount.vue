<script setup>
import { inject, reactive, ref } from 'vue';
import axios from 'axios';
import Panel from 'primevue/panel';
import Checkbox from 'primevue/checkbox';
import Message from 'primevue/message';
import Button from 'primevue/button';
import { useToast } from 'primevue/usetoast';

const props = defineProps({
    url: String,
    organizations: Object,
});
const toast = reactive(useToast());
const config = inject('config');
const emit = defineEmits(['downloadFile'])

const form = ref({
    selectedOrganizations: Object.values(props.organizations).map((item) => item.code),    
});
const loading = ref(false);
const displayErrors = ref([]);

// запрос на экспорт файла
const exportToExcel = () => {
    loading.value = true;
    displayErrors.value = [];
    axios.post(props.url, form.value, { responseType: 'blob' })        
        .then((response) => emit('downloadFile', response.data, 'consumable-count.xlsx'))
        .catch((error) => {     
            console.log(error);
            if (error.response.status == 500) {
                toast.add({
                    severity: 'error',
                    summary: 'Ошибка',
                    detail: error.response.statusText,
                    life: config.toast.timeLife,
                })
                return;
            }
            try {                
                error.response.data.text().then((text) => { 
                    const json = JSON.parse(text);
                    if (!json['errors']) {
                        return;
                    }

                    const errors = json.errors;
                    const arrErrors = Object.values(errors);
                    if (Array.isArray(arrErrors)) {                        
                        displayErrors.value = arrErrors;
                    }
                });
            }
            catch(e) {}
        })
        .finally(() => {
            loading.value = false;
        });
}

</script>
<template>
        
    <form @submit.prevent="exportToExcel">
                        
        <Panel header="Список организаций">                    
            <div v-for="organization in organizations" :key="organization.code" class="flex items-center mt-2">
                <Checkbox v-model="form.selectedOrganizations" :id="organization.code" name="organizations" :value="organization.code" />
                <label :for="organization.code" class="ml-2 cursor-pointer">
                    {{ organization.label }}
                </label>
            </div>
        </Panel>
        
        <Message v-if="displayErrors.length > 0" severity="error" :closable="false">
            <ul>
                <template v-for="errors of displayErrors">
                    <li v-for="error of errors">
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