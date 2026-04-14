<script setup>
import { Head, useForm } from '@inertiajs/inertia-vue3'
import Panel from 'primevue/panel';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import InlineMessage from 'primevue/inlinemessage';
import Checkbox from 'primevue/checkbox';
import Label from '@/Shared/Label';
import { inject } from 'vue';
import FloatLabel from 'primevue/floatlabel';

const props = defineProps({
  labels: Object,
})

const urls = inject('urls')

const form = useForm({
  name: null,
  password: null,
  remember: false,
})

const login = () => {
  form.post(urls.auth.login())
}
const title = 'Аутентификация'
</script>

<template>

  <Head :title="title" />

  <div class="flex items-center justify-center p-6 min-h-screen bg-indigo-800">
    <form @submit.prevent="login" class="w-full max-w-md">
      <Panel :header="title" class="w-full max-w-md">
        <template #footer>
          <div class="flex justify-end">
            <Button :loading="form.processing" @click="login" class="btn-indigo" type="submit" label="Вход" />
          </div>
        </template>

        <div class="grid grid-cols-1 grid-rows-2 gap-8 my-4">
          <div>
            <FloatLabel>
              <InputText id="username" v-model="form.name" :invalid="form.errors?.name?.length > 0" class="w-full" type="text" autofocus />
              <label for="username">{{ labels.name }}</label>
            </FloatLabel>
            <InlineMessage v-if="form.errors?.name" class="mt-2" severity="error">{{ form.errors?.name }}
            </InlineMessage>
          </div>
          <div>
          <FloatLabel>
            <InputText id="password" v-model="form.password" :invalid="form.errors?.password?.length > 0" class="w-full" type="password" />
            <label for="password">{{ labels.password }}</label>
          </FloatLabel>
          <InlineMessage v-if="form.errors?.password" class="mt-2" severity="error">{{ form.errors?.password }}
          </InlineMessage>
          </div>
          <div>
            <div class="flex">
              <Checkbox v-model="form.remember" inputId="remember" name="remember" value="remember" :binary="true" />
              <Label class="ml-2" for="remember">Запомнить</Label>
            </div>
          </div>
        </div>
      </Panel>
    </form>
  </div>
</template>
