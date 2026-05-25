<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Checkbox from 'primevue/checkbox';
import Label from '@/Shared/Label.vue';
import FloatLabel from 'primevue/floatlabel';
import Message from 'primevue/message';
import Card from '@/Shared/Card.vue';
import FieldRowVertical from '@/Shared/Form/FieldRowVertical.vue';
import Password from 'primevue/password';

defineProps({
  labels: Object,
});

const form = useForm({
  name: null,
  password: null,
  remember: true,
});

const login = () => {
  form.post(route('login'));
};
const title = 'Аутентификация';
</script>
<template>
  <Head :title="title" />

  <div class="flex items-center justify-center p-6 min-h-screen bg-indigo-800">
    <form class="w-full max-w-md" @submit.prevent="login">
      <Card padding-body-classes="p-5" padding-footer-classes="p-5" footer-container-classes="flex justify-end">
        <template #header>
          <span class="text-base font-bold">🔓 {{ title }}</span>
        </template>
        <div class="grid gap-y-8 my-2">
          <FieldRowVertical>
            <template #field>
              <FloatLabel>
                <InputText
                  v-model="form.name"
                  input-id="username"
                  :invalid="!!form.errors?.name"
                  class="w-full"
                  type="text"
                  autofocus
                />
                <label for="username">{{ labels.name }}</label>
              </FloatLabel>
            </template>
            <template #message>
              <Message v-if="!!form.errors?.name" class="mt-2" severity="error">
                {{ form.errors?.name }}
              </Message>
            </template>
          </FieldRowVertical>

          <FieldRowVertical>
            <template #field>
              <FloatLabel>
                <Password
                  v-model="form.password"
                  input-id="password"
                  :invalid="!!form.errors?.password"
                  type="password"
                  class="w-full"
                  input-class="w-full"
                  toggle-mask
                  :feedback="false"
                  show-clear
                />
                <label for="password">{{ labels.password }}</label>
              </FloatLabel>
            </template>
            <template #message>
              <Message v-if="!!form.errors?.password" class="mt-2" severity="error">
                {{ form.errors?.password }}
              </Message>
            </template>
          </FieldRowVertical>
          <FieldRowVertical>
            <template #field>
              <div class="flex">
                <Checkbox
                  v-model="form.remember"
                  input-id="remember"
                  name="remember"
                  value="remember"
                  :binary="true"
                />
                <Label class="ml-2 cursor-pointer" for="remember">Запомнить</Label>
              </div>
            </template>
          </FieldRowVertical>
        </div>
        <template #footer>
          <Button
            :loading="form.processing"
            type="submit"
            label="Вход"
          />
        </template>
      </Card>
    </form>
  </div>
</template>
