<script setup>
import { Head, useForm, router } from '@inertiajs/vue3'
import Layout from '@/Shared/Layout'
import TrashedMessage from '@/Shared/TrashedMessage'
import Breadcrumbs from '@/Shared/Breadcrumbs'
import Panel from 'primevue/panel'
import Checkbox from 'primevue/checkbox'
import { ref, computed, inject, reactive } from 'vue'
import Menu from 'primevue/menu'
import ProgressSpinner from 'primevue/progressspinner'
import Button from 'primevue/button'

const props = defineProps({
    user: Object,
    roles: Object,
    organizations: Object,
});

defineOptions({
    layout: Layout
});

const urls = inject('urls');
const auth = inject('auth');

const user = props.user;
const userRoles = Array.from(Object.values(props.user.roles), (key) => key.name);
const userOrganizations = Array.from(Object.values(props.user.organizations), (key) => key.code);
const menu = ref(null);
const menuItems = ref([
    { label: 'Удалить', icon: 'pi pi-times', visible: user.deleted_at === null, command: () => destroy() },
    { label: 'Восстановить', icon: 'pi pi-undo', visible: user.deleted_at !== null, command: () => restore() },
]);
const toggleMenu = (event) => {
    menu.value.toggle(event)
};
// форма
const form = useForm({
    _method: 'put',
    name: user.name,
    email: user.email,
    password: '',
    photo: null,
    selectedRoles: userRoles,
    selectedOrganizations: userOrganizations,
});
const formFields = reactive({
    name: form.name,
    email: form.email,
    selected_roles: form.selectedRoles,
    selected_organizations: form.selectedOrganizations,
});
const title = `${user.fio ?? ''} (${user.name})`;

const LogActions = inject('LogActions');

function update() {
    const url = urls.users.update(user.id);
    form.post(url, {
        onSuccess: () => {
            LogActions.save(url, 'POST', 'Обновление данных пользователя', formFields);
            form.reset('password', 'photo');
        },
        preserveState: true,
        preserveScroll: true,
    })
}
const destroy = () => {
    if (confirm('Вы уверены, что хотите удалить данного пользователя?')) {
        const url = urls.users.delete(user.id);
        form.delete(url, {
            onSuccess: () => {
                LogActions.save(url, 'DELETE', 'Удаление пользователя', formFields);
                router.get(urls.users.edit(user.id));
            },
        })
    }
}
const restore = () => {
    if (confirm('Вы уверены, что хотите восстановить данного пользователя?')) {
        const url = urls.users.restore(user.id);
        form.put(url, {
            onSuccess: () => {
                LogActions.save(url, 'PUT', 'Восстановление пользователя', formFields);
                router.get(urls.users.edit(user.id));
            },
        })
    }
}

const uploadFile = async(event) => {
    form.photo = event.files[0]
    update()
}

const isSelectedAdmin = computed(() => {
    return form.selectedRoles.indexOf('admin') >= 0
})

const classLabelWeight = 'w-1/6';

