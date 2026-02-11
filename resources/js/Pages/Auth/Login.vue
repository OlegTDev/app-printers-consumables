<script setup>
import { Head, useForm } from '@inertiajs/inertia-vue3'
import Panel from 'primevue/panel';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import InlineMessage from 'primevue/inlinemessage';
import Checkbox from 'primevue/checkbox';
import Label from '@/Shared/Label';
import { inject } from 'vue';

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
        <Panel :header="title" class="w-full max-w-md">
            <template #footer>
                <div class="flex justify-end">
                    <Button :loading="form.processing" @click="login" class="btn-indigo" type="button" label="Вход" />
                </div>
            </template>
            <form @submit.prevent="login">
                <div class="grid grid-cols-1 grid-rows-1 gap-4">
                    <div>
                        <InputText v-model="form.name" :invalid="form.errors?.name?.length > 0"
                            class="w-full" label="Учетная запись" type="text" autofocus />
                        <InlineMessage v-if="form.errors?.name" class="mt-2" severity="error">{{ form.errors?.name }}</InlineMessage>
                    </div>
                    <div>
                        <InputText v-model="form.password" :invalid="form.errors?.password?.length > 0"
                            class="w-full" label="Пароль" type="password" />
                        <InlineMessage v-if="form.errors?.password" class="mt-2" severity="error">{{ form.errors?.password }}</InlineMessage>
                    </div>
                    <div>
                        <div class="flex">
                            <Checkbox v-model="form.remember" inputId="remember" name="remember" value="remember" />
                            <Label class="ml-2" for="remember">Запомнить</Label>
                        </div>
                    </div>
                </div>
            </form>
        </Panel>
    </div>
</template>
