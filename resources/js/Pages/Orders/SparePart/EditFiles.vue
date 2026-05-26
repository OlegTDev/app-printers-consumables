<script setup>
import Layout from '@/Shared/Layout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import Breadcrumbs from '@/Shared/Breadcrumbs.vue';
import { computed, ref } from 'vue';
import Label from '@/Shared/Label.vue';
import Button from 'primevue/button';
import { useConfirm } from 'primevue/useconfirm';
import Card from '@/Shared/Card.vue';
import Title from '@/Shared/Title.vue';
import { FileUpload } from 'primevue';
import FieldRowVertical from '@/Shared/Form/FieldRowVertical.vue';
import { useDate } from '@/Composables/useDate';

defineOptions({
  layout: Layout,
});

const props = defineProps({
  orderSparePartDetail: Object,
  labels: Object,
});

const { formatDate } = useDate();
const title = 'Изменение файлов';

const form = useForm({
  files: [],
});
const confirm = useConfirm();

const orderSparePartDetailData = computed(() => props.orderSparePartDetail?.data || {});
const uploadFilesRef = ref({});

const uploadFiles = () => {
  form.post(route('orders.spare-parts.files.upload', { orderSparePartDetails: orderSparePartDetailData.value.id }), {
    forceFormData: true,
    onFinish: () => {
      form.reset('files');
      uploadFilesRef.value.clear();
    },
    preserveScroll: true,
  });
};

const deleteFile = (idFile) => {
  confirm.require({
    message: 'Вы уверены, что хотите удалить файл?',
    header: 'Удаление файла',
    accept: () => {
      const url = route('orders.spare-parts.files.delete', {
        orderSparePartDetails: orderSparePartDetailData.value.id,
        orderSparePartDetailsFile: idFile,
      });
      router.delete(url, {
        preserveScroll: true,
      });
    },
  });
};

const home = () => {
  router.get(route('orders.spare-parts.show', { orderSparePartDetails: orderSparePartDetailData.value.id }));
};

const select = (event) => {
  form.files = event.files;
};
</script>
<template>
  <Head :title="title" />

  <Breadcrumbs
    :home="{ label: 'Главная', url: route('home') }"
    :items="[
      { label: 'Заказ запчастей', url: route('orders.spare-parts.index') },
      {
        label: `Заказ № ${orderSparePartDetailData.order.id} от ${formatDate(orderSparePartDetailData.order.created_at, 'L')}`,
        url: route('orders.spare-parts.show', { orderSparePartDetails: orderSparePartDetailData.id }),
      },
      { label: title },
    ]"
  />

  <Card>
    <Title>{{ title }}</Title>

    <div class="w-1/2">
      <FieldRowVertical>
        <template #label>
          <Label for="files">{{ labels.files }}</Label>
        </template>
        <template #field>
          <div class="flex justify-between">
            <FileUpload ref="uploadFilesRef" name="files[]" mode="basic" multiple @select="select" />
            <Button
              v-if="form.files.length > 0"
              icon="pi pi-upload"
              label="Загрузить"
              severity="secondary"
              size="small"
              @click="uploadFiles"
            />
          </div>
          <div v-if="form.progress" class="w-full bg-gray-100 rounded-full mt-4">
            <div
              class="bg-primary-500 text-xs font-medium text-white text-center p-0.5 leading-none rounded-full h-4 flex items-center justify-center"
              :style="{ width: (form.progress?.percentage ?? 0) + '%' }"
            >
              {{ form.progress?.percentage ?? 0 }}%
            </div>
          </div>
        </template>
      </FieldRowVertical>
      <Card v-if="orderSparePartDetailData.files?.length > 0" class="mt-6" padding-body-classes="p-4">
        <Title :h="3">
          Загруженные файлы
        </Title>

        <div class="grid gap-y-2">
          <template v-for="item in orderSparePartDetailData.files" :key="item.id">
            <div class="flex gap-3 items-center">
              <Button
                v-tooltip="`Удалить`"
                severity="danger"
                rounded
                variant="outlined"
                size="small"
                icon="pi pi-times"
                @click="deleteFile(item.id)"
              />
              <div class="flex gap-2">
                <i class="pi pi-file text-gray-400" />
                <a :href="item.url_file_download" target="_blank">{{ item.basename }}</a>
              </div>
            </div>
          </template>
        </div>
      </Card>
    </div>

    <template #footer>
      <Button icon="pi pi-id-card" label="Вернуться" @click="home" />
    </template>
  </Card>
</template>
