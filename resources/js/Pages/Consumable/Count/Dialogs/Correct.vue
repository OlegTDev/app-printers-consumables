<script setup>
import { useForm } from '@inertiajs/inertia-vue3';
import Button from 'primevue/button';
import InlineMessage from 'primevue/inlinemessage';
import InputNumber from 'primevue/inputnumber';
import { inject } from 'vue';
import Label from '@/Shared/Label';

const urls = inject('urls');
const dialogRef = inject('dialogRef');
const LogActions = inject('LogActions');

const consumableCountId = dialogRef.value.data.consumableCountId;
const consumableCountValue = dialogRef.value.data.consumableCountValue;
const consumableCountLabels = dialogRef.value.data.consumableCountLabels;

const form = useForm({   
    id_consumable: consumableCountId,
    count: consumableCountValue,    
});

const save = () => {        
    const url = urls.consumables.counts.correct(consumableCountId);
    form.post(url, {
        onSuccess: () => {            
            LogActions.save(url, 'POST', 'Корректировка количества расходных материалов', {
                id_consumable: form.id_consumable,
                count: form.count,                
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
        <InputNumber class="w-full" v-model="form.count" :placeholder="consumableCountLabels.count"
          :invalid="form.errors?.count?.length > 0" />
        <InlineMessage v-if="form.errors?.count" class="mt-2" severity="error">{{ form.errors?.count }}</InlineMessage>
      </div>
    </div>
    <div class="flex items-center justify-between pt-5 w-full">
      <Button :loading="form.processing" class="font-bold" type="submit" label="Сохранить" />
    </div>
  </form>
</template>