<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import Layout from '@/Shared/Layout.vue';
import LoadingButton from '@/Shared/LoadingButton.vue';
import Breadcrumbs from '@/Shared/Breadcrumbs.vue';
import Panel from 'primevue/panel';
import Label from '@/Shared/Label.vue';
import InputText from 'primevue/inputtext';
import Message from 'primevue/message';

defineOptions({
  layout: Layout,
});

const form = useForm({
  name: null,
});

const save = () => {
  form.post(route('users.store'));
};
</script>
<template>
  <div>
    <Head title="Добавление пользователя" />

    <Breadcrumbs
      :home="{ label: 'Главная', url: route('home') }"
      :items="[
        { label: 'Пользователи', url: route('users.index') },
        { label: form.name }
      ]"
    />

    <form @submit.prevent="save">
      <Panel>
        <template #header>
          <h1 class="font-bold text-xl">
            Добавление пользователя
          </h1>
        </template>
        <div class="max-w-2xl">
          <div class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
            <div class="sm:col-span-4">
              <Label for="name">Учетная запись</Label>
              <InputText
                v-model="form.name"
                placeholder="XXXX-XX-XXX"
                class="w-full"
                :invalid="form.errors?.name?.length > 0"
              />
              <Message v-if="form.errors?.name" class="mt-2" severity="error">
                {{ form.errors?.name }}
              </Message>
            </div>
          </div>
        </div>
        <template #footer>
          <loading-button :loading="form.processing" class="font-bold" type="submit">
            Сохранить
          </loading-button>
        </template>
      </Panel>
    </form>
  </div>
</template>
