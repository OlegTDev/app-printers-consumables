<script setup>
import { useForm } from '@inertiajs/vue3'
import Layout from '@/Shared/Layout'
import Dropdown from 'primevue/dropdown'
import InputText from 'primevue/inputtext'
import Button from 'primevue/button'
import { reactive, computed, inject } from 'vue'
import Label from '@/Shared/Label'
import Textarea from 'primevue/textarea'
import InlineMessage from 'primevue/inlinemessage'

defineOptions({
    layout: Layout
});

const props = defineProps({
    isNew: Boolean,
    consumable: Object,
    consumableTypes: Object,
    labels: Object,
    cartridgeColors: Object,
});

const consumable = reactive(props.consumable);
const urls = inject('urls');
const form = useForm({
    type: consumable.type,
    name: consumable.name,
    color: consumable.color,
    description: consumable.description,
});

const consumableTypes = computed(() => {
    let res = [];
    Object.keys(props.consumableTypes).forEach((key) => {
        res.push({
            name: props.consumableTypes[key],
            code: key,
        })
    });
    return res;
});

const colors = computed(() => {
    let res = [];
    Object.keys(props.cartridgeColors).forEach((key) => {
        res.push({
            name: props.cartridgeColors[key]['name'],
            code: key,
            color: props.cartridgeColors[key]['color'],
        })
    });
    return res;
});

const LogActions = inject('LogActions');
const formFields = reactive({
    type: form.type,
    name: form.name,
    color: form.color,
    description: form.description,
});

const save = () => {
    if (props.isNew) {
        const url = urls.dictionary.consumables.store();
        form.post(url, { onSuccess: () => {
            LogActions.save(url, 'POST', 'Создание расходного материала', formFields);
        }});
    }
    else {
        const url = urls.dictionary.consumables.update(consumable.id);
        form.put(url, { onSuccess: () => {
            LogActions.save(url, 'PUT', 'Обновление расходного материала', formFields);
        }});
    }
};

</script>

<template>
    <form @submit.prevent="save">
        <div class="rounded-lg bg-white shadow-sm border border-gray-200">

            <div class="p-10">

                <div class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                    <div class="sm:col-span-4">
                        <Label for="type">{{ labels.type }}</Label>
                        <Dropdown
                            class="w-full"
                            v-model="form.type"
                            id="type"
                            :options="consumableTypes"
                            optionValue="code"
                            optionLabel="name"
                            :placeholder="labels.type"
                            :invalid="form.errors?.type?.length > 0"
                        />
                        <InlineMessage v-if="form.errors?.type" class="mt-2" severity="error">{{ form.errors?.type }}</InlineMessage>
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

                <div v-if="form.type === 'cartridge'" class="mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                    <div class="sm:col-span-4">
                        <Label for="color">{{ labels.color }}</Label>
                        <Dropdown
                            v-model="form.color"
                            :options="colors"
                            :invalid="form.errors?.color?.length > 0"
                            optionValue="code"
                            optionLabel="name"
                            :placeholder="labels.color"
                            class="w-full"
                        >
                        </Dropdown>
                        <InlineMessage v-if="form.errors?.color" class="mt-2" severity="error">{{ form.errors?.color }}</InlineMessage>
                    </div>
                </div>

                <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                    <div class="sm:col-span-4">
                        <Label for="name">{{ labels.description }}</Label>
                        <Textarea
                            class="w-full"
                            v-model="form.description"
                            rows="5"
                            :placeholder="labels.description"
                            :invalid="form.errors?.description?.length > 0"
                        />
                        <InlineMessage v-if="form.errors?.description" class="mt-2" severity="error">{{ form.errors?.description }}</InlineMessage>
                    </div>
                </div>

            </div>

            <div class="flex items-center justify-between p-5 py-4 bg-gray-50 border-t border-gray-100 w-full">
                <Button :loading="form.processing" class="font-bold" type="submit" label="Сохранить" />
            </div>

        </div>
    </form>

</template>
