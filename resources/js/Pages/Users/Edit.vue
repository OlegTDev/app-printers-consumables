<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import Layout from '@/Shared/Layout.vue';
import Breadcrumbs from '@/Shared/Breadcrumbs.vue';
import Checkbox from 'primevue/checkbox';
import { ref, computed } from 'vue';
import Button from 'primevue/button';
import { useAuth } from '@/Composables/useAuth';
import { useConfirm } from 'primevue/useconfirm';
import { Menu, Tree } from 'primevue';
import { onMounted } from 'vue';
import Card from '@/Shared/Card.vue';
import Title from '@/Shared/Title.vue';
import DetailViewer from '@/Shared/DetailViewer.vue';
import TrashedMessage from './TrashedMessage.vue';

const props = defineProps({
  user: Object,
  roles: Object,
  organizations: Object,
  labels: Object,
});

defineOptions({
  layout: Layout
});

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
  form.put(route('users.update', { user: props.user.id }), {
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
      form.delete(route('users.destroy', { user: props.user.id }), {
        onSuccess: () => {
          router.get(route('users.edit', { user: props.user.id }));
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
      form.put(route('users.restore', { user: props.user.id }), {
        onSuccess: () => {
          router.get(route('users.edit', { user: props.user.id }));
        },
      });
    },
  }));
};

const isSelectedAdmin = computed(() => {
  return form.selectedRoles.indexOf('admin') >= 0;
});

// function onNodeSelect(event) {
//   const { key, selected } = event.node;
//   selectedKeys.value[key] = selected;
// }

// function onNodeUnselect(event) {
//   const { key } = event.node;
//   selectedKeys.value[key] = false;
// }

// function toggleNode(node) {
//   selectedKeys.value[node.key] = !selectedKeys.value[node.key];
// }

const expandedKeys = ref({});
function expandAll() {
  const keys = {};

  const collectKeys = (list) => {
    for (const node of list) {
      keys[node.key] = true;
      if (node.children && node.children.length) {
        collectKeys(node.children);
      }
    }
  };

  collectKeys(props.organizations);
  expandedKeys.value = keys;
}
onMounted(() => expandAll());

const details = computed(() => [
  {
    label: props.labels.name,
    value: props.user.name,
  },
  {
    label: props.labels.fio,
    value: props.user.fio,
  },
  {
    label: props.labels.email,
    value: props.user.email,
  },
  {
    label: props.labels.domain,
    value: props.user.domain,
  },
  {
    label: props.labels.organization,
    value: `${props.user.org_code} ${(props.user.company ?? '')}`,
  },
  {
    label: props.labels.department,
    value: props.user.department,
  },
  {
    label: props.labels.post,
    value: props.user.post,
  },
  {
    label: props.labels.telephone,
    value: props.user.telephone,
  },
  {
    label: props.labels.lotus_mail,
    value: props.user.lotus_mail,
  },
  { label: 'Роли', keySlot: 'roles' },
  { label: 'Контекст', keySlot: 'context', hide: form.selectedRoles.includes('admin') },
]);
</script>
<template>
  <Head :title="title" />

  <Breadcrumbs
    :home="{ label: 'Главная', url: route('home') }"
    :items="[
      { label: 'Пользователи', url: route('users.index') },
      { label: form.name }
    ]"
  />

  <Card>
    <Title>
      {{ title }}
      <template v-if="can('admin')" #icons>
        <button class="p-panel-icon-header p-link cursor-pointer" @click="toggleMenu">
          <i class="pi pi-cog" />
        </button>
        <Menu ref="menu" :model="menuItems" popup />
      </template>
    </Title>

    <trashed-message v-if="user.deleted_at" @restore="restore" />

    <DetailViewer :items="details">
      <template #roles>
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
      </template>
      <template #context>
        <Tree
          :value="organizations"
          :expanded-keys="expandedKeys"
        >
          <template #default="slotProps">
            <div class="flex items-center gap-2">
              <template v-if="can('admin')">
                <Checkbox
                  v-model="form.selectedOrganizations"
                  :input-id="slotProps.node.key"
                  name="organizations"
                  :value="slotProps.node.key"
                />
                <label :for="slotProps.node.key" class="ml-2 cursor-pointer">
                  {{ slotProps.node.label }}
                </label>
              </template>
              <template v-else>
                <i class="pi pi-building-columns" />
                {{ slotProps.node.label }}
              </template>
            </div>
          </template>
        </Tree>
      </template>
    </DetailViewer>

    <template v-if="form.isDirty" #footer>
      <Button :loading="form.processing" class="font-bold" type="button" label="Сохранить" @click="update" />
    </template>
  </Card>
</template>
