<script setup>
import { useForm } from '@inertiajs/vue3';
import { inject } from 'vue';
import InputNumber from 'primevue/inputnumber';
import InlineMessage from 'primevue/inlinemessage';
import Label from '@/Shared/Label';
import Button from 'primevue/button';

const urls = inject('urls');
const dialogRef = inject('dialogRef');
const consumableCountLabels = dialogRef.value.data.consumableCountLabels;

const form = useForm({
    id_consumable: dialogRef.value.data.idConsumable,
    count: 1,
    selectedOrganizations: [dialogRef.value.data.organizations],
});
const LogActions = inject('LogActions');

const save = () => {
    const url = urls.consumables.counts.update(dialogRef.value.data.id);
    form.put(url, {
        onSuccess: () => {
            LogActions.save(url, 'PUT', 'Добавление количества расходных материалов', {
                id_consumable: form.id_consumable,
                count: form.count,
                selected_organizations: form.selectedOrganizations,
            });

            dialogRef.value.close();
        },
    })
};

</script>

<template>
    <form @submit.prevent="save">
        <div class="grid gap-x-6 gap-y-8">
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
        <div class="flex items-center justify-between pt-5 w-full">
            <Button :loading="form.processing" class="font-bold" type="submit" label="Сохранить" />
        </div>
    </form>
</template>
