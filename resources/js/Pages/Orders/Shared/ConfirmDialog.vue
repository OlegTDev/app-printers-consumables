<script setup>
import { useForm } from '@inertiajs/vue3';
import { inject } from 'vue';
import Textarea from 'primevue/textarea';
import Button from 'primevue/button';
import Label from '@/Shared/Label.vue';
import Message from 'primevue/message';

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
        <Textarea
          v-model="form.comment"
          placeholder="введите комментарий"
          class="w-full"
          rows="4"
          :invalid="!!form.errors?.comment"
        />
        <Message v-if="form.errors?.comment" class="mt-2" severity="error">
          {{ form.errors?.comment }}
        </Message>
      </div>
    </div>
    <div class="flex items-center justify-between pt-5 w-full">
      <Button :loading="form.processing" :severity="btnSeverity ?? null" class="font-bold" type="submit" :label="buttonLabel" />
    </div>
  </form>
</template>
