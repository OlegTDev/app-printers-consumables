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
const emit = defineEmits(['downloadFile']);
const moment = inject('moment');

const form = ref({
    selectedOrganizations: Object.values(props.organizations).map((item) => item.code),
    dateFrom: moment().subtract(6, 'months').format('YYYY-MM-DD'),
    dateTo: moment().format('YYYY-MM-DD'),
    withoutPeriod: ref(true),
});
const loading = ref(false);
const displayErrors = ref([]);

// запрос на экспорт файла
const exportToExcel = () => {
    loading.value = true;
    displayErrors.value = [];
    axios.post(props.url, form.value, { responseType: 'blob' })
        //.then((response) => downloadFile(response.data, 'printers-workplace.xlsx'))
        .then((response) => emit('downloadFile', response.data, 'printers-workplace.xlsx'))
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
            catch (e) { }
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
                <Checkbox v-model="form.selectedOrganizations" :id="organization.code" name="organizations"
                    :value="organization.code" />
                <label :for="organization.code" class="ml-2 cursor-pointer">
                    {{ organization.label }}
                </label>
            </div>
        </Panel>
        <Panel header="Период" class="mt-4">
            <div class="text-gray-400 text-base">
                Используется для расчета количественных значений
            </div>
            <div class="flex items-center mt-2">
                <Checkbox v-model="form.withoutPeriod" inputId="withoutPeriod" :binary="true" />
                <label for="withoutPeriod" class="ml-2 cursor-pointer">
                    Без учета периода
                </label>
            </div>
            <div class="mt-3" v-if="!form.withoutPeriod">
                с <input type="date" v-model="form.dateFrom" :disabled="form.withoutPeriod"
                    class="bg-white border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" />
                по <input type="date" v-model="form.dateTo" :disabled="form.withoutPeriod"
                    class="bg-white border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" />
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