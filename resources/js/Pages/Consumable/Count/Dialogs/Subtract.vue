<script setup>
import axios from 'axios';
import { useToast } from 'primevue/usetoast';
import { inject, onMounted, reactive, ref, watch } from 'vue';
import Dropdown from 'primevue/dropdown';
import { useForm } from '@inertiajs/vue3';
import Label from '@/Shared/Label';
import InlineMessage from 'primevue/inlinemessage';
import ProgressSpinner from 'primevue/progressspinner';
import Message from 'primevue/message';
import InputNumber from 'primevue/inputnumber';
import IconColorPrint from '@/Shared/IconColorPrint.vue';
import Button from 'primevue/button';

const urls = inject('urls');
const config = inject('config');
const dialogRef = inject('dialogRef');
const toast = reactive(useToast());

const printersWorkplaces = ref();
const selectedPrinter = ref();
const loading = ref(false);
const isEmpty = ref(false);

watch(
    () => selectedPrinter.value,
    (item) => {
        form.id_printer_workplace = item?.id
    }
);

const form = useForm({
    id_consumable_count: dialogRef.value.data.idConsumableCount,
    id_printer_workplace: null,
    count: 1,
});

/**
 * загрузка списка принтеров, привязанных к текущему расходному материалу
 */
onMounted(() => {
    loading.value = true
    axios.get(urls.printers.list(dialogRef.value.data.idConsumable))
        .then((response) => {
            if (Array.isArray(response.data)) {
                printersWorkplaces.value = []
                response.data.forEach(item => {
                    printersWorkplaces.value.push({
                        id: item.id,
                        name: `${item.location} каб. - ${item.vendor} ${item.model} ${item.is_color_print ? 'цветной' : ''} (инвентарный № ${item.inventory_number}, серийный № ${item.serial_number})`,
                        location: item.location,
                        vendor: item.vendor,
                        model: item.model,
                        is_color_print: item.is_color_print,
                        inventory_number: item.inventory_number,
                        serial_number: item.serial_number,
                    })
                })
                if (!printersWorkplaces.value.length) {
                    isEmpty.value = true
                }
            }
            else {
                isEmpty.value = true
            }
        })
        .catch((error) => {
            toast.add({
                severity: 'error',
                summary: 'Ошибка',
                detail: error.message,
                life: config.toast.timeLife,
            })
            console.error(error)
        })
        .finally(() => loading.value = false)
});

const LogActions = inject('LogActions');

const save = () => {
    const idConsumable = dialogRef.value.data.idConsumable;
    const idConsumableCount = dialogRef.value.data.idConsumableCount;
    const url = urls.consumables.counts.subtract(idConsumable, idConsumableCount);
    form.post(url, {
        onSuccess: () => {
            LogActions.save(url, 'POST', 'Вычитание расходных материалов', {
                id_consumable: form.id_consumable,
                count: form.count,
                id_printer_workplace: form.id_printer_workplace,
            });

            dialogRef.value.close();
        },
    })
};

</script>
<template>
    <form @submit.prevent="save">

        <div v-if="loading">
            <ProgressSpinner style="width: 50px; height: 50px" strokeWidth="8" class="fill-surface-0 dark:fill-surface-800"
                animationDuration=".5s" aria-label="Custom ProgressSpinner" />
        </div>

        <div v-else-if="isEmpty">
            <Message severity="warn" :closable="false">
                Нет привязки принтеров к данному расходному материалу и текущей организации, либо нет таких принтеров на рабочих местах!
            </Message>
        </div>

        <div v-else class="grid gap-y-8">
            <div class="grid gap-x-6">
                <Label for="id_printer_workplace">Принтер</Label>
                <Dropdown
                    :invalid="form.errors?.id_printer_workplace != null"
                    v-model="selectedPrinter"
                    filter
                    :options="printersWorkplaces"
                    optionLabel="name"
                    placeholder="Выберите принтер"
                    class="w-full"
                    showClear
                >
                    <template #value="slotProps">
                        <div v-if="slotProps.value" class="gap-y-8">
                            <div class="gap-x-4">
                                <i class="fa-solid fa-location-dot"></i>
                                {{ slotProps.value.location }} каб.
                            </div>
                            <div class="flex gap-x-4">
                                {{ `${slotProps.value.vendor} ${slotProps.value.model}` }}
                                <span v-if="slotProps.value.is_color_print">
                                    <IconColorPrint class="h-4 w-4" />
                                </span>
                            </div>
                            <div class="text-gray-500">
                                инвентарный: {{ slotProps.value.inventory_number }}, серийный: {{ slotProps.value.serial_number }}
                            </div>
                        </div>
                        <span v-else>
                            {{ slotProps.placeholder }}
                        </span>
                    </template>
                    <template #option="slotProps">
                        <div class="gap-y-8">
                            <div class="gap-x-4">
                                <i class="fa-solid fa-location-dot"></i>
                                {{ slotProps.option.location }} каб.
                            </div>
                            <div class="flex gap-x-4">
                                {{ `${slotProps.option.vendor} ${slotProps.option.model}` }}
                                <span v-if="slotProps.option.is_color_print">
                                    <IconColorPrint class="h-4 w-4" />
                                </span>
                            </div>
                            <div class="text-gray-500">
                                инвентарный: {{ slotProps.option.inventory_number }}, серийный: {{ slotProps.option.serial_number }}
                            </div>
                        </div>
                    </template>
                </Dropdown>
                <div>
                    <InlineMessage v-if="form.errors?.id_printer_workplace" class="mt-2 justify-start" severity="error">
                        {{ form.errors?.id_printer_workplace }}
                    </InlineMessage>
                </div>
            </div>
            <div class="grid grid-cols-none gap-x-6">
                <Label for="count">Количество</Label>
                <InputNumber
                    class="w-full"
                    v-model="form.count"
                    placeholder="Количество"
                    :invalid="form.errors?.count?.length > 0"
                />
                <div>
                    <InlineMessage v-if="form.errors?.count" class="mt-2" severity="error">{{ form.errors?.count }}</InlineMessage>
                </div>
            </div>
            <div>
                <Button type="submit" :loading="form.processing" icon="pi pi-save" label="Сохранить" />
            </div>
        </div>

    </form>
</template>
