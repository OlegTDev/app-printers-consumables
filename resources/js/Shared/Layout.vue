<script setup>
import { router } from '@inertiajs/vue3';
import Logo from '@/Shared/Logo';
import MainMenu from '@/Shared/MainMenu';
import FlashMessages from '@/Shared/FlashMessages';
import { defineAsyncComponent, inject, ref } from 'vue';
import Button from 'primevue/button';
import { useDialog } from 'primevue/usedialog';
import Menubar from 'primevue/menubar';
import Avatar from 'primevue/avatar';
import Menu from 'primevue/menu';

const props = defineProps({
  auth: Object,
  appName: String,
});

/** @type {typeof import('@/config/urls').urls} */
const urls = inject('urls');

const dialog = useDialog();
const OrganizationsDialog = defineAsyncComponent(
  () => import('@/Pages/Users/Organizations/Dialog.vue')
);
const openOrganizationsDialog = () => {
  const dialogRef = dialog.open(OrganizationsDialog, {
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
    data: {
      auth: props.auth,
    },
  });
};

const profileMenu = ref([
  {
    label: 'Профиль',
    icon: 'pi pi-user-edit',
    command: () => {
      router.get(urls.users.edit(props.auth.user.id));
    },
  },
  {
    label: 'Выход',
    icon: 'pi pi-sign-out',
    command: () => {
      router.delete(urls.auth.logout());
    },
  },
]);
const profileMenuToggled = ref();
const toggleProfileMenu = (event) => {
    profileMenuToggled.value.toggle(event);
};

</script>

<template>
  <div class="flex flex-col h-screen bg-gray-100">
    <Menubar class="shrink-0 h-14">
      <template #start>
        <div class="flex items-center gap-3">
          <logo class="w-10 h-10" />
        </div>
        <h1 class="ps-3 text-2xl font-extrabold">
          {{ props.appName }}
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
            {{ auth?.user?.org_code }}
          </Button>
          <div class="h-6 w-px bg-gray-200 mx-2" />
          <Avatar
            v-tooltip="`${auth?.user?.fio ? auth.user.fio : ''} (${auth?.user?.name})`"
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
    <div class="flex flex-1 overflow-hidden">
      <aside class="hidden md:flex flex-col w-64 bg-indigo-800 overflow-y-auto">
        <MainMenu class="flex-1 px-4 py-6" />
      </aside>

      <main class="flex-1 overflow-y-auto p-8" scroll-region>
        <FlashMessages />
        <slot />
      </main>
    </div>
  </div>
  <DynamicDialog />
  <ConfirmDialog />
</template>
<style>
.p-menubar {
    background-color: var(--color-gray-50) !important;
    color: var(--color-surface-700) !important;
    border: 1px solid var(--color-gray-200) !important;
    border-radius: 0 !important;
    box-shadow: 2px 0 10px rgba(0, 0, 0, .1);
}
</style>
