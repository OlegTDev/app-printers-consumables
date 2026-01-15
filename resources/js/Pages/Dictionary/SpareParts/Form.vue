<script setup>
import { useForm } from '@inertiajs/inertia-vue3';
import Label from '@/Shared/Label';
import InputText from 'primevue/inputtext';
import InlineMessage from 'primevue/inlinemessage';
import LoadingButton from '@/Shared/LoadingButton';
import Button from 'primevue/button';
import { inject } from 'vue';
import { useConfirm } from 'primevue/useconfirm';
import { Inertia } from '@inertiajs/inertia';

const props = defineProps({
    isNew: Boolean,
    labels: Object,
    sparePart: Object,
});

const form = useForm({
    name: props.sparePart.name,
    description: props.sparePart.description,
});

const urls = inject('urls');
const confirm = useConfirm();

const save = () => {
    if (props.isNew) {
        const url = urls.dictionary.spareParts.store();
        form.post(url);
    }
    else {
        const url = urls.dictionary.spareParts.update(props.sparePart?.id);
        form.put(url);
    }
};

const destroy = () => {   
    confirm.require({
        message: 'Вы уверены, что хотите удалить?',
        header: 'Удаление',
        accept: () => {
            const url = urls.dictionary.spareParts.delete(props.sparePart?.id);
            Inertia.delete(url);
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
                        <Label for="name">{{ labels.name }}</Label>
                        <InputText class="w-full" v-model="form.name" :placeholder="labels.name"
                            :invalid="form.errors?.name?.length > 0" />
                        <InlineMessage v-if="form.errors?.name" class="mt-2" severity="error">{{ form.errors?.name }}
                        </InlineMessage>
                    </div>
                </div>

                <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                    <div class="sm:col-span-4">
                        <Label for="description">{{ labels.description }}</Label>
                        <InputText class="w-full" v-model="form.description" :placeholder="labels.description"
                            :invalid="form.errors?.description?.length > 0" />
                        <InlineMessage v-if="form.errors?.description" class="mt-2" severity="error">{{
                            form.errors?.description }}
                        </InlineMessage>
                    </div>
                </div>

            </div>

            <div class="flex items-center justify-between p-5 bg-gray-50 border-t border-gray-100 w-full">
                <loading-button :loading="form.processing" class="font-bold" type="submit">Сохранить</loading-button>
                <Button v-if="!props.isNew" severity="danger" class="font-bold" type="button" @click="destroy">
                    Удалить
                </Button>
            </div>

        </div>
    </form>
</template>