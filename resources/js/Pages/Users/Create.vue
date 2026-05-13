<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import Layout from '@/Shared/Layout';
import LoadingButton from '@/Shared/LoadingButton';
import Breadcrumbs from '@/Shared/Breadcrumbs';
import Panel from 'primevue/panel';
import Label from '@/Shared/Label';
import InputText from 'primevue/inputtext';
import { useConfig } from '@/Composables/useConfig';
import Message from 'primevue/message';

defineOptions({
  layout: Layout,
});

const { urls } = useConfig();

const form = useForm({
  name: null,
});

const save = () => {
  form.post(urls.users.store());
};
</script>
<template>
  <div>
    <Head title="Добавление пользователя" />

    <Breadcrumbs
      :home="{ label: 'Главная', url: urls.home }"
      :items="[
        { label: 'Пользователи', url: urls.users.index() },
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
