<script setup>
import { Link } from '@inertiajs/vue3';
import Logo from '@/Shared/Logo';
import Dropdown from '@/Shared/Dropdown';
import MainMenu from '@/Shared/MainMenu';
import FlashMessages from '@/Shared/FlashMessages';
import { defineAsyncComponent, inject, onMounted } from 'vue';
import { initFlowbite } from 'flowbite';
import Button from 'primevue/button';
import { useDialog } from 'primevue/usedialog';

const props = defineProps({
  auth: Object,
  appName: String,
});

onMounted(() => {
  initFlowbite();
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
</script>

<template>
  <div>
    <div id="dropdown" />
    <div class="md:flex md:flex-col">
      <div class="md:flex md:flex-col md:h-screen">
        <div class="md:flex md:shrink-0">
          <div
            class="flex items-center justify-between px-6 bg-indigo-900 md:shrink-0 md:justify-center md:w-56"
          >
            <Link class="mt-1" href="/">
              <logo class="fill-white" width="120" height="28" />
            </Link>
            <dropdown class="md:hidden" placement="bottom-end">
              <template #default>
                <svg
                  class="w-6 h-6 fill-white"
                  xmlns="http://www.w3.org/2000/svg"
                  viewBox="0 0 20 20"
                >
                  <path d="M0 3h20v2H0V3zm0 6h20v2H0V9zm0 6h20v2H0v-2z" />
                </svg>
              </template>
              <template #dropdown>
                <div class="mt-2 px-8 py-4 bg-indigo-800 rounded shadow-lg">
                  <main-menu />
                </div>
              </template>
            </dropdown>
          </div>
          <div
            class="text-md flex items-center justify-between p-4 w-full text-sm bg-white border-b border-b-gray-200 px-12 py-0"
          >
            <div>
              <h1 class="ps-3 text-2xl font-extrabold text-gray-800">
                {{ props.appName }}
              </h1>
            </div>
            <div class="font-bold">
              <Button
                v-tooltip="`Текущая организация`"
                text
                size="small"
                plain
                @click="openOrganizationsDialog"
              >
                <div class="flex gap-2 underline decoration-dashed">
                  <i class="pi pi-building" />
                  {{ auth.user.org_code }}
                </div>
              </Button>

              <dropdown class="ms-6 mt-1" placement="bottom-end">
                <template #default>
                  <Button text size="small" plain>
                    <div class="flex gap-x-2">
                      <i class="pi pi-user" />
                      <span v-if="auth.user.fio">{{ auth.user.fio }}</span>
                      <span v-else>{{ auth.user.name }}</span>
                      <i class="pi pi-angle-down" />
                    </div>
                  </Button>
                </template>
                <template #dropdown>
                  <div class="mt-2 py-2 text-sm bg-white rounded shadow-xl">
                    <Link
                      class="block px-6 py-2 hover:text-white hover:bg-indigo-500"
                      :href="urls.users.edit(auth.user.id)"
                    >
                      Профиль
                    </Link>
                    <Link
                      href="/logout"
                      as="button"
                      class="block px-6 py-2 w-full text-left hover:text-white hover:bg-indigo-500"
                      method="delete"
                    >
                      Выход
                    </Link>
                  </div>
                </template>
              </dropdown>
            </div>
          </div>
        </div>
        <div class="md:flex md:grow md:overflow-hidden">
          <MainMenu
            id="sidebar-multi-level-sidebar"
            class="hidden shrink-0 p-12 w-56 bg-indigo-800 overflow-y-auto md:block text-indigo-300"
            aria-label="Sidebar"
          />
          <div
            class="px-4 py-8 md:flex-1 md:p-12 md:overflow-y-auto"
            scroll-region
          >
            <flash-messages />
            <slot />
          </div>
        </div>
      </div>
    </div>
  </div>
  <DynamicDialog />
  <ConfirmDialog />
</template>
