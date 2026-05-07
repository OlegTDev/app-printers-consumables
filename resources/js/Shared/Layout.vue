<script setup>
import MainMenu from '@/Shared/MainMenu';
import FlashMessages from '@/Shared/FlashMessages';
import { inject } from 'vue';
import Menubar from './Menubar.vue';

const props = defineProps({
  auth: Object,
  appName: String,
});

/** @type {typeof import('@/config/urls').urls} */
const urls = inject('urls');

</script>

<template>
  <div :class="$style.layout">
    <nav :class="$style.nav">
      <Menubar :app-name="props.appName" :auth="auth" :urls="urls" />
    </nav>
    <aside :class="$style.aside">
      <MainMenu class="flex-1 px-4 py-6" />
    </aside>
    <main :class="$style.main" scroll-region>
      <FlashMessages />
      <slot />
    </main>
  </div>
  <DynamicDialog />
  <ConfirmDialog />
</template>

<style module>
.layout {
  display: grid;
  grid-template-areas:
    "nav nav"
    "aside main";
  grid-template-columns: 250px 1fr;
  grid-template-rows: 58px 1fr;
  height: 100vh;
}

.nav {
  grid-area: nav;
}

.aside {
  grid-area: aside;
  background-color: var(--color-indigo-800);
}

.main {
  grid-area: main;
  padding: 20px;
}
</style>
