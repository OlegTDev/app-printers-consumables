<script setup>
import { Head } from '@inertiajs/inertia-vue3'
import Layout from '@/Shared/Layout'
import Breadcrumbs from '@/Shared/Breadcrumbs';
import TabPanel from 'primevue/tabpanel';
import TabView from 'primevue/tabview';
import { inject, ref } from 'vue';
import TabPrinterWorkplace from './TabPrinterWorkplace.vue';
import TabConsumableCount from './TabConsumableCount.vue';


defineOptions({
    layout: Layout
});
const urls = inject('urls');

const props = defineProps({
    organizations: Object,    
})

const title = 'Отчеты';

const downloadFile = (data, fileName) => {
    const href = URL.createObjectURL(data);
    const link = document.createElement('a');    
    link.href = href;
    link.setAttribute('download', fileName);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(href);
}

</script>
<template>
    <Head :title="title" />

    <Breadcrumbs :home="{ label: 'Главная', url: '/' }" :items="[
        { label: title },
    ]" />

    <div class="card">
        <TabView>
            
            <TabPanel header="Принтеры на местах">
                <!-- <form @submit.prevent="exportPrintersWorkplace">
                    
                    <Panel header="Список организаций">                    
                        <div v-for="organization in organizations" :key="organization.code" class="flex items-center mt-2">
                            <Checkbox v-model="formPrinterWorkplace.selectedOrganizations" :inputId="organization.code" name="organizations" :value="organization.code" />
                            <label :for="organization.code" class="ml-2 cursor-pointer">
                                {{ organization.label }}
                            </label>
                        </div>
                    </Panel>
                    
                    
                    <Message v-if="errorMessagePrintersWorkplace.length > 0" severity="error" :closable="false">
                        <ul>
                            <template v-for="errors of errorMessagePrintersWorkplace">
                                <li v-for="error of errors">
                                    {{ error }}
                                </li>
                            </template>
                        </ul>                        
                    </Message>
                    
                    <div class="mt-4">
                        <Button :loading="loading" icon="pi pi-file-excel" label="Экспорт" type="submit" />
                    </div>

                </form> -->
                
                <TabPrinterWorkplace :url="urls.reports.exportPrintersWorkplace()" :organizations="organizations" @downloadFile="downloadFile" />

            </TabPanel>

            <TabPanel header="Остатки картриджей">
                <TabConsumableCount :url="urls.reports.exportConsumableCount()" :organizations="organizations" @downloadFile="downloadFile" />
            </TabPanel>

        </TabView>
    </div>

</template>