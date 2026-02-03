<script setup>
import Layout from '@/Shared/Layout';
import { Head } from '@inertiajs/vue3';
import Breadcrumbs from '@/Shared/Breadcrumbs';
import { computed, inject, ref } from 'vue';
import Label from '@/Shared/Label.vue';
import Panel from 'primevue/panel';
import { useForm } from '@inertiajs/inertia-vue3';
import Button from 'primevue/button';
import { Inertia } from '@inertiajs/inertia';
import { useConfirm } from 'primevue/useconfirm';

defineOptions({
  layout: Layout,
});

const props = defineProps({
  orderSparePartDetail: Object,
  labels: Object,
});

const urls = inject('urls');
const moment = inject('moment');
const title = 'Изменение файлов заказа';

const form = useForm({
  files: [],
});
const confirm = useConfirm();

const orderSparePartDetailData = computed(() => props.orderSparePartDetail?.data || {});
const uploadFilesRef = ref();

const uploadFiles = () => {
  form.post(urls.orders.spareParts.uploadFile(orderSparePartDetailData.value.id), {
    onFinish: () => {
      form.files = [];
      uploadFilesRef.value.value = '';
    },
    preserveScroll: true,
  });
}

const deleteFile = (idFile) => {
  confirm.require({
    message: 'Вы уверены, что хотите удалить файл?',
    header: 'Отмена заказа',
    accept: () => {
      const url = urls.orders.spareParts.deleteFile(orderSparePartDetailData.value.id, idFile);
      Inertia.delete(url, {
        preserveScroll: true,
      });
    },
  });
};

const home = () => {
  Inertia.get(urls.orders.spareParts.show(orderSparePartDetailData.value.id));
}

</script>
<template>

  <Head :title="title" />

  <Breadcrumbs :home="{ label: 'Главная', url: '/' }" :items="[
    { label: 'Заказ запчастей', url: urls.orders.spareParts.index() },
    {
      label: `Заказ № ${orderSparePartDetailData.order.id} от ${moment(orderSparePartDetailData.order.created_at).format('L')}`,
      url: urls.orders.spareParts.show(orderSparePartDetailData.order.id),
    },
    { label: title },
  ]" />

  <div class="flex flex-col justify-stretch bg-white rounded-md shadow overflow-hidden mt-4">

    <div class="p-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
      <div class="sm:col-span-4">
        <Label for="files">{{ labels.files }}</Label>
        <div v-if="!form.is_new" class="mt-2">
          <div class="flex gap-3">
            <input ref="uploadFilesRef" type="file" @input="form.files = $event.target.files" multiple class="w-full" />
            <Button v-if="form.files.length > 0" icon="pi pi-upload" @click="uploadFiles" size="small"
              label="Загрузить" />
          </div>
          <div v-if="form.progress" class="w-full bg-gray-100 rounded-full mt-4">
            <div
              class="bg-primary-500 text-xs font-medium text-white text-center p-0.5 leading-none rounded-full h-4 flex items-center justify-center"
              :style="{ width: (form.progress?.percentage ?? 0) + '%' }">
              {{ form.progress?.percentage ?? 0 }}%
            </div>
          </div>
        </div>

        <Panel header="Загруженные файлы" class="mt-2">
          <table>
            <tr v-for="item in orderSparePartDetailData.files" :key="item.id">
              <td class="w-8">
                <i class="far fa-file"></i>
              </td>
              <td>
                <a :href="item.url_file_download" target="_blank">{{ item.basename }}</a>
              </td>
              <td>
                <Button severity="danger" text size="small" @click="deleteFile(item.id)">
                  <i class="fas fa-times me-2"></i>
                  Удалить
                </Button>
              </td>
            </tr>
          </table>
        </Panel>
      </div>
    </div>

    <div class="p-5 bg-gray-50 border-t border-gray-100 w-full">
      <div class="flex justify-between w-full">
        <div>
          <Button @click="home" icon="pi pi-id-card" label="Вернуться" />
        </div>
      </div>
    </div>

  </div>


</template>
