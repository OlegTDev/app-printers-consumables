<script setup>
import { inject, onMounted, reactive, ref, watch } from 'vue';
import Button from 'primevue/button';
import Dropdown from 'primevue/dropdown';
import { useForm, router } from '@inertiajs/vue3';
import Label from '@/Shared/Label';
import InlineMessage from 'primevue/inlinemessage';
import axios from 'axios';
import IconColorPrint from '@/Shared/IconColorPrint'
import { useToast } from 'primevue/usetoast';
import InputNumber from 'primevue/inputnumber';
import Message from 'primevue/message';

const urls = inject('urls');
const config = inject('config');
const dialogRef = inject('dialogRef');
const step = ref(0);

const loadingPrinters = ref(false);
const loadingConsumables = ref(false);

const printersWorkplacesData = ref();
const printersWorkplacesIsEmpty = ref(false);
const printersWorkplacesSelected = ref();

const consumableData = ref();
const consumableIsEmpty = ref(false);
const consumableSelected = ref();
let consumableTypes = null;
let cartridgeColors = null;

const form = useForm({
    id_printer_workplace: null,
    id_consumable_count: null,
    count: 1,
    step: step,
})
const toast = reactive(useToast());

onMounted(() => {
    loadingPrinters.value = true;
    axios.get(urls.printers.all())
        .then((response) => {
            if (Array.isArray(response.data)) {
                printersWorkplacesData.value = []
                response.data.forEach(item => {
                    printersWorkplacesData.value.push({
                        id: item.id,
                        id_printer: item.printer.id,
                        location: item.location,
                        vendor: item.printer.vendor,
                        model: item.printer.model,
                        is_color_print: item.printer.is_color_print,
                        inventory_number: item.inventory_number,
                        serial_number: item.serial_number,
                        label: `${item.location} ${item.printer.vendor} ${item.printer.model} ${item.inventory_number} ${item.serial_number}`,
                    })
                })
                if (!printersWorkplacesData.value.length) {
                    printersWorkplacesIsEmpty.value = true
                }
                else {
                    printersWorkplacesIsEmpty.value = false
                }
            }
            else {
                printersWorkplacesIsEmpty.value = true
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
        .finally(() => loadingPrinters.value = false)
})

const loadConsumableByPrinterId = (idPrinter) => {
    loadingConsumables.value = true
    axios.get(urls.consumables.counts.listByPrinter(idPrinter))
        .then((response) => {
            consumableTypes = response.data.consumableTypes
            cartridgeColors = response.data.cartridgeColors
            if (Array.isArray(response.data.data)) {
                consumableData.value = []
                response.data.data.forEach(item => {
                    item.label = `${consumableTypes[item.type]} ${item.name} ${item.color} ${cartridgeColors[item.color]?.name ?? null} ${item.serial_number}`
                    item.isDisabled = item.count <= 0
                    consumableData.value.push(item)
                })
                if (!consumableData.value.length) {
                    consumableIsEmpty.value = true
                }
                else {
                    consumableIsEmpty.value = false
                }
            }
            else {
                consumableIsEmpty.value = true
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
        .finally(() => loadingConsumables.value = false)
}

const onChangePrinterWorkplace = (event) => {
    if (event.value?.id) {
        form.errors.id_printer_workplace = null
        consumableIsEmpty.value = false
        loadConsumableByPrinterId(printersWorkplacesSelected.value.id_printer)
    }
}

const LogActions = inject('LogActions');

const save = () => {
    const idConsumable = consumableSelected.value.id_consumable;
    const idConsumableCount = consumableSelected.value.id;
    const url = urls.consumables.counts.subtract(idConsumable, idConsumableCount);
    form.post(url, {
        onSuccess: () => {
            // статистика посещения
            LogActions.save('POST', 'Вычитание расходного материала', {
                idConsumable: idConsumable,
                idConsumableCount: idConsumableCount,
            });

            dialogRef.value.close();
            router.get(window.location.href);
        },
    })
}

watch(
    () => printersWorkplacesSelected.value,
    (item) => {
        form.id_printer_workplace = item?.id
    }
)
watch(
    () => consumableSelected.value,
    (item) => {
        form.id_consumable_count = item?.id
    }
)

</script>
<template>
    <form @submit.prevent="save">
        <div class="grid gap-y-5">
            <div class="grid grid-cols-none gap-x-6">
                <Label for="id_printer_workplace">Выберите принтер</Label>
                <Dropdown
                    :invalid="form.errors?.id_printer_workplace != null"
                    v-model="printersWorkplacesSelected"
                    filter
                    :options="printersWorkplacesData"
                    optionLabel="label"
                    placeholder="Выберите принтер"
                    class="w-full"
                    @change="onChangePrinterWorkplace"
                    :loading="loadingPrinters"
                >
                    <template #value="slotProps">
                        <div v-if="slotProps.value" class="grid gap-y-2">
                            <div class="flex gap-x-2">
                                <i class="fa-solid fa-location-dot"></i>
                                {{ slotProps.value.location }} каб.
                            </div>
                            <div class="flex gap-x-2">
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
                        <div class="grid gap-y-2">
                            <div class="flex gap-x-2">
                                <i class="fa-solid fa-location-dot"></i>
                                {{ slotProps.option.location }} каб.
                            </div>
                            <div class="flex gap-x-2">
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
                    <InlineMessage v-if="form.errors?.id_printer_workplace" class="mt-2" severity="error">{{ form.errors?.id_printer_workplace }}</InlineMessage>
                </div>
            </div>

            <div v-if="printersWorkplacesSelected" class="grid gap-y-5">
                <div class="grid grid-cols-none gap-x-6 gap-y-8">
                    <div v-if="consumableIsEmpty">
                        <Message severity="warn" :closable="false" class="w-full">
                            Нет расходных материалов
                        </Message>
                    </div>
                    <div v-else>
                        <Label for="id_consumable_count">Выберите расходный материал</Label>
                        <Dropdown
                            :invalid="form.errors?.id_consumable_count != null"
                            v-model="consumableSelected"
                            filter
                            :options="consumableData"
                            optionLabel="label"
                            placeholder="Выберите расходный материал"
                            class="w-full"
                            optionDisabled="isDisabled"
                            :loading="loadingConsumables"
                        >
                            <template #value="slotProps">
                                <div v-if="slotProps.value" class="grid gap-y-2">
                                    <div class="grid gap-x-1">
                                        {{ consumableTypes[slotProps.value.type] }}
                                        {{ slotProps.value.name }}
                                    </div>
                                    <div v-if="slotProps.value.type == 'cartridge'">
                                        <div class="flex">
                                            <div :class="['rounded-full', 'size-4', 'mr-2', cartridgeColors[slotProps.value.color]['bg']]"></div>
                                            <div>
                                                {{ cartridgeColors[slotProps.value.color]['name'] }}
                                            </div>
                                        </div>
                                    </div>
                                    <div v-if="slotProps.value.count > 0" class="text-gray-400">
                                        Доступно: {{ slotProps.value.count }}
                                    </div>
                                    <div v-else class="text-red-600">
                                        Отсутствует
                                    </div>
                                </div>
                                <span v-else>
                                    {{ slotProps.placeholder }}
                                </span>
                            </template>
                            <template #option="slotProps">
                                <div class="grid gap-y-2">
                                    <div class="grid gap-x-1">
                                        {{ consumableTypes[slotProps.option.type] }}
                                        {{ slotProps.option.name }}
                                    </div>
                                    <div v-if="slotProps.option.type == 'cartridge'">
                                        <div class="flex">
                                            <div :class="['rounded-full', 'size-4', 'mr-2', cartridgeColors[slotProps.option.color]['bg']]"></div>
                                            <div>
                                                {{ cartridgeColors[slotProps.option.color]['name'] }}
                                            </div>
                                        </div>
                                    </div>
                                    <div v-if="slotProps.option.count > 0" class="text-gray-400">
                                        Доступно: {{ slotProps.option.count }}
                                    </div>
                                    <div v-else class="text-red-600">
                                        Отсутствует
                                    </div>
                                </div>
                            </template>
                        </Dropdown>
                        <div>
                            <InlineMessage v-if="form.errors?.id_consumable_count" class="mt-2" severity="error">{{ form.errors?.id_consumable_count }}</InlineMessage>
                        </div>
                    </div>
                </div>
                <div v-if="!consumableIsEmpty" class="grid grid-cols-none gap-x-6 gap-y-8">
                    <div>
                        <Label for="count">Количество</Label>
                        <InputNumber
                            class="w-full"
                            v-model="form.count"
                            placeholder="Количество"
                            :invalid="form.errors?.count?.length > 0"
                        />
                        <InlineMessage v-if="form.errors?.count" class="mt-2" severity="error">{{ form.errors?.count }}</InlineMessage>
                    </div>
                </div>
            </div>

            <div v-if="consumableSelected && !consumableIsEmpty" class1="flex justify-start gap-x-2 p-5 bg-gray-50 border-t border-gray-100 w-full mt-4">
                <Button type="submit" :loading="form.processing" icon="pi pi-save" label="Сохранить" />
            </div>

        </div>

    </form>
</template>
