<script setup>
import { useForm } from '@inertiajs/inertia-vue3';
import { inject } from 'vue';
import Textarea from 'primevue/textarea';
import InlineMessage from 'primevue/inlinemessage';
import Button from 'primevue/button';
import Label from '@/Shared/Label';


const dialogRef = inject('dialogRef');

const { idOrder, message, url, buttonLabel, btnSeverity } = dialogRef.value.data;


const form = useForm({
  id_order: idOrder,
  comment: null,
});

const save = () => {
  form.put(url, {
    onSuccess: () => {
      dialogRef.value.close();
    },
  })
};
</script>
<template>
  <form @submit.prevent="save">
    <div class="grid gap-x-6 gap-y-8">
      <div>
        <Label for="count">{{ message }}</Label>
        <Textarea class="w-full" rows="4" v-model="form.comment" placeholder="введите комментарий"
          :invalid="!!form.errors?.comment" />
        <InlineMessage v-if="form.errors?.comment" class="mt-2" severity="error">{{ form.errors?.comment }}
        </InlineMessage>
      </div>
    </div>
    <div class="flex items-center justify-between pt-5 w-full">
      <Button :loading="form.processing" :severity="btnSeverity ?? null" class="font-bold" type="submit" :label="buttonLabel" />
    </div>
  </form>
</template>
