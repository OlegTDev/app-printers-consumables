<script setup>
import { Head, useForm } from '@inertiajs/inertia-vue3'
import Layout from '@/Shared/Layout'
import Breadcrumbs from '@/Shared/Breadcrumbs'
import { inject, ref, defineAsyncComponent, computed } from 'vue'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Chip from 'primevue/chip'
import DetailViewer from '@/Shared/DetailViewer'
import Button from 'primevue/button'
import { useDialog } from 'primevue/usedialog'
import ShowJournal from './ShowJournal.vue'
import TabView from 'primevue/tabview'
import TabPanel from 'primevue/tabpanel'
import Checkbox from 'primevue/checkbox'
import InlineMessage from 'primevue/inlinemessage'
import Panel from 'primevue/panel'


defineOptions({
    layout: Layout
});
const props = defineProps({
    consumableCount: Object,
    consumable: Object,
    consumableTitle: String,
    consumableCountLabels: Object,
    organizations: Array,
    organizationLabels: Object,
    allOrganizations: Array,
    auth: Object,
});

const urls = inject('urls');
const authenticate = inject('auth');
const title = props.consumableTitle;
const consumableCountLabels = props.consumableCountLabels;
const dialog = useDialog();
const AddDialog = defineAsyncComponent(() => import('./Dialogs/Add.vue'));
const SubtractDialog = defineAsyncComponent(() => import('./Dialogs/Subtract.vue'));
const CorrectDialog = defineAsyncComponent(() => import('./Dialogs/Correct.vue'));

const actions = {
    add: () => {
        dialog.open(AddDialog, {
            props: {
                header: 'Добавить',
                style: {
                    width: '50vw',
                },
                breakpoints: {
                    '960px': '75vw',
                    '640px': '90vw'
                },
                modal: true,
            },
            data: {
                idConsumable: props.consumable.id,
                id: props.consumableCount.id,
                organizations: Array.from(props.organizations),
                consumableCountLabels: props.consumableCountLabels,
            }
        })
    },
    subtract: () => {
        dialog.open(SubtractDialog, {
            props: {
                header: 'Вычесть',
                style: {
                    width: '50vw',
                },
                breakpoints: {
                    '960px': '75vw',
                    '640px': '90vw'
                },
                modal: true,
            },
            data: {
                idConsumable: props.consumable.id,
                idConsumableCount: props.consumableCount.id,
            }
        })
    },

    correct: () => {
        dialog.open(CorrectDialog, {
            props: {
                header: 'Корректировка',
                style: {
                    width: '50vw',
                },
                breakpoints: {
                    '960px': '75vw',
                    '640px': '90vw'
                },
                modal: true,
            },
            data: {
                consumableCountId: props.consumableCount.id,
                consumableCountValue: props.consumableCount.count,
                consumableCount: props.consumableCount,
                consumableCountLabels: props.consumableCountLabels,
            }
        })
    },
};

const bgColor = computed(() => props.consumableCount.count <= 1 ? 'bg-red-500' :
    (props.consumableCount.count < 10 ? 'bg-yellow-500' : 'bg-primary-500')
);

const visibleOrganizationsEdit = ref(false);
const form = useForm({
    id_consumable: props.consumable.id,
    count: 1,
    selectedOrganizations: Array.from(props.organizations).map((item) => item.code),
});

const LogActions = inject('LogActions');
const saveOrganizations = () => {
    const url = urls.consumables.counts.updateOrganizations(props.consumableCount.id);
    form.put(url, {
        onSuccess: () => {
            // статистика посещения            
            LogActions.save(url, 'PUT', 'Обновление списка организаций', {
                id_consumable: form.id_consumable,
                selected_organizations: form.selectedOrganizations,
            });

            visibleOrganizationsEdit.value = false;
        }
    })
};

</script>
<template>

    <Head :title="title" />

    <Breadcrumbs :home="{ label: 'Главная', url: urls.home }" :items="[
        { label: 'Количество расходных материалов', url: urls.consumables.counts.index() },
        { label: title },
    ]" />

    <TabView :lazy="true">
        <TabPanel>
            <template #header>
                <i class="pi pi-home me-2"></i> Главная
            </template>
            <Chip class="pl-0 pr-3">
                <span class="font-bold text-lg text-surface-0 rounded-full w-12 h-12 flex items-center justify-center"
                    :class="bgColor">
                    {{ consumableCount.count }}
                </span>
                <span class="ml-2 font-medium">
                    доступное количество
                </span>
                <Button v-if="authenticate.can('admin', 'add-consumables')" text rounded icon="pi pi-plus"
                    class="font-bold" @click="actions.add" v-tooltip="`Добавить`" />
                <Button v-if="authenticate.can('admin', 'subtract-consumable') && consumableCount.count > 0" text
                    rounded icon="pi pi-minus" class="font-bold" @click="actions.subtract" severity="danger"
                    v-tooltip="`Вычесть`" />
                <Button v-if="authenticate.can('admin', 'subtract-consumable')" text rounded icon="pi pi-pencil" class="font-bold"
                    @click="actions.correct" severity="info" v-tooltip="`Корректировка`" />
            </Chip>

            <DetailViewer class="mt-8" :items="[
                {
                    label: consumableCountLabels.created_at,
                    value: props.consumableCount.created_at,
                    is_date: true,
                    icon: 'far fa-calendar',
                },
                {
                    label: consumableCountLabels.updated_at,
                    value: props.consumableCount.updated_at,
                    is_date: true,
                    icon: 'far fa-calendar-alt',
                },
            ]"></DetailViewer>
        </TabPanel>

        <TabPanel>
            <template #header>
                <i class="pi pi-replay me-2"></i> Журнал
            </template>
            <ShowJournal :consumable="consumable" :consumableCount="consumableCount" :auth="auth" />
        </TabPanel>

        <TabPanel>
            <template #header>
                <i class="pi pi-list me-2"></i> Организации
            </template>

            <div v-if="visibleOrganizationsEdit">
                <div>
                    <form @submit.prevent="saveOrganizations">
                        <Panel header="Редактирование списка организаций">
                            <template #footer>
                                <Button severity="secondary" @click="visibleOrganizationsEdit = false" size="small"
                                    label="Назад" />
                                <Button type="submit" :loading="form.processing" class="ms-2" icon="pi pi-save"
                                    label="Сохранить" size="small" />
                            </template>
                            <div class="w-1/3">
                                <div class="w-full" id="organizations">
                                    <div v-for="organization in allOrganizations" :key="organization.code"
                                        class="flex items-center mt-2">
                                        <Checkbox v-model="form.selectedOrganizations" :inputId="organization.code"
                                            name="organizations" :value="organization.code" />
                                        <label :for="organization.code" class="ml-2 cursor-pointer">
                                            {{ `${organization.name} (${organization.code})` }}
                                        </label>
                                    </div>
                                </div>
                                <InlineMessage v-if="form.errors?.selectedOrganizations" class="mt-2" severity="error">
                                    {{
                                        form.errors?.selectedOrganizations }}</InlineMessage>
                            </div>
                        </Panel>
                    </form>
                </div>
            </div>

            <DataTable :value="organizations" dataKey="code" class="w-1/3" selectionMode="single" v-else>
                <template #header v-if="authenticate.can('admin', 'add-consumables')">
                    <Button severity="success" @click="visibleOrganizationsEdit = true" size="small"
                        label="Редактировать" />
                </template>

                <Column :header="props.organizationLabels.code" field="code" sortable />
                <Column :header="props.organizationLabels.name" field="name" sortable />

                <template #empty> Нет данных </template>

            </DataTable>

        </TabPanel>

    </TabView>


</template>