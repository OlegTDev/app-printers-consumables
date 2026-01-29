<script setup>
import Label from '@/Shared/Label';
import { useForm } from '@inertiajs/inertia-vue3';
import axios from 'axios';
import Dropdown from 'primevue/dropdown';
import InlineMessage from 'primevue/inlinemessage';
import { useToast } from 'primevue/usetoast';
import { inject, onMounted, reactive, ref } from 'vue';
import IconColorPrint from '@/Shared/IconColorPrint';
import InputSwitch from 'primevue/inputswitch';
import Button from 'primevue/button';
import TextArea from 'primevue/textarea';
import Panel from 'primevue/panel';
import { useConfirm } from 'primevue/useconfirm';
import { Inertia } from '@inertiajs/inertia';
import ProgressBar from 'primevue/progressbar';


const props = defineProps({
  isNew: Boolean,
  spareParts: Array,
  labels: Object,
  orderSparePart: Object,
});
const urls = inject('urls');
const config = inject('config');
const confirm = useConfirm();

const loadingPrinters = ref(false);
const printersWorkplacesData = ref();
const printersWorkplacesIsEmpty = ref(false);
const printersWorkplacesSelected = ref();
const uploadFilesRef = ref();

const form = useForm({
  id: props.orderSparePart?.id,
  id_printers_workplace: props.orderSparePart?.id_printers_workplace,
  id_spare_part: props.orderSparePart?.id_spare_part,
  call_specialist: props.orderSparePart?.call_specialist ?? false,
  comment: props.orderSparePart?.comment,
  files: [],
  is_new: props.isNew,
});
const formUploadFile = useForm({
  files: [],
});
const toast = reactive(useToast());

onMounted(() => {
  loadingPrinters.value = true;
  axios.get(urls.printers.all())
    .then((response) => {
      if (Array.isArray(response.data)) {
        printersWorkplacesData.value = [];
        response.data.forEach(item => {
          const printer = {
            id: item.id,
            id_printer: item.printer.id,
            location: item.location,
            vendor: item.printer.vendor,
            model: item.printer.model,
            is_color_print: item.printer.is_color_print,
            inventory_number: item.inventory_number,
            serial_number: item.serial_number,
            label: `${item.location} ${item.printer.vendor} ${item.printer.model} ${item.inventory_number} ${item.serial_number}`,
          };

          printersWorkplacesData.value.push(printer);
          if (!props.isNew && item.id == form.id_printers_workplace) {
            printersWorkplacesSelected.value = printer;
          }

        })
        if (!printersWorkplacesData.value.length) {
          printersWorkplacesIsEmpty.value = true
        }
        else {
          printersWorkplacesIsEmpty.value = false
        }
      }
      else {
        printersWorkplacesIsEmpty.value = true
      }
    })
    .catch((error) => {
      toast.add({
        severity: 'error',
        summary: 'Ошибка',
        detail: error.message,
        life: config.toast.timeLife,
      })
      console.error(error);
    })
    .finally(() => loadingPrinters.value = false);
});

const onChangePrinterWorkplace = (event) => {
  if (event.value?.id) {
    form.id_printers_workplace = event.value.id;
    form.errors.id_printers_workplace = null;
  } else {
    form.id_printers_workplace = null;
  }
};


const save = () => {
  if (props.isNew) {
    form.post(urls.orders.spareParts.store());
  } else {
    form.put(urls.orders.spareParts.update(form.id));
  }
};

const uploadFiles = () => {
  formUploadFile.post(urls.orders.spareParts.uploadFile(props.orderSparePart.id), {
    onFinish: () => {
      formUploadFile.files = [];
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
      const url = urls.orders.spareParts.deleteFile(props.orderSparePart.id, idFile);
      Inertia.delete(url, {
        preserveScroll: true,
      });
    },
  });
};

