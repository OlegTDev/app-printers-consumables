<script setup>
import { inject, ref } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
import Layout from '@/Shared/Layout'
import Breadcrumbs from '@/Shared/Breadcrumbs'
import Dropdown from 'primevue/dropdown'
import Label from '@/Shared/Label'
import InlineMessage from 'primevue/inlinemessage'
import InputNumber from 'primevue/inputnumber'
import Checkbox from 'primevue/checkbox'
import Steps from 'primevue/steps'
import Button from 'primevue/button'
import axios from 'axios'
import Message from 'primevue/message'


defineOptions({
    layout: Layout
});
const props = defineProps({
    auth: Object,
    consumables: Array,
    consumableCountLabels: Object,
    availableOrganizations: Array,
});
const urls = inject('urls');
const title = 'Добавление';

const step = ref(0);

const form = useForm({
    id_consumable: null,
    count: null,
    selectedOrganizations: [props.auth.user.org_code],
    changeOrganization: false,
    step: step,
});

const save = () => {
    form.post(urls.consumables.counts.store())
};

const changeOrganization = ref(form.changeOrganization);
const consumableData = ref();
const consumableFind = ref(false);

// поиск документа с таким жа расходным материалом и текущей организацией
const findConsumable = () => {
    consumableFind.value = false
    form.changeOrganization = false

    axios.post(urls.consumables.counts.checkExists(), form.data())
        .then(function(response) {
            if (response.data?.id != null) {
                consumableFind.value = true
                consumableData.value = response.data
            }
        })
        .catch(function (error) {
            console.log(error)
        })
        .finally(() => step.value++)
};

// валидация формы
const validateForm = () => {
    form.post(urls.consumables.counts.validate(), {
        onSuccess: () => {
            if (step.value == 0) {
                // поиск документа с уже выбранным расходным материалом
                findConsumable()
            }
            else {
                step.value++
            }
        }
    })
};

// кнопка "Далее"
const next = () => {
    validateForm()
};

// кнопка "Назад"
const prev = () => {
    step.value--
};

// шаги мастера
const items = ref([
    { label: 'Выбор расходного материала' },
    { label: 'Перечень организаций' },
    { label: 'Количество' },
]);
</script>
<template>

    <Head :title="title" />

    <Breadcrumbs :home="{ label: 'Главная', url: urls.home }" :items="[
        { label: 'Количество расходных материалов', url: urls.consumables.counts.index() },
        { label: title },
    ]" />


    <form @submit.prevent="save">
        <div class="rounded-lg bg-white shadow-sm border border-gray-200">
            <div class="py-4 border-b border-b-gray-200">
                <Steps :model="items" v-model:activeStep="step" />
            </div>
            <div class="p-10">
                <p class="text-2xl">{{ form.changeOrganization.value }}</p>
                <div v-if="step === 0" class="grid grid-cols-none gap-x-6 gap-y-8">
                    <div>
                        <Label for="id_consumable">{{ consumableCountLabels.id_consumable }}</Label>
                        <Dropdown
                            :invalid="form.errors?.id_consumable != null"
                            v-model="form.id_consumable"
                            filter
                            :options="consumables"
                            optionLabel="name"
                            optionValue="id"
                            placeholder="Выберите расходный материал"
                            class="w-full"
                            showClear
                        />
                        <InlineMessage v-if="form.errors?.id_consumable" class="mt-2" severity="error">{{ form.errors?.id_consumable }}</InlineMessage>
                    </div>
                </div>

                <div v-if="step === 1">

                    <div v-if="consumableFind" class="grid grid-cols-none gap-x-6 gap-y-8">
                        <Message severity="info" :closable="false" class="w-full">
                            Найден документ с текущим расходным материалом.<br />
                            Идентификатор документа: <strong>{{ consumableData.id }}</strong>,
                            коды организаций: <strong>{{ consumableData.organizations.join(', ') }}</strong>,
                            текущее количество: <strong>{{ consumableData.count }}</strong>!<br />
                            Если необходимо изменить перечень организаций, то установите галочку "Изменить список организаций" и укажите нужные организации.
                        </Message>
                    </div>

                    <div v-if="consumableFind" class="grid grid-cols-none gap-x-6 gap-y-8 mb-4">
                        <div>
                            <div class="w-full flex gap-x-4" id="organizations">
                                <Checkbox v-model="changeOrganization" :binary="true" inputId="changeOrganization" />
                                <Label for="changeOrganization">
                                    Изменить список организаций
                                </Label>
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="(consumableFind && changeOrganization) || !consumableFind"
                        class="grid grid-cols-none gap-x-6 gap-y-8"
                    >
                        <div>
                            <Label for="organizations">
                                {{ consumableCountLabels.selectedOrganizations }}
                            </Label>
                            <div class="w-full" id="organizations">
                                <div v-for="organization in availableOrganizations" :key="organization.code" class="flex items-center mt-2">
                                    <Checkbox v-model="form.selectedOrganizations" :inputId="organization.code" name="organizations" :value="organization.code" />
                                    <label :for="organization.code" class="ml-2 cursor-pointer">
                                        {{ organization.label }}
                                    </label>
                                </div>
                            </div>
                            <InlineMessage v-if="form.errors?.selectedOrganizations" class="mt-2" severity="error">{{ form.errors?.selectedOrganizations }}</InlineMessage>
                        </div>
                    </div>
                </div>

                <div v-if="step === 2" class="grid grid-cols-none gap-x-6 gap-y-8">
                    <div>
                        <Label for="count">{{ consumableCountLabels.count }}</Label>
                        <InputNumber
                            class="w-full"
                            v-model="form.count"
                            :placeholder="consumableCountLabels.count"
                            :invalid="form.errors?.count?.length > 0"
                        />
                        <InlineMessage v-if="form.errors?.count" class="mt-2" severity="error">{{ form.errors?.count }}</InlineMessage>
                    </div>
                </div>

            </div>

            <div class="flex justify-start gap-x-2 p-5 bg-gray-50 border-t border-gray-100 w-full">
                <Button @click="prev" v-if="step > 0" severity="info" :loading="form.processing" icon="pi pi-arrow-left" label="Назад" />
                <Button @click="next" v-if="step < 2" severity="info" :loading="form.processing" icon="pi pi-arrow-right" label="Далее" iconPos="right" />
                <Button v-if="step === 2" type="submit" :loading="form.processing" icon="pi pi-save" label="Сохранить" />
            </div>

        </div>
    </form>

</template>
