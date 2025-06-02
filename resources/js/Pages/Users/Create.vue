<script setup>
import { Head, Link, useForm } from '@inertiajs/inertia-vue3'
import Layout from '@/Shared/Layout'
import LoadingButton from '@/Shared/LoadingButton'
import Breadcrumbs from '@/Shared/Breadcrumbs'
import Panel from 'primevue/panel'
import Label from '@/Shared/Label'
import InputText from 'primevue/inputtext'
import InlineMessage from 'primevue/inlinemessage'
import { inject } from 'vue'

defineOptions({
    layout: Layout
})

const props = defineProps({
    domainName: String,
})

const urls = inject('urls')

const form = useForm({
    name: null,   
    domain: props.domainName,
})

const LogActions = inject('LogActions');

const save = () => {
    const url = urls.users.store();
    form.post(url, {
        onSuccess: () => {
            LogActions.save(url, 'POST', 'Добавление пользователя', {
                name: form.name,
                domain: form.domain,
            });
        },
    })
}

</script>

<template>
    <div>

        <Head title="Добавление пользователя" />

        <Breadcrumbs :home="{ label: 'Главная', url: urls.home }" :items="[
            { label: 'Пользователи', url: urls.users.index() },
            { label: form.name }
        ]" />

        <form @submit.prevent="save">
            <Panel>
                <template #header>
                    <h1 class="font-bold text-xl">Добавление пользователя</h1>
                </template>            
                    <div class="max-w-2xl">
                        <div class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                            <div class="sm:col-span-4">                                        
                                <Label for="domain">Домен</Label>
                                <InputText
                                    class="w-full"
                                    v-model="form.domain" 
                                    placeholder="DOMAIN" 
                                    :invalid="form.errors?.domain?.length > 0"
                                />
                                <InlineMessage v-if="form.errors?.domain" class="mt-2" severity="error">{{ form.errors?.domain }}</InlineMessage>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                            <div class="sm:col-span-4">                                        
                                <Label for="name">Учетная запись</Label>
                                <InputText
                                    class="w-full"
                                    v-model="form.name" 
                                    placeholder="XXXX-XX-XXX" 
                                    :invalid="form.errors?.name?.length > 0"
                                />
                                <InlineMessage v-if="form.errors?.name" class="mt-2" severity="error">{{ form.errors?.name }}</InlineMessage>
                            </div>
                        </div>                        
                    </div>
                <template #footer>
                    <loading-button :loading="form.processing" class="font-bold" type="submit">Сохранить</loading-button>
                </template>
            </Panel>
        </form>

    </div>
</template>
