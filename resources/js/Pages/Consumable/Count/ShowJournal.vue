<script setup>
import axios from 'axios';
import TabView from 'primevue/tabview';
import TabPanel from 'primevue/tabpanel';
import { inject, ref, onMounted, reactive } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import { router } from '@inertiajs/vue3';
import { useToast } from 'primevue/usetoast';
import { useConfirm } from "primevue/useconfirm";

const props = defineProps({
    consumable: Object,
    consumableCount: Object,
    isOpen: Boolean,
    auth: Object,
});
const urls = inject('urls');
const authenticate = inject('auth');
const moment = inject('moment');
const toast = reactive(useToast());
const confirm = useConfirm();

const idConsumable = props.consumableCount.id_consumable;
const idConsumableCount = props.consumableCount.id;

const dataItemsAdded = ref();
const dataItemsInstalled = ref();

const journalAdded = {
    loading: ref(false),
    loadingDelete: ref(false),
    idDelete: ref(0),
};
const journalInstalled = JSON.parse(JSON.stringify(journalAdded));

// загрузка журнала
const baseUpdate = (url, refObj, refParamsObj, errorTitle) => {
    refParamsObj.loading.value = true
    axios.get(url)
        .then((response) => {
            if (Array.isArray(response.data)) {
                refObj.value = response.data
            }
            refParamsObj.loading.value = false
        })
        .catch((error) => {
            toast.add({
                severity: 'error',
                summary: errorTitle,
                detail: error.message,
                life: 5000,
            })
            console.error(error)
        });
};

const updateJournalAdded = () => {
    baseUpdate(
        urls.consumables.counts.journal.added.index(idConsumable, idConsumableCount),
        dataItemsAdded,
        journalAdded,
        'Ошибка загрузки журнала добавления расходных материалов',
    )
};
const updateJournalInstalled = () => {
    baseUpdate(
        urls.consumables.counts.journal.installed.index(idConsumable, idConsumableCount),
        dataItemsInstalled,
        journalInstalled,
        'Ошибка загрузки журнала установки расходных материалов',
    )
};

const LogActions = inject('LogActions');

const baseRedo = (url, id, refLoadingObj, fnUpdate) => {
    confirm.require({
        message: 'Вы уверены, что хотите отменить операцию?',
        header: 'Отмена операции',
        accept: () => {
            refLoadingObj.idDelete.value = id;
            refLoadingObj.loadingDelete.value = true;

            router.delete(url, {
                onSuccess: () => {
                    LogActions.save(url, 'DELETE', 'Отмена операции');

                    fnUpdate();
                    refLoadingObj.idDelete.value = 0;
                    refLoadingObj.loadingDelete.value = false;
                },
            })
        },
    })
};

const redoJournalAdded = (id) => {
    baseRedo(
        urls.consumables.counts.journal.added.redo(idConsumable, idConsumableCount, id),
        id,
        journalAdded,
        updateJournalAdded
    )
};
const redoJournalInstalled = (id) => {
    baseRedo(
        urls.consumables.counts.journal.installed.redo(idConsumable, idConsumableCount, id),
        id,
        journalInstalled,
        updateJournalInstalled
    )
};

onMounted(() => {
    updateJournalAdded()
    updateJournalInstalled()
});

</script>
<template>
    <TabView>
        <TabPanel>
            <template #header>
                <div class="text-green-600">
                    <i class="fa-solid fa-arrow-up fa-rotate-by text-green-600" style="--fa-rotate-angle: 45deg;"></i>
                    Добавлены
                </div>
            </template>
            <DataTable :value="dataItemsAdded"
                paginator
                :rows="10"
                dataKey="id"
                :metaKeySelection="false"
                class="w-full"
                tableStyle="min-width: 50rem"
                selectionMode="single"
                :loading="journalAdded.loading.value"
            >
                <Column header="#" headerStyle="width:3rem" sortable>
                    <template #body="slotProps">
                        {{ slotProps.index + 1 }}
                    </template>
                </Column>
                <Column header="Количество" field="count" sortable />
                <Column header="Автор" field="author.name" sortable>
                    <template #body="{ data }">
                        <div v-if="data?.author?.fio" class="mb-2">
                            {{ data.author.fio }}
                        </div>
                        {{ data?.author?.name ?? 'Неизвестно' }}
                    </template>
                </Column>
                <Column field="created_at" header="Дата" sortable>
                    <template #body="{ data }">
                        <div>
                            <div v-tooltip="moment(data.created_at).format('LLLL')">
                                <i class="far fa-calendar"></i>
                                {{ moment(data.created_at).fromNow() }}
                            </div>
                        </div>
                    </template>
                </Column>
                <Column header="">
                    <template #body="{ data }">
                        <Button
                            v-if="authenticate.can('admin') || data.id_author == auth?.user?.id"
                            v-tooltip="`Отменить`"
                            icon="fas fa-redo-alt fa-flip-horizontal"
                            @click="redoJournalAdded(data.id)"
                            :loading="journalAdded.loadingDelete && data.id === journalAdded.idDelete.value"
                            severity="danger"
                        />
                    </template>
                </Column>

                <template #empty> Нет данных </template>

            </DataTable>
        </TabPanel>
        <TabPanel>
            <template #header>
                <div class="text-red-600">
                    <i class="fa-solid fa-arrow-down fa-rotate-by text-red-600" style="--fa-rotate-angle: -45deg;"></i>
                    Установлены
                </div>
            </template>

            <DataTable :value="dataItemsInstalled"
                paginator
                :rows="10"
                dataKey="id"
                :metaKeySelection="false"
                class="w-full"
                tableStyle="min-width: 50rem"
                selectionMode="single"
                :loading="journalInstalled.loading.value"
            >
                <Column header="#" headerStyle="width:3rem" sortable>
                    <template #body="slotProps">
                        {{ slotProps.index + 1 }}
                    </template>
                </Column>
                <Column header="Количество" field="count" sortable />
                <Column header="Автор" field="author.name" sortable>
                    <template #body="{ data }">
                        <div v-if="data?.author?.fio" class="mb-2">
                            {{ data.author.fio }}
                        </div>
                        {{ data?.author?.name ?? 'Неизвестно' }}
                    </template>
                </Column>
                <Column field="created_at" header="Дата" sortable>
                    <template #body="{ data }">
                        <div>
                            <div v-tooltip="moment(data.created_at).format('LLLL')">
                                <i class="far fa-calendar"></i>
                                {{ moment(data.created_at).fromNow() }}
                            </div>
                        </div>
                    </template>
                </Column>
                <Column header="">
                    <template #body="{ data }">
                        <Button
                            v-if="authenticate.can('admin') || data.id_author == auth?.user?.id"
                            v-tooltip="`Отменить`"
                            icon="fas fa-redo-alt fa-flip-horizontal"
                            @click="redoJournalInstalled(data.id)"
                            :loading="journalInstalled.loadingDelete && data.id === journalInstalled.idDelete.value"
                            severity="danger"
                        />
                    </template>
                </Column>

                <template #empty> Нет данных </template>

            </DataTable>

        </TabPanel>

    </TabView>


</template>