</script>
<template>

    <div>
        <Head :title="title" />

        <Breadcrumbs :home="{ label: 'Главная', url: urls.home }" :items="[
            { label: 'Пользователи', url: urls.users.index() },
            { label: form.name }
        ]" />

        <trashed-message v-if="user.deleted_at" class="mb-6 text-lg" @restore="restore"> Пользователь был удален. </trashed-message>

        <Panel>

            <template #header>
                <h1 class="font-bold text-xl">{{ user.fio }} ({{ user.name }})</h1>
            </template>
            <template #icons v-if="auth.can('admin')">
                <button class="p-panel-icon-header p-link" @click="toggleMenu">
                    <i class="pi pi-cog"></i>
                </button>
                <Menu ref="menu" :model="menuItems" popup />
            </template>

            <div class="w-full">

                <!-- <div class="mb-5 flex">
                    <div :class="classLabelWeight">
                        <img v-if="user.photo" class="block rounded-full" :src="user.photo" />
                    </div>
                    <div class="w-1/3">
                        <FileUpload
                            mode="basic"
                            type="file"
                            accept="image/*"
                            :multiple="false"
                            name="photo"
                            @uploader="uploadFile"
                            customUpload
                            chooseLabel="Обзор"
                            uploadLabel="Загрузить"
                        >
                        </FileUpload>
                    </div>
                </div> -->

                <div class="flex mb-6">
                    <div class="text-gray-500 font-semibold" :class="classLabelWeight">Имя</div>
                    <div>{{ user.name }}</div>
                </div>

                <div class="flex mb-6">
                    <div class="text-gray-500 font-semibold" :class="classLabelWeight">ФИО</div>
                    <div>{{ user.fio }}</div>
                </div>

                <div class="flex mb-6">
                    <div class="text-gray-500 font-semibold" :class="classLabelWeight">Учетная запись</div>
                    <div>{{ user.email }}</div>
                </div>

                <div class="flex mb-6">
                    <div class="text-gray-500 font-semibold" :class="classLabelWeight">Домен</div>
                    <div>{{ user.domain }}</div>
                </div>

                <div class="flex mb-6">
                    <div class="text-gray-500 font-semibold" :class="classLabelWeight">Организация</div>
                    <div>{{ user.org_code }} {{ user.company }}</div>
                </div>

                <div class="flex mb-6">
                    <div class="text-gray-500 font-semibold" :class="classLabelWeight">Отдел</div>
                    <div>{{ user.department }}</div>
                </div>

                <div class="flex mb-6">
                    <div class="text-gray-500 font-semibold" :class="classLabelWeight">Должность</div>
                    <div>{{ user.post }}</div>
                </div>

                <div class="flex mb-6">
                    <div class="text-gray-500 font-semibold" :class="classLabelWeight">Телефон</div>
                    <div>{{ user.telephone }}</div>
                </div>

                <div class="flex mb-6">
                    <div class="text-gray-500 font-semibold" :class="classLabelWeight">Email</div>
                    <div>{{ user.lotus_mail }}</div>
                </div>

                <ProgressSpinner v-if="form.processing"></ProgressSpinner>
                <template v-else>
                    <div class="flex mb-6">
                        <div class="text-gray-500 font-semibold" :class="classLabelWeight">Роли</div>
                        <div>
                            <div v-if="auth.can('admin')">
                                <div v-for="role in roles" :key="role.name" class="flex items-center mt-2">
                                    <template v-if="!(isSelectedAdmin && role.name != 'admin')">
                                        <Checkbox v-model="form.selectedRoles" :inputId="role.name" name="roles" :value="role.name" />
                                        <label :for="role.name" class="ml-2 cursor-pointer">
                                            {{ role.description }}
                                        </label>
                                    </template>
                                </div>
                            </div>
                            <div v-else>
                                <div v-if="user.roles.length == 0" class="text-orange-600">
                                    Роли не назначены
                                </div>
                                <ul v-else>
                                    <li v-for="role in user.roles" class="mt-2" :key="role">
                                        <span class="pi pi-users me-1"></span>
                                        {{ role.description }}
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="flex mb-6" v-if="!isSelectedAdmin">
                        <div class="w-1/6 text-gray-500 font-semibold">Контекст</div>

                        <div v-if="auth.can('admin')">
                            <div v-for="organization in organizations" :key="organization.code" class="mb-2">
                                <Checkbox v-model="form.selectedOrganizations" :inputId="organization.code" name="organizations" :value="organization.code" />
                                <label :for="organization.code" class="ml-2 cursor-pointer">
                                    {{ `${organization.name} (${organization.code})` }}
                                </label>
                                <div class="my-2" v-if="organization.children.length > 0">
                                    <div v-for="subOrganization in organization.children" :key="subOrganization.code" class="ms-5 mb-2">
                                        <Checkbox v-model="form.selectedOrganizations" :inputId="subOrganization.code" name="organizations" :value="subOrganization.code" />
                                        <label :for="subOrganization.code" class="ml-2 cursor-pointer">
                                            {{ `${subOrganization.name} (${subOrganization.code})` }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else>
                            <ul>
                                <li v-for="organization in organizations" class="mt-2" :key="organization.code">
                                    <span class="pi pi-building me-1"></span>
                                    {{ organization.name }}
                                    <template v-if="organization.children.length > 0">
                                        <ul class="ms-5">
                                            <li v-for="subOrganization in organization.children" class="mt-2" :key="subOrganization.code">
                                                <span class="pi pi-building me-1"></span>
                                                {{ subOrganization.name }}
                                            </li>
                                        </ul>
                                    </template>
                                </li>
                            </ul>
                        </div>

                    </div>
                </template>

                <div class="flex mb-6" v-if="form.isDirty">
                    <Button :loading="form.processing" class="font-bold" type="button" @click="update" label="Сохранить" />
                </div>

            </div>
        </Panel>

    </div>
</template>
