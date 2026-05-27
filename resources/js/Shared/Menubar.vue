<script setup>
import Avatar from 'primevue/avatar';
import Button from 'primevue/button';
import Menu from 'primevue/menu';
import Menubar from 'primevue/menubar';
import { defineAsyncComponent, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import Logo from '@/Shared/Logo.vue';
import { useDialog } from 'primevue/usedialog';
import { useAuth } from '@/Composables/useAuth';

const { appName } = defineProps({
  appName: {
    type: String,
    required: true,
  },
});

const { user } = useAuth();

const dialog = useDialog();
const OrganizationsDialog = defineAsyncComponent(
  () => import('@/Pages/Users/Organizations/Dialog.vue')
);
const openOrganizationsDialog = () => {
  dialog.open(OrganizationsDialog, {
    props: {
      header: 'Выбор организации',
      style: {
        width: '50vw',
      },
      breakpoints: {
        '960px': '75vw',
        '640px': '90vw',
      },
      modal: true,
    },
  });
};

const profileMenu = ref([
  {
    label: 'Профиль',
    icon: 'pi pi-user-edit',
    command: () => {
      router.get(route('users.edit', { user: user.value.id }));
    },
  },
  {
    label: 'Выход',
    icon: 'pi pi-sign-out',
    command: () => {
      router.delete(route('logout.custom'));
    },
  },
]);
const profileMenuToggled = ref();
const toggleProfileMenu = (event) => {
    profileMenuToggled.value.toggle(event);
};

</script>
<template>
  <Menubar :class="$style['p-menubar']">
    <template #start>
      <div class="flex items-center gap-3">
        <Logo class="w-10 h-10" />
      </div>
      <h1 class="ps-3 text-2xl font-extrabold">
        {{ appName }}
      </h1>
    </template>
    <template #end>
      <div class="flex items-center gap-4">
        <Button
          v-tooltip.bottom="`Текущая организация`"
          severity="secondary"
          class="font-bold"
          @click="openOrganizationsDialog"
        >
          <i class="pi pi-building-columns" />
          {{ user.org_code }}
        </Button>
        <div class="h-6 w-px bg-gray-200 mx-2" />
        <Avatar
          v-tooltip="`${user.fio || ''} (${user.name})`"
          aria-controls="overlay_menu_profile"
          class="font-extrabold cursor-pointer"
          shape="circle"
          @click="toggleProfileMenu"
        >
          <i class="pi pi-user" />
        </Avatar>
        <Menu id="overlay_menu_profile" ref="profileMenuToggled" :model="profileMenu" :popup="true" />
      </div>
    </template>
  </Menubar>
</template>
<style module>
.p-menubar {
    background-color: var(--color-gray-50);
    color: var(--color-surface-700);
    border: 1px solid var(--color-gray-200);
    border-radius: 0;
    box-shadow: 2px 0 10px rgba(0, 0, 0, .1);
}
</style>
