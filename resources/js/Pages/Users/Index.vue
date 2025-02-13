<script setup>
import { Head } from '@inertiajs/inertia-vue3'
import pickBy from 'lodash/pickBy'
import Layout from '@/Shared/Layout'
import throttle from 'lodash/throttle'
import Tag from 'primevue/tag'
import { inject, reactive, ref, watch } from 'vue'
import { Inertia } from '@inertiajs/inertia'
import { onMounted } from 'vue'
import { initFlowbite } from 'flowbite'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import InputText from 'primevue/inputtext'
import IconField from 'primevue/iconfield'
import InputIcon from 'primevue/inputicon'
import MultiSelect from 'primevue/multiselect'
import Breadcrumbs from '@/Shared/Breadcrumbs'
import Button from 'primevue/button'


const props = defineProps({
    filters: Object,
    users: Array,
    roles: Array,    
})

defineOptions({
    layout: Layout
})
const filters = reactive(props.filters)

const urls = inject('urls')

const form = reactive({
    search: filters.search,
    role: filters.role,
    trashed: filters.trashed,
})

watch(
    () => form,
    throttle(() => {
        Inertia.get(urls.users.index(), pickBy(form), { preserveState: true })
    }, 150),
    { deep: true }
)

onMounted(() => {
    initFlowbite()
})

const create = () => {
    Inertia.get(urls.users.create())
}

const onRowSelect = (event) => {
    Inertia.get(urls.users.edit(event.data.id))
}

const refTableUsers = ref(null);

const onPageChange = () => {    
    const elementTableUsers = refTableUsers.value.$el;
    if (elementTableUsers) {
        elementTableUsers.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

const title = 'Пользователи'

</script>
<template>
    <div>
        <Head :title="title" />

        <Breadcrumbs :home="{ label: 'Главная', url: urls.home }" :items="[
            { label: title },
        ]" />

        <div class="flex justify-stretch bg-white rounded-md shadow overflow-hidden mt-4">
            <DataTable 
                ref="refTableUsers"
                :value="users"
                paginator :rows="10"
                dataKey="id" :metaKeySelection="false"
                class="w-full" tableStyle="min-width: 50rem" selectionMode="single"
                @rowSelect="onRowSelect"
                @page="onPageChange"
            >
                <template #header>
                    <div class="flex justify-between">
                        <Button @click="create">Добавить</Button>
                        <div class="flex">
                            <MultiSelect v-model="form.role" :options="roles" optionValue="name" optionLabel="description" placeholder="Роли" class="w-56" />
                            <IconField iconPosition="left" class="ml-3">
                                <InputIcon><i class="pi pi-search"></i></InputIcon>
                                <InputText v-model="form.search" placeholder="Поиск"></InputText>
                            </IconField>
                        </div>
                    </div>               
                </template>
                
                <Column field="org_code" header="Код НО" />
                <Column field="name" header="Учетная запись">
                    <template #body="{ data }">
                        <div class="flex items-center">
                            <img v-if="data.photo" class="block -my-2 mr-2 w-5 h-5 rounded-full" :src="data.photo" />
                            {{ data.name }}                            
                        </div>
                    </template>
                </Column>
                <Column field="fio" header="ФИО" />
                <Column field="department" header="Отдел" />
                <Column field="lotus_mail" header="Коммуникация" class="w-50">
                    <template #body="{ data }">
                        <div v-if="data.telephone">
                            <span class="pi pi-phone me-1"></span>
                            {{ data.telephone }}
                        </div>
                        <div v-if="data.lotus_mail">
                            <span class="pi pi-send me-1 mt-2"></span>
                            {{ data.lotus_mail }}
                        </div>
                    </template>
                </Column>                
                <Column header="Роли" class="w-64">
                    <template #body="{ data }">
                        <ul v-if="data.roles?.length > 0">
                            <li v-for="role in data.roles"
                                class="bg-blue-100 text-blue-800 text-sm font-medium m-2 px-2.5 py-0.5 rounded-lg">
                                {{ role.description }}
                            </li>
                        </ul>
                        <span v-else class="bg-yellow-100 text-yellow-800 text-sm font-medium m-2 px-2.5 py-0.5 rounded-lg">
                            Нет ролей
                        </span>
                    </template>
                </Column>
                <Column header="Контекст" class="w-52">
                    <template #body="{ data }">
                        <ul v-if="data.organizations.length > 0">
                            <li v-for="organization in data.organizations" class="bg-blue-100 text-blue-800 text-sm font-medium m-2 px-2.5 py-0.5 rounded-lg">
                                {{ organization.name }}
                            </li>
                        </ul>
                        <span v-else class="bg-yellow-100 text-yellow-800 text-sm font-medium m-2 px-2.5 py-0.5 rounded-lg">
                            Без контекста
                        </span>
                    </template>
                </Column>
                <Column header="Статус">
                    <template #body="{ data }">
                        <Tag v-if="data.deleted_at" severity="danger" icon="pi pi-times" value="Удалена" />
                        <Tag v-else severity="success" icon="pi pi-check" value="Действующая" />
                    </template>
                </Column>
            </DataTable>
        </div>
    </div>    
</template>