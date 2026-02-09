<script setup>
import Label from '@/Shared/Label';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import { ref } from 'vue';

const props = defineProps({
  labelFiles: String,
  labelComment: String,
  labelServiceRequestNumber: String,
  labelServiceRequestDate: String,
  selectedFiles: FileList|Array,
  textComment: String,
  serviceRequestNumber: String,
  serviceRequestDate: String,
  isNew: Boolean,
});

const commentModel = ref(props.textComment);
const serviceRequestNumberModel = ref(props.serviceRequestNumber);
const serviceRequestDateModel = ref(props.serviceRequestDate);

const emit = defineEmits(['update:selectedFiles', 'update:changeTextComment', 'update:clearFiles',
  'update:serviceRequestNumber', 'update:serviceRequestDate']);
const onSelectFiles = (files) => {
  emit('update:selectedFiles', files);
}
const onChangeTextComment = (event) => {
  emit('update:changeTextComment', event);
}
const onClearFiles = () => {
  emit('update:clearFiles');
}
const onChangeServiceRequestNumber = (event) => {
  emit('update:serviceRequestNumber', event);
}
const onChangeServiceRequestDate = (event) => {
  emit('update:serviceRequestDate', event);
}

</script>
<template>
  <div v-if="isNew" class="mb-10">
    <div>
      <Label for="files">{{ labelFiles }}</Label>
      <div class="flex gap-3">
        <input ref="filesRef" type="file" @input="onSelectFiles" multiple class="w-full" />
        <Button v-if="selectedFiles?.length && selectedFiles.length > 0" @click="onClearFiles" size="small">Очистить</Button>
      </div>
    </div>
    <ul v-if="selectedFiles" class="my-4">
      <li v-for="file in selectedFiles" :key="file.name" class="mt-2">
        {{ file.name }}
      </li>
    </ul>
  </div>
  <div>
    <Label for="comment">{{ labelComment }}</Label>
    <Textarea v-model="commentModel" @change="onChangeTextComment" class="w-full" rows="5" />
  </div>
  <div class="mt-10">
    <Label for="serviceRequestNumber">{{ labelServiceRequestNumber }}</Label>
    <InputText type="text" v-model="serviceRequestNumberModel" @change="onChangeServiceRequestNumber" class="w-full" />
  </div>
  <div class="mt-2">
    <Label for="serviceRequestNumber">{{ labelServiceRequestDate }}</Label>
    <InputText type="date" v-model="serviceRequestDateModel" @change="onChangeServiceRequestDate" class="w-full" />
  </div>

</template>
