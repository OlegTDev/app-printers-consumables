<script setup>
import { useForm } from '@inertiajs/vue3';
import { computed, inject } from 'vue';
import InputNumber from 'primevue/inputnumber';
import Label from '@/Shared/Label.vue';
import Button from 'primevue/button';
import FieldRowVertical from '@/Shared/Form/FieldRowVertical.vue';
import { Message } from 'primevue';

const dialogRef = inject('dialogRef');
const dialogData = computed(() => dialogRef.value?.data || {});
const labels = dialogData.value.consumableCountLabels;

const form = useForm({
  count: dialogData.value.count || 1,
});

const save = () => {
  const url = route('consumables.counts.update', { count: dialogData.value.id });
  form.put(url, {
    onSuccess: () => dialogRef.value.close(),
  });
};
</script>

<template>
  <form @submit.prevent="save">
    <div class="grid gap-x-6 gap-y-8">
      <FieldRowVertical>
        <template #label>
          <Label for="count">{{ labels.count }}</Label>
        </template>
        <template #field>
          <InputNumber
            v-model="form.count"
            :placeholder="labels.count"
            :invalid="!!form.errors?.count"
            :min="1"
            show-buttons
            button-layout="horizontal"
            input-id="count"
            input-class="text-center w-24"
          >
            <template #incrementicon>
              <i class="pi pi-plus" />
            </template>
            <template #decrementicon>
              <span class="pi pi-minus" />
            </template>
          </InputNumber>
        </template>
        <template #message>
          <Message
            v-if="form.errors.count"
            class="mt-2"
            severity="error"
          >
            {{ form.errors.count }}
          </Message>
        </template>
      </FieldRowVertical>
      <div class="flex justify-end">
        <Button
          :loading="form.processing"
          class="font-bold"
          type="submit"
          label="Сохранить"
        />
      </div>
    </div>
  </form>
</template>
