<script setup>
import axios from 'axios';
import { inject, onMounted, reactive, ref } from 'vue';
import Card from 'primevue/card';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import ProgressSpinner from 'primevue/progressspinner';
import { useToast } from 'primevue/usetoast';

const moment = inject('moment');
const config = inject('config');

const props = defineProps({
    urls: Object,
    printerId: Number,
    consumableTypes: Object,
    cartridgeColors: Object,
    consumableLabels: Object,
    consumableCountLabels: Object,
});

const data = ref([]);
const loading = ref(false);
const toast = reactive(useToast());

onMounted(async () => {
    loading.value = true;
    try {
        const resp = await axios.get(props.urls.printers.consumablesInstalled(props.printerId));
        data.value = await resp.data;
    }
    catch (error) {
        console.error(error);
        toast.add({
            severity: 'error',
            summary: 'Ошибка',
            detail: error.message,
            life: config.toast.timeLife,
        });
    }
    loading.value = false;
});

</script>
<template>
    <Card class="mt-4">
        <template #title>История установки расходных материалов</template>
        <template #content>
            <DataTable :value="data" paginator :rows="10" dataKey="id" :metaKeySelection="false" class="w-full"
                tableStyle="min-width: 50rem" selectionMode="single" :loading="loading">

                <Column header="Дата" field="date_installed">
                    <template #body="{ data }">
                        {{ moment(data.date_installed).format('llll') }}
                    </template>
                </Column>
                <Column :header="consumableLabels.type" field="type">
                    <template #body="{ data }">
                        {{ consumableTypes[data.type] }}
                    </template>
                </Column>
                <Column :header="consumableLabels.name" field="consumable.name">
                    <template #body="{ data }">
                        <div class="grid grid-rows-2 gap-4">
                            <div>
                                {{ data.name }}
                            </div>
                            <div v-if="data.type === 'cartridge'">
                                <div class="flex">
                                    <div :class="['rounded-full', 'size-4', 'mr-2', cartridgeColors[data.color]['bg']]">
                                    </div>
                                    <div>
                                        {{ cartridgeColors[data.color]['name'] }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </Column>
                <Column :header="consumableCountLabels.count" field="count" />
                <Column header="Исполнитель">
                    <template #body="{ data }">
                        <i class="pi pi-user"></i> {{ data.user_name }}
                        {{ data.user_fio ? ` (${data.user_fio})` : '' }}
                        <span class="block mt-2" v-if="data.user_department">{{ data.user_department }}</span>
                        <span class="block mt-2" v-if="data.user_post">{{ data.user_post }}</span>
                    </template>
                </Column>

                <template #empty> Нет данных </template>

                <template #loading>
                    <ProgressSpinner style="width: 50px; height: 50px" strokeWidth="8" fill="transparent"
                        animationDuration=".5s" aria-label="Custom ProgressSpinner" />
                </template>

            </DataTable>
        </template>
    </Card>
</template>