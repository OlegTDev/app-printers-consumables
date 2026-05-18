<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import Layout from '@/Shared/Layout.vue';
import TrashedMessage from '@/Shared/TrashedMessage.vue';
import Breadcrumbs from '@/Shared/Breadcrumbs.vue';
import Panel from 'primevue/panel';
import Checkbox from 'primevue/checkbox';
import { ref, computed } from 'vue';
import Menu from 'primevue/menu';
import ProgressSpinner from 'primevue/progressspinner';
import Button from 'primevue/button';
import { useConfig } from '@/Composables/useConfig';
import { useAuth } from '@/Composables/useAuth';
import { useConfirm } from 'primevue/useconfirm';

const props = defineProps({
  user: Object,
  roles: Object,
  organizations: Object,
});

defineOptions({
  layout: Layout
});

const { urls } = useConfig();
const { can } = useAuth();
const confirm = useConfirm();

const objectToArray = (obj, field) => Array.from(Object.values(obj), (key) => key[field]);

const userRoles = objectToArray(props.user.roles, 'name');
const userOrganizations = objectToArray(props.user.organizations, 'code');

const menu = ref(null);
const menuItems = computed(() => [
  { label: 'Удалить', icon: 'pi pi-times', visible: props.user.deleted_at === null, command: () => destroy() },
  { label: 'Восстановить', icon: 'pi pi-undo', visible: props.user.deleted_at !== null, command: () => restore() },
]);
const toggleMenu = (event) => menu.value.toggle(event);

const form = useForm({
  name: props.user.name,
  email: props.user.email,
  password: '',
  photo: null,
  selectedRoles: userRoles,
  selectedOrganizations: userOrganizations,
});

const title = `${props.user.fio ?? ''} (${props.user.name})`;

function update() {
  form.put(urls.users.update(props.user.id), {
    onError: () => {

    },
    preserveState: true,
    preserveScroll: true,
  });
}

const destroy = () => {
  if (confirm.require({
    message: 'Вы уверены, что хотите удалить данного пользователя?',
    header: 'Удаление',
    accept: () => {
      form.delete(urls.users.delete(props.user.id), {
        onSuccess: () => {
          router.get(urls.users.edit(props.user.id));
        },
      });
    },
  }));
};

const restore = () => {
  if (confirm.require({
    message: 'Вы уверены, что хотите восстановить данного пользователя?',
    header: 'Восстановление',
    accept: () => {
      form.put(urls.users.restore(props.user.id), {
        onSuccess: () => {
          router.get(urls.users.edit(props.user.id));
        },
      });
    },
  }));
};

const isSelectedAdmin = computed(() => {
  return form.selectedRoles.indexOf('admin') >= 0;
});

</script>
<template>
  <Head :title="title" />

  <Breadcrumbs
    :home="{ label: 'Главная', url: urls.home }"
    :items="[
      { label: 'Пользователи', url: urls.users.index() },
      { label: form.name }
    ]"
  />

  <trashed-message v-if="user.deleted_at" class="mb-6 text-lg" @restore="restore">
    Пользователь был удален.
  </trashed-message>

  <Panel>
    <template #header>
      <h1 class="font-bold text-xl">
        {{ user.fio }} ({{ user.name }})
      </h1>
    </template>
    <template v-if="can('admin')" #icons>
      <button class="p-panel-icon-header p-link" @click="toggleMenu">
        <i class="pi pi-cog" />
      </button>
      <Menu ref="menu" :model="menuItems" popup />
    </template>

    <div class="grid grid-cols-6 gap-6">
      <template
        v-for="[ label, value ] in [
          ['Имя', user.name],
          ['ФИО', user.fio],
          ['Учетная запись', user.email],
          ['Домен', user.domain],
          ['Организация', user.org_code + ' ' + (user.company ?? '')],
          ['Отдел', user.department],
          ['Должность', user.post],
          ['Телефон', user.telephone],
          ['Email', user.lotus_mail]
        ]"
        :key="label"
      >
        <div class="text-gray-500 font-semibold col-span-1">
          {{ label }}
        </div>
        <div class="col-span-5">
          {{ value }}
        </div>
      </template>

      <ProgressSpinner v-if="form.processing" />
      <template v-else>
        <div class="text-gray-500 font-semibold col-span-1">
          Роли
        </div>
        <div class="col-span-5">
          <div v-if="can('admin')">
            <div v-for="role in roles" :key="role.name" class="flex items-center mt-2">
              <template v-if="!(isSelectedAdmin && role.name != 'admin')">
                <Checkbox v-model="form.selectedRoles" :input-id="role.name" name="roles" :value="role.name" />
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
              <li v-for="role in user.roles" :key="role" class="mt-2">
                <i class="pi pi-users me-1" />
                {{ role.description }}
              </li>
            </ul>
          </div>
        </div>

        <div class="text-gray-500 font-semibold col-span-1">
          Контекст
        </div>
        <div class="col-span-5">
          <div v-if="can('admin')">
            <div v-for="organization in organizations" :key="organization.code" class="mb-2">
              <Checkbox
                v-model="form.selectedOrganizations"
                :input-id="organization.code"
                name="organizations"
                :value="organization.code"
              />
              <label :for="organization.code" class="ml-2 cursor-pointer">
                {{ `${organization.name} (${organization.code})` }}
              </label>
              <div v-if="organization.children.length > 0" class="my-2">
                <div v-for="subOrganization in organization.children" :key="subOrganization.code" class="ms-5 mb-2">
                  <Checkbox
                    v-model="form.selectedOrganizations"
                    :input-id="subOrganization.code"
                    name="organizations"
                    :value="subOrganization.code"
                  />
                  <label :for="subOrganization.code" class="ml-2 cursor-pointer">
                    {{ `${subOrganization.name} (${subOrganization.code})` }}
                  </label>
                </div>
              </div>
            </div>
          </div>
          <div v-else>
            <ul>
              <li v-for="organization in organizations" :key="organization.code" class="mt-2">
                <i class="pi pi-building me-1" />
                {{ organization.name }}
                <template v-if="organization.children.length > 0">
                  <ul class="ms-5">
                    <li v-for="subOrganization in organization.children" :key="subOrganization.code" class="mt-2">
                      <i class="pi pi-building me-1" />
                      {{ subOrganization.name }}
                    </li>
                  </ul>
                </template>
              </li>
            </ul>
          </div>
        </div>
      </template>

      <div v-if="form.isDirty" class="col-span-6">
        <Button :loading="form.processing" class="font-bold" type="button" label="Сохранить" @click="update" />
      </div>
    </div>
  </Panel>
</template>
