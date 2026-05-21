<script setup>
import { useDate } from '@/Composables/useDate';
import FieldRowVertical from '@/Shared/Form/FieldRowVertical.vue';
import Label from '@/Shared/Label.vue';
import { DatePicker, FileUpload } from 'primevue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import { ref } from 'vue';

const props = defineProps({
  labelFiles: {
    type: String,
  },
  labelComment: {
    type: String,
  },
  labelServiceRequestNumber: {
    type: String,
  },
  labelServiceRequestDate: {
    type: String,
  },
  selectedFiles: {
    type: Array,
  },
  textComment: {
    type: String,
  },
  serviceRequestNumber: {
    type: String,
  },
  serviceRequestDate: {
    type: String,
  },
  isNew: {
    type: Boolean,
  },
});

const { formatDate } = useDate();
const commentModel = ref(props.textComment);
const serviceRequestNumberModel = ref(props.serviceRequestNumber);
const serviceRequestDateModel = ref(props.serviceRequestDate);

const filesRef = ref(null);

const emit = defineEmits([
  'update:selectedFiles',
  'update:changeTextComment',
  'update:clearFiles',
  'update:serviceRequestNumber',
  'update:serviceRequestDate',
]);

const onSelectFiles = (files) => {
  emit('update:selectedFiles', files);
};
const onChangeTextComment = (event) => {
  emit('update:changeTextComment', event);
};
const onClearFiles = () => {
  filesRef.value.clear();
  emit('update:clearFiles');
};
const onChangeServiceRequestNumber = (event) => {
  emit('update:serviceRequestNumber', event);
};
const onChangeServiceRequestDate = (date) => {
  emit('update:serviceRequestDate', formatDate(date, 'YYYY-MM-DD'));
};

</script>
<template>
  <div class="w-1/2 grid gap-y-8">
    <FieldRowVertical v-if="isNew">
      <template #field>
        <div class="flex justify-between">
          <FileUpload ref="filesRef" mode="basic" multiple @select="onSelectFiles" />
          <Button
            v-if="selectedFiles?.length && selectedFiles.length > 0"
            icon="pi pi-eraser"
            label="Очистить"
            severity="secondary"
            size="small"
            @click="onClearFiles"
          />
        </div>
        <ul v-if="selectedFiles" class="my-4">
          <li v-for="file in selectedFiles" :key="file.name" class="mt-2">
            <i class="pi pi-file text-gray-400" />
            {{ file.name }}
          </li>
        </ul>
      </template>
    </FieldRowVertical>

    <FieldRowVertical>
      <template #label>
        <Label for="comment">{{ labelComment }}</Label>
      </template>
      <template #field>
        <Textarea v-model="commentModel" rows="5" @change="onChangeTextComment" />
      </template>
    </FieldRowVertical>

    <FieldRowVertical>
      <template #label>
        <Label for="serviceRequestNumber">{{ labelServiceRequestNumber }}</Label>
      </template>
      <template #field>
        <InputText v-model="serviceRequestNumberModel" type="text" @change="onChangeServiceRequestNumber" />
      </template>
    </FieldRowVertical>

    <FieldRowVertical>
      <template #label>
        <Label for="serviceRequestNumber">{{ labelServiceRequestDate }}</Label>
      </template>
      <template #field>
        <DatePicker
          v-model="serviceRequestDateModel"
          class="w-56"
          date-format="dd.mm.yy"
          show-icon
          fluid
          icon-display="input"
          @value-change="onChangeServiceRequestDate"
        />
      </template>
    </FieldRowVertical>
  </div>
</template>
