<script setup>
import InputText from 'primevue/inputtext'
import InlineMessage from 'primevue/inlinemessage'
import InputSwitch from 'primevue/inputswitch'
import Label from '@/Shared/Label'
import { inject, reactive, ref } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import LoadingButton from '@/Shared/LoadingButton'
import Button from 'primevue/button'
import { useConfirm } from "primevue/useconfirm";
import Dropdown from 'primevue/dropdown';

const props = defineProps({
    isNew: Boolean,
    labels: Object,
    printer: Object,
    manufacturers: Array,
});
const printer = reactive(props.printer);
const urls = inject('urls');
const confirm = useConfirm();
const dropdownManufacturers = ref(props.manufacturers);

const form = useForm({
    vendor: printer.vendor,
    model: printer.model,
    is_color_print: printer.is_color_print,
});

const LogActions = inject('LogActions');
const formFields = reactive({
    vendor: form.vendor,
    model: form.model,
    is_color_print: form.is_color_print,
})

const save = () => {
    if (props.isNew) {
        const url = urls.dictionary.printers.index();
        form.post(url, {
            onSuccess: () => {
                LogActions.save(url, 'POST', 'Добавление принтера', formFields);
            },
        });
    }
    else {
        const url = urls.dictionary.printers.update(printer.id);
        form.put(url, {
            onSuccess: () => {
                LogActions.save(url, 'PUT', 'Обновление принтера', formFields);
            },
        });
    }
};

const destroy = () => {
    confirm.require({
        message: 'Вы уверены, что хотите удалить?',
        header: 'Удаление',
        accept: () => {
            const url = urls.dictionary.printers.delete(printer.id);
            router.delete(url, {
                onSuccess: () => {
                    LogActions.save(url, 'DELETE', 'Удаление принтера', formFields);
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
                        <Label for="vendor">{{ labels.vendor }}</Label>
                        <Dropdown
                            class="w-full"
                            v-model="form.vendor"
                            :options="dropdownManufacturers"
                            optionLabel="label"
                            optionValue="value"
                            :placeholder="labels.vendor"
                            :invalid="form.errors?.vendor?.length > 0"
                        />
                        <InlineMessage v-if="form.errors?.vendor" class="mt-2" severity="error">{{ form.errors?.vendor }}</InlineMessage>
                    </div>
                </div>

                <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                    <div class="sm:col-span-4">
                        <Label for="model">{{ labels.model }}</Label>
                        <InputText
                            class="w-full"
                            v-model="form.model"
                            :placeholder="labels.model"
                            :invalid="form.errors?.model?.length > 0"
                        />
                        <InlineMessage v-if="form.errors?.model" class="mt-2" severity="error">{{ form.errors?.model }}</InlineMessage>
                    </div>
                </div>

                <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                    <div class="sm:col-span-4">
                        <Label for="is_color_print">{{ labels.is_color_print }}</Label>
                        <InputSwitch v-model="form.is_color_print" />
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
