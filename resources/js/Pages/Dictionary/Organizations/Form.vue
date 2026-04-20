<script setup>
import InputText from 'primevue/inputtext'
import InlineMessage from 'primevue/inlinemessage'
import Label from '@/Shared/Label'
import { inject, reactive } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import Button from 'primevue/button'
import { useConfirm } from "primevue/useconfirm"

const props = defineProps({
    isNew: Boolean,
    labels: Object,
    organization: Object,
});
const organization = props.organization;
const urls = inject('urls');
const confirm = useConfirm();

const form = useForm({
    code: organization.code,
    name: organization.name,
    parent: organization.parent,
});

const LogActions = inject('LogActions');
const formFields = reactive({
    code: form.organization,
    name: form.name,
    parent: form.parent,
});

const save = () => {
    if (props.isNew) {
        const url = urls.dictionary.organizations.store();
        form.post(url, { onSuccess: () => {
            LogActions.save(url, 'POST', 'Добавление организации', formFields);
        }});
    }
    else {
        const url = urls.dictionary.organizations.update(organization.code);
        form.put(url, { onSuccess: () => {
            LogActions.save(url, 'PUT', 'Обновление организации', formFields);
        }});
    }
};

const destroy = () => {
    confirm.require({
        message: 'Вы уверены, что хотите удалить?',
        header: 'Удаление',
        accept: () => {
            const url = urls.dictionary.organizations.delete(organization.code);
            router.delete(url, {
                onSuccess: () => {
                    LogActions.save(url, 'DELETE', 'Удаление организации', organization);
                },
            });
        },
    });
};
</script>
<template>
    <form @submit.prevent="save">
        <div class="rounded-lg bg-white shadow-sm border border-gray-200">

            <div class="p-10">

                <div class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                    <div class="sm:col-span-4">
                        <Label for="code">{{ labels.code }}</Label>
                        <InputText
                            class="w-full"
                            v-model="form.code"
                            :placeholder="labels.code"
                            :invalid="form.errors?.code?.length > 0"
                        />
                        <InlineMessage v-if="form.errors?.code" class="mt-2" severity="error">{{ form.errors?.code }}</InlineMessage>
                    </div>
                </div>

                <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                    <div class="sm:col-span-4">
                        <Label for="parent">{{ labels.parent }}</Label>
                        <InputText
                            class="w-full"
                            v-model="form.parent"
                            :placeholder="labels.parent"
                            :invalid="form.errors?.parent?.length > 0"
                        />
                        <InlineMessage v-if="form.errors?.parent" class="mt-2" severity="error">{{ form.errors?.parent }}</InlineMessage>
                    </div>
                </div>

                <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                    <div class="sm:col-span-4">
                        <Label for="name">{{ labels.name }}</Label>
                        <InputText
                            class="w-full"
                            v-model="form.name"
                            :placeholder="labels.name"
                            :invalid="form.errors?.name?.length > 0"
                        />
                        <InlineMessage v-if="form.errors?.name" class="mt-2" severity="error">{{ form.errors?.name }}</InlineMessage>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between p-5 bg-gray-50 border-t border-gray-100 w-full">
                <Button :loading="form.processing" class="font-bold" type="submit" label="Сохранить" />
                <Button v-if="!props.isNew" severity="danger" class="font-bold" type="button" @click="destroy">
                    Удалить
                </Button>
            </div>

        </div>

    </form>
</template>