</script>
<template>

  <form @submit.prevent="save" class="w-full">
    <div class="p-10">
      <div class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
        <div class="sm:col-span-4">
          <Label for="id_printers_workplace">{{ labels.id_printers_workplace }}</Label>
          <Dropdown :invalid="form.errors?.id_printers_workplace != null" v-model="printersWorkplacesSelected" filter
            :options="printersWorkplacesData" optionLabel="label" placeholder="Выберите принтер" class="w-full"
            @change="onChangePrinterWorkplace" :loading="loadingPrinters">
            <template #value="slotProps">
              <div v-if="slotProps.value" class="grid gap-y-2">
                <div class="flex gap-x-2">
                  <i class="fa-solid fa-location-dot"></i>
                  {{ slotProps.value.location }} каб.
                </div>
                <div class="flex gap-x-2">
                  {{ `${slotProps.value.vendor} ${slotProps.value.model}` }}
                  <span v-if="slotProps.value.is_color_print">
                    <IconColorPrint class="h-4 w-4" />
                  </span>
                </div>
                <div class="text-gray-500">
                  инвентарный: {{ slotProps.value.inventory_number }}, серийный: {{
                    slotProps.value.serial_number }}
                </div>
              </div>
              <span v-else>
                {{ slotProps.placeholder }}
              </span>
            </template>
            <template #option="slotProps">
              <div class="grid gap-y-2">
                <div class="flex gap-x-2">
                  <i class="fa-solid fa-location-dot"></i>
                  {{ slotProps.option.location }} каб.
                </div>
                <div class="flex gap-x-2">
                  {{ `${slotProps.option.vendor} ${slotProps.option.model}` }}
                  <span v-if="slotProps.option.is_color_print">
                    <IconColorPrint class="h-4 w-4" />
                  </span>
                </div>
                <div class="text-gray-500">
                  инвентарный: {{ slotProps.option.inventory_number }}, серийный: {{
                    slotProps.option.serial_number }}
                </div>
              </div>
            </template>
          </Dropdown>
          <div>
            <InlineMessage v-if="form.errors?.id_printers_workplace" class="mt-2" severity="error">
              {{ form.errors?.id_printers_workplace }}</InlineMessage>
          </div>
        </div>
      </div>

      <template v-if="!loadingPrinters">

        <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6" v-if="!loadingPrinters">
          <div class="sm:col-span-4">
            <Label for="call_specialist">{{ labels.call_specialist }}</Label>
            <InputSwitch v-model="form.call_specialist" />
            <InlineMessage v-if="form.errors?.call_specialist" class="mt-2" severity="error">
              {{ form.errors?.call_specialist }}</InlineMessage>
          </div>
        </div>

        <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6" v-if="!form.call_specialist">
          <div class="sm:col-span-4">
            <Label for="id_spare_part">{{ labels.id_spare_part }}</Label>
            <Dropdown v-model="form.id_spare_part" :options="spareParts" optionLabel="name" optionValue="id"
              placeholder="Выберите запчасть" class="w-full" />
            <InlineMessage v-if="form.errors?.id_spare_part" class="mt-2" severity="error">
              {{ form.errors?.id_spare_part }}</InlineMessage>
          </div>
        </div>

        <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
          <div class="sm:col-span-4">
            <Label for="comment">{{ labels.order.comment }}</Label>
            <TextArea v-model="form.comment" class="w-full" rows="5" />
            <InlineMessage v-if="form.errors?.comment" class="mt-2" severity="error">
              {{ form.errors?.comment }}</InlineMessage>
          </div>
        </div>

        <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
          <div class="sm:col-span-4">
            <Label for="files">{{ labels.files }}</Label>
            <div v-if="form.is_new">
              <input type="file" @input="form.files = $event.target.files" multiple class="w-full" />
            </div>

            <Panel header="Загруженные файлы" v-if="orderSparePart?.files" class="mt-2">
              <table>
                <tr v-for="item in orderSparePart.files" :key="item.id">
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
              <div v-if="!form.is_new" class="mt-2">
                <div class="flex gap-3">
                  <input ref="uploadFilesRef" type="file" @input="formUploadFile.files = $event.target.files" multiple class="w-full" />
                  <Button v-if="formUploadFile.files.length > 0" @click="uploadFiles" size="small">Загрузить</Button>
                </div>
                <ProgressBar v-if="formUploadFile.progress" :value="formUploadFile.progress?.percentage || 0">
                </ProgressBar>
              </div>
            </Panel>

            <InlineMessage v-if="form.errors?.files" class="mt-2" severity="error">
              {{ form.errors?.files }}</InlineMessage>
          </div>
        </div>

      </template>

      <div v-if="form.progress" class="w-full bg-gray-100 rounded-full mt-4">
        <div
          class="bg-primary-500 text-xs font-medium text-white text-center p-0.5 leading-none rounded-full h-4 flex items-center justify-center"
          :style="{ width: (form.progress?.percentage ?? 0) + '%' }">
          {{ form.progress?.percentage ?? 0 }}%
        </div>
      </div>

    </div>

    <div v-if="!loadingPrinters"
      class="flex items-center justify-between p-5 bg-gray-50 border-t border-gray-100 w-full">
      <Button :loading="form.processing" class="font-bold" type="submit" :label="isNew ? 'Заказать' : 'Сохранить'" />
    </div>

  </form>

</template>